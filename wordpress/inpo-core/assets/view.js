/* INPO blocks on the front end: newsletter, contact form, publication library.
   Every form already works without JS (normal POST/GET); this only makes it instant. */
(function () {
  'use strict';
  var REST = (window.inpoCore && window.inpoCore.rest) || '/wp-json/inpo/v1/';
  var EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var esc = function (s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); };
  var CHECK = '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg>';

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
      btn.disabled = true;
      post('subscribe', { email: v, inpo_hp: (form.elements.inpo_hp || {}).value || '' })
        .then(function (res) {
          if (!res.ok) throw res;
          form.innerHTML = '<div class="nl-done" role="status">' + CHECK + '<span>U abonuat me <b>' + esc(v) + '</b>. Buletini i parë vjen në fillim të muajit.</span></div>';
        })
        .catch(function (err) {
          btn.disabled = false;
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
    form.addEventListener('submit', function (e) {
      e.preventDefault();
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
      btn.disabled = true;
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
          btn.disabled = false;
          if (!err || !err.errors) { form.submit(); return; }
          showErrors(err.errors);
        });
    });
  });

  /* Publication library: filter without reloading, keep the URL shareable */
  var lib = $('[data-inpo-library]');
  if (lib && window.fetch && window.DOMParser) {
    var results = $('#pub-results'), count = $('#pub-count'), typeInput = $('input[type=hidden][name=lloji]', lib);
    var timer, ctrl;
    function url() {
      var params = new URLSearchParams();
      var q = lib.elements.q.value.trim(), t = typeInput.value;
      if (q) params.set('q', q);
      if (t) params.set('lloji', t);
      ['programi', 'viti'].forEach(function (name) { var el = lib.elements[name]; if (el && el.value) params.set(name, el.value); });
      var s = params.toString();
      return lib.getAttribute('action').split('?')[0] + (s ? '?' + s : '');
    }
    function load(href, push) {
      if (ctrl) ctrl.abort();
      ctrl = window.AbortController ? new AbortController() : null;
      results.setAttribute('aria-busy', 'true');
      fetch(href, { signal: ctrl ? ctrl.signal : undefined, headers: { Accept: 'text/html' } })
        .then(function (r) { return r.text(); })
        .then(function (html) {
          var doc = new DOMParser().parseFromString(html, 'text/html');
          var next = $('#pub-results', doc), nextCount = $('#pub-count', doc);
          if (!next) { location.href = href; return; }
          results.innerHTML = next.innerHTML;
          if (nextCount) count.textContent = nextCount.textContent;
          results.removeAttribute('aria-busy');
          if (push) history.replaceState(null, '', href);
        })
        .catch(function (err) { if (!err || err.name !== 'AbortError') location.href = href; });
    }
    function sync() {
      $$('[name=lloji][aria-pressed]', lib).forEach(function (b) { b.setAttribute('aria-pressed', String(b.value === typeInput.value)); });
    }
    lib.addEventListener('submit', function (e) {
      e.preventDefault();
      var sub = e.submitter;
      if (sub && sub.name === 'lloji') { typeInput.value = sub.value; sync(); }
      load(url(), true);
    });
    lib.elements.q.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(function () { load(url(), true); }, 220); });
    $$('select', lib).forEach(function (sel) { sel.addEventListener('change', function () { load(url(), true); }); });
    results.addEventListener('click', function (e) {
      var a = e.target.closest('.pager a');
      if (!a) return;
      e.preventDefault();
      load(a.href, true);
      lib.scrollIntoView({ block: 'start' });
    });
  }
})();
