-- Private, visitor-token-protected website chat for Nur Muhammad's portfolio.
-- Run in Supabase SQL Editor. Visitors can only use the narrowly scoped RPCs;
-- only allowlisted dashboard admins can read the underlying tables or reply.

create table if not exists public.site_chat_conversations (
  id uuid primary key default gen_random_uuid(),
  visitor_token_hash text not null,
  created_at timestamptz not null default now(),
  last_message_at timestamptz not null default now(),
  constraint site_chat_visitor_token_hash_length check (char_length(visitor_token_hash) = 32)
);

create table if not exists public.site_chat_messages (
  id uuid primary key default gen_random_uuid(),
  conversation_id uuid not null references public.site_chat_conversations(id) on delete cascade,
  sender text not null check (sender in ('visitor', 'admin')),
  body text not null check (char_length(btrim(body)) between 1 and 2000),
  created_at timestamptz not null default now()
);

create index if not exists site_chat_conversations_last_message_idx
  on public.site_chat_conversations (last_message_at desc);
create index if not exists site_chat_messages_conversation_created_idx
  on public.site_chat_messages (conversation_id, created_at asc);

alter table public.site_chat_conversations enable row level security;
alter table public.site_chat_messages enable row level security;

revoke all on public.site_chat_conversations from anon, authenticated;
revoke all on public.site_chat_messages from anon, authenticated;
grant select on public.site_chat_conversations, public.site_chat_messages to authenticated;

create policy "Admins can read site chat conversations"
  on public.site_chat_conversations for select to authenticated
  using (exists (
    select 1 from public.dashboard_admins as admins
    where admins.user_id = (select auth.uid())
  ));

create policy "Admins can read site chat messages"
  on public.site_chat_messages for select to authenticated
  using (exists (
    select 1 from public.dashboard_admins as admins
    where admins.user_id = (select auth.uid())
  ));

create or replace function public.create_site_chat(
  p_visitor_token text,
  p_message text
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
begin
  if p_visitor_token is null or p_visitor_token !~ '^[0-9a-fA-F-]{36}$' then
    raise exception 'invalid_visitor_token' using errcode = '22023';
  end if;
  if p_message is null or char_length(btrim(p_message)) not between 1 and 2000 then
    raise exception 'invalid_message' using errcode = '22023';
  end if;

  insert into public.site_chat_conversations (visitor_token_hash)
  values (md5(lower(p_visitor_token)))
  returning id into v_conversation_id;

  insert into public.site_chat_messages as inserted (conversation_id, sender, body)
  values (v_conversation_id, 'visitor', btrim(p_message))
  returning inserted.id, inserted.created_at into v_message_id, v_created_at;

  return query select v_conversation_id, v_message_id, v_created_at;
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
begin
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
  ) then
    raise exception 'conversation_not_found' using errcode = '42501';
  end if;
  if (select count(*) from public.site_chat_messages as messages
      where messages.conversation_id = p_conversation_id
        and messages.sender = 'visitor'
        and messages.created_at > now() - interval '1 minute') >= 20 then
    raise exception 'rate_limit_exceeded' using errcode = '54000';
  end if;

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
  if p_visitor_token is null or p_visitor_token !~ '^[0-9a-fA-F-]{36}$'
     or not exists (
       select 1 from public.site_chat_conversations as conversations
       where conversations.id = p_conversation_id
         and conversations.visitor_token_hash = md5(lower(p_visitor_token))
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

create or replace function public.admin_send_site_chat_message(
  p_conversation_id uuid,
  p_message text
)
returns uuid
language plpgsql
security definer
set search_path = ''
as $$
declare
  v_message_id uuid;
begin
  if auth.uid() is null or not exists (
    select 1 from public.dashboard_admins as admins
    where admins.user_id = auth.uid()
  ) then
    raise exception 'not_authorized' using errcode = '42501';
  end if;
  if p_message is null or char_length(btrim(p_message)) not between 1 and 2000 then
    raise exception 'invalid_message' using errcode = '22023';
  end if;
  if not exists (
    select 1 from public.site_chat_conversations as conversations
    where conversations.id = p_conversation_id
  ) then
    raise exception 'conversation_not_found' using errcode = 'P0002';
  end if;

  insert into public.site_chat_messages (conversation_id, sender, body)
  values (p_conversation_id, 'admin', btrim(p_message))
  returning id into v_message_id;
  update public.site_chat_conversations
    set last_message_at = now()
    where id = p_conversation_id;
  return v_message_id;
end;
$$;

revoke all on function public.create_site_chat(text, text) from public, anon, authenticated;
revoke all on function public.send_site_chat_message(uuid, text, text) from public, anon, authenticated;
revoke all on function public.get_site_chat_messages(uuid, text) from public, anon, authenticated;
revoke all on function public.admin_send_site_chat_message(uuid, text) from public, anon, authenticated;
grant execute on function public.create_site_chat(text, text) to anon, authenticated;
grant execute on function public.send_site_chat_message(uuid, text, text) to anon, authenticated;
grant execute on function public.get_site_chat_messages(uuid, text) to anon, authenticated;
grant execute on function public.admin_send_site_chat_message(uuid, text) to authenticated;
