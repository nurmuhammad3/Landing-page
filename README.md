# Nur Muhammad — Digital Marketing Portfolio

This website is rendered by the Vercel PHP community runtime. `api/index.php` routes the website and API requests. The private contact lead inbox is available at `/dashboard`.

## Connect Supabase

1. Create a Supabase project and open its SQL Editor.
2. Run [`supabase/schema.sql`](supabase/schema.sql).
3. In Supabase Authentication, create an email/password user for the site owner. Disable public sign-ups in the Auth settings.
4. Copy that user's UUID from Authentication → Users, then run the `insert into public.dashboard_admins ...` statement at the bottom of `supabase/schema.sql`, replacing the placeholder UUID.
5. In Vercel → Project Settings → Environment Variables, add:
   - `SUPABASE_URL`: the project URL from Supabase Project Settings → API.
   - `SUPABASE_PUBLISHABLE_KEY`: the project's publishable key. A legacy anon key may be used instead under `SUPABASE_ANON_KEY`.
6. Redeploy the Vercel project. Submit a consented test inquiry through the contact form, then sign in at `/dashboard` with the owner account.

The website only exposes the public publishable/anon key. Never put the Supabase `service_role` or secret key in Vercel client-visible variables, website source, or browser code. Row Level Security allows anonymous visitors to insert consented inquiries; only UUIDs added to `dashboard_admins` can read inquiries or change their status.

The contact form stores name, email and/or phone, message, and consent in Supabase. The dashboard shows those inquiries and allows status changes; it does not connect to Meta Ads or Google Ads accounts.
