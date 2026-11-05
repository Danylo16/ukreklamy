/* global wp */
(() => {
  'use strict';
  if (!window.wp?.media) return;
  function setupPicker(prefix, title) {
    const select = document.getElementById(`ukr_${prefix}_select`);
    const remove = document.getElementById(`ukr_${prefix}_remove`);
    const input = document.getElementById(`ukr_${prefix}_image_id`);
    const image = document.getElementById(`ukr_${prefix}_preview_img`);
    const empty = document.getElementById(`ukr_${prefix}_empty`);
    if (!select || !remove || !input || !image || !empty) return;
    let frame;
    const update = (id, src, alt = '') => {
      input.value = id ? String(id) : '0';
      image.src = src || '';
      image.alt = alt;
      image.hidden = !src;
      empty.hidden = Boolean(src);
      remove.disabled = !src;
    };
    select.addEventListener('click', () => {
      if (!frame) {
        frame = wp.media({title, button: {text:'Використати фото'}, library:{type:'image'}, multiple:false});
        frame.on('select', () => {
          const data = frame.state().get('selection').first()?.toJSON();
          if (data) update(data.id, data.sizes?.large?.url || data.sizes?.medium?.url || data.url, data.alt || '');
        });
      }
      frame.open();
    });
    remove.addEventListener('click', () => update(0, ''));
  }
  setupPicker('hero', 'Виберіть головне фото UKreklamy');
  setupPicker('team', 'Виберіть фото команди / виробництва');
})();
