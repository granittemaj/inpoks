/* INPO slides: an infographic's images as a row you swipe, with arrows, a counter and a full-screen view.
   No dependencies. Shared by the prototype (assets/js/slides.js) and the WordPress plugin
   (wordpress/inpo-core/assets/slides.js): keep the two files identical.
   Markup: [data-slides] > .slides-nav (.slides-count, [data-slides-go="-1"|"1"]) + .slides > .slide > a[href=full image] > img.
   Without JS the row still scrolls and each image opens as a file. */
(function () {
  'use strict';
  var pad = function (n) { return (n < 10 ? '0' : '') + n; };
  var box;

  // One full-screen viewer for the page, made on first use.
  function viewer() {
    if (box) return box;
    box = document.createElement('dialog');
    box.className = 'slides-view';
    box.setAttribute('aria-label', 'Infografika');
    box.innerHTML =
      '<figure><img alt=""><figcaption></figcaption></figure>' +
      '<button type="button" class="icon-btn sv-close" aria-label="Mbyll"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>' +
      '<button type="button" class="icon-btn sv-prev" aria-label="Pamja e mëparshme"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg></button>' +
      '<button type="button" class="icon-btn sv-next" aria-label="Pamja tjetër"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg></button>';
    document.body.appendChild(box);
    box.addEventListener('click', function (e) {
      if (e.target === box || e.target.closest('.sv-close')) box.close();
      if (e.target.closest('.sv-prev')) box.step(-1);
      if (e.target.closest('.sv-next')) box.step(1);
    });
    box.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowLeft') box.step(-1);
      if (e.key === 'ArrowRight') box.step(1);
    });
    return box;
  }

  function mount(root) {
    if (root.dataset.slidesReady) return;
    root.dataset.slidesReady = '1';
    var row = root.querySelector('.slides');
    var links = Array.prototype.slice.call(row.querySelectorAll('.slide a'));
    var count = root.querySelector('.slides-count');
    var prev = root.querySelector('[data-slides-go="-1"]'), next = root.querySelector('[data-slides-go="1"]');
    var n = links.length;
    function current() {
      var x = row.scrollLeft, best = 0, d = Infinity;
      links.forEach(function (a, i) { var dd = Math.abs(a.parentNode.offsetLeft - row.offsetLeft - x - parseFloat(getComputedStyle(row).scrollPaddingLeft || 0)); if (dd < d) { d = dd; best = i; } });
      return best;
    }
    function update() {
      var i = current();
      if (count) count.textContent = pad(i + 1) + ' / ' + pad(n);
      if (prev) prev.disabled = row.scrollLeft < 4;
      if (next) next.disabled = row.scrollLeft + row.clientWidth >= row.scrollWidth - 4;
    }
    function go(i) {
      i = Math.max(0, Math.min(n - 1, i));
      row.scrollTo({ left: links[i].parentNode.offsetLeft - row.offsetLeft - parseFloat(getComputedStyle(row).scrollPaddingLeft || 0), behavior: 'smooth' });
    }
    root.addEventListener('click', function (e) {
      var b = e.target.closest('[data-slides-go]');
      if (b) { go(current() + Number(b.getAttribute('data-slides-go'))); return; }
      var a = e.target.closest('.slide a');
      if (!a || e.metaKey || e.ctrlKey || e.shiftKey || !window.HTMLDialogElement) return;
      e.preventDefault();
      var v = viewer(), at = links.indexOf(a);
      v.step = function (step) {
        at = (at + step + n) % n;
        var img = v.querySelector('img'), link = links[at];
        img.src = link.href;
        img.alt = link.querySelector('img').alt;
        v.querySelector('figcaption').textContent = pad(at + 1) + ' / ' + pad(n);
        go(at);
      };
      v.step(0);
      if (!v.open) v.showModal();
      v.querySelector('.sv-next').focus();
    });
    var t;
    row.addEventListener('scroll', function () { clearTimeout(t); t = setTimeout(update, 60); }, { passive: true });
    window.addEventListener('resize', update);
    update();
  }

  window.inpoSlides = { mount: mount };
  document.querySelectorAll('[data-slides]').forEach(mount);
})();
