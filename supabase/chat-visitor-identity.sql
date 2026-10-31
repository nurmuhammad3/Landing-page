-- Add visitor names and emails to existing website chat conversations.
-- Safe to run more than once; existing messages and conversations are preserved.

alter table public.site_chat_conversations
  add column if not exists visitor_name text,
  add column if not exists visitor_email text;

create or replace function public.create_site_chat_with_identity(
  p_visitor_token text,
  p_message text,
  p_visitor_name text,
  p_visitor_email text
)
returns table (conversation_id uuid, message_id uuid, created_at timestamptz)
language plpgsql
security definer
set search_path = ''
as $$
declare
  v_conversation_id uuid;
  v_message_id uuid;
  v_created_at timestamptz;
  v_email text := lower(btrim(p_visitor_email));
begin
  if p_visitor_token is null or p_visitor_token !~ '^[0-9a-fA-F-]{36}$' then
    raise exception 'invalid_visitor_token' using errcode = '22023';
  end if;
  if p_message is null or char_length(btrim(p_message)) not between 1 and 2000 then
    raise exception 'invalid_message' using errcode = '22023';
  end if;
  if p_visitor_name is null or char_length(btrim(p_visitor_name)) not between 1 and 120 then
    raise exception 'invalid_visitor_name' using errcode = '22023';
  end if;
  if v_email is null or char_length(v_email) > 254
     or length(v_email) - length(replace(v_email, '@', '')) <> 1
     or position('@' in v_email) < 2
     or position('.' in split_part(v_email, '@', 2)) < 2 then
    raise exception 'invalid_visitor_email' using errcode = '22023';
  end if;

  insert into public.site_chat_conversations (visitor_token_hash, visitor_name, visitor_email)
  values (md5(lower(p_visitor_token)), btrim(p_visitor_name), v_email)
  returning id into v_conversation_id;

  insert into public.site_chat_messages as inserted (conversation_id, sender, body)
  values (v_conversation_id, 'visitor', btrim(p_message))
  returning inserted.id, inserted.created_at into v_message_id, v_created_at;

  return query select v_conversation_id, v_message_id, v_created_at;
end;
$$;

create or replace function public.update_site_chat_identity(
  p_conversation_id uuid,
  p_visitor_token text,
  p_visitor_name text,
  p_visitor_email text
)
returns void
language plpgsql
security definer
set search_path = ''
as $$
declare
  v_email text := lower(btrim(p_visitor_email));
begin
  if p_visitor_token is null or p_visitor_token !~ '^[0-9a-fA-F-]{36}$' then
    raise exception 'invalid_visitor_token' using errcode = '22023';
  end if;
  if p_visitor_name is null or char_length(btrim(p_visitor_name)) not between 1 and 120 then
    raise exception 'invalid_visitor_name' using errcode = '22023';
  end if;
  if v_email is null or char_length(v_email) > 254
     or length(v_email) - length(replace(v_email, '@', '')) <> 1
     or position('@' in v_email) < 2
     or position('.' in split_part(v_email, '@', 2)) < 2 then
    raise exception 'invalid_visitor_email' using errcode = '22023';
  end if;
  update public.site_chat_conversations
    set visitor_name = btrim(p_visitor_name), visitor_email = v_email
    where id = p_conversation_id
      and visitor_token_hash = md5(lower(p_visitor_token));
  if not found then
    raise exception 'conversation_not_found' using errcode = '42501';
  end if;
end;
$$;

revoke all on function public.create_site_chat_with_identity(text, text, text, text) from public, anon, authenticated;
revoke all on function public.update_site_chat_identity(uuid, text, text, text) from public, anon, authenticated;
grant execute on function public.create_site_chat_with_identity(text, text, text, text) to anon, authenticated;
grant execute on function public.update_site_chat_identity(uuid, text, text, text) to anon, authenticated;

