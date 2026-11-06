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
    if (e.target.closest('[data-top]')) {
      window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
      var logo = $('.site-header .logo');
      if (logo) logo.focus({ preventScroll: true });
    }
  });

  /* Header border on scroll */
  var hdr = $('#hdr');
  if (hdr) {
    var toTop = $('#to-top');
    var onScroll = function () {
      hdr.classList.toggle('scrolled', window.scrollY > 8);
      if (toTop) toTop.classList.toggle('show', window.scrollY > window.innerHeight * 0.8);
    };
    addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  /* Rolling digits: each digit spins through 0 to 9 and stops on its value (the figures in the hero). */
  function odometer(el, delay) {
    var text = el.getAttribute('data-text') || el.textContent.trim();
    el.setAttribute('data-text', text);
    el.setAttribute('aria-label', text);
    var html = '', i = 0;
    text.split('').forEach(function (ch) {
      if (/[0-9]/.test(ch)) {
        var col = '';
        for (var k = 0; k < 20; k++) col += '<span>' + (k % 10) + '</span>';
        html += '<span class="odo" aria-hidden="true"><span class="odo-col" style="--to:' + (10 + Number(ch)) + ';--i:' + (i++) + '">' + col + '</span></span>';
      } else {
        html += '<span aria-hidden="true">' + esc(ch) + '</span>';
      }
    });
    el.innerHTML = html;
    el.classList.remove('odo-run');
    void el.offsetWidth;
    setTimeout(function () { el.classList.add('odo-run'); }, delay);
  }

  /* Lists (news, publications, projects, archives, search): infinite scroll. The numbered pager stays in
     the page for search engines and without JS; here the next page loads as the visitor nears the end
     and its items are added to the list. A "Shfaq më shumë" button does the same by hand. */
  function infinite(scope) {
    if (!window.fetch || !window.DOMParser) return;
    $$('.pager', scope || document).forEach(function (pager) {
      var list = pager.previousElementSibling, next = $('a.next', pager);
      if (!list || !next || pager.getAttribute('data-infinite')) return;
      pager.setAttribute('data-infinite', '1');
      pager.hidden = true;
      var url = next.href, busy = false;
      var box = document.createElement('div');
      box.className = 'more';
      box.innerHTML = '<button class="btn btn-line" type="button">Shfaq më shumë</button><p class="more-status" role="status" aria-live="polite"></p>';
      pager.parentNode.insertBefore(box, pager);
      var btn = $('button', box), status = $('.more-status', box), io;
      function done() { if (io) io.disconnect(); btn.remove(); }
      function more() {
        if (busy || !url) return;
        busy = true;
        box.classList.add('is-loading');
        status.textContent = 'Duke ngarkuar…';
        fetch(url, { headers: { Accept: 'text/html' }, credentials: 'same-origin' })
          .then(function (r) { return r.text(); })
          .then(function (html) {
            var doc = new DOMParser().parseFromString(html, 'text/html');
            var p2 = $('.pager', doc), l2 = p2 && p2.previousElementSibling;
            var items = l2 ? Array.prototype.slice.call(l2.children) : [];
            items.forEach(function (it) { list.appendChild(document.importNode(it, true)); });
            status.textContent = items.length ? items.length + ' të tjera u shfaqën.' : '';
            var n2 = p2 && $('a.next', p2);
            url = n2 ? n2.href : '';
            if (!url) done();
          })
          .catch(function () { url = next.href; })
          .then(function () {
            busy = false;
            box.classList.remove('is-loading');
            // Still near the end (a tall screen, a fast scroll): ask again.
            if (url && io) { io.unobserve(box); io.observe(box); }
          });
      }
      btn.addEventListener('click', more);
      if ('IntersectionObserver' in window) {
        io = new IntersectionObserver(function (entries) { if (entries[0].isIntersecting) more(); }, { rootMargin: '800px 0px' });
        io.observe(box);
      }
    });
  }
  window.inpoInfinite = infinite;
  infinite();

  /* Home load sequence: headline reveal, ring draw, rolling figures */
  if ($('.hero') || $('.bento')) {
    requestAnimationFrame(function () { requestAnimationFrame(function () { document.body.classList.add('loaded'); }); });
    if (!reduce) {
      $$('[data-count]').forEach(function (el) { odometer(el, 450); });
      $$('.hero-figs a').forEach(function (a) {
        a.addEventListener('mouseenter', function () { var el = $('[data-count]', a); if (el) odometer(el, 0); });
      });
    }
  } else {
    document.body.classList.add('loaded');
  }

  /* Hero: INPO's rings drift with the pointer (mouse only, not with reduced motion) */
  var heroEl = $('.hero');
  if (heroEl && !reduce && window.matchMedia('(pointer: fine)').matches) {
    heroEl.addEventListener('pointermove', function (e) {
      var r = heroEl.getBoundingClientRect();
      heroEl.style.setProperty('--mx', (((e.clientX - r.left) / r.width) - 0.5) * 2);
      heroEl.style.setProperty('--my', (((e.clientY - r.top) / r.height) - 0.5) * 2);
    });
    heroEl.addEventListener('pointerleave', function () { heroEl.style.setProperty('--mx', 0); heroEl.style.setProperty('--my', 0); });
  }

  /* No-JS form results land on #kontakt: move focus to the message */
  var sent = $('.sent') || $('.nl-done');
  if (sent && /inpo_(contact|nl)=/.test(location.search)) { sent.setAttribute('tabindex', '-1'); sent.focus({ preventScroll: true }); }
})();
