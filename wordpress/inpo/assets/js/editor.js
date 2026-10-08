/* Header and footer previews in the Site Editor. They have no settings:
   menus come from Appearance > Menus, details from INPO > Cilësimet. */
(function (wp) {
  'use strict';
  var el = wp.element.createElement;
  ['inpo/site-header', 'inpo/site-footer'].forEach(function (name) {
    wp.blocks.registerBlockType(name, {
      edit: function () {
        return el('div', wp.blockEditor.useBlockProps(), el(wp.components.Disabled, null, el(wp.serverSideRender, { block: name })));
      },
      save: function () { return null; }
    });
  });
})(window.wp);
