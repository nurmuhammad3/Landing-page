# Nur Muhammad — Digital Marketing Portfolio

This website is rendered by the Vercel PHP community runtime. `api/index.php` routes the website and API requests. The private contact lead inbox is available at `/dashboard`.

## Connect Supabase

1. Create a Supabase project and open its SQL Editor.
2. Run [`supabase/schema.sql`](supabase/schema.sql).
3. Run [`supabase/site-chat.sql`](supabase/site-chat.sql) to create the visitor chat tables and RPC functions.
4. Run [`supabase/chat-visitor-identity.sql`](supabase/chat-visitor-identity.sql) to add chat identity fields and require a verified Google identity for visitor messages. It preserves existing conversations and messages.
5. Configure Google sign-in in Supabase Authentication → Sign In / Providers. Create a Google OAuth web client, add the website as an authorized JavaScript origin, and add the callback URL shown by Supabase as an authorized redirect URI. Store the client ID and secret only in Supabase's Google provider settings; never in website code.
6. In Supabase Authentication → URL Configuration, set the site URL to `https://landing-page-nur-811a.vercel.app` and allow `https://landing-page-nur-811a.vercel.app/**` as a redirect URL. Enable new user sign-ups so visitors can sign in with Google. RLS still limits lead and chat inbox access to IDs in `dashboard_admins`.
7. In Supabase Authentication, create an email/password user for the site owner.
8. Copy that user's UUID from Authentication → Users, then run the `insert into public.dashboard_admins ...` statement at the bottom of `supabase/schema.sql`, replacing the placeholder UUID.
9. In Vercel → Project Settings → Environment Variables, add:
   - `SUPABASE_URL`: the project URL from Supabase Project Settings → API.
   - `SUPABASE_PUBLISHABLE_KEY`: the project's publishable key. A legacy anon key may be used instead under `SUPABASE_ANON_KEY`.
10. Redeploy the Vercel project after adding or changing environment variables. Submit a consented test inquiry through the contact form, then sign in at `/dashboard` with the owner account.

The website only exposes the public publishable/anon key. Never put the Supabase `service_role` or secret key in Vercel client-visible variables, website source, or browser code. Row Level Security allows anonymous visitors to insert consented inquiries; only UUIDs added to `dashboard_admins` can read inquiries or change their status.

The contact form stores name, email and/or phone, message, and consent in Supabase. The live chat requires Google sign-in; it stores the email and profile name returned by Google's linked identity, after the visitor consents. The database derives these values from the verified Google identity instead of trusting browser-supplied fields. A Google profile name is account profile information, not proof of legal identity. The private dashboard inbox shows these details with each conversation. The public contact email is `contactnurdm@gmail.com`. The dashboard shows inquiries, allows status changes, and contains the in-site customer chat inbox; it does not connect to Meta Ads or Google Ads accounts.
