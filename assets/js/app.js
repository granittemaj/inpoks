/* INPO website app: hash router, page renderers and UI behaviour.
   No build step, no dependencies. Content lives in data.js. */
(function(){
'use strict';

const { NEWS, CATS, PUBS, PTYPES, PFILTERS, PROGS, DONORS } = window.INPO_DATA;

/* ---------- Helpers ---------- */
const $ = (s, r=document) => r.querySelector(s);
const $$ = (s, r=document) => Array.from(r.querySelectorAll(s));
const norm = s => String(s).normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase();
const ARROW = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7"/><path d="M8 7h9v9"/></svg>';
const DL = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v11"/><path d="m7 10 5 5 5-5"/><path d="M5 20h14"/></svg>';
const rings = (size, n, cls='') => { const c=size/2, step=(c-8)/n; let s=''; for(let i=1;i<=n;i++) s+=`<circle cx="${c}" cy="${c}" r="${Math.round(step*i+ (i===1?step*.4:0))}"/>`; return `<svg class="rings ${cls}" width="${size}" height="${size}" viewBox="0 0 ${size} ${size}" aria-hidden="true">${s}</svg>`; };
const ICO = {
  qeverisje:'<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="5" r="2.5"/><circle cx="5" cy="18" r="2.5"/><circle cx="12" cy="18" r="2.5"/><circle cx="19" cy="18" r="2.5"/><path d="M12 7.5v8"/><path d="M12 11c-4 0-7 1.5-7 4.5"/><path d="M12 11c4 0 7 1.5 7 4.5"/></svg>',
  edukim:'<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m2 9 10-5 10 5-10 5-10-5Z"/><path d="M6 11v5c0 1.5 2.7 3 6 3s6-1.5 6-3v-5"/><path d="M22 9v6"/></svg>'
};
const DOTS_ON = [2,7,10,15];

function cover(p, i){
  const v = ['rings','bars','dots','word'][i % 4];
  const top = `<div class="cover-top"><span class="pill">${PTYPES[p.type]}</span>${p.year ? `<span class="cover-year">${p.year}</span>` : ''}</div>`;
  const foot = `<div class="cover-foot"><span class="cover-mark">INPO</span><span class="dl">PDF ${DL}</span></div>`;
  let art = '';
  if (v==='rings') art = rings(260,3);
  if (v==='bars') art = '<div class="bars" aria-hidden="true"><i style="height:40%"></i><i style="height:66%"></i><i style="height:52%"></i><i style="height:100%"></i></div>';
  if (v==='dots') art = '<div class="dots" aria-hidden="true">' + Array.from({length:18},(_,k)=>`<i${DOTS_ON.includes(k)?' class="on"':''}></i>`).join('') + '</div>';
  if (v==='word') art = `<span class="word" aria-hidden="true">${p.short}</span>`;
  // A document cover: decorative, so hidden from screen readers (the title is in the card or the h1).
  return `<div class="cover cv-${v}" aria-hidden="true">${art}${top}<span class="cover-title">${p.title}</span>${foot}</div>`;
}
const pubCard = (p,i) => `<div class="pub"><a class="pub-cover" href="#/publikimet/${p.id}" tabindex="-1" aria-hidden="true">${cover(p,i)}</a><a class="pub-title" href="#/publikimet/${p.id}">${p.title}</a></div>`;
const newsRow = n => `<a class="row" href="#/lajme/${n.id}"><div class="row-meta"><time datetime="${n.iso}">${n.date}</time><span>${CATS[n.cat]}</span></div><span class="row-title">${n.title}</span><span class="arrow-circle">${ARROW}</span></a>`;
const donorsHTML = () => `<div class="donors">${DONORS.map(d=>`<div class="donor">${d}</div>`).join('')}</div><p class="fineprint">Ky grant është financuar nga projekti Promovimi i Shoqërisë Demokratike (DSP), i financuar nga Zyra Zvicerane për Bashkëpunim në Kosovë (SCO-K) dhe Ministria e Punëve të Jashtme e Danimarkës (DANIDA) dhe menaxhuar nga Fondacioni Kosovar për Shoqëri Civile (KCSF).</p>`;
const pageHead = (kicker, title, lead, crumbs) => `<section class="wrap grid12 page-head"><div class="ph-l">${crumbs||`<span class="kicker">${kicker}</span>`}<h1 class="h-page" data-focus>${title}</h1></div><div class="ph-r">${lead?`<p class="lead">${lead}</p>`:''}</div></section>`;

/* ---------- Pages ---------- */
function home(){
  const latestPubs = [PUBS[0],PUBS[1],PUBS[2],PUBS[3]];
  return `
  <section class="wrap grid12 hero">
    <span class="badge"><span class="dot"></span>Organizatë joqeveritare në Ferizaj dhe Prishtinë, që nga 2005</span>
    <h1 class="h-display" data-focus><span class="line"><span>Llogaridhënie,</span></span><span class="line accent"><span>jo premtime.</span></span></h1>
    <div class="hero-side">
      <p class="lead">Monitorojmë prokurimin publik dhe vendimet e institucioneve në Kosovë, publikojmë çfarë gjejmë dhe kërkojmë që paraja publike të shpenzohet me ligj.</p>
      <div class="hero-ctas"><a class="btn btn-accent" href="#/publikimet">Shfleto publikimet</a><a class="btn btn-line" href="#/rreth-nesh">Rreth INPO-s</a></div>
    </div>
  </section>
  <section class="wrap" aria-label="Rastet që po ndjekim">
    <div class="bento">
      <a class="feat" href="#/lajme/pastrimi">
        ${rings(620,4)}
        <div class="feat-top"><span class="pill">Reagim publik</span><time datetime="2025-06-18">18.06.2025</time></div>
        <div class="feat-bottom"><span class="feat-title">${NEWS[0].short}</span><span class="go">${ARROW}</span></div>
      </a>
      <a class="stat light" href="#/lajme/mkrs-125">
        <div class="stat-top"><span>Prokurimi i hapur, MKRS</span><time datetime="2025-05-12">12.05.2025</time></div>
        <div class="stat-body"><span class="num" data-count="125">125</span><p>ditë pa vendim për tenderin 56 milionësh të stadiumeve. Pa transparencë, pa përgjegjësi.</p></div>
      </a>
      <a class="stat dark" href="#/lajme/vushtrri">
        <div class="stat-top"><span>Reagim publik, Vushtrri</span><time datetime="2025-04-28">28.04.2025</time></div>
        <div class="stat-body"><span class="num" data-count="4.98" data-prefix="€" data-suffix="M" data-dec="2">€4,98M</span><p>Kërkojmë anulimin e tenderit për kritere diskriminuese.</p></div>
      </a>
    </div>
  </section>
  <section class="wrap sec">
    <div class="grid12 sec-head"><span class="kicker">Programet</span><h2 class="h2">Dy programe, një qëllim: institucione që u përgjigjen qytetarëve.</h2></div>
    <div class="progs">${['qeverisje','edukim'].map(k=>`
      <a class="prog" href="#/programet/${k}">
        <span class="prog-ico">${ICO[k]}</span>
        <div><h3>${PROGS[k].name}</h3><p>${PROGS[k].desc}</p><span class="link-u">Zbulo programin ${ARROW}</span></div>
      </a>`).join('')}
    </div>
  </section>
  <section class="wrap sec">
    <div class="pub-head">
      <div style="display:flex;flex-direction:column;gap:20px"><span class="kicker">Publikimet</span><h2 class="h2">Të dhëna, jo opinione.</h2></div>
      <div class="chips">${PFILTERS.slice(0,5).map(([k,l],i)=>`<a class="chip${i===0?' on':''}" href="#/publikimet${k==='all'?'':'?lloji='+k}">${l}</a>`).join('')}</div>
    </div>
    <div class="pubs">${latestPubs.map(pubCard).join('')}</div>
  </section>
  <section class="wrap sec grid12 news-sec">
    <div class="news-intro">
      <span class="kicker">Lajme</span>
      <h2 class="h2">Lajme dhe reagime</h2>
      <p class="lead" style="font-size:17px">Qëndrimet tona publike për tenderët, vendimet dhe institucionet që monitorojmë.</p>
      <a class="btn btn-line btn-sm" href="#/lajme">Të gjitha lajmet</a>
    </div>
    <div class="news-list">${[NEWS[1],NEWS[3],NEWS[5]].map(newsRow).join('')}</div>
  </section>
  <section class="wrap sec"><span class="kicker">Donatorët dhe partnerët</span>${donorsHTML()}</section>`;
}

function newsPage(q){
  const f = q.kategoria && CATS[q.kategoria] ? q.kategoria : 'all';
  const list = NEWS.filter(n => f==='all' || n.cat===f);
  const chips = [['all','Të gjitha'],['reagim','Reagim publik'],['prokurimi','Prokurimi i hapur']];
  return pageHead('Lajme','Lajme dhe reagime','Qëndrimet tona publike për tenderët, vendimet dhe institucionet që monitorojmë.') + `
  <section class="wrap">
    <div class="toolbar">
      <div class="chips" role="group" aria-label="Filtro sipas kategorisë">${chips.map(([k,l])=>`<a class="chip${k===f?' on':''}" href="#/lajme${k==='all'?'':'?kategoria='+k}" ${k===f?'aria-current="true"':''}>${l}</a>`).join('')}</div>
      <span class="muted" style="font-size:14px">${list.length} artikuj</span>
    </div>
    <div class="news-grid">${list.map((n,i)=>`
      <a class="ncard${i===0?' big':''}" href="#/lajme/${n.id}">
        ${i===0?rings(560,4):''}
        <div class="ncard-meta"><span class="pill">${CATS[n.cat]}</span><time datetime="${n.iso}">${n.date}</time></div>
        <div><h2>${n.title}</h2><p>${n.excerpt}</p></div>
      </a>`).join('')}
    </div>
  </section>`;
}

function articlePage(id){
  const n = NEWS.find(x=>x.id===id); if(!n) return notFound();
  const words = n.body.join(' ').split(/\s+/).length + 180;
  const mins = Math.max(2, Math.round(words/200));
  const related = NEWS.filter(x=>x.id!==id).slice(0,3);
  const pub = n.pub ? PUBS.find(p=>p.id===n.pub) : null;
  return `
  <article class="wrap article">
    <div class="art-head">
      <nav class="crumbs" aria-label="Gjurma"><a href="#/lajme">Lajme</a><span aria-hidden="true">/</span><a href="#/lajme?kategoria=${n.cat}">${CATS[n.cat]}</a></nav>
      <h1 data-focus>${n.title}</h1>
      <div class="art-meta"><span class="pill">${CATS[n.cat]}</span><time datetime="${n.iso}">${n.date}</time><span>${mins} min lexim</span></div>
    </div>
    <div class="grid12 art-body">
      <div class="prose">
        <p class="lede">${n.excerpt}</p>
        ${n.body.map(p=>`<p>${p}</p>`).join('')}
        ${n.todo?'<p class="todo">Teksti i plotë i reagimit, dokumentet dhe fotografitë importohen nga faqja aktuale gjatë migrimit.</p>':''}
      </div>
      <aside class="aside">
        <div class="box"><h2>Shpërndaje</h2><div class="share">
          <button class="btn btn-line btn-sm" type="button" data-copy>Kopjo linkun</button>
          <a class="btn btn-line btn-sm" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u=https%3A%2F%2Finpo-ks.org">Facebook</a>
          <a class="btn btn-line btn-sm" target="_blank" rel="noopener" href="https://x.com/intent/post?text=${encodeURIComponent(n.title)}">X</a>
        </div></div>
        ${pub?`<a class="box" href="#/publikimet/${pub.id}"><h2>Publikimi i plotë</h2><span style="font-weight:600;font-size:17px;line-height:1.35">${pub.title}</span><span class="link-u">Shiko publikimin ${ARROW}</span></a>`:''}
        <div class="box acc"><h2>Keni informata për këtë rast?</h2><p>Na dërgoni dokumente ose detaje. Ekipi ynë i monitorimit i shqyrton të gjitha.</p><a class="btn btn-sm" style="background:#fff;color:#0E0E1A;align-self:flex-start" href="#/kontakti?tema=tender">Na shkruani</a></div>
      </aside>
    </div>
  </article>
  <section class="wrap sec">
    <div class="pub-head"><h2 class="h2">Lexo edhe</h2><a class="btn btn-line btn-sm" href="#/lajme">Të gjitha lajmet</a></div>
    <div>${related.map(newsRow).join('')}</div>
  </section>`;
}

function pubsPage(q){
  const f = q.lloji && PTYPES[q.lloji] ? q.lloji : 'all';
  return pageHead('Biblioteka','Publikimet','Analiza, hulumtime, infografika dhe buletine mbi prokurimin publik dhe qeverisjen në Kosovë. Të gjitha falas, në PDF.') + `
  <section class="wrap">
    <div class="toolbar" style="border-bottom:0;padding-bottom:0">
      <div style="flex:1 1 420px;max-width:560px">
        <label for="q-pubs" class="sr">Kërko në publikime</label>
        <div class="search-inline"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg><input id="q-pubs" type="search" placeholder="Kërko, p.sh. OShP, tenderë, komuna" value="${q.q?q.q.replace(/"/g,'&quot;'):''}"></div>
      </div>
    </div>
    <div class="toolbar" style="margin-top:20px">
      <div class="chips" role="group" aria-label="Filtro sipas llojit">${PFILTERS.map(([k,l])=>`<button type="button" class="chip" data-ptype="${k}" aria-pressed="${k===f}">${l}</button>`).join('')}</div>
      <div class="toolbar-r">
        <span class="muted" style="font-size:14px" id="pub-count" aria-live="polite"></span>
        <label for="pub-prog" style="font-size:14px;font-weight:500">Programi</label>
        <select class="sel" id="pub-prog"><option value="all">Të gjitha</option><option value="qeverisje">Qeverisje e mirë</option><option value="edukim">Edukim demokratik</option></select>
      </div>
    </div>
    <div class="results"><div class="pubs three" id="pub-grid"></div></div>
  </section>`;
}
function bindPubs(q){
  const state = { type: q.lloji && PTYPES[q.lloji] ? q.lloji : 'all', q: q.q||'', prog:'all' };
  const grid = $('#pub-grid'), count = $('#pub-count'), input = $('#q-pubs');
  function draw(){
    const needle = norm(state.q.trim());
    const list = PUBS.filter(p => (state.type==='all'||p.type===state.type) && (state.prog==='all'||p.prog===state.prog) && (!needle || norm(p.title+' '+PTYPES[p.type]).includes(needle)));
    count.textContent = list.length===1 ? '1 publikim' : list.length+' publikime';
    grid.innerHTML = list.length ? list.map(pubCard).join('') : `<div class="empty"><b>Asnjë publikim për këtë kërkim.</b><span class="muted">Provoni një fjalë tjetër ose hiqni filtrat.</span><button class="btn btn-line btn-sm" type="button" id="reset-pubs">Hiq filtrat</button></div>`;
    $$('[data-ptype]').forEach(b=>b.setAttribute('aria-pressed', String(b.dataset.ptype===state.type)));
    const r = $('#reset-pubs'); if(r) r.onclick = ()=>{ state.type='all'; state.q=''; state.prog='all'; input.value=''; $('#pub-prog').value='all'; draw(); };
  }
  $$('[data-ptype]').forEach(b=>b.addEventListener('click',()=>{ state.type=b.dataset.ptype; draw(); }));
  input.addEventListener('input',()=>{ state.q=input.value; draw(); });
  $('#pub-prog').addEventListener('change',e=>{ state.prog=e.target.value; draw(); });
  draw();
}

function pubDetail(id){
  const i = PUBS.findIndex(p=>p.id===id); if(i<0) return notFound();
  const p = PUBS[i];
  const more = PUBS.filter(x=>x.id!==id && x.type===p.type).concat(PUBS.filter(x=>x.id!==id && x.type!==p.type)).slice(0,3);
  return `
  <section class="wrap grid12 pd">
    ${cover(p,i)}
    <div class="pd-info">
      <nav class="crumbs" aria-label="Gjurma"><a href="#/publikimet">Publikimet</a><span aria-hidden="true">/</span><a href="#/publikimet?lloji=${p.type}">${PTYPES[p.type]}</a></nav>
      <h1 data-focus>${p.title}</h1>
      <dl class="facts">
        <div><dt>Lloji</dt><dd>${PTYPES[p.type]}</dd></div>
        <div><dt>Programi</dt><dd>${p.prog?PROGS[p.prog].name:'INPO'}</dd></div>
        <div><dt>${p.partners?'Partnerë':'Viti'}</dt><dd>${p.partners||p.year||'[Viti]'}</dd></div>
      </dl>
      <p class="lead">Përmbledhja e shkurtër e publikimit shfaqet këtu: pyetja që trajton, gjetjet kryesore dhe rekomandimet për institucionet.</p>
      <div class="hero-ctas"><a class="btn btn-accent" href="#" data-noop data-toast="Shkarkimi lidhet me skedarin PDF gjatë migrimit.">${DL} Shkarko PDF</a><button class="btn btn-line" type="button" data-copy>Kopjo linkun</button></div>
      ${p.donor?`<p class="muted" style="margin:0;font-size:14px">Me mbështetjen e ${p.donor}.</p>`:''}
    </div>
  </section>
  <section class="wrap sec">
    <div class="pub-head"><h2 class="h2">Publikime të ngjashme</h2><a class="btn btn-line btn-sm" href="#/publikimet">Biblioteka</a></div>
    <div class="pubs three">${more.map(x=>pubCard(x,PUBS.indexOf(x))).join('')}</div>
  </section>`;
}

function progPage(id){
  const p = PROGS[id]; if(!p) return notFound();
  const pubs = PUBS.filter(x=>x.prog===id).slice(0,3);
  const other = id==='qeverisje'?'edukim':'qeverisje';
  return `
  <section class="wrap page-head" style="display:block">
    <nav class="crumbs" aria-label="Gjurma" style="margin-bottom:22px"><span>Programet</span><span aria-hidden="true">/</span><span>${p.name}</span></nav>
    <h1 class="h-page" data-focus>${p.name}</h1>
  </section>
  <section class="wrap"><div class="prog-hero">${rings(700,4)}<span class="prog-ico">${ICO[id]}</span><p>${p.desc}</p></div></section>
  <section class="wrap sec">
    <h2 class="h2">Si punojmë</h2>
    <div class="ways">${p.ways.map(w=>`<div class="way"><h3>${w[0]}</h3><p>${w[1]}</p></div>`).join('')}</div>
  </section>
  ${pubs.length?`<section class="wrap sec"><div class="pub-head"><h2 class="h2">Publikime nga programi</h2><a class="btn btn-line btn-sm" href="#/publikimet">Të gjitha publikimet</a></div><div class="pubs three">${pubs.map(x=>pubCard(x,PUBS.indexOf(x))).join('')}</div></section>`:''}
  ${id==='qeverisje'?`<section class="wrap sec"><div class="pub-head"><h2 class="h2">Reagimet e fundit</h2><a class="btn btn-line btn-sm" href="#/lajme">Të gjitha lajmet</a></div><div>${NEWS.slice(0,3).map(newsRow).join('')}</div></section>`:''}
  <section class="wrap sec"><a class="prog" href="#/programet/${other}" style="min-height:0;flex-direction:row;align-items:center;justify-content:space-between"><div><span class="muted" style="font-size:14px">Programi tjetër</span><h3 style="margin:6px 0 0">${PROGS[other].name}</h3></div><span class="arrow-circle">${ARROW}</span></a></section>`;
}

function aboutPage(){
  const tl = [
    ['2005','Themelimi në Ferizaj','INPO nis me aktivitete që ndihmojnë integrimin e qytetarëve në proceset sociale dhe vendimmarrëse.'],
    ['2010','Fushata “Zgjohuni”','Orë me zile për këshilltarët komunalë të Ferizajt, si kujtesë se janë të zgjedhurit e popullit.'],
    ['2019','Monitorim në të gjitha komunat','Me Institutin e Kosovës për Drejtësi, 160 aktivitete prokurimi të monitoruara në komunat e Kosovës.'],
    ['2023','Raporti për tenderët e anuluar','Publikuar me mbështetjen e Byrosë Ndërkombëtare për Çështje të Narkotikëve dhe Zbatimit të Ligjit (INL).']
  ];
  return pageHead('Rreth nesh','Rreth nesh','Organizatë joqeveritare dhe jofitimprurëse, e themeluar në vitin 2005. Politikat publike janë fokusi i punës sonë.') + `
  <section class="wrap"><div class="mv">
    <div class="m">${rings(560,4)}<h2>Misioni</h2><p>Të promovojmë demokratizimin e qeverisjes lokale përmes aktivizmit qytetar dhe të avancojmë vlerat e një shoqërie të hapur.</p></div>
    <div class="v"><h2>Vizioni</h2><p>Institucione lokale që qeverisen me vlera demokratike, ku qytetari është bashkëvendimmarrës.</p></div>
  </div></section>
  <section class="wrap sec">
    <div class="grid12 sec-head"><span class="kicker">Historia</span><h2 class="h2">Nga performancat protestuese te auditimi i tenderëve.</h2></div>
    <ol class="timeline">${tl.map(t=>`<li><span class="yr">${t[0]}</span><div><h3>${t[1]}</h3><p>${t[2]}</p></div></li>`).join('')}</ol>
  </section>
  <section class="wrap sec">
    <h2 class="h2">Transparenca fillon nga ne</h2>
    <div class="duo">
      <div class="box" style="padding:36px"><h2 style="font-size:22px;letter-spacing:-.02em">Raportet e auditimit</h2><p class="muted" style="margin:0">Pasqyrat financiare të audituara, të publikuara çdo vit.</p>
        <div class="doc-list"><a href="#/publikimet/auditimi-2018">Raporti i auditimit 2018 <span class="arrow-circle">${DL}</span></a><a href="#/publikimet?lloji=inpo">Të gjitha raportet për INPO <span class="arrow-circle">${ARROW}</span></a></div></div>
      <div class="box" style="padding:36px;background:var(--bg-soft);border-color:transparent"><h2 style="font-size:22px;letter-spacing:-.02em">Mundësi punësimi</h2><p class="muted" style="margin:0">Pozitat dhe thirrjet e hapura publikohen këtu. Abonohuni për të marrë njoftim kur hapet një pozitë e re.</p><button class="btn btn-ink btn-sm" style="align-self:flex-start;margin-top:auto" type="button" data-scroll="newsletter">Merr njoftime</button></div>
    </div>
  </section>
  <section class="wrap sec"><span class="kicker">Donatorët dhe partnerët</span>${donorsHTML()}</section>`;
}

function contactPage(q){
  const topics = [['tender','Informatë për tender'],['publikime','Publikimet'],['media','Media'],['bashkepunim','Bashkëpunim']];
  const t = topics.some(x=>x[0]===q.tema) ? q.tema : 'tender';
  return pageHead('Kontakti','Le të flasim.','Keni informata për një tender, pyetje për raportet tona ose një ide për bashkëpunim? Na shkruani.') + `
  <section class="wrap grid12 contact">
    <form class="form-card" id="c-form" novalidate>
      <div class="two">
        <div class="field"><label for="c-emri">Emri</label><input id="c-emri" name="emri" autocomplete="given-name" required><span class="err" id="e-emri"></span></div>
        <div class="field"><label for="c-mbiemri">Mbiemri <span class="opt">(opsionale)</span></label><input id="c-mbiemri" name="mbiemri" autocomplete="family-name"><span class="err"></span></div>
      </div>
      <div class="field"><label for="c-email">Adresa e email-it</label><input id="c-email" name="email" type="email" autocomplete="email" placeholder="emri@shembull.com" required><span class="err" id="e-email"></span></div>
      <fieldset class="field"><legend>Tema</legend><div class="topics">${topics.map(([k,l])=>`<label><input type="radio" name="tema" value="${k}" ${k===t?'checked':''}>${l}</label>`).join('')}</div></fieldset>
      <div class="field"><label for="c-msg">Mesazhi</label><textarea id="c-msg" name="mesazhi" rows="6" required></textarea><span class="err" id="e-msg"></span></div>
      <div class="field"><label class="consent"><input type="checkbox" id="c-ok">Pajtohem që të dhënat e mia të përdoren vetëm për t'iu përgjigjur këtij mesazhi.</label><span class="err" id="e-ok"></span></div>
      <div class="form-foot"><span class="muted" style="font-size:14px">Ose shkruani direkt: <a class="link-u" href="mailto:info@inpo-ks.org">info@inpo-ks.org</a></span><button class="btn btn-accent" type="submit">Dërgo mesazhin</button></div>
    </form>
    <div class="side">
      <div class="office a">${rings(360,3)}<small>Zyra në Prishtinë</small><address>Rr. “Qamil Hoxha”, H32,<br>nr. 09, kati IV, 10000 Prishtinë</address>
        <div class="office-foot"><a href="tel:+38349354000">+383 (0) 49 354 000</a><a class="btn btn-sm" target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query=Qamil+Hoxha+Prishtin%C3%AB">Hap në hartë</a></div></div>
      <div class="office b"><small>Zyra në Ferizaj</small><address>Rr. “Latif Hasani”, pn,<br>70000 Ferizaj</address>
        <div class="office-foot"><a href="tel:+38349354000">+383 (0) 49 354 000</a><a class="btn btn-ink btn-sm" target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query=Latif+Hasani+Ferizaj">Hap në hartë</a></div></div>
    </div>
  </section>`;
}
function bindContact(){
  const f = $('#c-form');
  const set = (id, el, msg) => { $('#'+id).textContent = msg; el.closest('.field').classList.toggle('invalid', !!msg); if(msg) el.setAttribute('aria-invalid','true'); else el.removeAttribute('aria-invalid'); };
  // After the first attempt, re-check a field as soon as the visitor leaves it.
  const rules = { 'c-emri': v => v ? '' : 'Shkruani emrin.', 'c-email': v => /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v) ? '' : 'Shkruani një adresë email të vlefshme, p.sh. emri@shembull.com.', 'c-msg': v => v.length >= 10 ? '' : 'Shkruani mesazhin, të paktën 10 shkronja.' };
  const errId = { 'c-emri': 'e-emri', 'c-email': 'e-email', 'c-msg': 'e-msg' };
  let tried = false;
  Object.keys(rules).forEach(id => $('#'+id).addEventListener('blur', e => { if (tried || e.target.value.trim()) set(errId[id], e.target, rules[id](e.target.value.trim())); }));
  f.addEventListener('submit', e => {
    tried = true;
    e.preventDefault();
    const emri=$('#c-emri'), email=$('#c-email'), msg=$('#c-msg'), ok=$('#c-ok');
    set('e-emri', emri, emri.value.trim() ? '' : 'Shkruani emrin.');
    set('e-email', email, /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email.value.trim()) ? '' : 'Shkruani një adresë email të vlefshme, p.sh. emri@shembull.com.');
    set('e-msg', msg, msg.value.trim().length >= 10 ? '' : 'Shkruani mesazhin, të paktën 10 shkronja.');
    $('#e-ok').textContent = ok.checked ? '' : 'Pranoni kushtin për të dërguar mesazhin.';
    const first = $('[aria-invalid="true"]', f) || (!ok.checked ? ok : null);
    if (first) { first.focus(); return; }
    const name = emri.value.trim();
    const wrap = document.createElement('div');
    wrap.className = 'sent'; wrap.setAttribute('tabindex','-1');
    wrap.innerHTML = `<span class="check"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg></span><h2>Faleminderit, ${name.replace(/</g,'&lt;')}.</h2><p class="lead">Mesazhi u dërgua. Do t'ju përgjigjemi në ${email.value.trim().replace(/</g,'&lt;')}.</p><button class="btn btn-line" type="button" id="again">Dërgo një mesazh tjetër</button>`;
    f.replaceWith(wrap); wrap.focus();
    $('#again').onclick = () => render();
  });
}

