/* INPO PDF reader: opens publications in a full-screen viewer without leaving the page.
   Any element with data-pdf opens it. pdf.js (bundled) loads only on first use; pages render
   lazily as they scroll into view. Back button, Esc and the close button all close it.
   Also draws the real first page into a detail-page cover that has no server preview. */
(function () {
  'use strict';
  var cfg = window.inpoReader || {};
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var esc = function (s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); };
  var ICON = {
    close: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>',
    prev: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m18 15-6-6-6 6"/></svg>',
    next: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>',
    minus: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14"/></svg>',
    plus: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>',
    dl: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v11"/><path d="m7 10 5 5 5-5"/><path d="M5 20h14"/></svg>',
    ext: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7"/><path d="M8 7h9v9"/></svg>'
  };
  var ZOOMS = [0.5, 0.75, 1, 1.25, 1.5, 2, 3];

  /* ---------- pdf.js, loaded once ---------- */
  var libPromise;
  function lib() {
    if (!libPromise) {
      libPromise = new Promise(function (resolve, reject) {
        if (window.pdfjsLib) { resolve(window.pdfjsLib); return; }
        var s = document.createElement('script');
        s.src = cfg.lib;
        s.onload = function () {
          if (!window.pdfjsLib) { reject(new Error('pdf.js')); return; }
          window.pdfjsLib.GlobalWorkerOptions.workerSrc = cfg.worker;
          resolve(window.pdfjsLib);
        };
        s.onerror = reject;
        document.head.appendChild(s);
      });
    }
    return libPromise;
  }
  function openDoc(url, onProgress) {
    return lib().then(function (pdfjs) {
      var task = pdfjs.getDocument({ url: url, isEvalSupported: false, disableAutoFetch: true, rangeChunkSize: 262144 });
      if (onProgress) task.onProgress = onProgress;
      return task.promise;
    });
  }

  /* ---------- Reader UI ---------- */
  var dlg, pagesEl, thumbsEl, statusEl, pageNowEl, pageAllEl, zoomEl, titleEl, metaEl, dlEl, extEl, liveEl;
  var doc = null, docUrl = '', baseSize = null, zoom = 1, current = 1, opener = null, pushed = false, io = null, tio = null, token = 0;

  function build() {
    dlg = document.createElement('dialog');
    dlg.className = 'reader';
    dlg.setAttribute('aria-labelledby', 'reader-title');
    dlg.innerHTML =
      '<div class="reader-bar">' +
        '<button class="icon-btn" type="button" data-reader-close aria-label="Mbyll lexuesin">' + ICON.close + '</button>' +
        '<div class="reader-head"><small class="reader-meta"></small><h2 class="reader-title" id="reader-title"></h2></div>' +
        '<div class="reader-tools">' +
          '<div class="reader-group" role="group" aria-label="Faqet">' +
            '<button class="icon-btn" type="button" data-reader-prev aria-label="Faqja e mëparshme">' + ICON.prev + '</button>' +
            '<span class="reader-count"><b class="reader-now">1</b> / <span class="reader-all">…</span></span>' +
            '<button class="icon-btn" type="button" data-reader-next aria-label="Faqja tjetër">' + ICON.next + '</button>' +
          '</div>' +
          '<div class="reader-group reader-zoom" role="group" aria-label="Zmadhimi">' +
            '<button class="icon-btn" type="button" data-reader-out aria-label="Zvogëlo">' + ICON.minus + '</button>' +
            '<span class="reader-pct">100%</span>' +
            '<button class="icon-btn" type="button" data-reader-in aria-label="Zmadho">' + ICON.plus + '</button>' +
          '</div>' +
          '<a class="btn btn-accent btn-sm reader-dl" download>' + ICON.dl + '<span>Shkarko</span></a>' +
        '</div>' +
      '</div>' +
      '<div class="reader-body">' +
        '<nav class="reader-thumbs" aria-label="Pamjet e faqeve"></nav>' +
        '<div class="reader-pages" tabindex="0" aria-label="Dokumenti"></div>' +
      '</div>' +
      '<div class="reader-status" role="status"></div>' +
      '<div class="sr" aria-live="polite" data-reader-live></div>';
    document.body.appendChild(dlg);
    pagesEl = $('.reader-pages', dlg); thumbsEl = $('.reader-thumbs', dlg); statusEl = $('.reader-status', dlg);
    pageNowEl = $('.reader-now', dlg); pageAllEl = $('.reader-all', dlg); zoomEl = $('.reader-pct', dlg);
    titleEl = $('.reader-title', dlg); metaEl = $('.reader-meta', dlg); dlEl = $('.reader-dl', dlg); liveEl = $('[data-reader-live]', dlg);

    $('[data-reader-close]', dlg).addEventListener('click', close);
    $('[data-reader-prev]', dlg).addEventListener('click', function () { go(current - 1, true); });
    $('[data-reader-next]', dlg).addEventListener('click', function () { go(current + 1, true); });
    $('[data-reader-out]', dlg).addEventListener('click', function () { setZoom(-1); });
    $('[data-reader-in]', dlg).addEventListener('click', function () { setZoom(1); });
    dlg.addEventListener('cancel', function (e) { e.preventDefault(); close(); });
    dlg.addEventListener('keydown', function (e) {
      if (e.target.closest('input,textarea')) return;
      if (e.key === '+' || e.key === '=') { e.preventDefault(); setZoom(1); }
      else if (e.key === '-') { e.preventDefault(); setZoom(-1); }
      else if (e.key === 'ArrowRight' || (e.key === 'PageDown' && e.target !== pagesEl)) { e.preventDefault(); go(current + 1, true); }
      else if (e.key === 'ArrowLeft' || (e.key === 'PageUp' && e.target !== pagesEl)) { e.preventDefault(); go(current - 1, true); }
      else if (e.key === 'Home' && e.target !== pagesEl) { e.preventDefault(); go(1, true); }
      else if (e.key === 'End' && e.target !== pagesEl) { e.preventDefault(); go(doc ? doc.numPages : 1, true); }
    });
    var ticking = false;
    pagesEl.addEventListener('scroll', function () {
      if (ticking) return;
      ticking = true;
      requestAnimationFrame(function () { ticking = false; track(); });
    }, { passive: true });
    window.addEventListener('resize', function () { if (dlg.open && doc) relayout(); });
  }

  function status(html, kind) {
    statusEl.innerHTML = html;
    statusEl.className = 'reader-status' + (kind ? ' is-' + kind : '');
    statusEl.hidden = !html;
  }

  function open(trigger) {
    if (!dlg) build();
    opener = trigger;
    var url = trigger.getAttribute('data-pdf');
    titleEl.textContent = trigger.getAttribute('data-pdf-title') || '';
    metaEl.textContent = trigger.getAttribute('data-pdf-meta') || '';
    dlEl.href = url;
    dlg.showModal();
    document.documentElement.classList.add('reader-open');
    if (!reduce) { dlg.classList.remove('is-in'); requestAnimationFrame(function () { dlg.classList.add('is-in'); }); } else { dlg.classList.add('is-in'); }
    // Back closes the reader; on a publication page the URL becomes …/#lexo so it can be shared.
    if (!pushed) {
      history.pushState({ inpoReader: true }, '', trigger.hasAttribute('data-pdf-hash') ? '#lexo' : location.href);
      pushed = true;
    }
    pagesEl.focus({ preventScroll: true });
    if (url === docUrl && doc) { relayout(); return; }
    load(url);
  }

  function load(url) {
    var my = ++token;
    docUrl = url; doc = null; baseSize = null; current = 1; zoom = 1;
    pagesEl.innerHTML = ''; thumbsEl.innerHTML = '';
    pageAllEl.textContent = '…'; pageNowEl.textContent = '1'; zoomEl.textContent = '100%';
    status('<div class="reader-loading"><span class="reader-spin" aria-hidden="true"></span><p>Duke hapur dokumentin…</p><div class="reader-progress"><i style="width:0%"></i></div></div>', 'loading');
    var bar = $('.reader-progress i', statusEl);
    openDoc(url, function (p) {
      if (my !== token || !p.total) return;
      bar.style.width = Math.min(100, Math.round(p.loaded / p.total * 100)) + '%';
    }).then(function (pdf) {
      if (my !== token) return;
      doc = pdf;
      pageAllEl.textContent = pdf.numPages;
      return pdf.getPage(1).then(function (page) {
        var vp = page.getViewport({ scale: 1 });
        baseSize = { w: vp.width, h: vp.height };
        layout();
        status('');
      });
    }).catch(function () {
      if (my !== token) return;
      status('<div class="reader-error"><h3>Dokumenti nuk u hap këtu.</h3><p>Mund ta shkarkoni ose ta hapni në një skedë të re.</p><div class="hero-ctas"><a class="btn btn-accent btn-sm" href="' + esc(url) + '" download>' + ICON.dl + ' Shkarko PDF</a><a class="btn btn-line btn-sm" href="' + esc(url) + '" target="_blank" rel="noopener">' + ICON.ext + ' Hape në skedë të re</a></div></div>', 'error');
    });
  }

  /* Page slots keep their size before rendering, so scrolling never jumps. */
  function width() {
    var avail = pagesEl.clientWidth - (window.innerWidth < 640 ? 24 : 64);
    return Math.max(200, Math.min(avail, 860) * zoom);
  }
  function layout() {
    var w = width(), h = Math.round(w * baseSize.h / baseSize.w);
    var frag = document.createDocumentFragment(), tfrag = document.createDocumentFragment();
    for (var n = 1; n <= doc.numPages; n++) {
      var slot = document.createElement('div');
      slot.className = 'reader-page';
      slot.dataset.n = n;
      slot.style.width = w + 'px';
      slot.style.height = h + 'px';
      slot.setAttribute('aria-label', 'Faqja ' + n);
      slot.innerHTML = '<span class="reader-pn" aria-hidden="true">' + n + '</span>';
      frag.appendChild(slot);
      var t = document.createElement('button');
      t.type = 'button';
      t.className = 'reader-thumb';
      t.dataset.n = n;
      t.setAttribute('aria-label', 'Shko te faqja ' + n);
      t.innerHTML = '<span class="reader-thumb-page" style="aspect-ratio:' + baseSize.w + '/' + baseSize.h + '"></span><span class="reader-thumb-n">' + n + '</span>';
      t.addEventListener('click', function () { go(+this.dataset.n, true); });
      tfrag.appendChild(t);
    }
    pagesEl.appendChild(frag);
    thumbsEl.appendChild(tfrag);
    observe();
    track();
  }
  function relayout() {
    var w = width(), h = Math.round(w * baseSize.h / baseSize.w), keep = current;
    Array.prototype.forEach.call(pagesEl.children, function (slot) {
      slot.style.width = w + 'px';
      slot.style.height = h + 'px';
      slot.dataset.scale = '';
    });
    observe();
    go(keep, false);
  }

  function observe() {
    if (io) io.disconnect();
    io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) draw(e.target, false);
        else if (Math.abs(e.boundingClientRect.top) > 6000) release(e.target);
      });
    }, { root: pagesEl, rootMargin: '900px 0px' });
    Array.prototype.forEach.call(pagesEl.children, function (s) { io.observe(s); });
    if (tio) tio.disconnect();
    if (getComputedStyle(thumbsEl).display === 'none') return;
    tio = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) { if (e.isIntersecting) { drawThumb(e.target); tio.unobserve(e.target); } });
    }, { root: thumbsEl, rootMargin: '400px 0px' });
    Array.prototype.forEach.call(thumbsEl.children, function (t) { tio.observe(t); });
  }

  function render(n, canvas, cssWidth) {
    return doc.getPage(n).then(function (page) {
      var dpr = Math.min(window.devicePixelRatio || 1, 2);
      var vp = page.getViewport({ scale: cssWidth / page.getViewport({ scale: 1 }).width * dpr });
      canvas.width = Math.floor(vp.width);
      canvas.height = Math.floor(vp.height);
      return page.render({ canvasContext: canvas.getContext('2d', { alpha: false }), viewport: vp }).promise;
    });
  }
  function draw(slot, force) {
    var w = parseFloat(slot.style.width);
    if (!doc || (!force && slot.dataset.scale === String(w))) return;
    slot.dataset.scale = String(w);
    var canvas = slot.querySelector('canvas') || document.createElement('canvas');
    canvas.setAttribute('aria-hidden', 'true');
    var my = token;
    render(+slot.dataset.n, canvas, w).then(function () {
      if (my !== token) return;
      if (!canvas.parentNode) slot.appendChild(canvas);
      slot.classList.add('is-ready');
    }).catch(function () {});
  }
  function release(slot) {
    var c = slot.querySelector('canvas');
    if (c) { c.width = 0; c.height = 0; c.remove(); }
    slot.dataset.scale = '';
    slot.classList.remove('is-ready');
  }
  function drawThumb(btn) {
    var holder = btn.querySelector('.reader-thumb-page'), canvas = document.createElement('canvas');
    render(+btn.dataset.n, canvas, holder.clientWidth || 96).then(function () { holder.appendChild(canvas); }).catch(function () {});
  }

  function track() {
    if (!doc) return;
    var mid = pagesEl.scrollTop + pagesEl.clientHeight / 3, n = 1;
    Array.prototype.some.call(pagesEl.children, function (s) {
      if (s.offsetTop + s.offsetHeight > mid) { n = +s.dataset.n; return true; }
      return false;
    });
    if (n !== current) setCurrent(n);
  }
  function setCurrent(n) {
    current = n;
    pageNowEl.textContent = n;
    Array.prototype.forEach.call(thumbsEl.children, function (t) {
      var on = +t.dataset.n === n;
      t.classList.toggle('is-current', on);
      if (on) t.setAttribute('aria-current', 'page'); else t.removeAttribute('aria-current');
      if (on && getComputedStyle(thumbsEl).display !== 'none') {
        var r = t.getBoundingClientRect(), b = thumbsEl.getBoundingClientRect();
        if (r.top < b.top || r.bottom > b.bottom) thumbsEl.scrollTop += r.top - b.top - b.height / 2 + r.height / 2;
      }
    });
  }
  function go(n, announce) {
    if (!doc) return;
    n = Math.max(1, Math.min(doc.numPages, n));
    var slot = pagesEl.children[n - 1];
    if (!slot) return;
    pagesEl.scrollTo({ top: slot.offsetTop - 16, behavior: reduce || !announce ? 'auto' : 'smooth' });
    setCurrent(n);
    if (announce) liveEl.textContent = 'Faqja ' + n + ' nga ' + doc.numPages;
  }
  function setZoom(dir) {
    if (!doc) return;
    var i = ZOOMS.indexOf(zoom);
    i = Math.max(0, Math.min(ZOOMS.length - 1, (i < 0 ? 2 : i) + dir));
    if (ZOOMS[i] === zoom) return;
    zoom = ZOOMS[i];
    zoomEl.textContent = Math.round(zoom * 100) + '%';
    liveEl.textContent = 'Zmadhimi ' + Math.round(zoom * 100) + '%';
    relayout();
  }

  function close() {
    if (!dlg || !dlg.open) return;
    if (pushed && history.state && history.state.inpoReader) { history.back(); return; }
    finish();
  }
  function finish() {
    pushed = false;
    if (!dlg.open) return;
    dlg.close();
    document.documentElement.classList.remove('reader-open');
    if (opener && document.contains(opener)) opener.focus({ preventScroll: true });
  }
  window.addEventListener('popstate', function () { if (dlg && dlg.open) finish(); });

  document.addEventListener('click', function (e) {
    var t = e.target.closest('[data-pdf]');
    if (!t || !cfg.lib) return;
    e.preventDefault();
    open(t);
  });

  // A shared link to …/#lexo opens the reader on arrival.
  if (location.hash === '#lexo') {
    var first = document.querySelector('[data-pdf][data-pdf-hash]');
    if (first) {
      history.replaceState(null, '', location.href.split('#')[0]);
      open(first);
    }
  }

  /* ---------- Real first page on a publication's cover ---------- */
  var cover = document.querySelector('.pd-cover[data-first-page]');
  var conn = navigator.connection || {};
  if (cover && 'IntersectionObserver' in window && !conn.saveData && !/2g/.test(conn.effectiveType || '')) {
    var trigger = cover.querySelector('[data-pdf]');
    var start = function () {
      openDoc(trigger.getAttribute('data-pdf')).then(function (pdf) {
        return pdf.getPage(1).then(function (page) {
          var box = cover.querySelector('.cover');
          var canvas = document.createElement('canvas');
          canvas.className = 'cover-img';
          canvas.setAttribute('aria-hidden', 'true');
          var dpr = Math.min(window.devicePixelRatio || 1, 2);
          var vp = page.getViewport({ scale: box.clientWidth / page.getViewport({ scale: 1 }).width * dpr });
          canvas.width = Math.floor(vp.width);
          canvas.height = Math.floor(vp.height);
          return page.render({ canvasContext: canvas.getContext('2d', { alpha: false }), viewport: vp }).promise.then(function () {
            Array.prototype.forEach.call(box.querySelectorAll('.rings,.bars,.dots,.word'), function (el) { el.remove(); });
            box.insertBefore(canvas, box.firstChild);
            box.className = box.className.replace(/\bcv-\w+/, 'cv-page');
            cover.classList.add('is-real');
          });
        });
      }).catch(function () {});
    };
    var seen = new IntersectionObserver(function (entries) {
      if (!entries[0].isIntersecting) return;
      seen.disconnect();
      if (window.requestIdleCallback) requestIdleCallback(start, { timeout: 2000 }); else setTimeout(start, 200);
    });
    seen.observe(cover);
  }
})();
