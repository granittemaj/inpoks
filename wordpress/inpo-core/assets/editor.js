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
  var POSTS = 'post';
  // Field labels are English (wp-admin); the values typed in are public site copy (Albanian).
  var FIELDS = {
    'inpo/hero': [['badge', 'Badge'], ['line1', 'Headline, line 1'], ['line2', 'Headline, line 2 (indigo)'], ['lead', 'Intro', 'textarea'], ['cta1Text', 'Button 1: text'], ['cta1Url', 'Button 1: link', 'text', DIRECT], ['cta2Text', 'Button 2: text'], ['cta2Url', 'Button 2: link', 'text', DIRECT]],
    'inpo/bento': [
      ['label', 'Label for screen readers'],
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
    'inpo/publication-library': [['placeholder', 'Search placeholder'], ['programLabel', 'Programme filter label']],
    'inpo/news-list': [
      ['layout', 'Layout', 'select', [['intro', 'With an intro on the left'], ['section', 'Heading and button'], ['plain', 'List only (page results)']]],
      ['kicker', 'Kicker'], ['heading', 'Heading'], ['lead', 'Intro', 'textarea'], ['linkText', 'Button text'],
      ['count', 'Number', 'number'], ['category', 'Only this category (slug)'],
      ['skipShown', 'Skip stories shown higher on the page', 'toggle'], ['excludeCurrent', 'Leave out the current story', 'toggle'], ['inherit', 'Use the page query', 'toggle']
    ],
    'inpo/news-grid': [['allLabel', '"All" filter label']],
    'inpo/donors': [['kicker', 'Kicker'], ['showFineprint', 'Show the grant note', 'toggle']],
    'inpo/team': [['heading', 'Heading']],
    'inpo/newsletter': [['heading', 'Heading'], ['text', 'Text', 'textarea'], ['label', 'Field label'], ['placeholder', 'Field placeholder'], ['button', 'Button']],
    'inpo/contact': [['submit', 'Button text']],
    'inpo/page-head': [
      ['source', 'Source', 'select', [['custom', 'The text below'], ['post', 'Current page'], ['archive', 'Current archive'], ['search', 'Search'], ['404', 'Page not found']]],
      ['kicker', 'Kicker'], ['title', 'Title'], ['lead', 'Intro', 'textarea']
    ],
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

  function control(f, attrs, set) {
    var key = f[0], label = f[1], type = f[2] || 'text', extra = f[3];
    var change = function (v) { var o = {}; o[key] = v; set(o); };
    var common = { key: key, label: label, help: typeof extra === 'string' ? extra : undefined, __nextHasNoMarginBottom: true };
    if (type === POSTS) return el(PostSelect, { key: key, label: label, value: attrs[key], onChange: change });
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
