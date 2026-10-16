/* INPO blocks in the editor. No build step: plain JS on the wp.* globals.
   Each block is previewed with ServerSideRender and edited from the sidebar.
   Field list per block: [attribute, label, type, options]. */
(function (wp) {
  'use strict';
  var el = wp.element.createElement;
  var be = wp.blockEditor;
  var c = wp.components;
  var useSelect = wp.data.useSelect;

  var DIRECT = 'Empty = automatic';
  var IMPACT_SOURCES = [['manual', 'Number typed below'], ['reagime', 'Count: news in Reagime dhe qëndrime publike'], ['publikime', 'Count: publications'], ['monitorime', 'Count: monitoring reports'], ['projekte', 'Count: projects'], ['vite', 'Years since 2005']];
  var POSTS = 'post';
  // Field labels are English (wp-admin); the values typed in are public site copy (Albanian).
  var FIELDS = {
    'inpo/hero': [['line1', 'Headline, line 1'], ['line2', 'Headline, line 2 (indigo)'], ['lead', 'Intro', 'textarea'], ['cta1Text', 'Button 1: text'], ['cta1Url', 'Button 1: link', 'text', DIRECT], ['cta2Text', 'Button 2: text'], ['cta2Url', 'Button 2: link', 'text', DIRECT], ['areasLabel', 'Fields of action: label'], ['areas', 'Fields of action', 'textarea', 'One per line: Label | link. Empty = the two programmes and three thematic fields.'], ['showImage', 'Show the photo', 'toggle'], ['image', 'Photo', 'image', 'Empty = the photo from the report launch, February 2025.', 'imageCaption'], ['imageCaption', 'Photo caption', 'textarea']],
    'inpo/field': [['kicker', 'Kicker'], ['heading', 'Heading', 'textarea'], ['text', 'Text', 'textarea'], ['buttonText', 'Button text'], ['buttonUrl', 'Button link', 'text', 'Empty = the About page'], ['photos', 'Photos', 'gallery', 'The first is shown large. Captions come from Media (edit them below). Empty = five photos from INPO events (2023 to 2024).']],
    'inpo/bento': [
      ['heading', 'Heading (key findings)'], ['linkText', 'Link text'], ['linkUrl', 'Link', 'text', 'Empty = news filtered by "Reagime dhe qëndrime publike"'], ['label', 'Label for screen readers (when there is no heading)'],
      ['featuredId', 'Featured story', POSTS], ['featuredTitle', 'Title on the card', 'text', 'Empty = the story\'s short title'],
      ['stat1Id', 'Figure 1: story', POSTS], ['stat1Label', 'Figure 1: label'], ['stat1Number', 'Figure 1: number'], ['stat1Prefix', 'Figure 1: before the number'], ['stat1Suffix', 'Figure 1: after the number'], ['stat1Decimals', 'Figure 1: decimals', 'number'], ['stat1Text', 'Figure 1: text', 'textarea'],
      ['stat2Id', 'Figure 2: story', POSTS], ['stat2Label', 'Figure 2: label'], ['stat2Number', 'Figure 2: number'], ['stat2Prefix', 'Figure 2: before the number'], ['stat2Suffix', 'Figure 2: after the number'], ['stat2Decimals', 'Figure 2: decimals', 'number'], ['stat2Text', 'Figure 2: text', 'textarea']
    ],
    'inpo/programs': [['kicker', 'Kicker'], ['heading', 'Heading', 'textarea'], ['linkLabel', 'Link text']],
    'inpo/publications': [
      ['mode', 'Show', 'select', [['latest', 'Latest'], ['related', 'Related to the current publication'], ['program', 'From the current programme']]],
      ['kicker', 'Kicker'], ['heading', 'Heading'], ['count', 'Number', 'number'],
      ['columns', 'Columns', 'select', [['4', '4'], ['3', '3']]],
      ['type', 'Only this type (slug)', 'text', 'E.g. analiza'], ['program', 'Only this programme (slug)', 'text', 'E.g. qeverisje'],
      ['showChips', 'Show type filters in the header', 'toggle'], ['linkText', 'Button text'], ['linkUrl', 'Button link', 'text', DIRECT]
    ],
    'inpo/publication-library': [['placeholder', 'Search placeholder'], ['allLabel', '"All" filter label'], ['programLabel', 'Programme filter label']],
    'inpo/news-list': [
      ['layout', 'Layout', 'select', [['intro', 'With an intro on the left'], ['section', 'Heading and button'], ['plain', 'List only (page results)']]],
      ['kicker', 'Kicker'], ['heading', 'Heading'], ['lead', 'Intro', 'textarea'], ['linkText', 'Button text'],
      ['count', 'Number', 'number'], ['category', 'Only this category (slug)'],
      ['skipShown', 'Skip stories shown higher on the page', 'toggle'], ['excludeCurrent', 'Leave out the current story', 'toggle'], ['inherit', 'Use the page query', 'toggle']
    ],
    'inpo/news-grid': [['placeholder', 'Search placeholder'], ['allLabel', '"All" filter label'], ['programLabel', 'Programme filter label']],
    'inpo/donors': [['kicker', 'Kicker'], ['heading', 'Heading', 'text', 'Optional'], ['anchor', 'Anchor (for links like #partneret)'], ['showFineprint', 'Show the grant note', 'toggle']],
    'inpo/team': [['heading', 'Heading', 'text', 'Optional'], ['teamLabel', 'Team label'], ['boardLabel', 'Board label'], ['wrapped', 'Own section (off inside a page section)', 'toggle'], ['anchor', 'Anchor']],
    'inpo/newsletter': [['heading', 'Heading'], ['text', 'Text', 'textarea'], ['label', 'Field label'], ['placeholder', 'Field placeholder'], ['button', 'Button']],
    'inpo/contact': [['submit', 'Button text']],
    'inpo/page-head': [
      ['source', 'Source', 'select', [['custom', 'The text below'], ['post', 'Current page'], ['archive', 'Current archive'], ['search', 'Search'], ['404', 'Page not found']]],
      ['kicker', 'Kicker'], ['title', 'Title'], ['lead', 'Intro', 'textarea']
    ],
    'inpo/impact': [
      ['kicker', 'Kicker'], ['heading', 'Heading'], ['tone', 'Style', 'select', [['accent', 'Indigo panel'], ['light', 'Light panel']]], ['buttonText', 'Button text'], ['buttonUrl', 'Button link', 'text', DIRECT], ['anchor', 'Anchor (e.g. ndikimi)'],
      ['item1Source', 'Figure 1: source', 'select', IMPACT_SOURCES], ['item1Number', 'Figure 1: number (manual)'], ['item1Label', 'Figure 1: label'],
      ['item2Source', 'Figure 2: source', 'select', IMPACT_SOURCES], ['item2Number', 'Figure 2: number (manual)'], ['item2Label', 'Figure 2: label'],
      ['item3Source', 'Figure 3: source', 'select', IMPACT_SOURCES], ['item3Number', 'Figure 3: number (manual)'], ['item3Label', 'Figure 3: label'],
      ['item4Source', 'Figure 4: source', 'select', IMPACT_SOURCES], ['item4Number', 'Figure 4: number (manual)'], ['item4Label', 'Figure 4: label']
    ],
    'inpo/projects': [['kicker', 'Kicker'], ['heading', 'Heading'], ['count', 'Number of projects', 'number'], ['linkText', 'Button text'], ['showDonors', 'Show donors and partners', 'toggle'], ['donorsHeading', 'Donors heading']],
    'inpo/project-list': [['allLabel', '"All" filter label'], ['activeLabel', 'Active filter label'], ['doneLabel', 'Completed filter label'], ['links', 'Section links', 'textarea', 'One per line: anchor | Label']],
    'inpo/project-detail': [],
    'inpo/impact-cases': [['kicker', 'Kicker'], ['heading', 'Heading'], ['text', 'Text', 'textarea'], ['count', 'Number of news examples', 'number'], ['anchor', 'Anchor']],
    'inpo/documents': [['layout', 'Layout', 'select', [['grouped', 'All categories, grouped'], ['list', 'A plain list']]], ['categories', 'Only these categories (slugs, comma separated)', 'text', 'Empty = all'], ['showLinks', 'Show category links', 'toggle'], ['yearFilter', 'Show the year filter', 'toggle'], ['wrapped', 'Own section (off inside a page section)', 'toggle'], ['anchor', 'Anchor']],
    'inpo/dataset-list': [['notice', 'Notice above the list', 'textarea']],
    'inpo/dataset': [['notice', 'Notice above the chart', 'textarea']],
    'inpo/section-links': [['label', 'Label'], ['items', 'Links', 'textarea', 'One per line: anchor | Label, e.g. historia | Historia e organizatës']],
    'inpo/listing': [['for', 'List', 'select', [['publikimet', 'Publications'], ['projektet', 'Projects'], ['te-dhenat', 'Data'], ['lajme', 'News']]], ['part', 'Show', 'select', [['head', 'Heading and intro'], ['content', 'Sections added under the list']]]],
    'inpo/article': [],
    'inpo/publication-detail': [],
    'inpo/program-page': [],
    'inpo/transparency': [['heading', 'Heading'], ['auditTitle', 'Audit: title'], ['auditText', 'Audit: text', 'textarea'], ['auditCount', 'Number of reports', 'number'], ['allText', 'All reports link'], ['jobsTitle', 'Jobs: title'], ['jobsText', 'Jobs: text', 'textarea'], ['jobsButton', 'Jobs: button']]
  };

  function PostSelect(props) {
    var posts = useSelect(function (select) {
      return select('core').getEntityRecords('postType', 'post', { per_page: 50, _fields: 'id,title' });
    }, []);
    var options = [{ value: 0, label: 'Automatic (by slug, else the latest)' }].concat((posts || []).map(function (p) {
      return { value: p.id, label: p.title.rendered || ('#' + p.id) };
    }));
    return el(c.SelectControl, { label: props.label, value: props.value || 0, options: options, onChange: function (v) { props.onChange(parseInt(v, 10) || 0); }, __nextHasNoMarginBottom: true });
  }

  // "Choose from Media": one image (stores its URL) or several (lines "URL | caption", the caption from Media).
  function MediaField(props) {
    var multiple = props.multiple;
    var button = function (open) {
      return el(c.Button, { variant: 'secondary', onClick: open }, multiple ? 'Choose photos from Media' : (props.value ? 'Replace photo' : 'Choose photo from Media'));
    };
    var picked = function (media) {
      var list = [].concat(media).map(function (m) {
        var url = (m.sizes && m.sizes.large ? m.sizes.large.url : m.url) || '';
        var cap = (m.caption && m.caption.raw !== undefined ? m.caption.raw : m.caption) || m.alt || m.title || '';
        return multiple ? url + ' | ' + String(cap).replace(/<[^>]+>/g, '').trim() : url;
      });
      props.onChange(list.join('\n'));
      if (!multiple && props.onCaption) {
        var m = [].concat(media)[0];
        var cap = (m.caption && m.caption.raw !== undefined ? m.caption.raw : m.caption) || m.alt || '';
        if (cap) props.onCaption(String(cap).replace(/<[^>]+>/g, '').trim());
      }
    };
    return el('div', { style: { display: 'grid', gap: '8px' } },
      el('span', { style: { fontWeight: 500 } }, props.label),
      !multiple && props.value ? el('img', { src: props.value, alt: '', style: { width: '100%', height: 'auto', borderRadius: '8px' } }) : null,
      el(be.MediaUploadCheck, null, el(be.MediaUpload, { onSelect: picked, allowedTypes: ['image'], multiple: multiple ? 'add' : false, gallery: false, render: function (o) { return button(o.open); } })),
      props.value ? el(c.Button, { variant: 'link', isDestructive: true, onClick: function () { props.onChange(''); } }, multiple ? 'Use the default photos' : 'Use the default photo') : null,
      props.help ? el('p', { style: { margin: 0, fontSize: '12px', color: '#757575' } }, props.help) : null
    );
  }

  function control(f, attrs, set) {
    var key = f[0], label = f[1], type = f[2] || 'text', extra = f[3];
    var change = function (v) { var o = {}; o[key] = v; set(o); };
    var common = { key: key, label: label, help: typeof extra === 'string' ? extra : undefined, __nextHasNoMarginBottom: true };
    if (type === POSTS) return el(PostSelect, { key: key, label: label, value: attrs[key], onChange: change });
    if (type === 'image') return el(MediaField, { key: key, label: label, value: attrs[key], help: common.help, onChange: change, onCaption: f[4] ? function (v) { var o = {}; o[f[4]] = v; set(o); } : null });
    if (type === 'gallery') return el('div', { key: key, style: { display: 'grid', gap: '8px' } },
      el(MediaField, { label: label, value: attrs[key], multiple: true, help: common.help, onChange: change }),
      el(c.TextareaControl, { label: 'Photos and captions (one per line: URL | caption)', value: attrs[key] || '', onChange: change, __nextHasNoMarginBottom: true }));
    if (type === 'textarea') return el(c.TextareaControl, Object.assign(common, { value: attrs[key] || '', onChange: change }));
    if (type === 'toggle') return el(c.ToggleControl, Object.assign(common, { checked: !!attrs[key], onChange: change }));
    if (type === 'number') return el(c.TextControl, Object.assign(common, { type: 'number', value: attrs[key], onChange: function (v) { change(parseInt(v, 10) || 0); } }));
    if (type === 'select') {
      var numeric = typeof attrs[key] === 'number';
      return el(c.SelectControl, Object.assign(common, {
        value: String(attrs[key]),
        options: extra.map(function (o) { return { value: o[0], label: o[1] }; }),
        onChange: function (v) { change(numeric ? parseInt(v, 10) : v); }
      }));
    }
    return el(c.TextControl, Object.assign(common, { value: attrs[key] || '', onChange: change }));
  }

  Object.keys(FIELDS).forEach(function (name) {
    wp.blocks.registerBlockType(name, {
      edit: function (props) {
        var fields = FIELDS[name];
        var query = props.context && props.context.postId ? { post_id: props.context.postId } : undefined;
        return el('div', be.useBlockProps(),
          fields.length ? el(be.InspectorControls, null,
            el(c.PanelBody, { title: 'Settings', initialOpen: true },
              el('div', { style: { display: 'grid', gap: '16px' } }, fields.map(function (f) { return control(f, props.attributes, props.setAttributes); }))
            )
          ) : null,
          el(c.Disabled, null, el(wp.serverSideRender, { block: name, attributes: props.attributes, urlQueryArgs: query }))
        );
      },
      save: function () { return null; }
    });
  });
})(window.wp);
