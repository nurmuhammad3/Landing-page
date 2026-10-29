-- Website lead inbox for Nur Muhammad's portfolio.
-- Run this in Supabase SQL Editor after creating a project.

create table if not exists public.website_leads (
  id uuid primary key default gen_random_uuid(),
  name text not null check (char_length(btrim(name)) between 1 and 120),
  email text,
  phone text,
  message text not null check (char_length(btrim(message)) between 10 and 3000),
  privacy_consent boolean not null default false check (privacy_consent),
  source text not null default 'website' check (source = 'website'),
  status text not null default 'new' check (status in ('new', 'contacted', 'closed')),
  created_at timestamptz not null default now(),
  constraint website_leads_has_contact_method check (
    nullif(btrim(email), '') is not null or nullif(btrim(phone), '') is not null
  )
);

create index if not exists website_leads_created_at_idx
  on public.website_leads (created_at desc);

create table if not exists public.dashboard_admins (
  user_id uuid primary key references auth.users(id) on delete cascade,
  created_at timestamptz not null default now()
);

alter table public.website_leads enable row level security;
alter table public.dashboard_admins enable row level security;

revoke all on public.website_leads from anon, authenticated;
revoke all on public.dashboard_admins from anon, authenticated;
grant insert on public.website_leads to anon;
grant select, update (status) on public.website_leads to authenticated;
grant select on public.dashboard_admins to authenticated;

drop policy if exists "Public can submit consented leads" on public.website_leads;
create policy "Public can submit consented leads"
  on public.website_leads
  for insert
  to anon
  with check (
    privacy_consent = true
    and char_length(btrim(name)) between 1 and 120
    and char_length(btrim(message)) between 10 and 3000
    and (nullif(btrim(email), '') is not null or nullif(btrim(phone), '') is not null)
  );

drop policy if exists "Admins can see their allowlist entry" on public.dashboard_admins;
create policy "Admins can see their allowlist entry"
  on public.dashboard_admins
  for select
  to authenticated
  using (user_id = (select auth.uid()));

drop policy if exists "Admins can read website leads" on public.website_leads;
create policy "Admins can read website leads"
  on public.website_leads
  for select
  to authenticated
  using (
    exists (
      select 1
      from public.dashboard_admins as admins
      where admins.user_id = (select auth.uid())
    )
  );

drop policy if exists "Admins can update lead status" on public.website_leads;
create policy "Admins can update lead status"
  on public.website_leads
  for update
  to authenticated
  using (
    exists (
      select 1
      from public.dashboard_admins as admins
      where admins.user_id = (select auth.uid())
    )
  )
  with check (
    exists (
      select 1
      from public.dashboard_admins as admins
      where admins.user_id = (select auth.uid())
    )
  );

-- After creating your email/password user in Authentication > Users,
-- add that user's UUID here to authorize the private dashboard:
-- insert into public.dashboard_admins (user_id) values ('YOUR_AUTH_USER_UUID');
