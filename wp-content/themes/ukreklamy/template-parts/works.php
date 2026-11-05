<?php
/** Editorial portfolio carousel and accessible, image-rich case study dialog. */
$items = ukr_posts('ukr_work');
?>
<section class="section wrap ukr-works-v113 ukr-works-v114" id="works" aria-labelledby="ukr-works-heading">
  <div class="sectionhead">
    <div><span class="eyebrow">Виконаний проєкт</span>
      <h2 id="ukr-works-heading"><?php echo esc_html(ukr_copy('works_title', 'Роботи говорять краще')); ?></h2></div>
    <p><?php echo nl2br(esc_html(ukr_copy('works_description', 'Реальні роботи компанії з фотографіями та описами.'))); ?></p>
  </div>
  <?php if ($items): ?>
  <div class="ukr-works-gallery" data-ukr-carousel="works">
    <button type="button" class="ukr-carousel-nav ukr-works-prev" aria-label="Попередня робота" disabled>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M19 12H5m7-7-7 7 7 7"/></svg>
    </button>
    <div class="worktrack" tabindex="0" role="region" aria-roledescription="карусель" aria-label="Реалізовані проєкти">
    <?php foreach($items as $item):
      $work_id = (int)$item->ID;
      $title = get_the_title($item);
      $excerpt = trim(wp_strip_all_tags($item->post_excerpt ?: $item->post_content));
      $short = wp_trim_words($excerpt, 23, '…');
      $featured = get_post_thumbnail_id($work_id);
      $gallery = array_values(array_filter(array_map('absint', explode(',', (string)get_post_meta($work_id, '_ukr_gallery', true))), 'wp_attachment_is_image'));
      $images = array_values(array_unique(array_filter(array_merge([$featured], $gallery))));
    ?>
      <article class="work ukr-work-card ukr-work-flip-card" data-ukr-work-card>
        <div class="ukr-work-flipper">
          <div class="ukr-work-face ukr-work-face-front">
            <button type="button" class="ukr-work-flip-trigger" aria-label="Показати короткий опис: <?php echo esc_attr($title); ?>" aria-pressed="false">
              <span class="ukr-work-front-photo">
                <?php if($images): ?>
                  <?php echo wp_get_attachment_image($images[0], 'large', false, ['loading'=>'lazy','decoding'=>'async','alt'=>$title,'sizes'=>'(max-width: 650px) 85vw, (max-width: 1100px) 45vw, 30vw']); ?>
                <?php else: ?><span class="ukr-work-no-image">Фото проєкту буде додано</span><?php endif; ?>
              </span>
              <span class="ukr-work-front-caption"><strong><?php echo esc_html($title); ?></strong><span class="ukr-work-front-action">Детальніше <span aria-hidden="true">↗</span></span></span>
            </button>
          </div>
          <div class="ukr-work-face ukr-work-face-back" inert aria-hidden="true">
            <div class="ukr-work-back-top"><span class="ukr-work-back-label">Виконана робота</span>
              <button type="button" class="ukr-work-unflip" aria-label="Повернути фотографію: <?php echo esc_attr($title); ?>"><span aria-hidden="true">↶</span></button></div>
            <h3><?php echo esc_html($title); ?></h3>
            <p><?php echo esc_html($short ?: 'Деталі виконаного проєкту можна переглянути на фотографіях.'); ?></p>
            <?php if ($images): ?>
              <button class="ukr-work-open" type="button" data-ukr-work="<?php echo $work_id; ?>" aria-haspopup="dialog" aria-label="Переглянути фотографії проєкту: <?php echo esc_attr($title); ?>">
                Переглянути фотографії
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 12h14m-7-7 7 7-7 7"/></svg>
              </button>
            <?php else: ?><span class="ukr-work-no-photos">Фотографії очікуються</span><?php endif; ?>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
    </div>
    <button type="button" class="ukr-carousel-nav ukr-works-next" aria-label="Наступна робота">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 12h14m-7-7 7 7-7 7"/></svg>
    </button>
    <span class="ukr-works-count sr-only" aria-live="polite">1 / <?php echo count($items); ?></span>
  </div>
  <?php foreach ($items as $item):
    $featured = get_post_thumbnail_id($item->ID);
    $gallery = array_values(array_filter(array_map('absint',explode(',',(string)get_post_meta($item->ID,'_ukr_gallery',true))), 'wp_attachment_is_image'));
    $images = array_values(array_unique(array_filter(array_merge([$featured], $gallery))));
    if (!$images) continue;
  ?>
  <template id="ukr-work-<?php echo (int)$item->ID; ?>">
    <div class="ukr-work-detail ukr-work-detail-v115">
      <div class="ukr-work-detail-images">
        <div class="ukr-work-detail-track" tabindex="0" aria-label="Фотографії проєкту">
          <?php foreach ($images as $i => $image_id): ?>
          <figure class="ukr-work-detail-slide" aria-label="Фото <?php echo (int) ($i + 1); ?> із <?php echo count($images); ?>">
            <?php echo wp_get_attachment_image($image_id, 'medium_large', false, ['loading'=>'lazy', 'decoding'=>'async', 'alt'=>'', 'aria-hidden'=>'true', 'class'=>'ukr-work-detail-ambient']); ?>
            <?php echo wp_get_attachment_image($image_id, 'large', false, ['loading'=>$i ? 'lazy':'eager', 'decoding'=>'async', 'alt'=>get_post_meta($image_id,'_wp_attachment_image_alt',true) ?: get_the_title($item), 'class'=>'ukr-work-detail-primary','sizes'=>'(max-width: 700px) 96vw, 980px']); ?>
          </figure>
          <?php endforeach; ?>
        </div>
        <?php if(count($images)>1): ?>
        <div class="ukr-work-detail-controls" aria-label="Навігація фотографіями">
          <button type="button" class="ukr-carousel-nav ukr-work-detail-prev" aria-label="Попереднє фото" disabled>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M19 12H5m7-7-7 7 7 7"/></svg>
          </button>
          <span class="ukr-work-detail-count" aria-live="polite">1 / <?php echo count($images); ?></span>
          <button type="button" class="ukr-carousel-nav ukr-work-detail-next" aria-label="Наступне фото">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 12h14m-7-7 7 7-7 7"/></svg>
          </button>
        </div>
        <div class="ukr-work-detail-thumbs" role="group" aria-label="Вибрати фотографію">
          <?php foreach ($images as $index => $image_id): ?>
          <button type="button" class="ukr-work-detail-thumb<?php echo $index === 0 ? ' is-active' : ''; ?>"
            data-ukr-work-thumb="<?php echo (int) $index; ?>" aria-label="Показати фото <?php echo (int) ($index+1); ?> із <?php echo count($images); ?>"
            aria-current="<?php echo $index === 0 ? 'true' : 'false'; ?>">
            <?php echo wp_get_attachment_image($image_id, 'thumbnail', false, ['loading'=>'lazy','decoding'=>'async','alt'=>'']); ?>
          </button>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
      <div class="ukr-work-detail-copy">
        <span class="eyebrow">Виконаний проєкт</span>
        <h2 id="ukr-work-dialog-title"><?php echo esc_html(get_the_title($item)); ?></h2>
        <?php
          $work_description = preg_replace('~<figure\b[^>]*>.*?</figure>~is', '', (string)$item->post_content);
          $work_description = preg_replace('~<a\b[^>]*>\s*<img\b[^>]*>\s*</a>~is', '', $work_description);
          $work_description = preg_replace('~<img\b[^>]*>~is', '', $work_description);
        ?>
        <?php if(trim(wp_strip_all_tags($work_description))): ?>
          <div class="ukr-work-detail-description"><?php echo wpautop(wp_kses_post($work_description)); ?></div>
        <?php endif; ?>
      </div>
    </div>
  </template>
  <?php endforeach; ?>
  <dialog class="ukr-work-dialog" id="ukr-work-dialog" aria-modal="true" aria-labelledby="ukr-work-dialog-title">
    <div class="ukr-work-dialog-surface">
      <button type="button" class="ukr-work-dialog-close" aria-label="Закрити перегляд проєкту">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
      <div id="ukr-work-dialog-content"></div>
    </div>
  </dialog>
  <?php else: ?>
    <p class="empty-state"><?php echo esc_html(ukr_copy('works_empty', 'Портфоліо готується до публікації.')); ?></p>
  <?php endif; ?>
</section>
