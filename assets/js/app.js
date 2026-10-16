/* INPO website app: hash router, page renderers and UI behaviour.
   No build step, no dependencies. Content lives in data.js. */
(function(){
'use strict';

const D = window.INPO_DATA;
const { NEWS, CATS, CAT1, PUBS, PTYPES, PFILTERS, THEMES, INSTS, PROGS, DONORS, DONOR_LOGOS, PROJECTS, IMPACT, CASES, TEAM, BOARD, DCATS, DOCS, DATASETS, NAV } = D;

/* ---------- Helpers ---------- */
const $ = (s, r=document) => r.querySelector(s);
const $$ = (s, r=document) => Array.from(r.querySelectorAll(s));
const norm = s => String(s).normalize('NFD').replace(/[̀-ͯ]/g,'').toLowerCase();
const esc = s => String(s).replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
const ARROW = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7"/><path d="M8 7h9v9"/></svg>';
const DL = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v11"/><path d="m7 10 5 5 5-5"/><path d="M5 20h14"/></svg>';
const CHEV = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>';
const SEARCH = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>';
const X = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>';
const rings = (size, n, cls='') => { const c=size/2, step=(c-8)/n; let s=''; for(let i=1;i<=n;i++) s+=`<circle cx="${c}" cy="${c}" r="${Math.round(step*i+ (i===1?step*.4:0))}"/>`; return `<svg class="rings ${cls}" width="${size}" height="${size}" viewBox="0 0 ${size} ${size}" aria-hidden="true">${s}</svg>`; };
const ICO = {
  qeverisje:'<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="5" r="2.5"/><circle cx="5" cy="18" r="2.5"/><circle cx="12" cy="18" r="2.5"/><circle cx="19" cy="18" r="2.5"/><path d="M12 7.5v8"/><path d="M12 11c-4 0-7 1.5-7 4.5"/><path d="M12 11c4 0 7 1.5 7 4.5"/></svg>',
  edukim:'<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m2 9 10-5 10 5-10 5-10-5Z"/><path d="M6 11v5c0 1.5 2.7 3 6 3s6-1.5 6-3v-5"/><path d="M22 9v6"/></svg>'
};
const DOTS_ON = [2,7,10,15];
const YEARS = String(new Date().getFullYear() - 2005);
const yearOf = p => p.year || (p.date ? p.date.slice(-4) : '');
const isoOf = d => d ? d.split('.').reverse().join('-') : '';

// Bars for a cover from a chart's first series (at most 8), the highest in the accent colour.
function coverBars(c){
  const vals = c.rows.slice(0,8).map(r=>+r[1]||0), max = Math.max(...vals, 1);
  return '<div class="bars data" aria-hidden="true">' + vals.map(v=>`<i style="height:${Math.max(6, Math.round(v/max*100))}%"${v===max?' class="on"':''}></i>`).join('') + '</div>';
}
function cover(p, i){
  const v = p.data ? 'bars' : ['rings','bars','dots','word'][i % 4];
  const y = yearOf(p);
  const top = `<div class="cover-top"><span class="pill">${PTYPES[p.type]}</span>${y ? `<span class="cover-year">${y}</span>` : ''}</div>`;
  const foot = `<div class="cover-foot"><span class="cover-mark">INPO</span><span class="dl">PDF ${DL}</span></div>`;
  let art = '';
  if (v==='rings') art = rings(260,3);
  if (v==='bars') art = '<div class="bars" aria-hidden="true"><i style="height:40%"></i><i style="height:66%"></i><i style="height:52%"></i><i style="height:100%"></i></div>';
  // An infographic as images: its first image is the cover.
  if (p.slides) return `<div class="cover cv-page" aria-hidden="true"><img class="cover-img" src="assets/img/infografika/${p.slides[0][0]}" alt="" loading="lazy" decoding="async">${top}<span class="cover-title">${p.title}</span>${foot}</div>`;
  // An infographic with data: its chart with the most bars, drawn small on the cover.
  if (p.data) art = coverBars(p.data.charts.filter(c=>c.chart!=='table').sort((x,y)=>y.rows.length-x.rows.length)[0] || p.data.charts[0]);
  if (v==='dots') art = '<div class="dots" aria-hidden="true">' + Array.from({length:18},(_,k)=>`<i${DOTS_ON.includes(k)?' class="on"':''}></i>`).join('') + '</div>';
  if (v==='word') art = `<span class="word" aria-hidden="true">${p.short}</span>`;
  // A document cover: decorative, so hidden from screen readers (the title is in the card or the h1).
  return `<div class="cover cv-${v}" aria-hidden="true">${art}${top}<span class="cover-title">${p.title}</span>${foot}</div>`;
}
const pubCard = (p,i) => `<div class="pub"><a class="pub-cover" href="#/publikimet/${p.id}" tabindex="-1" aria-hidden="true">${cover(p,i)}</a><a class="pub-title" href="#/publikimet/${p.id}">${p.title}</a></div>`;
const newsRow = n => `<a class="row" href="#/lajme/${n.id}"><div class="row-meta"><time datetime="${n.iso}">${n.date}</time><span>${CAT1[n.cat]}</span></div><span class="row-title">${n.title}</span><span class="arrow-circle">${ARROW}</span></a>`;
// Columns (5, 4 or 3) that leave the fewest empty cells in the last row; same rule as inpo_core_grid_cols().
const gridCols = n => n < 3 ? Math.max(1,n) : [5,4,3].filter(c=>n>=c).reduce((b,c)=>((c-n%c)%c) < ((b-n%b)%b) ? c : b);
const donorsHTML = () => `<div class="donors" style="--cols:${gridCols(DONORS.length)}">${DONORS.map(d=>`<div class="donor">${DONOR_LOGOS[d]?`<img src="assets/img/donors/${DONOR_LOGOS[d]}" alt="${d}" loading="lazy" decoding="async">`:d}</div>`).join('')}</div><p class="fineprint">Ky grant është financuar nga projekti Promovimi i Shoqërisë Demokratike (DSP), i financuar nga Zyra Zvicerane për Bashkëpunim në Kosovë (SCO-K) dhe Ministria e Punëve të Jashtme e Danimarkës (DANIDA) dhe menaxhuar nga Fondacioni Kosovar për Shoqëri Civile (KCSF).</p>`;
const pageHead = (kicker, title, lead, crumbs) => `<section class="wrap grid12 page-head"><div class="ph-l">${crumbs||`<span class="kicker">${kicker}</span>`}<h1 class="h-page" data-focus>${title}</h1></div><div class="ph-r">${lead?`<p class="lead">${lead}</p>`:''}</div></section>`;
// In-page links to the sections of a page (About, Projects, Transparency).
const subnav = (label, items, base) => `<nav class="subnav" aria-label="${label}"><span class="subnav-k" aria-hidden="true">Në këtë faqe</span><span class="subnav-l">${items.map(([k,l])=>`<a href="#/${base}?seksioni=${k}">${l}</a>`).join('')}</span></nav>`;
// Figures: big number, short label.
const impactGrid = items => `<dl class="impact-grid">${items.map(([n,l])=>`<div><dt>${l}</dt><dd>${n==='YEARS'?YEARS:n}</dd></div>`).join('')}</dl>`;
const projStatus = p => p.status==='aktiv' ? 'Aktiv' : 'I përfunduar';
// Cards leave out values still in [brackets]; the project page lists every field.
const known = v => v && !/^\[/.test(v);
const projCard = p => `<a class="proj" href="#/projektet/${p.id}"><div class="proj-top"><span class="pill${p.status==='aktiv'?' on':''}">${projStatus(p)}</span>${known(p.period)?`<span class="proj-period">${p.period}</span>`:''}</div><h3>${p.title}</h3><dl class="proj-meta">${known(p.donor)?`<div><dt>Donatori</dt><dd>${p.donor}</dd></div>`:''}${known(p.partners)?`<div><dt>Partnerët</dt><dd>${p.partners}</dd></div>`:''}</dl><span class="link-u">Shiko projektin ${ARROW}</span></a>`;
const docRow = d => `<div class="doc-row" data-year="${d.year}"><span class="doc-yr">${d.year}</span><span class="doc-title">${d.title}</span><span class="doc-act"><a class="btn btn-line btn-sm" href="#" data-noop data-toast="Skedari PDF lidhet gjatë migrimit të përmbajtjes.">${DL} PDF</a></span></div>`;
// The board: names and roles in a ruled list (no photos).
const boardList = list => `<ul class="board">${list.map(([name, role])=>`<li${/^Kryesues/.test(role)?' class="chair"':''}><span class="board-n">${name}</span><span class="board-r">${role}</span></li>`).join('')}</ul>`;
const member = ([name, role, email, photo]) => `<div class="member"><div class="member-photo">${photo?`<img src="assets/img/team/${photo}" alt="" width="640" height="800" loading="lazy" decoding="async">`:`<span class="member-initials" aria-hidden="true">${/^\[/.test(name)?'':name.split(/\s+/).slice(0,2).map(w=>w[0]).join('')}</span>`}</div><h4>${name}</h4><p>${role}</p>${email?`<a class="link-u" href="mailto:${email}">${email}</a>`:''}</div>`;

/* ---------- Chrome: navigation and mobile menu (built from NAV) ---------- */
const sectionOf = h => (h.replace(/^#\/?/,'').split(/[/?]/)[0] || '');
const SHORT = { 'Hulumtime dhe publikime':'Publikime', 'Lajme dhe qëndrime':'Lajme', 'Projektet dhe ndikimi':'Projektet' };
function buildChrome(){
  const nav = $('#nav'), menuLinks = $('#menu-links');
  let n = 0;
  // Desktop bar: no "Kryefaqja" (the logo is the link home) and short labels; full names in the dropdowns.
  nav.innerHTML = NAV.filter(([, href]) => href !== '#/').map(([label, href, kids]) => {
    const key = sectionOf(href), short = SHORT[label] || label;
    if (!kids) return `<a href="${href}" data-nav="${key}">${short}</a>`;
    n++;
    const all = kids[0][1] === href ? '' : `<a class="dd-all" href="${href}">${label}<small>Përmbledhje</small></a>`;
    return `<div class="dd" data-nav="${key}"><button type="button" aria-expanded="false" aria-controls="dd-${n}">${short} ${CHEV}</button><div class="dd-menu" id="dd-${n}">${all}${kids.map(([l,h])=>`<a href="${h}">${l}</a>`).join('')}</div></div>`;
  }).join('');
  menuLinks.innerHTML = NAV.map(([label, href, kids]) => !kids ? `<a href="${href}">${label}</a>`
    : `<details class="menu-group"><summary>${label} ${CHEV}</summary><div class="menu-sub">${kids[0][1] === href ? '' : `<a href="${href}">${label}: përmbledhje</a>`}${kids.map(([l,h])=>`<a href="${h}">${l}</a>`).join('')}</div></details>`).join('');
}
buildChrome();

/* ---------- Pages ---------- */
// Who we are, with photos from INPO's work in the field.
const fieldHTML = () => `<section class="wrap sec field" aria-labelledby="h-field"><div class="grid12 field-grid">
    <div class="field-intro"><span class="kicker">${D.FIELD.kicker}</span><h2 class="h2" id="h-field">${D.FIELD.title}</h2><p class="lead">${D.FIELD.text}</p><a class="btn btn-line" href="#/rreth-nesh">Më shumë për INPO</a></div>
    <div class="field-photos">${D.FIELD.photos.map(([f,c],i)=>`<figure class="fp${i===0?' big':''}"><img src="assets/img/photos/${f}" alt="${c}" width="${i===0?1280:800}" height="${i===0?800:600}" loading="lazy" decoding="async"><figcaption>${c}</figcaption></figure>`).join('')}</div>
  </div></section>`;
function home(){
  const latestPubs = PUBS.slice(0,4);
  // Active projects first, then the latest completed ones.
  const projects = PROJECTS.filter(p=>p.status==='aktiv').concat(PROJECTS.filter(p=>p.status!=='aktiv')).slice(0,3);
  return `
  <section class="wrap grid12 hero">
    <h1 class="h-display" data-focus><span class="line"><span>Llogaridhënie,</span></span><span class="line accent"><span>jo premtime.</span></span></h1>
    <div class="hero-side">
      <p class="lead">Monitorojmë prokurimin publik dhe vendimet e institucioneve në Kosovë, publikojmë çfarë gjejmë dhe kërkojmë që paraja publike të shpenzohet me ligj.</p>
      <div class="hero-ctas"><a class="btn btn-accent" href="#/publikimet">Shfleto publikimet</a><a class="btn btn-line" href="#/rreth-nesh">Rreth INPO-s</a></div>
    </div>
    <figure class="hero-photo"><img src="assets/img/photos/${D.HERO_PHOTO[0]}" alt="${D.HERO_PHOTO[1]}" width="960" height="1200" fetchpriority="high" decoding="async"><figcaption>${D.HERO_PHOTO[1]}</figcaption></figure>
    <div class="areas"><span class="areas-k" id="areas-k">Fushat e veprimit</span><ul aria-labelledby="areas-k">${D.AREAS.map(([l,h])=>`<li><a href="${h}">${l}</a></li>`).join('')}</ul></div>
  </section>
  <section class="wrap" aria-labelledby="h-gjetjet">
    <div class="bento-head"><h2 class="kicker" id="h-gjetjet">Gjetjet kryesore nga monitorimet</h2><a class="link-u" href="#/lajme?kategoria=reagime">Të gjitha reagimet ${ARROW}</a></div>
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
  ${fieldHTML()}
  <section class="wrap sec">
    <div class="pub-head">
      <div class="pub-head-l"><span class="kicker">Hulumtime dhe publikime</span><h2 class="h2">Hulumtimet dhe publikimet më të fundit</h2></div>
      <div class="chips">${PFILTERS.slice(0,4).map(([k,l],i)=>`<a class="chip${i===0?' on':''}" href="#/publikimet${k==='all'?'':'?lloji='+k}">${l}</a>`).join('')}</div>
    </div>
    <div class="pubs">${latestPubs.map(pubCard).join('')}</div>
  </section>
  <section class="wrap sec">
    <div class="impact">${rings(640,4)}
      <div class="impact-head"><span class="kicker">Rezultatet dhe ndikimi</span><h2 class="h2">Ndikimi i punës sonë, në numra.</h2></div>
      ${impactGrid(IMPACT)}
      <a class="btn" href="#/projektet?seksioni=ndikimi">Shembuj të ndikimit</a>
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
  <section class="wrap sec grid12 news-sec">
    <div class="news-intro">
      <span class="kicker">Lajme dhe qëndrime</span>
      <h2 class="h2">Lajmet dhe reagimet më të fundit</h2>
      <p class="lead">Qëndrimet tona publike për tenderët, vendimet dhe institucionet që monitorojmë.</p>
      <a class="btn btn-line btn-sm" href="#/lajme">Të gjitha lajmet</a>
    </div>
    <div class="news-list">${[NEWS[1],NEWS[3],NEWS[5]].map(newsRow).join('')}</div>
  </section>
  <section class="wrap sec">
    <div class="pub-head"><div class="pub-head-l"><span class="kicker">Projektet dhe partnerët</span><h2 class="h2">Projektet tona</h2></div><a class="btn btn-line btn-sm" href="#/projektet">Të gjitha projektet</a></div>
    <div class="projs">${projects.map(projCard).join('')}</div>
    <h3 class="sub-h">Donatorët dhe partnerët</h3>${donorsHTML()}
  </section>`;
}

/* ---------- Filters (Lajme, Publikimet) ----------
   Same markup and behaviour as the WordPress blocks: search, selects, chips with counts,
   a live count and removable active filters. State lives in the hash, so a view can be shared. */
const SORTS = [['','Më të rejat'],['te-vjetrat','Më të vjetrat'],['titulli','Sipas titullit']];
const FILTERS = {
  lajme: {
    key:'news', facet:'kategoria', facets:Object.entries(CATS), items:()=>NEWS, of:n=>n.cat, year:n=>n.iso.slice(0,4), selects:[],
    text:n=>n.title+' '+n.excerpt, label:'Kërko në lajme', ph:'Kërko, p.sh. OShP, stadiume, Vushtrri',
    count:n=>n===1?'1 artikull':n+' artikuj', none:'Asnjë lajm nuk përputhet me këtë kërkim.', grid:'news-grid', card:(n,i,big)=>ncard(n,big)
  },
  publikimet: {
    key:'pub', facet:'lloji', facets:PFILTERS.slice(1), items:()=>PUBS, of:p=>p.type, year:yearOf,
    // As in INPO's brief: filter by year, thematic field, institution and category (plus programme).
    selects:[['programi','Programi',p=>p.prog,PROGS,v=>v.name],['fusha','Fusha tematike',p=>p.theme,THEMES,v=>v],['institucioni','Institucioni',p=>p.inst,INSTS,v=>v]],
    text:p=>p.title+' '+PTYPES[p.type]+' '+(THEMES[p.theme]||'')+' '+(INSTS[p.inst]||''), label:'Kërko në publikime', ph:'Kërko, p.sh. OShP, tenderë, komuna',
    count:n=>n===1?'1 publikim':n+' publikime', none:'Asnjë publikim nuk përputhet me këtë kërkim.', grid:'pubs three', card:(p)=>pubCard(p,PUBS.indexOf(p))
  }
};
const ncard = (n, big) => `<a class="ncard${big?' big':''}" href="#/lajme/${n.id}">${big?rings(560,4):''}<div class="ncard-meta"><span class="pill">${CAT1[n.cat]}</span><time datetime="${n.iso}">${n.date}</time></div><div><h2>${n.title}</h2><p>${n.excerpt}</p></div></a>`;
function selPill(id, name, label, opts, val){
  const cur = (opts.find(o=>o[0]===val)||opts[0])[1];
  return `<div class="sel-pill${val?' is-set':''}"><label class="sel-k" for="${id}">${label}</label><span class="sel-v" aria-hidden="true">${cur}</span><select id="${id}" name="${name}">${opts.map(([v,l])=>`<option value="${v}"${v===val?' selected':''}>${l}</option>`).join('')}</select></div>`;
}
function filterState(kind, q){
  const c = FILTERS[kind], keys = c.facets.map(f=>f[0]);
  const st = { q:q.q||'', f:keys.includes(q[c.facet])?q[c.facet]:'', viti:q.viti||'', rendit:SORTS.some(s=>s[0]===q.rendit)?q.rendit:'' };
  c.selects.forEach(([name,,,dict]) => { st[name] = dict[q[name]] ? q[name] : ''; });
  return st;
}
function filtersHTML(kind, st){
  const c = FILTERS[kind], items = c.items(), k = c.key;
  const years = [...new Set(items.map(c.year).filter(Boolean))].sort().reverse();
  let sels = '';
  // A select appears once the content carries that field.
  c.selects.forEach(([name,label,get,dict,lab]) => {
    const used = [...new Set(items.map(get).filter(Boolean))];
    if (used.length) sels += selPill(`${k}-${name}`, name, label, [['','Të gjitha']].concat(Object.keys(dict).filter(x=>used.includes(x)).map(x=>[x,lab(dict[x])])), st[name]);
  });
  if (years.length > 1) sels += selPill(`${k}-year`,'viti','Viti',[['','Të gjitha']].concat(years.map(y=>[y,y])),st.viti);
  // Categories without content stay out of the way (the chosen one always shows).
  const chips = [['','Të gjitha']].concat(c.facets).filter(([k2])=>!k2 || k2===st.f || items.some(x=>c.of(x)===k2)).map(([k2,l])=>`<button type="button" class="chip" data-facet="${k2}" aria-pressed="${k2===st.f}">${l}<span class="chip-n"></span></button>`).join('');
  // One row: search and filters. Then the categories. Then the count, active filters and the order.
  return `<form class="filters" role="search" aria-label="${c.label}" data-filters="${kind}">
      <div class="filters-top">
        <div class="search-inline">${SEARCH}<label for="${k}-q" class="sr">${c.label}</label><input id="${k}-q" name="q" type="search" placeholder="${c.ph}" value="${esc(st.q)}" autocomplete="off"></div>
        ${sels?`<div class="filters-sel">${sels}</div>`:''}
      </div>
      <div class="chips" role="group" aria-label="Filtro sipas kategorisë">${chips}</div>
      <div class="res-bar"><p class="res-count" id="${k}-count" role="status"></p><div class="res-active" id="${k}-active"></div><div class="res-sort">${selPill(`${k}-sort`,'rendit','Rendit',SORTS,st.rendit)}</div></div>
    </form>
    <div class="results" id="${k}-results"></div>`;
}
// A row that scrolls sideways: fade the side that has more to show.
function scrollFade(el){
  if (!el) return;
  const set = () => { const max = el.scrollWidth - el.clientWidth; el.classList.toggle('fade-l', el.scrollLeft > 2); el.classList.toggle('fade-r', max > 2 && el.scrollLeft < max - 2); };
  el.addEventListener('scroll', set, { passive:true }); window.addEventListener('resize', set); set();
  // Keep the chosen chip in view.
  const on = $('[aria-pressed="true"]', el); if (on && on.offsetLeft + on.offsetWidth > el.clientWidth) el.scrollLeft = on.offsetLeft - 24;
}
function bindFilters(kind, q){
  const c = FILTERS[kind], st = filterState(kind, q), form = $('[data-filters]');
  const count = $(`#${c.key}-count`), active = $(`#${c.key}-active`), results = $(`#${c.key}-results`);
  const input = form.elements.q;
  const extra = c.selects.map(s=>s[0]);
  const match = (x, skipFacet) => {
    const needle = norm(st.q.trim());
    return (skipFacet || !st.f || c.of(x)===st.f)
      && c.selects.every(([name,,get]) => !st[name] || get(x)===st[name])
      && (!st.viti || String(c.year(x))===st.viti) && (!needle || norm(c.text(x)).includes(needle));
  };
  const sorted = list => {
    if (st.rendit==='titulli') return list.slice().sort((a,b)=>a.title.localeCompare(b.title,'sq'));
    if (st.rendit==='te-vjetrat') return list.slice().reverse();
    return list;
  };
  const facetName = k => (c.facets.find(f=>f[0]===k)||['',''])[1];
  function draw(){
    const base = c.items().filter(x=>match(x,true));
    const list = sorted(base.filter(x=>!st.f || c.of(x)===st.f));
    // Counts per chip under the other active filters.
    $$('[data-facet]', form).forEach(b => {
      const k = b.dataset.facet;
      $('.chip-n', b).textContent = k ? base.filter(x=>c.of(x)===k).length : base.length;
      b.setAttribute('aria-pressed', String(k===st.f));
    });
    $$('.sel-pill', form).forEach(p => { const s=$('select',p); p.classList.toggle('is-set', !!s.value); $('.sel-v',p).textContent = s.options[s.selectedIndex].text; });
    count.textContent = c.count(list.length);
    const tags = [];
    if (st.q.trim()) tags.push(['q', `“${esc(st.q.trim())}”`]);
    if (st.f) tags.push(['f', facetName(st.f)]);
    c.selects.forEach(([name,,,dict,lab]) => { if (st[name]) tags.push([name, lab(dict[st[name]])]); });
    if (st.viti) tags.push(['viti', st.viti]);
    active.innerHTML = tags.length ? tags.map(([k,l])=>`<button type="button" class="tag" data-clear="${k}" aria-label="Hiq filtrin: ${l.replace(/"/g,'')}">${l}${X}</button>`).join('') + (tags.length>1?'<button type="button" class="res-clear" data-clear="all">Hiq të gjitha</button>':'') : '';
    const plain = !st.q.trim() && !st.viti && !st.rendit && extra.every(k=>!st[k]);
    let html;
    if (list.length) html = `<div class="${c.grid}">${list.map((x,i)=>c.card(x,i,plain && i===0)).join('')}</div>`;
    else {
      const wide = st.q.trim() && (st.f || st.viti || extra.some(k=>st[k]));
      html = `<div class="empty"><b>${c.none}</b><span class="muted">Provoni një fjalë tjetër ose hiqni disa filtra.</span><div class="hero-ctas">${wide?`<button class="btn btn-line btn-sm" type="button" data-clear="keep-q">Kërko “${esc(st.q.trim())}” kudo</button>`:''}<button class="btn btn-ink btn-sm" type="button" data-clear="all">Hiq filtrat</button></div></div>`;
    }
    results.innerHTML = html;
    // Keep the view shareable without re-rendering the page.
    const params = new URLSearchParams();
    if (st.q.trim()) params.set('q', st.q.trim());
    if (st.f) params.set(c.facet, st.f);
    extra.concat(['viti','rendit']).forEach(k=>{ if (st[k]) params.set(k, st[k]); });
    const qs = params.toString();
    history.replaceState(null, '', `#/${kind}${qs?'?'+qs:''}`);
  }
  function clear(k){
    if (k==='all' || k==='keep-q') { st.f=''; st.viti=''; st.rendit=''; extra.forEach(x=>{ st[x]=''; }); if (k==='all') st.q=''; }
    else if (k==='f') st.f=''; else st[k]='';
    input.value = st.q;
    $$('select', form).forEach(s=>{ s.value = st[s.name]; });
    draw();
    const next = $('.tag', active) || input; next.focus();
  }
  let t;
  input.addEventListener('input', ()=>{ clearTimeout(t); t=setTimeout(()=>{ st.q=input.value; draw(); }, 150); });
  form.addEventListener('submit', e=>{ e.preventDefault(); st.q=input.value; draw(); });
  form.addEventListener('change', e=>{ if (e.target.tagName==='SELECT') { st[e.target.name]=e.target.value; draw(); } });
  form.addEventListener('click', e=>{ const b=e.target.closest('[data-facet]'); if (b) { st.f=b.dataset.facet; draw(); } });
  active.addEventListener('click', e=>{ const b=e.target.closest('[data-clear]'); if (b) clear(b.dataset.clear); });
  results.addEventListener('click', e=>{ const b=e.target.closest('[data-clear]'); if (b) clear(b.dataset.clear); });
  draw();
  scrollFade($('.chips', form));
}

function newsPage(q){
  return pageHead('Lajme dhe qëndrime','Lajme dhe qëndrime','Aktivitetet, komunikatat, reagimet dhe qëndrimet tona publike për tenderët, vendimet dhe institucionet që monitorojmë.') + `
  <section class="wrap">${filtersHTML('lajme', filterState('lajme', q))}</section>`;
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
      <nav class="crumbs" aria-label="Gjurma"><a href="#/lajme">Lajme dhe qëndrime</a><span aria-hidden="true">/</span><a href="#/lajme?kategoria=${n.cat}">${CATS[n.cat]}</a></nav>
      <h1 data-focus>${n.title}</h1>
      <div class="art-meta"><span class="pill">${CAT1[n.cat]}</span><time datetime="${n.iso}">${n.date}</time><span>${mins} min lexim</span></div>
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
  return pageHead('Hulumtime dhe publikime','Hulumtime dhe publikime','Raporte monitorimi, hulumtime, analiza të politikave publike dhe analiza ligjore, infografika dhe rekomandime për institucionet. Të gjitha falas, në PDF.') + `
  <section class="wrap">${filtersHTML('publikimet', filterState('publikimet', q))}</section>`;
}

// An infographic's images: a row to swipe, arrows, a counter and a full-screen view (slides.js).
const SLIDE_ARROW = d => `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="${d}"/></svg>`;
function slidesHTML(p){
  const n = p.slides.length, pad = x => String(x).padStart(2,'0');
  return `<section class="wrap sec slides-sec" aria-labelledby="h-slides" data-slides>
    <div class="pub-head"><h2 class="h2" id="h-slides">Infografika</h2><div class="slides-nav"><span class="slides-count" aria-hidden="true">01 / ${pad(n)}</span><button class="icon-btn" type="button" data-slides-go="-1" aria-label="Pamja e mëparshme">${SLIDE_ARROW('m15 18-6-6 6-6')}</button><button class="icon-btn" type="button" data-slides-go="1" aria-label="Pamja tjetër">${SLIDE_ARROW('m9 18 6-6-6-6')}</button></div></div>
    <div class="slides" role="list">${p.slides.map(([f,alt],i)=>`<figure class="slide" role="listitem"><a href="assets/img/infografika/${f}" aria-label="Hap në ekran të plotë: pamja ${i+1} nga ${n}"><img src="assets/img/infografika/${f}" alt="${alt}" width="1400" height="1400" loading="${i<2?'eager':'lazy'}" decoding="async"></a></figure>`).join('')}</div>
  </section>`;
}
// An infographic's numbers on the page: key figures, then each chart (interactive, with a table and CSV).
function pubData(p){
  const d = p.data;
  return `<section class="wrap sec" aria-labelledby="h-data">
    <div class="grid12 sec-head"><span class="kicker">Infografika në numra</span><h2 class="h2" id="h-data">Të dhënat e infografikës</h2></div>
    ${d.figures ? `<div class="pub-figures">${impactGrid(d.figures).replace('impact-grid','impact-grid plain')}</div>` : ''}
    ${d.charts.map((c,k)=>`<div class="pub-chart"><h3 class="sub-h">${c.title}</h3><div class="dataset-view" data-dataset='${esc(JSON.stringify({ cols:c.cols, rows:c.rows, chart:c.chart, unit:c.unit, title:c.title, file:p.id+'-'+(k+1) }))}'></div></div>`).join('')}
    ${d.source ? `<p class="fineprint">Burimi: ${d.source}</p>` : ''}
  </section>`;
}
function pubDetail(id){
  const i = PUBS.findIndex(p=>p.id===id); if(i<0) return notFound();
  const p = PUBS[i];
  const more = PUBS.filter(x=>x.id!==id && x.type===p.type).concat(PUBS.filter(x=>x.id!==id && x.type!==p.type)).slice(0,3);
  const fact = (k, v, link) => `<div><dt>${k}</dt><dd>${link?`<a href="${link}">${v}</a>`:v}</dd></div>`;
  return `
  <section class="wrap grid12 pd">
    ${cover(p,i)}
    <div class="pd-info">
      <nav class="crumbs" aria-label="Gjurma"><a href="#/publikimet">Hulumtime dhe publikime</a><span aria-hidden="true">/</span><a href="#/publikimet?lloji=${p.type}">${PFILTERS.find(f=>f[0]===p.type)[1]}</a></nav>
      <h1 data-focus>${p.title}</h1>
      <dl class="facts">
        ${fact('Kategoria', PTYPES[p.type])}
        ${fact('Data e publikimit', p.date ? `<time datetime="${isoOf(p.date)}">${p.date}</time>` : '[Data]')}
        ${fact('Autori ose ekipi', p.authors || '[Autori ose ekipi hulumtues]')}
        ${fact('Programi', p.prog ? PROGS[p.prog].name : 'INPO', p.prog ? `#/programet/${p.prog}` : '')}
        ${fact('Fusha tematike', p.theme ? THEMES[p.theme] : '[Fusha tematike]', p.theme ? `#/publikimet?fusha=${p.theme}` : '')}
        ${p.inst ? fact('Institucioni', INSTS[p.inst], `#/publikimet?institucioni=${p.inst}`) : (p.partners ? fact('Partnerë', p.partners) : fact('Institucioni', 'Disa institucione'))}
      </dl>
      <p class="lead">Përmbledhja e shkurtër e publikimit shfaqet këtu: pyetja që trajton, gjetjet kryesore dhe rekomandimet për institucionet.</p>
      <div class="hero-ctas"><a class="btn btn-accent" href="#" data-noop data-toast="Shkarkimi lidhet me skedarin PDF gjatë migrimit.">${DL} Shkarko PDF</a><button class="btn btn-line" type="button" data-copy>Kopjo linkun</button></div>
      ${p.donor?`<p class="muted" style="margin:0;font-size:14px">Me mbështetjen e ${p.donor}.</p>`:''}
    </div>
  </section>
  ${p.slides ? slidesHTML(p) : ''}
  ${p.data ? pubData(p) : ''}
  <section class="wrap sec">
    <div class="pub-head"><h2 class="h2">Publikime të ngjashme</h2><a class="btn btn-line btn-sm" href="#/publikimet">Të gjitha publikimet</a></div>
    <div class="pubs three">${more.map(x=>pubCard(x,PUBS.indexOf(x))).join('')}</div>
  </section>`;
}

function progPage(id){
  const p = PROGS[id]; if(!p) return notFound();
  const pubs = PUBS.filter(x=>x.prog===id);
  const projs = PROJECTS.filter(x=>x.prog===id);
  const other = id==='qeverisje'?'edukim':'qeverisje';
  // Results: counted from the content linked to the programme, plus figures INPO adds.
  const results = [[String(pubs.length),'hulumtime dhe publikime'],[String(projs.length),'projekte'],['[N]','rekomandime për institucionet']];
  return `
  <section class="wrap page-head" style="display:block">
    <nav class="crumbs" aria-label="Gjurma" style="margin-bottom:22px"><span>Programet</span><span aria-hidden="true">/</span><span>${p.name}</span></nav>
    <h1 class="h-page" data-focus>${p.name}</h1>
  </section>
  <section class="wrap"><div class="prog-hero">${rings(700,4)}<span class="prog-ico">${ICO[id]}</span><p>${p.desc}</p></div></section>
  <section class="wrap sec">
    <div class="grid12 sec-head"><span class="kicker">Objektivat</span><h2 class="h2">Çfarë synon programi</h2></div>
    <ol class="goals">${p.goals.map((g,i)=>`<li><span>${String(i+1).padStart(2,'0')}</span>${g}</li>`).join('')}</ol>
  </section>
  <section class="wrap sec">
    <div class="grid12 sec-head"><span class="kicker">Fushat e ndërhyrjes</span><h2 class="h2">Ku ndërhyjmë</h2></div>
    <div class="ways">${p.ways.map(w=>`<div class="way"><h3>${w[0]}</h3><p>${w[1]}</p></div>`).join('')}</div>
  </section>
  <section class="wrap sec">
    <div class="grid12 sec-head"><span class="kicker">Aktivitetet</span><h2 class="h2">Aktivitetet kryesore</h2></div>
    <ol class="timeline acts">${p.acts.map((a,i)=>`<li><span class="yr">${String(i+1).padStart(2,'0')}</span><div><p>${a}</p></div></li>`).join('')}</ol>
  </section>
  ${pubs.length?`<section class="wrap sec"><div class="pub-head"><div class="pub-head-l"><span class="kicker">Hulumtimet</span><h2 class="h2">Hulumtime dhe publikime nga programi</h2></div><a class="btn btn-line btn-sm" href="#/publikimet?programi=${id}">Të gjitha (${pubs.length})</a></div><div class="pubs three">${pubs.slice(0,3).map(x=>pubCard(x,PUBS.indexOf(x))).join('')}</div></section>`:''}
  <section class="wrap sec">
    <div class="impact light"><div class="impact-head"><span class="kicker">Rezultatet</span><h2 class="h2">Rezultatet e programit</h2></div>${impactGrid(results)}</div>
  </section>
  ${id==='qeverisje'?`<section class="wrap sec"><div class="pub-head"><h2 class="h2">Lajme dhe reagime nga programi</h2><a class="btn btn-line btn-sm" href="#/lajme">Të gjitha lajmet</a></div><div>${NEWS.slice(0,3).map(newsRow).join('')}</div></section>`:''}
  <section class="wrap sec"><a class="prog prog-next" href="#/programet/${other}"><div><span class="muted" style="font-size:14px">Programi tjetër</span><h3 style="margin:6px 0 0">${PROGS[other].name}</h3></div><span class="arrow-circle">${ARROW}</span></a></section>`;
}

const ABOUT_SECTIONS = [['historia','Historia e organizatës'],['misioni','Misioni, vizioni dhe vlerat'],['ekipi','Ekipi dhe bordi'],['dokumentet','Strategjitë dhe dokumentet organizative']];
function aboutPage(){
  const strat = DOCS.filter(d=>d.cat==='strategjite' || d.cat==='qeverisja');
  return pageHead('Rreth nesh','Rreth nesh','Organizatë joqeveritare dhe jofitimprurëse, e themeluar në vitin 2005. Politikat publike janë fokusi i punës sonë.') + `
  <section class="wrap">${subnav('Seksionet e faqes', ABOUT_SECTIONS, 'rreth-nesh')}</section>
  <section class="wrap sec anchor" id="historia" aria-labelledby="h-historia">
    <div class="grid12 sec-head"><span class="kicker">Historia e organizatës</span><h2 class="h2" id="h-historia">Nga performancat protestuese te auditimi i tenderëve.</h2></div>
    <div class="grid12 about-text"><span class="kicker">Kush jemi</span><div class="prose">${D.ABOUT.map(t=>`<p>${t}</p>`).join('')}</div></div>
    <ol class="timeline">${D.HISTORY.map(t=>`<li><span class="yr">${t[0]}</span><div><h3>${t[1]}</h3><p>${t[2]}</p></div></li>`).join('')}</ol>
  </section>
  <section class="wrap sec anchor" id="misioni" aria-labelledby="h-misioni">
    <div class="grid12 sec-head"><span class="kicker">Misioni, vizioni dhe vlerat</span><h2 class="h2" id="h-misioni">Pse ekzistojmë dhe si punojmë.</h2></div>
    <div class="mv">
      <div class="m">${rings(560,4)}<h3>Misioni</h3><p>${D.MISSION}</p></div>
      <div class="v"><h3>Vizioni</h3><p>${D.VISION}</p></div>
    </div>
    <h3 class="sub-h">Vlerat që promovon organizata</h3>
    <div class="ways">${D.VALUES.map(v=>`<div class="way"><h4>${v[0]}</h4><p>${v[1]}</p></div>`).join('')}</div>
  </section>
  <section class="wrap sec anchor" id="ekipi" aria-labelledby="h-ekipi">
    <div class="grid12 sec-head"><span class="kicker">Ekipi dhe bordi</span><h2 class="h2" id="h-ekipi">Njerëzit pas punës sonë.</h2></div>
    <h3 class="sub-h">Ekipi</h3><div class="team">${TEAM.map(member).join('')}</div>
    <h3 class="sub-h">Bordi</h3>${boardList(BOARD)}
  </section>
  <section class="wrap sec anchor" id="dokumentet" aria-labelledby="h-dokumentet">
    <div class="grid12 sec-head"><span class="kicker">Dokumentet</span><h2 class="h2" id="h-dokumentet">Strategjitë dhe dokumentet organizative</h2></div>
    <div class="duo">
      <div class="box box-pad"><h3 class="box-h">Strategjitë dhe dokumentet</h3><p class="muted" style="margin:0">Plani strategjik, statuti dhe dokumentet që rregullojnë punën e organizatës.</p>
        <div class="doc-list">${strat.length ? strat.map(d=>`<a href="#" data-noop>${d.title} <span class="arrow-circle">${DL}</span></a>`).join('') : '<p class="muted doc-empty">[Plani strategjik dhe statuti shtohen këtu.]</p>'}<a href="#/transparenca">Të gjitha dokumentet në Transparenca <span class="arrow-circle">${ARROW}</span></a></div></div>
      <div class="box box-pad soft"><h3 class="box-h">Mundësi punësimi</h3><p class="muted" style="margin:0">Pozitat dhe thirrjet e hapura publikohen këtu.</p><a class="btn btn-ink btn-sm" style="align-self:flex-start;margin-top:auto" href="#/kontakti">Na kontaktoni</a></div>
    </div>
  </section>`;
}

const PROJ_SECTIONS = [['ndikimi','Rezultatet dhe ndikimi institucional'],['partneret','Partnerët dhe donatorët']];
function projectsPage(q){
  const f = q.statusi==='aktive' ? 'aktiv' : q.statusi==='perfunduara' ? 'perfunduar' : '';
  const list = PROJECTS.filter(p=>!f || p.status===f);
  const n = s => PROJECTS.filter(p=>!s || p.status===s).length;
  const chip = (v, s, l) => `<a class="chip${f===s?' on':''}" href="#/projektet${v?'?statusi='+v:''}"${f===s?' aria-current="true"':''}>${l}<span class="chip-n">${n(s)}</span></a>`;
  const cases = CASES.map(id=>NEWS.find(x=>x.id===id)).filter(Boolean);
  const results = PROJECTS.flatMap(p=>p.results.map(r=>[r[0], r[1], p]));
  return pageHead('Projektet dhe ndikimi','Projektet dhe ndikimi','Projektet që zbatojmë me partnerët dhe donatorët tanë, dhe ndryshimet që kanë sjellë në institucione.') + `
  <section class="wrap">${subnav('Seksionet e faqes', PROJ_SECTIONS, 'projektet')}</section>
  <section class="wrap anchor" id="projektet" aria-labelledby="h-projektet">
    <h2 class="sr" id="h-projektet">Projektet</h2>
    <div class="filters"><div class="chips" role="group" aria-label="Filtro sipas statusit">${chip('','','Të gjitha')}${chip('aktive','aktiv','Projektet aktive')}${chip('perfunduara','perfunduar','Projektet e përfunduara')}</div></div>
    <div class="results"><div class="projs">${list.map(projCard).join('')}</div></div>
  </section>
  <section class="wrap sec anchor" id="ndikimi" aria-labelledby="h-ndikimi">
    <div class="impact">${rings(640,4)}
      <div class="impact-head"><span class="kicker">Rezultatet dhe ndikimi institucional</span><h2 class="h2" id="h-ndikimi">Ndikimi i punës sonë, në numra.</h2></div>
      ${impactGrid(IMPACT)}
    </div>
    <div class="grid12 cases">
      <div class="cases-intro"><span class="kicker">Shembuj konkretë</span><h3 class="sub-h">Kur institucionet veprojnë pas INPO-s</h3><p class="muted">Raste ku një institucion ndryshoi vendim, publikoi dokumente ose rishikoi një tender pas monitorimit dhe reagimit tonë.</p></div>
      <div class="cases-list">${cases.map(newsRow).join('')}${results.map(([num,label,p])=>`<a class="row" href="#/projektet/${p.id}"><div class="row-meta"><span class="case-num">${num}</span><span>Projekt</span></div><span class="row-title">${label[0].toUpperCase()+label.slice(1)}. ${p.title}</span><span class="arrow-circle">${ARROW}</span></a>`).join('')}</div>
    </div>
  </section>
  <section class="wrap sec anchor" id="partneret" aria-labelledby="h-partneret">
    <div class="grid12 sec-head"><span class="kicker">Partnerët dhe donatorët</span><h2 class="h2" id="h-partneret">Me kë punojmë.</h2></div>
    ${donorsHTML()}
  </section>`;
}
function projectPage(id){
  const p = PROJECTS.find(x=>x.id===id); if(!p) return notFound();
  const pub = p.pub ? PUBS.find(x=>x.id===p.pub) : null;
  const others = PROJECTS.filter(x=>x.id!==id).slice(0,3);
  return `
  <section class="wrap grid12 page-head">
    <div class="ph-l"><nav class="crumbs" aria-label="Gjurma"><a href="#/projektet">Projektet dhe ndikimi</a><span aria-hidden="true">/</span><a href="#/projektet?statusi=${p.status==='aktiv'?'aktive':'perfunduara'}">${p.status==='aktiv'?'Projektet aktive':'Projektet e përfunduara'}</a></nav>
      <h1 class="h-headline" data-focus>${p.title}</h1></div>
    <div class="ph-r"><span class="pill${p.status==='aktiv'?' on':''}">${projStatus(p)}</span></div>
  </section>
  <section class="wrap grid12 proj-body">
    <dl class="facts proj-facts">
      <div><dt>Periudha e zbatimit</dt><dd>${p.period}</dd></div>
      <div><dt>Donatori</dt><dd>${p.donor}</dd></div>
      <div><dt>Partnerët</dt><dd>${p.partners}</dd></div>
      <div><dt>Programi</dt><dd><a href="#/programet/${p.prog}">${PROGS[p.prog].name}</a></dd></div>
    </dl>
    <div class="proj-main">
      <h2 class="sub-h">Objektivat</h2>
      <ol class="goals one">${p.goals.map((g,i)=>`<li><span>${String(i+1).padStart(2,'0')}</span>${g}</li>`).join('')}</ol>
      <h2 class="sub-h">Rezultatet</h2>
      ${p.results.length ? `<dl class="impact-grid plain">${p.results.map(([n,l])=>`<div><dt>${l}</dt><dd>${n}</dd></div>`).join('')}</dl>` : '<p class="muted">[Rezultatet e matshme të projektit shtohen këtu.]</p>'}
      ${pub?`<h2 class="sub-h">Publikimi i projektit</h2><div class="pubs three">${pubCard(pub, PUBS.indexOf(pub))}</div>`:''}
    </div>
  </section>
  <section class="wrap sec"><div class="pub-head"><h2 class="h2">Projekte të tjera</h2><a class="btn btn-line btn-sm" href="#/projektet">Të gjitha projektet</a></div><div class="projs">${others.map(projCard).join('')}</div></section>`;
}

function transparencyPage(){
  const years = [...new Set(DOCS.map(d=>d.year))].sort().reverse();
  // Categories without documents go on one "coming soon" line (their anchors stay, for menu links).
  const full = DCATS.filter(([k])=>DOCS.some(d=>d.cat===k)), soon = DCATS.filter(([k])=>!DOCS.some(d=>d.cat===k));
  return pageHead('Transparenca','Transparenca','Raportet tona vjetore, financiare dhe të auditimit, si dhe dokumentet që rregullojnë punën e organizatës, sipas kategorisë dhe vitit.') + `
  <section class="wrap">
    <div class="filters docs-filters">
      ${subnav('Kategoritë e dokumenteve', full, 'transparenca')}
      ${selPill('docs-year','viti','Viti',[['','Të gjitha']].concat(years.map(y=>[y,y])),'')}
    </div>
    ${full.map(([k,l])=>{ const rows = DOCS.filter(d=>d.cat===k); return `<section class="docs-group anchor" id="${k}" aria-labelledby="h-${k}"><h2 class="docs-h" id="h-${k}">${l}<span class="chip-n">${rows.length}</span></h2><div class="doc-rows">${rows.map(docRow).join('')}</div><p class="muted doc-empty" hidden>Asnjë dokument për këtë vit.</p></section>`; }).join('')}
    ${soon.length?`<p class="docs-soon"><b>Së shpejti</b> ${soon.map(([k,l])=>`<span class="anchor" id="${k}">${l}</span>`).join('<span aria-hidden="true"> · </span>')}</p>`:''}
  </section>`;
}
function bindTransparency(){
  const sel = $('#docs-year');
  sel.addEventListener('change', () => {
    const y = sel.value;
    $('.sel-v', sel.parentNode).textContent = sel.options[sel.selectedIndex].text;
    sel.parentNode.classList.toggle('is-set', !!y);
    $$('.docs-group').forEach(g => {
      const rows = $$('.doc-row', g); let shown = 0;
      rows.forEach(r => { const on = !y || r.dataset.year===y; r.hidden = !on; shown += on ? 1 : 0; });
      $('.chip-n', g).textContent = shown;
      $('.doc-empty', g).hidden = shown > 0;
    });
  });
}

function dataPage(){
  return pageHead('Hulumtime dhe publikime','Të dhëna dhe statistika','Statistika të prokurimit publik nga monitorimi ynë, në grafikë interaktivë dhe tabela që mund t’i shkarkoni.') + `
  <section class="wrap">
    <p class="notice">Të dhëna ilustruese: vlerat këtu tregojnë si funksionojnë grafikët. INPO i zëvendëson me statistikat e veta të prokurimit.</p>
    <div class="datasets">${DATASETS.map(d=>`<a class="dataset" href="#/te-dhenat/${d.id}"><span class="pill">${d.chart==='line'?'Grafik me vija':d.chart==='table'?'Tabelë':'Grafik me shtylla'}</span><h2>${d.title}</h2><p>${d.lead}</p><span class="link-u">Hap të dhënat ${ARROW}</span></a>`).join('')}</div>
  </section>`;
}
function datasetPage(id){
  const d = DATASETS.find(x=>x.id===id); if(!d) return notFound();
  return `
  <section class="wrap grid12 page-head">
    <div class="ph-l"><nav class="crumbs" aria-label="Gjurma"><a href="#/publikimet">Hulumtime dhe publikime</a><span aria-hidden="true">/</span><a href="#/te-dhenat">Të dhëna dhe statistika</a></nav><h1 class="h-headline" data-focus>${d.title}</h1></div>
    <div class="ph-r"><p class="lead">${d.lead}</p></div>
  </section>
  <section class="wrap">
    ${d.sample?'<p class="notice">Të dhëna ilustruese: INPO i zëvendëson me statistikat e veta.</p>':''}
    <div class="dataset-view" data-dataset='${esc(JSON.stringify({ cols:d.cols, rows:d.rows, chart:d.chart, unit:d.unit, title:d.title, file:d.id }))}'></div>
  </section>`;
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
  else if (a==='lajme' && !b) { html = newsPage(q); title='Lajme dhe qëndrime · INPO'; after=()=>bindFilters('lajme', q); }
  else if (a==='lajme') { html = articlePage(b); const n=NEWS.find(x=>x.id===b); title=(n?n.title.slice(0,60)+'… ':'')+'· INPO'; }
  else if (a==='publikimet' && !b) { html = pubsPage(q); title='Hulumtime dhe publikime · INPO'; after=()=>bindFilters('publikimet', q); }
  else if (a==='publikimet') { html = pubDetail(b); title='Publikim · INPO'; after=()=>{ $$('[data-dataset]').forEach(el=>window.inpoCharts && window.inpoCharts.mount(el)); $$('[data-slides]').forEach(el=>window.inpoSlides && window.inpoSlides.mount(el)); }; }
  else if (a==='te-dhenat') { html = b ? datasetPage(b) : dataPage(); title='Të dhëna dhe statistika · INPO'; section='publikimet'; after=()=>$$('[data-dataset]').forEach(el=>window.inpoCharts && window.inpoCharts.mount(el)); }
  else if (a==='programet') { html = progPage(b); title=(PROGS[b]?PROGS[b].full:'Programet')+' · INPO'; }
  else if (a==='projektet') { html = b ? projectPage(b) : projectsPage(q); title='Projektet dhe ndikimi · INPO'; }
  else if (a==='transparenca') { html = transparencyPage(); title='Transparenca · INPO'; after=bindTransparency; }
  else if (a==='rreth-nesh') { html = aboutPage(); title='Rreth nesh · INPO'; }
  else if (a==='kontakti') { html = contactPage(q); title='Kontakti · INPO'; after=bindContact; }
  else { html = notFound(); section='404'; }
  app.innerHTML = html;
  document.title = title;
  $$('[data-nav]').forEach(l => {
    const on = l.dataset.nav===section;
    if (l.tagName==='A') { if (on) l.setAttribute('aria-current','page'); else l.removeAttribute('aria-current'); }
    else l.classList.toggle('current', on);
  });
  closeDD();
  if (menu.open) menu.close();
  if (after) after();
  // ?seksioni=x jumps to a section of the page (the nav's sub-items) and focuses its heading.
  const target = q.seksioni && document.getElementById(q.seksioni);
  if (target) {
    const h = $('h2', target) || target;
    h.setAttribute('tabindex','-1');
    target.scrollIntoView({block:'start'});
    h.focus({preventScroll:true});
  } else if (!firstRender) { window.scrollTo(0,0); const h=$('[data-focus]',app); if(h){h.setAttribute('tabindex','-1');h.focus({preventScroll:true});} }
  if (!a) heroIntro(firstRender);
  firstRender = false;
}

/* ---------- Hero intro + count-up ---------- */
const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
function heroIntro(){
  document.body.classList.remove('loaded');
  requestAnimationFrame(()=>requestAnimationFrame(()=>document.body.classList.add('loaded')));
  $$('.bento [data-count]', app).forEach(el => {
    if (reduce) return;
    const to=parseFloat(el.dataset.count), dec=+(el.dataset.dec||0), pre=el.dataset.prefix||'', suf=el.dataset.suffix||'';
    const fmt = v => pre + v.toFixed(dec).replace('.',',') + suf;
    const start = performance.now()+550, dur=1400;
    el.textContent = fmt(0);
    const tick = now => { const t=Math.min(1,Math.max(0,(now-start)/dur)); const e=1-Math.pow(1-t,4); el.textContent=fmt(to*e); if(t<1) requestAnimationFrame(tick); };
    requestAnimationFrame(tick);
  });
}

/* ---------- UI: dropdowns, dialogs, search, theme, forms ---------- */
const dds = $$('.nav .dd');
function closeDD(except){ dds.forEach(dd => { if (dd===except) return; dd.classList.remove('open'); $('button',dd).setAttribute('aria-expanded','false'); }); }
dds.forEach(dd => {
  const btn = $('button', dd);
  btn.addEventListener('click', e => { e.stopPropagation(); const o=!dd.classList.contains('open'); closeDD(dd); dd.classList.toggle('open',o); btn.setAttribute('aria-expanded',String(o)); });
  dd.addEventListener('focusout', e => { if (e.relatedTarget && !dd.contains(e.relatedTarget)) closeDD(); });
});
document.addEventListener('click', e => { if(!e.target.closest('.dd')) closeDD(); });

const menu = $('#menu'), search = $('#search');
$('#open-menu').addEventListener('click', ()=>{ menu.showModal(); $('#open-menu').setAttribute('aria-expanded','true'); });
menu.addEventListener('close', ()=>$('#open-menu').setAttribute('aria-expanded','false'));
$$('[data-close]').forEach(b=>b.addEventListener('click',()=>b.closest('dialog').close()));
menu.addEventListener('click', e=>{ if (e.target.closest('.menu-links a, .menu-top .logo')) menu.close(); });

function openSearch(){ search.showModal(); const i=$('#q-global'); i.value=''; drawSearch(''); i.focus(); }
function drawSearch(v){
  const res = $('#search-res'), n = norm(v.trim());
  if (!n) { res.innerHTML = `<div class="search-empty">Shkruani një fjalë, p.sh. <b>Vushtrri</b>, <b>OShP</b> ose <b>stadiume</b>.</div>`; return; }
  const hits = NEWS.filter(x=>norm(x.title+' '+x.excerpt).includes(n)).map(x=>({h:'#/lajme/'+x.id,t:x.title,k:CAT1[x.cat]}))
    .concat(PUBS.filter(p=>norm(p.title+' '+PTYPES[p.type]).includes(n)).map(p=>({h:'#/publikimet/'+p.id,t:p.title,k:PTYPES[p.type]})))
    .concat(PROJECTS.filter(p=>norm(p.title+' '+p.donor).includes(n)).map(p=>({h:'#/projektet/'+p.id,t:p.title,k:'Projekt'})))
    .concat(DOCS.filter(d=>norm(d.title).includes(n)).map(d=>({h:'#/transparenca?seksioni='+d.cat,t:d.title,k:'Dokument'})));
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

/* Header border on scroll */
const hdr = $('#hdr');
const toTop = $('#to-top');
const onScroll = () => { hdr.classList.toggle('scrolled', scrollY>8); toTop.classList.toggle('show', scrollY > innerHeight * .8); };
addEventListener('scroll', onScroll, {passive:true}); onScroll();
$('#yr').textContent = new Date().getFullYear();

addEventListener('hashchange', render);
render();
})();