-- Google OAuth verification: derive visitor identity from the signed-in
-- Google identity instead of trusting editable form values from the browser.
alter table public.site_chat_conversations
  add column if not exists visitor_auth_user_id uuid references auth.users(id) on delete set null;

create or replace function public.google_chat_profile()
returns table (visitor_name text, visitor_email text)
language plpgsql
stable
security definer
set search_path = ''
as $$
declare
  v_user_id uuid := auth.uid();
  v_email text;
  v_email_confirmed_at timestamptz;
  v_identity_data jsonb;
  v_name text;
begin
  if v_user_id is null then
    raise exception 'google_sign_in_required' using errcode = '42501';
  end if;

  select users.email, users.email_confirmed_at
    into v_email, v_email_confirmed_at
    from auth.users as users
    where users.id = v_user_id
      and users.raw_app_meta_data ->> 'provider' = 'google';
  if not found or v_email_confirmed_at is null or v_email is null then
    raise exception 'verified_google_email_required' using errcode = '42501';
  end if;

  select identities.identity_data
    into v_identity_data
    from auth.identities as identities
    where identities.user_id = v_user_id
      and identities.provider = 'google'
    order by identities.created_at desc
    limit 1;
  if not found then
    raise exception 'google_profile_required' using errcode = '42501';
  end if;

  v_name := coalesce(
    nullif(btrim(v_identity_data ->> 'full_name'), ''),
    nullif(btrim(v_identity_data ->> 'name'), ''),
    nullif(btrim(concat_ws(' ', v_identity_data ->> 'given_name', v_identity_data ->> 'family_name')), '')
  );
  if v_name is null then
    raise exception 'google_profile_name_required' using errcode = '42501';
  end if;

  return query select left(v_name, 120), lower(v_email);
end;
$$;

create or replace function public.create_site_chat_with_identity(
  p_visitor_token text,
  p_message text,
  p_visitor_name text,
  p_visitor_email text
)
returns table (conversation_id uuid, message_id uuid, created_at timestamptz)
language plpgsql
security definer
set search_path = ''
as $$
declare
  v_conversation_id uuid;
  v_message_id uuid;
  v_created_at timestamptz;
  v_name text;
  v_email text;
begin
  select profile.visitor_name, profile.visitor_email
    into v_name, v_email
    from public.google_chat_profile() as profile;
  if p_visitor_token is null or p_visitor_token !~ '^[0-9a-fA-F-]{36}$' then
    raise exception 'invalid_visitor_token' using errcode = '22023';
  end if;
  if p_message is null or char_length(btrim(p_message)) not between 1 and 2000 then
    raise exception 'invalid_message' using errcode = '22023';
  end if;

  insert into public.site_chat_conversations (visitor_token_hash, visitor_name, visitor_email, visitor_auth_user_id)
  values (md5(lower(p_visitor_token)), v_name, v_email, auth.uid())
  returning id into v_conversation_id;

  insert into public.site_chat_messages as inserted (conversation_id, sender, body)
  values (v_conversation_id, 'visitor', btrim(p_message))
  returning inserted.id, inserted.created_at into v_message_id, v_created_at;

  return query select v_conversation_id, v_message_id, v_created_at;
end;
$$;

create or replace function public.update_site_chat_identity(
  p_conversation_id uuid,
  p_visitor_token text,
  p_visitor_name text,
  p_visitor_email text
)
returns void
language plpgsql
security definer
set search_path = ''
as $$
declare
  v_name text;
  v_email text;
begin
  select profile.visitor_name, profile.visitor_email
    into v_name, v_email
    from public.google_chat_profile() as profile;
  if p_visitor_token is null or p_visitor_token !~ '^[0-9a-fA-F-]{36}$' then
    raise exception 'invalid_visitor_token' using errcode = '22023';
  end if;

  update public.site_chat_conversations
    set visitor_name = v_name, visitor_email = v_email
    where id = p_conversation_id
      and visitor_token_hash = md5(lower(p_visitor_token))
      and visitor_auth_user_id = auth.uid();
  if not found then
    raise exception 'conversation_not_found' using errcode = '42501';
  end if;
