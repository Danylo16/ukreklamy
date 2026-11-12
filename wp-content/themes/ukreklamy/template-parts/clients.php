<?php
/** Only real, published customer logos are displayed. */
$clients = class_exists('UKR_Editorial_Sections') ? UKR_Editorial_Sections::entries('ukr_client') : [];
$editor_preview = !$clients && current_user_can('edit_posts');
if (!$clients && !$editor_preview) return;
?>
<section class="ukr-clients clients ukr-clients-v113 ukr-clients-v114" id="clients" aria-labelledby="ukr-clients-title">
  <div class="wrap ukr-clients-layout">
    <div class="ukr-clients-heading">
      <?php echo ukr_optional_eyebrow('clients_eyebrow', 'Наші клієнти'); ?>
      <h2 id="ukr-clients-title"><?php echo esc_html(ukr_copy('clients_title', 'Нам довіряють')); ?></h2>
    </div>
    <?php if ($editor_preview): ?>
      <div class="ukr-editorial-empty" role="status">
        <strong>Клієнтів ще не видно.</strong> Додайте опублікований запис із логотипом.
        <a href="<?php echo esc_url(admin_url('admin.php?page=ukr-editorial-status')); ?>">Перевірити клієнтів</a>
      </div>
    <?php else: ?>
      <div class="ukr-clients-gallery" data-ukr-carousel="clients">
        <button type="button" class="ukr-carousel-nav ukr-clients-prev" aria-label="Попередні логотипи" disabled>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M19 12H5m7-7-7 7 7 7"/></svg>
        </button>
        <div class="ukr-clients-track" tabindex="0" role="region" aria-roledescription="карусель" aria-label="Логотипи клієнтів">
          <?php foreach ($clients as $client):
            $name = get_the_title($client);
            $logo = get_post_thumbnail_id($client->ID);
            $url = (string)get_post_meta($client->ID, '_ukr_client_url', true);
          ?>
          <?php if ($url): ?><a class="ukr-client-logo" href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr($name.' — відвідати сайт'); ?>">
          <?php else: ?><div class="ukr-client-logo" aria-label="<?php echo esc_attr($name); ?>">
          <?php endif; ?>
            <?php echo wp_get_attachment_image($logo, 'medium', false, ['loading'=>'lazy', 'decoding'=>'async', 'alt'=>'Логотип: '.$name]); ?>
          <?php if ($url): ?></a><?php else: ?></div><?php endif; ?>
          <?php endforeach; ?>
        </div>
        <button type="button" class="ukr-carousel-nav ukr-clients-next" aria-label="Наступні логотипи">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 12h14m-7-7 7 7-7 7"/></svg>
        </button>
        <span class="ukr-clients-count sr-only" aria-live="polite">1 / <?php echo count($clients); ?></span>
      </div>
    <?php endif; ?>
  </div>
</section>
