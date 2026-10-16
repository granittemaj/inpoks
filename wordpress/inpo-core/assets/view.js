/* INPO blocks on the front end: newsletter, contact form, news and publication filters, document year filter.
   Every form already works without JS (normal POST/GET); this only makes it instant. */
(function () {
  'use strict';
  var REST = (window.inpoCore && window.inpoCore.rest) || '/wp-json/inpo/v1/';
  var EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var esc = function (s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); };
  var CHECK = '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg>';

  // Loading state for a submit button: disabled, "Duke dërguar…", announced via aria-busy.
  function busy(btn, on) {
    if (on) { btn.dataset.label = btn.textContent; btn.textContent = 'Duke dërguar…'; }
    else if (btn.dataset.label) { btn.textContent = btn.dataset.label; }
    btn.disabled = on;
    btn.setAttribute('aria-busy', on ? 'true' : 'false');
  }

  function post(path, data) {
    return fetch(REST + path, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify(data)
    }).then(function (r) { return r.json(); });
  }

  /* Newsletter */
  $$('[data-inpo-newsletter]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var input = $('input[type=email]', form), msg = $('.nl-msg', form), v = input.value.trim();
      if (!EMAIL.test(v)) {
        msg.textContent = 'Shkruani një adresë email të vlefshme, p.sh. emri@shembull.com.';
        input.setAttribute('aria-invalid', 'true');
        input.focus();
        return;
      }
      input.removeAttribute('aria-invalid');
      var btn = $('button[type=submit]', form);
      busy(btn, true);
      post('subscribe', { email: v, inpo_hp: (form.elements.inpo_hp || {}).value || '' })
        .then(function (res) {
          if (!res.ok) throw res;
          form.innerHTML = '<div class="nl-done" role="status">' + CHECK + '<span>U abonuat me <b>' + esc(v) + '</b>. Buletini i parë vjen në fillim të muajit.</span></div>';
        })
        .catch(function (err) {
          busy(btn, false);
          // REST blocked or not JSON (firewall, plugin): fall back to the normal form post.
          if (!err || typeof err.error !== 'string') { form.submit(); return; }
          msg.textContent = err.error || 'Abonimi nuk u krye. Provoni përsëri.';
        });
    });
  });

  /* Contact form: same validation and messages as the server */
  $$('[data-inpo-contact]').forEach(function (form) {
    var fields = {
      emri: [$('#c-emri', form), $('#e-emri', form)],
      email: [$('#c-email', form), $('#e-email', form)],
      mesazhi: [$('#c-msg', form), $('#e-msg', form)],
      pelqim: [$('#c-ok', form), $('#e-ok', form)]
    };
    var formErr = $('.form-err', form);
    function set(key, text) {
      var input = fields[key][0], out = fields[key][1];
      out.textContent = text || '';
      input.closest('.field').classList.toggle('invalid', !!text);
      if (text) input.setAttribute('aria-invalid', 'true'); else input.removeAttribute('aria-invalid');
    }
    function showErrors(errors) {
      Object.keys(fields).forEach(function (k) { set(k, errors[k]); });
      if (formErr) { formErr.textContent = errors.form || ''; formErr.hidden = !errors.form; }
      var first = $('[aria-invalid="true"]', form);
      if (first) first.focus(); else if (errors.form && formErr) formErr.focus();
    }
    // After the first attempt, re-check a field as soon as the visitor leaves it.
    var tried = false;
    function check(k) {
      var v = fields[k][0].value.trim();
      if (k === 'emri') return v ? '' : 'Shkruani emrin.';
      if (k === 'email') return EMAIL.test(v) ? '' : 'Shkruani një adresë email të vlefshme, p.sh. emri@shembull.com.';
      if (k === 'mesazhi') return v.length >= 10 ? '' : 'Shkruani mesazhin, të paktën 10 shkronja.';
      return fields.pelqim[0].checked ? '' : 'Pranoni kushtin për të dërguar mesazhin.';
    }
    ['emri', 'email', 'mesazhi'].forEach(function (k) {
      fields[k][0].addEventListener('blur', function () { if (tried || fields[k][0].value.trim()) set(k, check(k)); });
    });
    fields.pelqim[0].addEventListener('change', function () { if (tried) set('pelqim', check('pelqim')); });
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      tried = true;
      var errors = {};
      if (!fields.emri[0].value.trim()) errors.emri = 'Shkruani emrin.';
      if (!EMAIL.test(fields.email[0].value.trim())) errors.email = 'Shkruani një adresë email të vlefshme, p.sh. emri@shembull.com.';
      if (fields.mesazhi[0].value.trim().length < 10) errors.mesazhi = 'Shkruani mesazhin, të paktën 10 shkronja.';
      if (!fields.pelqim[0].checked) errors.pelqim = 'Pranoni kushtin për të dërguar mesazhin.';
      if (Object.keys(errors).length) { showErrors(errors); return; }
      showErrors({});
      var data = {};
      new FormData(form).forEach(function (v, k) { data[k] = v; });
      var btn = $('button[type=submit]', form);
      busy(btn, true);
      post('contact', data)
        .then(function (res) {
          if (!res.ok) throw res;
          var wrap = document.createElement('div');
          wrap.className = 'sent';
          wrap.setAttribute('tabindex', '-1');
          wrap.innerHTML = '<span class="check">' + CHECK.replace(/26/g, '30') + '</span><h2>Faleminderit, ' + esc(res.name) + '.</h2><p class="lead">Mesazhi u dërgua. Do t\'ju përgjigjemi në ' + esc(res.email) + '.</p><button class="btn btn-line" type="button">Dërgo një mesazh tjetër</button>';
          form.replaceWith(wrap);
          wrap.focus();
          $('button', wrap).addEventListener('click', function () { location.reload(); });
        })
        .catch(function (err) {
          busy(btn, false);
          if (!err || !err.errors) { form.submit(); return; }
          showErrors(err.errors);
        });
    });
  });

  /* Category chips stay on one row and scroll sideways: fade the side that has more to show. */
  $$('.filters .chips').forEach(function (el) {
    function set() {
      var max = el.scrollWidth - el.clientWidth;
      el.classList.toggle('fade-l', el.scrollLeft > 2);
      el.classList.toggle('fade-r', max > 2 && el.scrollLeft < max - 2);
    }
    el.addEventListener('scroll', set, { passive: true });
    window.addEventListener('resize', set);
    set();
    var on = $('[aria-pressed="true"]', el);
    if (on && on.offsetLeft + on.offsetWidth > el.clientWidth) el.scrollLeft = on.offsetLeft - 24;
  });

  /* Filters (news, publications): update the results without reloading, keep the URL shareable.
     The form works without JS; here every change fetches the same URL and swaps the results,
     the count, the active-filter tags and the chip counts. */
  $$('[data-inpo-filter]').forEach(function (form) {
    if (!window.fetch || !window.DOMParser) return;
    var kind = form.getAttribute('data-inpo-filter');
    var results = $('#' + kind + '-results'), count = $('#' + kind + '-count'), active = $('#' + kind + '-active');
    var facet = $('input[type=hidden]', form), facetName = facet.name;
    var timer, ctrl;
    function url() {
      var params = new URLSearchParams();
      var q = form.elements.q.value.trim();
      if (q) params.set('q', q);
      if (facet.value) params.set(facetName, facet.value);
      $$('select', form).forEach(function (s) { if (s.value) params.set(s.name, s.value); });
      var qs = params.toString();
      return form.getAttribute('action').split('?')[0] + (qs ? '?' + qs : '');
    }
    function pills() {
      $$('.sel-pill', form).forEach(function (p) {
        var s = $('select', p);
        p.classList.toggle('is-set', !!s.value);
        $('.sel-v', p).textContent = s.options[s.selectedIndex].text;
      });
    }
    // Copies state from a fetched page into this one, leaving focus where it is.
    function adopt(doc) {
      var f = $('[data-inpo-filter="' + kind + '"]', doc);
      if (!f) return false;
      results.innerHTML = $('#' + kind + '-results', doc).innerHTML;
      active.innerHTML = $('#' + kind + '-active', doc).innerHTML;
      count.textContent = $('#' + kind + '-count', doc).textContent;
      facet.value = $('input[type=hidden]', f).value;
      $$('.chip', f).forEach(function (c) {
        var mine = $$('.chip', form).filter(function (m) { return m.value === c.value; })[0];
        if (mine) { mine.setAttribute('aria-pressed', c.getAttribute('aria-pressed')); $('.chip-n', mine).textContent = $('.chip-n', c).textContent; }
      });
      $$('select', f).forEach(function (s) { var mine = form.elements[s.name]; if (mine) mine.value = s.value; });
      var q = f.elements.q.value;
      if (document.activeElement !== form.elements.q) form.elements.q.value = q;
      pills();
      return true;
    }
    function load(href, focusAfter) {
      if (ctrl) ctrl.abort();
      ctrl = window.AbortController ? new AbortController() : null;
      results.setAttribute('aria-busy', 'true');
      fetch(href, { signal: ctrl ? ctrl.signal : undefined, headers: { Accept: 'text/html' }, credentials: 'same-origin' })
        .then(function (r) { return r.text(); })
        .then(function (html) {
          if (!adopt(new DOMParser().parseFromString(html, 'text/html'))) { location.href = href; return; }
          results.removeAttribute('aria-busy');
          history.replaceState(null, '', href);
          if (focusAfter) focusAfter();
        })
        .catch(function (err) { if (!err || err.name !== 'AbortError') location.href = href; });
    }
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var sub = e.submitter;
      if (sub && sub.name === facetName) {
        facet.value = sub.value;
        $$('.chip', form).forEach(function (c) { c.setAttribute('aria-pressed', String(c === sub)); });
      }
      load(url());
    });
    form.elements.q.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(function () { load(url()); }, 250); });
    form.addEventListener('change', function (e) { if (e.target.tagName === 'SELECT') { pills(); load(url()); } });
    // Tags, "remove all", empty-state buttons and the pager are plain links: follow them in place.
    function follow(e) {
      var a = e.target.closest('.tag, .res-clear, .empty a, .pager a');
      if (!a || e.metaKey || e.ctrlKey || e.shiftKey) return;
      e.preventDefault();
      var paging = !!a.closest('.pager');
      if (paging) form.scrollIntoView({ block: 'start' });
      load(a.href, paging ? null : function () { ($('.tag', active) || form.elements.q).focus(); });
    }
    active.addEventListener('click', follow);
    results.addEventListener('click', follow);
  });
  /* Transparency documents: the year filter hides rows in place (the form works without JS too). */
  $$('[data-inpo-docs]').forEach(function (form) {
    var sel = $('select', form);
    form.addEventListener('submit', function (e) { e.preventDefault(); });
    sel.addEventListener('change', function () {
      var y = sel.value, pill = sel.closest('.sel-pill');
      pill.classList.toggle('is-set', !!y);
      $('.sel-v', pill).textContent = sel.options[sel.selectedIndex].text;
      $$('.docs-group').forEach(function (g) {
        var rows = $$('.doc-row', g), shown = 0;
        rows.forEach(function (r) { var on = !y || r.getAttribute('data-year') === y; r.hidden = !on; shown += on ? 1 : 0; });
        $('.chip-n', g).textContent = shown;
        $('.doc-empty', g).hidden = shown > 0;
      });
      var url = new URL(location.href);
      if (y) url.searchParams.set('viti', y); else url.searchParams.delete('viti');
      history.replaceState(null, '', url.toString());
    });
  });
})();