end;
$$;

create or replace function public.send_site_chat_message(
  p_conversation_id uuid,
  p_visitor_token text,
  p_message text
)
returns uuid
language plpgsql
security definer
set search_path = ''
as $$
declare
  v_message_id uuid;
  v_profile_name text;
  v_profile_email text;
begin
  select profile.visitor_name, profile.visitor_email
    into v_profile_name, v_profile_email
    from public.google_chat_profile() as profile;
  if p_visitor_token is null or p_visitor_token !~ '^[0-9a-fA-F-]{36}$' then
    raise exception 'invalid_visitor_token' using errcode = '22023';
  end if;
  if p_message is null or char_length(btrim(p_message)) not between 1 and 2000 then
    raise exception 'invalid_message' using errcode = '22023';
  end if;
  if not exists (
    select 1 from public.site_chat_conversations as conversations
    where conversations.id = p_conversation_id
      and conversations.visitor_token_hash = md5(lower(p_visitor_token))
      and conversations.visitor_auth_user_id = auth.uid()
  ) then
    raise exception 'conversation_not_found' using errcode = '42501';
  end if;
  if (select count(*) from public.site_chat_messages as messages
      where messages.conversation_id = p_conversation_id
        and messages.sender = 'visitor'
        and messages.created_at > now() - interval '1 minute') >= 20 then
    raise exception 'rate_limit_exceeded' using errcode = '54000';
  end if;

  update public.site_chat_conversations
    set visitor_name = v_profile_name, visitor_email = v_profile_email
    where id = p_conversation_id;
  insert into public.site_chat_messages (conversation_id, sender, body)
  values (p_conversation_id, 'visitor', btrim(p_message))
  returning id into v_message_id;
  update public.site_chat_conversations
    set last_message_at = now()
    where id = p_conversation_id;
  return v_message_id;
end;
$$;

create or replace function public.get_site_chat_messages(
  p_conversation_id uuid,
  p_visitor_token text
)
returns table (id uuid, sender text, body text, created_at timestamptz)
language plpgsql
security definer
set search_path = ''
as $$
begin
  perform public.google_chat_profile();
  if p_visitor_token is null or p_visitor_token !~ '^[0-9a-fA-F-]{36}$'
     or not exists (
       select 1 from public.site_chat_conversations as conversations
       where conversations.id = p_conversation_id
         and conversations.visitor_token_hash = md5(lower(p_visitor_token))
         and conversations.visitor_auth_user_id = auth.uid()
     ) then
    raise exception 'conversation_not_found' using errcode = '42501';
  end if;

  return query
    select recent.id, recent.sender, recent.body, recent.created_at
    from (
      select messages.id, messages.sender, messages.body, messages.created_at
      from public.site_chat_messages as messages
      where messages.conversation_id = p_conversation_id
      order by messages.created_at desc, messages.id desc
      limit 100
    ) as recent
    order by recent.created_at asc, recent.id asc;
end;
$$;

revoke all on function public.google_chat_profile() from public, anon, authenticated;
revoke all on function public.create_site_chat(text, text) from public, anon, authenticated;
revoke all on function public.create_site_chat_with_identity(text, text, text, text) from public, anon, authenticated;
revoke all on function public.update_site_chat_identity(uuid, text, text, text) from public, anon, authenticated;
revoke all on function public.send_site_chat_message(uuid, text, text) from public, anon, authenticated;
grant execute on function public.create_site_chat_with_identity(text, text, text, text) to authenticated;
grant execute on function public.update_site_chat_identity(uuid, text, text, text) to authenticated;
grant execute on function public.send_site_chat_message(uuid, text, text) to authenticated;
revoke all on function public.get_site_chat_messages(uuid, text) from public, anon, authenticated;
grant execute on function public.get_site_chat_messages(uuid, text) to authenticated;
