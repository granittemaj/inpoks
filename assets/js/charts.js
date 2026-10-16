/* INPO data views: an interactive SVG chart (bars or lines) and a sortable table, from one dataset.
   No dependencies. Shared by the prototype (assets/js/charts.js) and the WordPress plugin
   (wordpress/inpo-core/assets/charts.js): keep the two files identical.
   Usage: <div data-dataset='{"cols":["Viti","Vlera"],"rows":[["2023",12]],"chart":"bar","unit":"tenderë","file":"emri"}'></div>
   then inpoCharts.mount(el). The element's own content (a plain table) is the no-JS fallback. */
(function () {
  'use strict';
  var NS = 'http://www.w3.org/2000/svg';
  var esc = function (s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); };
  // Kosovo convention: 1.250,5 (dot for thousands, comma for decimals).
  var fmt = function (n) {
    if (typeof n !== 'number') return n === null ? '' : String(n);
    var parts = (Math.round(n * 100) / 100).toString().split('.');
    return parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.') + (parts[1] ? ',' + parts[1] : '');
  };
  // Axis labels stay short: 600 mln instead of 600.000.000.
  var tick = function (n) { return Math.abs(n) >= 1e6 ? fmt(Math.round(n / 1e5) / 10) + ' mln' : fmt(Math.round(n * 100) / 100); };
  var num = function (v) { var n = parseFloat(String(v).replace(/\s/g, '').replace(',', '.')); return isNaN(n) ? null : n; };
  function svg(tag, attrs) { var e = document.createElementNS(NS, tag); for (var k in attrs) e.setAttribute(k, attrs[k]); return e; }
  // A round upper bound and step for the value axis.
  function scale(max) {
    if (max <= 0) return { max: 1, step: 1 };
    var raw = max / 4, mag = Math.pow(10, Math.floor(Math.log10(raw))), f = raw / mag;
    var step = (f <= 1 ? 1 : f <= 2 ? 2 : f <= 2.5 ? 2.5 : f <= 5 ? 5 : 10) * mag;
    return { max: Math.ceil(max / step) * step, step: step };
  }

  function mount(el) {
    var d;
    try { d = JSON.parse(el.getAttribute('data-dataset')); } catch (e) { return; }
    var cols = d.cols || [], rows = (d.rows || []).map(function (r) { return [String(r[0])].concat(r.slice(1).map(num)); });
    var series = cols.slice(1), unit = d.unit || '';
    var id = 'ds' + Math.random().toString(36).slice(2, 8);
    var sortCol = -1, sortDir = 1, active = -1;

    var tableOnly = d.chart === 'table';
    el.innerHTML =
      (tableOnly ? '<div class="chart-top"><span></span><button type="button" class="btn btn-line btn-sm chart-dl">Shkarko CSV</button></div><p class="sr" aria-live="polite" id="' + id + '-live"></p>' : '<div class="chart-card">' +
        '<div class="chart-top">' +
          '<ul class="chart-legend" aria-label="Seritë">' + series.map(function (s, i) { return '<li><i class="sw s' + (i % 3) + '"></i>' + esc(s) + '</li>'; }).join('') + '</ul>' +
          '<button type="button" class="btn btn-line btn-sm chart-dl">Shkarko CSV</button>' +
        '</div>' +
        '<div class="chart" tabindex="0" role="group" aria-roledescription="grafik" aria-label="' + esc((d.title || 'Grafik') + '. Përdorni shigjetat majtas dhe djathtas për të lexuar vlerat.') + '">' +
          '<div class="chart-tip" aria-hidden="true" hidden></div>' +
        '</div>' +
        '<p class="sr" aria-live="polite" id="' + id + '-live"></p>' +
      '</div>') +
      '<div class="dtable-wrap"><table class="dtable" id="' + id + '-t"><caption class="sr">' + esc(d.title || '') + '</caption><thead><tr>' +
        cols.map(function (c, i) { return '<th scope="col"' + (i ? ' class="n"' : '') + ' aria-sort="none"><button type="button" data-col="' + i + '">' + esc(c) + '<span aria-hidden="true"></span></button></th>'; }).join('') +
      '</tr></thead><tbody></tbody></table></div>';

    var box = el.querySelector('.chart'), tip = el.querySelector('.chart-tip'), live = el.querySelector('#' + id + '-live');
    var tbody = el.querySelector('tbody');

    function label(i) {
      var r = rows[i];
      return r[0] + ': ' + series.map(function (s, k) { return s + ' ' + fmt(r[k + 1]) + (unit ? ' ' + unit : ''); }).join(', ');
    }
    function draw() {
      if (!box) return;
      var old = box.querySelector('svg'); if (old) old.remove();
      var W = Math.max(280, box.clientWidth), H = W < 640 ? 260 : 340;
      var max = 0;
      rows.forEach(function (r) { r.slice(1).forEach(function (v) { if (v !== null && v > max) max = v; }); });
      var sc = scale(max);
      // Room on the left for the longest axis label.
      var m = { l: Math.max(44, tick(sc.max).length * 7.5 + 18), r: 12, t: 16, b: 40 }, iw = W - m.l - m.r;
      // Category labels that do not fit under their bar are tilted, with room below for them.
      var longest = rows.reduce(function (n, r) { return Math.max(n, r[0].length); }, 0) * 7;
      var tilt = longest > iw / Math.max(1, rows.length) - 8;
      if (tilt) { m.b = Math.min(110, 30 + longest * 0.6); H += m.b - 40; }
      var ih = H - m.t - m.b;
      var y = function (v) { return m.t + ih - (v / sc.max) * ih; };
      var s = svg('svg', { viewBox: '0 0 ' + W + ' ' + H, width: W, height: H, 'aria-hidden': 'true' });
      for (var t = 0; t <= sc.max + 1e-9; t += sc.step) {
        s.appendChild(svg('line', { class: 'grid', x1: m.l, x2: W - m.r, y1: y(t), y2: y(t) }));
        var tl = svg('text', { class: 'tick', x: m.l - 10, y: y(t) + 4, 'text-anchor': 'end' }); tl.textContent = tick(t); s.appendChild(tl);
      }
      var band = iw / Math.max(1, rows.length);
      // Highlight for the active category, under the data.
      var hl = svg('rect', { class: 'band-hl', x: 0, y: m.t, width: band, height: ih, rx: 8, visibility: 'hidden' });
      s.appendChild(hl);
      if (d.chart === 'line') {
        series.forEach(function (name, k) {
          var pts = [];
          rows.forEach(function (r, i) { if (r[k + 1] !== null) pts.push([m.l + band * i + band / 2, y(r[k + 1])]); });
          s.appendChild(svg('polyline', { class: 'ln s' + (k % 3), points: pts.map(function (p) { return p.join(','); }).join(' ') }));
          pts.forEach(function (p) { s.appendChild(svg('circle', { class: 'pt s' + (k % 3), cx: p[0], cy: p[1], r: 4.5 })); });
        });
      } else {
        var gw = band * 0.68, bw = gw / Math.max(1, series.length);
        rows.forEach(function (r, i) {
          series.forEach(function (name, k) {
            var v = r[k + 1]; if (v === null) return;
            var x = m.l + band * i + (band - gw) / 2 + bw * k;
            s.appendChild(svg('rect', { class: 'bar s' + (k % 3), x: x + 1, y: y(v), width: Math.max(2, bw - 2), height: Math.max(0, m.t + ih - y(v)), rx: Math.min(6, bw / 3) }));
          });
        });
      }
      rows.forEach(function (r, i) {
        var cx = m.l + band * i + band / 2, ty = tilt ? m.t + ih + 16 : H - 14;
        var tx = svg('text', tilt ? { class: 'tick x', x: cx, y: ty, 'text-anchor': 'end', transform: 'rotate(-35 ' + cx + ' ' + ty + ')' } : { class: 'tick x', x: cx, y: ty, 'text-anchor': 'middle' });
        tx.textContent = r[0]; s.appendChild(tx);
      });
      box.insertBefore(s, tip);
      function show(i, fromKey) {
        active = i;
        if (i < 0) { hl.setAttribute('visibility', 'hidden'); tip.hidden = true; return; }
        hl.setAttribute('x', m.l + band * i); hl.setAttribute('visibility', 'visible');
        tip.innerHTML = '<b>' + esc(rows[i][0]) + '</b>' + series.map(function (n, k) { return '<span><i class="sw s' + (k % 3) + '"></i>' + esc(n) + ' <strong>' + fmt(rows[i][k + 1]) + '</strong></span>'; }).join('');
        tip.hidden = false;
        var left = (m.l + band * i + band / 2) / W * box.clientWidth;
        tip.style.left = Math.min(Math.max(left, 90), box.clientWidth - 90) + 'px';
        if (fromKey) live.textContent = label(i);
      }
      box.onpointermove = function (e) {
        var r = box.getBoundingClientRect(), x = (e.clientX - r.left) / r.width * W;
        var i = Math.floor((x - m.l) / band);
        show(i >= 0 && i < rows.length ? i : -1);
      };
      box.onpointerleave = function () { show(-1); };
      box.onkeydown = function (e) {
        if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
          e.preventDefault();
          var i = active < 0 ? 0 : Math.min(rows.length - 1, Math.max(0, active + (e.key === 'ArrowRight' ? 1 : -1)));
          show(i, true);
        } else if (e.key === 'Escape') { show(-1); }
      };
      box.onblur = function () { show(-1); };
    }
    function table() {
      var list = rows.slice();
      if (sortCol >= 0) list.sort(function (a, b) {
        var x = a[sortCol], y2 = b[sortCol];
        return (typeof x === 'number' && typeof y2 === 'number' ? x - y2 : String(x).localeCompare(String(y2), 'sq')) * sortDir;
      });
      tbody.innerHTML = list.map(function (r) { return '<tr>' + r.map(function (v, i) { return i ? '<td class="n">' + fmt(v) + '</td>' : '<th scope="row">' + esc(v) + '</th>'; }).join('') + '</tr>'; }).join('');
      el.querySelectorAll('th[aria-sort]').forEach(function (th, i) {
        th.setAttribute('aria-sort', i === sortCol ? (sortDir > 0 ? 'ascending' : 'descending') : 'none');
        th.querySelector('span').textContent = i === sortCol ? (sortDir > 0 ? ' ↑' : ' ↓') : '';
      });
    }
    el.querySelector('thead').addEventListener('click', function (e) {
      var b = e.target.closest('button[data-col]'); if (!b) return;
      var c = +b.getAttribute('data-col');
      sortDir = c === sortCol ? -sortDir : 1; sortCol = c; table();
    });
    el.querySelector('.chart-dl').addEventListener('click', function () {
      var q = function (v) { v = v === null ? '' : String(v); return /[",\n]/.test(v) ? '"' + v.replace(/"/g, '""') + '"' : v; };
      var csv = [cols].concat(rows).map(function (r) { return r.map(q).join(','); }).join('\n');
      var a = document.createElement('a');
      a.href = URL.createObjectURL(new Blob(['﻿' + csv], { type: 'text/csv;charset=utf-8' }));
      a.download = (d.file || 'te-dhenat') + '.csv';
      document.body.appendChild(a); a.click(); a.remove();
      setTimeout(function () { URL.revokeObjectURL(a.href); }, 1000);
    });
    draw(); table();
    if (box && window.ResizeObserver) {
      var w = box.clientWidth, t;
      new ResizeObserver(function () { if (Math.abs(box.clientWidth - w) < 2) return; w = box.clientWidth; clearTimeout(t); t = setTimeout(draw, 80); }).observe(box);
    }
  }

  window.inpoCharts = { mount: mount };
  document.querySelectorAll('[data-dataset]').forEach(mount);
})();
