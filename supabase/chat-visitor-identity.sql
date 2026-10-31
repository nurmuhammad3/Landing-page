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
