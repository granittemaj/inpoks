/* INPO theme UI: dropdown, mobile menu, search overlay, colour theme, toast,
   copy/scroll helpers and the home load sequence (the one orchestrated moment).
   Ported from the prototype's app.js. No dependencies. */
(function () {
  'use strict';
  var cfg = window.inpoTheme || {};
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var esc = function (s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); };
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var root = document.documentElement;

  /* Toast (also used by INPO Core) */
  var tt;
  function toast(msg) {
    var t = $('#toast');
    if (!t) return;
    t.textContent = msg;
    t.classList.add('show');
    clearTimeout(tt);
    tt = setTimeout(function () { t.classList.remove('show'); }, 2600);
  }
  window.inpoToast = toast;

  /* Dropdowns */
  var dds = $$('.nav .dd');
  function closeDD(except) {
    dds.forEach(function (dd) {
      if (dd === except) return;
      dd.classList.remove('open');
      $('button', dd).setAttribute('aria-expanded', 'false');
    });
  }
  dds.forEach(function (dd) {
    var btn = $('button', dd);
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      var open = !dd.classList.contains('open');
      closeDD(dd);
      dd.classList.toggle('open', open);
      btn.setAttribute('aria-expanded', String(open));
    });
    dd.addEventListener('focusout', function (e) { if (!dd.contains(e.relatedTarget)) closeDD(); });
  });
  document.addEventListener('click', function (e) { if (!e.target.closest('.dd')) closeDD(); });

  /* Mobile menu */
  var menu = $('#menu'), menuBtn = $('#open-menu');
  if (menu && menuBtn && menu.showModal) {
    menuBtn.addEventListener('click', function () { menu.showModal(); menuBtn.setAttribute('aria-expanded', 'true'); });
    menu.addEventListener('close', function () { menuBtn.setAttribute('aria-expanded', 'false'); });
    $$('.menu-links a, .logo', menu).forEach(function (a) { a.addEventListener('click', function () { menu.close(); }); });
  }
  $$('[data-close]').forEach(function (b) { b.addEventListener('click', function () { b.closest('dialog').close(); }); });

  /* Search overlay: INPO Core endpoint when available, core search otherwise */
  var search = $('#search'), input = $('#q-global'), res = $('#search-res');
  var timer, ctrl;
  function empty(html) { res.innerHTML = '<div class="search-empty">' + html + '</div>'; }
  function draw(v) {
    var q = v.trim();
    clearTimeout(timer);
    if (q.length < 2) { empty('Shkruani një fjalë, p.sh. <b>Vushtrri</b>, <b>OShP</b> ose <b>stadiume</b>.'); return; }
    timer = setTimeout(function () {
      if (ctrl) ctrl.abort();
      ctrl = window.AbortController ? new AbortController() : null;
      var url = cfg.hasPlugin
        ? cfg.search + (cfg.search.indexOf('?') < 0 ? '?' : '&') + 'q=' + encodeURIComponent(q)
        : cfg.coreSearch + (cfg.coreSearch.indexOf('?') < 0 ? '?' : '&') + 'per_page=8&search=' + encodeURIComponent(q);
      fetch(url, { signal: ctrl ? ctrl.signal : undefined })
        .then(function (r) { return r.json(); })
        .then(function (items) {
          if (!Array.isArray(items) || !items.length) { empty('Asnjë rezultat për “' + esc(q) + '”. Provoni një fjalë tjetër.'); return; }
          res.innerHTML = items.slice(0, 8).map(function (x) {
            var kind = x.kind || (x.subtype === 'post' ? 'Lajm' : x.subtype === 'publikimet' ? 'Publikim' : 'Faqe');
            return '<a href="' + esc(x.url) + '"><small>' + esc(kind) + '</small><span>' + esc(x.title) + '</span></a>';
          }).join('');
        })
        .catch(function (err) { if (!err || err.name !== 'AbortError') empty('Kërkimi nuk u krye. Shtypni Enter për faqen e rezultateve.'); });
    }, 160);
  }
  function openSearch() {
    if (!search || !search.showModal) { location.href = (cfg.home || '/') + '?s='; return; }
    search.showModal();
    input.value = '';
    draw('');
    input.focus();
  }
  if (search) {
    $('#open-search').addEventListener('click', openSearch);
    input.addEventListener('input', function () { draw(input.value); });
    res.addEventListener('click', function (e) { if (e.target.closest('a')) search.close(); });
    search.addEventListener('click', function (e) { if (e.target === search) search.close(); });
  }
  document.addEventListener('keydown', function (e) {
    var tag = document.activeElement && document.activeElement.tagName;
    if (e.key === '/' && !/INPUT|TEXTAREA|SELECT/.test(tag) && !(document.activeElement && document.activeElement.isContentEditable) && search && !search.open) { e.preventDefault(); openSearch(); }
    if (e.key === 'Escape') closeDD();
  });

  /* Colour theme toggle */
  var themeBtn = $('#theme');
  if (themeBtn) {
    themeBtn.addEventListener('click', function () {
      var cur = root.dataset.theme || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
      var next = cur === 'dark' ? 'light' : 'dark';
      root.dataset.theme = next;
      try { localStorage.setItem('inpo-theme', next); } catch (e) {}
      toast(next === 'dark' ? 'Tema e errët u aktivizua.' : 'Tema e çelët u aktivizua.');
    });
  }

  /* Global delegation: placeholders, smooth scroll, copy link, open search */
  document.addEventListener('click', function (e) {
    var noop = e.target.closest('[data-noop]');
    if (noop) { e.preventDefault(); if (noop.dataset.toast) toast(noop.dataset.toast); }
    var sc = e.target.closest('[data-scroll]');
    if (sc) {
      var target = document.getElementById(sc.dataset.scroll);
      if (target) {
        if (menu && menu.open) menu.close();
        target.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'center' });
        var field = $('input[type=email]', target);
        if (field) setTimeout(function () { field.focus({ preventScroll: true }); }, reduce ? 0 : 600);
      }
    }
    if (e.target.closest('[data-copy]')) {
      var url = location.href.split('#')[0];
      (navigator.clipboard ? navigator.clipboard.writeText(url) : Promise.reject())
        .then(function () { toast('Linku u kopjua.'); })
        .catch(function () { toast('Kopjimi nuk u lejua. Kopjoni linkun nga shiriti i adresës.'); });
    }
    if (e.target.closest('[data-open-search]')) openSearch();
  });

  /* Header border on scroll */
  var hdr = $('#hdr');
  if (hdr) {
    var onScroll = function () { hdr.classList.toggle('scrolled', window.scrollY > 8); };
    addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  /* Home load sequence: headline reveal, ring draw, count-up */
  if ($('.hero') || $('.bento')) {
    requestAnimationFrame(function () { requestAnimationFrame(function () { document.body.classList.add('loaded'); }); });
    if (!reduce) {
      $$('[data-count]').forEach(function (el) {
        var to = parseFloat(el.dataset.count), dec = +(el.dataset.dec || 0), pre = el.dataset.prefix || '', suf = el.dataset.suffix || '';
        if (isNaN(to)) return;
        var fmt = function (v) { return pre + v.toFixed(dec).replace('.', ',') + suf; };
        var start = performance.now() + 550, dur = 1400;
        el.textContent = fmt(0);
        var tick = function (now) {
          var t = Math.min(1, Math.max(0, (now - start) / dur)), k = 1 - Math.pow(1 - t, 4);
          el.textContent = fmt(to * k);
          if (t < 1) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
      });
    }
  } else {
    document.body.classList.add('loaded');
  }

  /* No-JS form results land on #kontakt / #newsletter: move focus to the message */
  var sent = $('.sent') || $('.nl-done');
  if (sent && /inpo_(contact|nl)=/.test(location.search)) { sent.setAttribute('tabindex', '-1'); sent.focus({ preventScroll: true }); }
})();
