/* WordPress media picker used only on the service-category edit screens. */
(function($){'use strict';
  $(document).on('click','.ukr-cat-pick',function(){
    const picker=wp.media({title:'Фото категорії',button:{text:'Використати фото'},library:{type:'image'},multiple:false});
    picker.on('select',function(){
      const photo=picker.state().get('selection').first().toJSON();
      $('#ukr_cat_image').val(photo.id);
      $('.ukr-cat-preview').empty().append($('<img>',{src:(photo.sizes?.thumbnail?.url||photo.url),alt:'Попередній перегляд',style:'max-width:140px;height:auto'}));
    });picker.open();
  });
  $(document).on('click','.ukr-cat-clear',function(){$('#ukr_cat_image').val('');$('.ukr-cat-preview').empty();});
})(jQuery);
