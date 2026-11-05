<?php
/** Five category cards; click to expand services, then open accessible service detail dialog. */
$categories = taxonomy_exists('ukr_service_category') ? get_terms(['taxonomy'=>'ukr_service_category','hide_empty'=>false]) : [];
if (is_wp_error($categories)) $categories = [];
$categories = array_values(array_filter($categories, static function ($category) {
    return (string) get_term_meta($category->term_id, '_ukr_cat_visible', true) !== '0' && (int)$category->parent === 0;
}));
usort($categories, static function($a,$b) {
    $order_a = (int)get_term_meta($a->term_id,'_ukr_cat_order',true);
    $order_b = (int)get_term_meta($b->term_id,'_ukr_cat_order',true);
    return ($order_a <=> $order_b) ?: strcmp($a->name,$b->name);
});
$services = ukr_posts('ukr_service');
$groups = []; $templates = [];
foreach ($services as $service) {
    if (get_post_meta($service->ID,'_ukr_visible',true) === '0') continue;
    $assigned = get_the_terms($service->ID,'ukr_service_category');
    if (!is_array($assigned)) continue;
    foreach ($assigned as $term) {
        $groups[$term->term_id][] = $service;
    }
    $templates[$service->ID] = $service;
}
?>
<section class="section wrap" id="services" aria-labelledby="ukr-services-heading">
  <div class="sectionhead"><div><span class="eyebrow"><?php echo esc_html(ukr_copy('services_eyebrow', 'Що виготовляємо')); ?></span><h2 id="ukr-services-heading"><?php echo esc_html(ukr_copy('services_title', 'Рішення для вашого бізнесу')); ?></h2></div><p><?php echo nl2br(esc_html(ukr_copy('services_description', 'П’ять основних напрямів. Оберіть напрям, щоб переглянути конкретні послуги.'))); ?></p></div>
  <div class="servicegrid ukr-catalog">
    <?php foreach ($categories as $category):
      $children = $groups[$category->term_id] ?? [];
      $panel_id = 'ukr-sublist-' . $category->term_id;
      $cat_photo = absint(get_term_meta($category->term_id,'_ukr_cat_image',true));
      $cat_desc = trim(wp_strip_all_tags($category->description));
    ?>
    <article class="service ukr-category">
      <?php if ($children): ?>
      <button class="ukr-category-trigger" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr($panel_id); ?>" aria-label="Переглянути послуги: <?php echo esc_attr($category->name); ?>">
      <?php else: ?>
      <div class="ukr-category-static">
      <?php endif; ?>
        <div class="visual<?php echo $cat_photo?' has-image':''; ?>">
          <?php if($cat_photo): echo wp_get_attachment_image($cat_photo,'medium_large',false,['loading'=>'lazy','alt'=>$category->name]); endif; ?>
        </div>
        <div class="servicebody"><h3><?php echo esc_html($category->name); ?></h3>
          <p><?php echo esc_html($cat_desc); ?></p>
          <div class="servicefoot">
            <span><?php echo $children ? ukr_copy('services_open','Переглянути послуги') : ukr_copy('services_empty_cat','Перелік уточнюється'); ?></span>
            <?php if ($children): ?><span class="ukr-category-chevron" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg></span><?php endif; ?>
          </div>
        </div>
      <?php if ($children): ?></button><?php else: ?></div><?php endif; ?>
      <?php if ($children): ?>
      <div class="ukr-subservices" id="<?php echo esc_attr($panel_id); ?>" aria-hidden="true" inert>
        <div class="ukr-subservices-inner"><ul class="ukr-subservices-list">
          <?php foreach ($children as $service): ?>
          <li><button class="ukr-subservice-btn" type="button" data-ukr-template="ukr-detail-<?php echo (int)$service->ID; ?>" data-ukr-title="<?php echo esc_attr(get_the_title($service)); ?>">
            <span><?php echo esc_html(get_the_title($service)); ?></span><svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 12h14m-7-7 7 7-7 7"/></svg>
          </button></li>
          <?php endforeach; ?>
        </ul></div>
      </div>
      <?php endif; ?>
    </article>
    <?php endforeach; ?>
    <?php if (!$categories): ?><p class="empty-state"><?php echo nl2br(esc_html(ukr_copy('services_empty_all', 'Напрями послуг ще не заповнені. Залиште запит у формі нижче.'))); ?></p><?php endif; ?>
  </div>
  <?php foreach ($templates as $service):
      $featured_id = get_post_thumbnail_id($service->ID);
      $extra_ids = array_values(array_filter(array_map('absint', explode(',', (string) get_post_meta($service->ID,'_ukr_service_gallery',true)))));
      $photo_ids = array_values(array_unique(array_filter(array_merge([$featured_id], $extra_ids), 'wp_attachment_is_image')));
      $desc = trim((string)$service->post_content);
      // Images belong to the gallery. Ignore legacy photos pasted into the rich-text description.
      $desc = preg_replace('~<figure\b[^>]*>.*?</figure>~is','',$desc);
      $desc = preg_replace('~<a\b[^>]*>\s*<img\b[^>]*>\s*</a>~is','',$desc);
      $desc = preg_replace('~<img\b[^>]*>~is','',$desc);
      $short = trim((string)get_post_meta($service->ID,'_ukr_short_description',true));
      $price = trim((string)get_post_meta($service->ID,'_ukr_price_from',true));
      $unit = trim((string)get_post_meta($service->ID,'_ukr_price_unit',true));
      $price_label = $price ? (preg_match('/^від\s/iu', $price) ? $price : 'від '.$price) : ukr_copy('modal_price_unknown','Ціна уточнюється');
  ?>
  <template id="ukr-detail-<?php echo (int)$service->ID; ?>">
    <div class="ukr-detail-layout">
      <div class="ukr-detail-media">
        <div class="ukr-gallery" role="region" aria-roledescription="карусель" aria-label="Фотографії: <?php echo esc_attr(get_the_title($service)); ?>">
          <div class="ukr-gallery-track" tabindex="0" aria-label="Гортайте фотографії горизонтально">
          <?php if($photo_ids): foreach($photo_ids as $index=>$image_id): ?>
            <div class="ukr-gallery-slide" role="group" aria-roledescription="слайд" aria-label="Фото <?php echo (int)($index+1); ?> із <?php echo count($photo_ids); ?>">
              <?php echo wp_get_attachment_image($image_id,'large',false,['alt'=>get_post_meta($image_id,'_wp_attachment_image_alt',true) ?: get_the_title($service),'loading'=>$index===0?'eager':'lazy','decoding'=>'async']); ?>
            </div>
          <?php endforeach; else: ?>
            <div class="ukr-gallery-slide ukr-gallery-empty"><span><?php echo esc_html(ukr_copy('modal_photo_empty', 'Фото послуги буде додано')); ?></span></div>
          <?php endif; ?>
          </div>
          <?php if(count($photo_ids)>1): ?>
            <button type="button" class="ukr-gallery-arrow ukr-gallery-prev" aria-label="Попереднє фото" disabled>‹</button>
            <button type="button" class="ukr-gallery-arrow ukr-gallery-next" aria-label="Наступне фото">›</button>
          <?php endif; ?>
        </div>
        <?php if(count($photo_ids)>1): ?>
          <div class="ukr-gallery-toolbar"><div class="ukr-gallery-thumbs" role="group" aria-label="Вибір фотографії">
            <?php foreach($photo_ids as $index=>$image_id): ?>
              <button type="button" class="ukr-gallery-thumb<?php echo $index===0?' is-current':''; ?>" data-ukr-slide="<?php echo (int)$index; ?>" aria-label="Показати фото <?php echo (int)($index+1); ?>" aria-current="<?php echo $index===0?'true':'false'; ?>">
                <?php echo wp_get_attachment_image($image_id,'thumbnail',false,['alt'=>'','loading'=>'lazy']); ?>
              </button>
            <?php endforeach; ?>
          </div><span class="ukr-gallery-count" aria-live="polite">01 / <?php echo str_pad((string)count($photo_ids),2,'0',STR_PAD_LEFT); ?></span></div>
        <?php endif; ?>
      </div>
      <div class="ukr-detail-main">
        <span class="eyebrow"><?php echo esc_html(ukr_copy('modal_eyebrow', 'Деталі послуги')); ?></span>
        <h2 class="ukr-detail-title" id="ukr-service-dialog-title"><?php echo esc_html(get_the_title($service)); ?></h2>
        <?php if ($short): ?><p class="ukr-detail-intro"><?php echo esc_html($short); ?></p><?php endif; ?>
        <?php if ($desc): ?><div class="ukr-detail-description"><?php echo wpautop(wp_kses_post($desc)); ?></div><?php else: ?><p class="ukr-detail-description"><?php echo esc_html(ukr_copy('modal_description_empty', 'Опис послуги буде додано.')); ?></p><?php endif; ?>
        <div class="ukr-detail-price"><span><?php echo esc_html(ukr_copy('modal_price_label', 'Орієнтовна вартість')); ?></span><strong><?php echo esc_html($price_label); ?></strong><?php if ($price && $unit): ?><small><?php echo esc_html($unit); ?></small><?php endif; ?></div>
        <a class="btn ukr-service-inquiry" href="#contact" data-ukr-ask-id="<?php echo (int)$service->ID; ?>" data-ukr-ask-name="<?php echo esc_attr(get_the_title($service)); ?>"><?php echo esc_html(ukr_copy('modal_cta', 'Запитати про послугу')); ?> <span aria-hidden="true">→</span></a>
      </div>
    </div>
  </template>
  <?php endforeach; ?>
</section>
<dialog id="ukr-service-dialog" class="ukr-service-dialog" aria-labelledby="ukr-service-dialog-title" aria-modal="true">
  <div class="ukr-dialog-surface">
    <button type="button" class="ukr-dialog-close" aria-label="Закрити вікно"><svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
    <div id="ukr-service-dialog-content" class="ukr-dialog-content"></div>
  </div>
</dialog>