function notFound(){
  return pageHead('Gabim 404','Faqja nuk u gjet.','Linku mund të jetë i vjetër ose i shkruar gabim. Kthehuni në kryefaqe ose kërkoni atë që ju duhet.') + `<section class="wrap"><div class="hero-ctas" style="margin-top:40px"><a class="btn btn-accent" href="#/">Kryefaqja</a><button class="btn btn-line" type="button" data-open-search>Kërko në faqe</button></div></section>`;
}

/* ---------- Router ---------- */
function parse(){
  const raw = location.hash.replace(/^#\/?/,'');
  const [path, qs] = raw.split('?');
  const q = Object.fromEntries(new URLSearchParams(qs||''));
  return { parts: path.split('/').filter(Boolean), q };
}
const app = $('#app');
let firstRender = true;
function render(){
  const { parts, q } = parse();
  const [a, b] = parts;
  let html, title='INPO · Iniciativa për Progres', section=a||'', after=null;
  if (!a) { html = home(); }
  else if (a==='lajme' && !b) { html = newsPage(q); title='Lajme · INPO'; }
  else if (a==='lajme') { html = articlePage(b); const n=NEWS.find(x=>x.id===b); title=(n?n.title.slice(0,60)+'… ':'')+'· INPO'; }
  else if (a==='publikimet' && !b) { html = pubsPage(q); title='Publikimet · INPO'; after=()=>bindPubs(q); }
  else if (a==='publikimet') { html = pubDetail(b); title='Publikim · INPO'; }
  else if (a==='programet') { html = progPage(b); title=(PROGS[b]?PROGS[b].full:'Programet')+' · INPO'; }
  else if (a==='rreth-nesh') { html = aboutPage(); title='Rreth nesh · INPO'; }
  else if (a==='kontakti') { html = contactPage(q); title='Kontakti · INPO'; after=bindContact; }
  else { html = notFound(); section='404'; }
  app.innerHTML = html;
  document.title = title;
  $$('[data-nav]').forEach(l => { if (l.dataset.nav===section) l.setAttribute('aria-current','page'); else l.removeAttribute('aria-current'); });
  $('#dd').classList.toggle('current', section==='programet');
  closeDD();
  if (menu.open) menu.close();
  if (after) after();
  if (!firstRender) { window.scrollTo(0,0); const h=$('[data-focus]',app); if(h){h.setAttribute('tabindex','-1');h.focus({preventScroll:true});} }
  if (!a) heroIntro(firstRender);
  firstRender = false;
}

/* ---------- Hero intro + count-up ---------- */
const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
function heroIntro(){
  document.body.classList.remove('loaded');
  requestAnimationFrame(()=>requestAnimationFrame(()=>document.body.classList.add('loaded')));
  $$('[data-count]', app).forEach(el => {
    if (reduce) return;
    const to=parseFloat(el.dataset.count), dec=+(el.dataset.dec||0), pre=el.dataset.prefix||'', suf=el.dataset.suffix||'';
    const fmt = v => pre + v.toFixed(dec).replace('.',',') + suf;
    const start = performance.now()+550, dur=1400;
    el.textContent = fmt(0);
    const tick = now => { const t=Math.min(1,Math.max(0,(now-start)/dur)); const e=1-Math.pow(1-t,4); el.textContent=fmt(to*e); if(t<1) requestAnimationFrame(tick); };
    requestAnimationFrame(tick);
  });
}

/* ---------- UI: dropdown, dialogs, search, theme, forms ---------- */
const dd = $('#dd'), ddBtn = $('#dd>button');
function closeDD(){ dd.classList.remove('open'); ddBtn.setAttribute('aria-expanded','false'); }
ddBtn.addEventListener('click', e => { e.stopPropagation(); const o=!dd.classList.contains('open'); dd.classList.toggle('open',o); ddBtn.setAttribute('aria-expanded',String(o)); });
document.addEventListener('click', e => { if(!dd.contains(e.target)) closeDD(); });

const menu = $('#menu'), search = $('#search');
$('#open-menu').addEventListener('click', ()=>{ menu.showModal(); $('#open-menu').setAttribute('aria-expanded','true'); });
menu.addEventListener('close', ()=>$('#open-menu').setAttribute('aria-expanded','false'));
$$('[data-close]').forEach(b=>b.addEventListener('click',()=>b.closest('dialog').close()));
$$('.menu-links a, #menu .logo').forEach(a=>a.addEventListener('click',()=>menu.close()));

function openSearch(){ search.showModal(); const i=$('#q-global'); i.value=''; drawSearch(''); i.focus(); }
function drawSearch(v){
  const res = $('#search-res'), n = norm(v.trim());
  if (!n) { res.innerHTML = `<div class="search-empty">Shkruani një fjalë, p.sh. <b>Vushtrri</b>, <b>OShP</b> ose <b>stadiume</b>.</div>`; return; }
  const hits = NEWS.filter(x=>norm(x.title+' '+x.excerpt).includes(n)).map(x=>({h:'#/lajme/'+x.id,t:x.title,k:CATS[x.cat]}))
    .concat(PUBS.filter(p=>norm(p.title+' '+PTYPES[p.type]).includes(n)).map(p=>({h:'#/publikimet/'+p.id,t:p.title,k:PTYPES[p.type]})));
  res.innerHTML = hits.length ? hits.slice(0,8).map(x=>`<a href="${x.h}"><small>${x.k}</small><span>${x.t}</span></a>`).join('') : `<div class="search-empty">Asnjë rezultat për “${v.replace(/</g,'&lt;')}”. Provoni një fjalë tjetër.</div>`;
}
$('#open-search').addEventListener('click', openSearch);
$('#q-global').addEventListener('input', e=>drawSearch(e.target.value));
$('#search-res').addEventListener('click', e=>{ if(e.target.closest('a')) search.close(); });
search.addEventListener('click', e=>{ if(e.target===search) search.close(); });
document.addEventListener('keydown', e => {
  if (e.key==='/' && !/INPUT|TEXTAREA|SELECT/.test(document.activeElement.tagName) && !search.open) { e.preventDefault(); openSearch(); }
  if (e.key==='Escape') closeDD();
});

/* Theme toggle */
const root = document.documentElement;
try { const t = localStorage.getItem('inpo-theme'); if (t) root.dataset.theme = t; } catch(e){}
$('#theme').addEventListener('click', ()=>{
  const cur = root.dataset.theme || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
  const next = cur==='dark' ? 'light' : 'dark';
  root.dataset.theme = next;
  try { localStorage.setItem('inpo-theme', next); } catch(e){}
  toast(next==='dark' ? 'Tema e errët u aktivizua.' : 'Tema e çelët u aktivizua.');
});

/* Toast */
let tt;
function toast(msg){ const t=$('#toast'); t.textContent=msg; t.classList.add('show'); clearTimeout(tt); tt=setTimeout(()=>t.classList.remove('show'),2600); }

/* Global delegation */
document.addEventListener('click', e => {
  const noop = e.target.closest('[data-noop]'); if (noop) { e.preventDefault(); if (noop.dataset.toast) toast(noop.dataset.toast); }
  const sc = e.target.closest('[data-scroll]'); if (sc) { if (menu.open) menu.close(); const el=$('#'+sc.dataset.scroll); el.scrollIntoView({behavior: reduce?'auto':'smooth', block:'center'}); setTimeout(()=>$('#nl-email').focus({preventScroll:true}), reduce?0:600); }
  const cp = e.target.closest('[data-copy]'); if (cp) { const url=location.href; (navigator.clipboard ? navigator.clipboard.writeText(url) : Promise.reject()).then(()=>toast('Linku u kopjua.')).catch(()=>toast('Kopjimi nuk u lejua. Kopjoni linkun nga shiriti i adresës.')); }
  if (e.target.closest('[data-open-search]')) openSearch();
  if (e.target.closest('[data-top]')) { window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' }); $('.logo').focus({ preventScroll: true }); }
});

/* Newsletter */
$('#nl-form').addEventListener('submit', e => {
  e.preventDefault();
  const i=$('#nl-email'), m=$('#nl-msg'), v=i.value.trim();
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v)) { m.textContent='Shkruani një adresë email të vlefshme, p.sh. emri@shembull.com.'; i.setAttribute('aria-invalid','true'); i.focus(); return; }
  i.removeAttribute('aria-invalid');
  $('#nl-form').innerHTML = `<div class="nl-done" role="status"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg><span>U abonuat me <b>${v.replace(/</g,'&lt;')}</b>. Buletini i parë vjen në fillim të muajit.</span></div>`;
});

/* Header border on scroll */
const hdr = $('#hdr');
addEventListener('scroll', ()=>hdr.classList.toggle('scrolled', scrollY>8), {passive:true});
$('#yr').textContent = new Date().getFullYear();

addEventListener('hashchange', render);
render();
})();
