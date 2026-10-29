<?php
$config = $GLOBALS['SUPABASE_PUBLIC_CONFIG'] ?? ['url' => '', 'anonKey' => ''];
$safeConfig = json_encode($config, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
?>
<!doctype html>
<html lang="bn">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="robots" content="noindex,nofollow"><meta name="theme-color" content="#171344">
  <title>Private lead dashboard | Nur Muhammad</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root{font-family:"Hind Siliguri","DM Sans",sans-serif;color:#17152f;background:#f7f6fc;font-synthesis:none}*{box-sizing:border-box}body{margin:0}.wrap{width:min(1060px,calc(100% - 32px));margin:42px auto}.top{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:26px}.brand{font:700 18px "DM Sans",sans-serif}.brand small{display:block;font:500 11px "DM Sans",sans-serif;letter-spacing:1px;color:#77738d;margin-top:4px}.top a{color:#6741d9;text-decoration:none}.card{background:#fff;border:1px solid #e7e4f0;border-radius:14px;padding:25px;box-shadow:0 8px 24px #34236f0a}.intro h1{font-size:32px;margin:0 0 5px}.muted{color:#716f83}.notice{padding:14px 16px;border-radius:9px;background:#f0ecff;color:#403276;margin:20px 0}.login{max-width:440px;margin:70px auto}.login h1{margin:0 0 8px}.field{display:grid;gap:6px;margin:14px 0}.field input{font:inherit;padding:11px;border:1px solid #dcd8e8;border-radius:8px}.button{border:0;border-radius:8px;padding:11px 16px;background:#6741d9;color:white;font:600 14px inherit;cursor:pointer}.button:disabled{opacity:.6;cursor:wait}.button.secondary{background:#f0ecff;color:#49348a}.error{color:#a23145;min-height:1.5em}.stats{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin:18px 0}.stat strong{font:700 25px "DM Sans",sans-serif;display:block}.stat span{color:#706d82;font-size:14px}.toolbar{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:14px}.leads{display:grid;gap:12px}.lead{padding:18px;border:1px solid #ece9f3;border-radius:10px}.lead-head{display:flex;justify-content:space-between;gap:14px;align-items:flex-start}.lead h2{margin:0;font-size:18px}.lead p{white-space:pre-wrap;margin:12px 0;color:#49465f}.lead-meta{font-size:13px;color:#716f83}.lead-meta a{color:#6741d9}.lead select{font:inherit;border:1px solid #dcd8e8;border-radius:7px;padding:7px;background:white}.empty{text-align:center;padding:40px;color:#716f83}.hidden{display:none!important}#setup{white-space:pre-wrap}
    @media(max-width:620px){.wrap{margin:22px auto}.card{padding:18px}.top{align-items:flex-start}.stats{grid-template-columns:1fr}.toolbar{align-items:flex-start;flex-direction:column}.lead-head{flex-direction:column}}
  </style>
</head>
<body>
  <main class="wrap">
    <header class="top"><a href="/" class="brand">Nur Muhammad<small>PRIVATE LEAD INBOX</small></a><a href="/">মূল ওয়েবসাইটে ফিরুন ↗</a></header>
    <section id="setup" class="notice hidden"></section>
    <section id="login" class="card login hidden">
      <h1>Sign in</h1><p class="muted">অনুমোদিত অ্যাকাউন্ট দিয়ে ব্যক্তিগত inquiry দেখুন।</p>
      <form id="loginForm"><label class="field">ইমেইল<input name="email" type="email" autocomplete="username" required></label><label class="field">পাসওয়ার্ড<input name="password" type="password" autocomplete="current-password" required></label><button class="button" type="submit">Sign in</button></form><p class="error" id="loginError" aria-live="polite"></p>
    </section>
    <section id="inbox" class="hidden">
      <header class="intro"><h1>Contact inquiries</h1><p class="muted">ওয়েবসাইটের যোগাযোগ ফর্ম থেকে পাওয়া বার্তা।</p></header>
      <div class="stats"><article class="card stat"><strong id="totalCount">0</strong><span>মোট inquiry</span></article><article class="card stat"><strong id="newCount">0</strong><span>নতুন</span></article><article class="card stat"><strong id="activeCount">0</strong><span>যোগাযোগ চলছে</span></article></div>
      <div class="toolbar"><strong>সব inquiry</strong><button class="button secondary" id="signOut" type="button">Sign out</button></div>
      <p class="error" id="inboxError" aria-live="polite"></p><div id="leadList" class="leads"></div>
    </section>
  </main>
  <script>window.SUPABASE_CONFIG = <?= $safeConfig ?: '{}' ?>;</script>
  <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
  <script>
    (async()=>{
      const config=window.SUPABASE_CONFIG||{};
      const setup=document.getElementById('setup'),login=document.getElementById('login'),inbox=document.getElementById('inbox');
      if(!config.url||!config.anonKey||!window.supabase){setup.classList.remove('hidden');setup.textContent='Supabase এখনও configure করা হয়নি। Project URL এবং publishable/anon key Vercel environment variables-এ যোগ করে redeploy করুন।';return}
      const client=window.supabase.createClient(config.url,config.anonKey);
      const esc=value=>String(value??'').replace(/[&<>"']/g,char=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
      async function loadLeads(){
        document.getElementById('inboxError').textContent='';
        const {data,error}=await client.from('website_leads').select('id,name,email,phone,message,status,created_at').order('created_at',{ascending:false});
        if(error){document.getElementById('inboxError').textContent='ডেটা লোড করা যায়নি। আপনার dashboard access allowlist-এ আছে কি না যাচাই করুন.';return}
        document.getElementById('totalCount').textContent=data.length;
        document.getElementById('newCount').textContent=data.filter(lead=>lead.status==='new').length;
        document.getElementById('activeCount').textContent=data.filter(lead=>lead.status==='contacted').length;
        const list=document.getElementById('leadList');
        if(!data.length){list.innerHTML='<div class="card empty">এখনো কোনো inquiry আসেনি।</div>';return}
        list.innerHTML=data.map(lead=>{const date=new Date(lead.created_at).toLocaleString('bn-BD');const contact=[lead.email?`<a href="mailto:${esc(lead.email)}">${esc(lead.email)}</a>`:'',lead.phone?`<a href="tel:${esc(lead.phone)}">${esc(lead.phone)}</a>`:''].filter(Boolean).join(' · ');return `<article class="card lead"><div class="lead-head"><div><h2>${esc(lead.name)}</h2><div class="lead-meta">${contact||'যোগাযোগের তথ্য নেই'} · ${esc(date)}</div></div><select aria-label="Inquiry status" data-id="${esc(lead.id)}"><option value="new" ${lead.status==='new'?'selected':''}>নতুন</option><option value="contacted" ${lead.status==='contacted'?'selected':''}>যোগাযোগ করা হয়েছে</option><option value="closed" ${lead.status==='closed'?'selected':''}>বন্ধ</option></select></div><p>${esc(lead.message)}</p></article>`}).join('');
        list.querySelectorAll('select[data-id]').forEach(select=>select.addEventListener('change',async()=>{select.disabled=true;const {error}=await client.from('website_leads').update({status:select.value}).eq('id',select.dataset.id);select.disabled=false;if(error){document.getElementById('inboxError').textContent='Status update failed. Check dashboard access.';await loadLeads()}else await loadLeads()}));
      }
      async function showSession(session){if(session){login.classList.add('hidden');inbox.classList.remove('hidden');await loadLeads()}else{inbox.classList.add('hidden');login.classList.remove('hidden')}}
      const {data:{session}}=await client.auth.getSession();await showSession(session);
      client.auth.onAuthStateChange((_event,nextSession)=>{setTimeout(()=>showSession(nextSession),0)});
      document.getElementById('loginForm').addEventListener('submit',async event=>{event.preventDefault();const form=new FormData(event.currentTarget);const errorNode=document.getElementById('loginError');errorNode.textContent='';const button=event.currentTarget.querySelector('button');button.disabled=true;const {error}=await client.auth.signInWithPassword({email:form.get('email'),password:form.get('password')});button.disabled=false;if(error)errorNode.textContent='Sign-in হয়নি। ইমেইল/পাসওয়ার্ড যাচাই করুন; signup ও allowlist setup-ও নিশ্চিত করুন.'});
      document.getElementById('signOut').addEventListener('click',()=>client.auth.signOut());
    })();
  </script>
</body>
</html>
