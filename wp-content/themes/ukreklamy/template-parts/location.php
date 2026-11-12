<?php
/** Privacy-aware map: external map content is not requested until a visitor asks to load it. */
if (!defined('ABSPATH')) exit;
$address = trim(ukr_contact('address'));
if ($address === '') $address = 'вул. Василя Тарновського, 34В, Чернігів, Україна';
$map_query = $address;
$embed_url = 'https://maps.google.com/maps?q=' . rawurlencode($map_query) . '&hl=uk&z=16&output=embed';
$direction_url = 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode($map_query);
?>
<section class="section ukr-location" id="location" aria-labelledby="ukr-location-title">
  <div class="wrap ukr-location-layout">
    <div class="ukr-location-description">
      <?php echo ukr_optional_eyebrow('map_eyebrow', 'Де нас знайти'); ?>
      <h2 id="ukr-location-title"><?php echo esc_html(ukr_copy('map_title', 'Ми в Чернігові')); ?></h2>
      <p><?php echo esc_html(ukr_copy('map_description', 'Адреса виробництва та місце, де можна нас знайти.')); ?></p>
      <address><?php echo esc_html($address); ?></address>
      <a class="btn ukr-location-route" href="<?php echo esc_url($direction_url); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html(ukr_copy('map_directions', 'Прокласти маршрут')); ?> <span aria-hidden="true">↗</span></a>
      <small><?php echo esc_html(ukr_copy('map_note', 'Перед візитом радимо узгодити час із менеджером.')); ?></small>
    </div>
    <div class="ukr-map-shell" data-ukr-map-url="<?php echo esc_url($embed_url); ?>" aria-label="Розташування: <?php echo esc_attr($address); ?>">
      <div class="ukr-map-placeholder">
        <span class="ukr-map-pin" aria-hidden="true"></span>
        <strong>Чернігів</strong>
        <span><?php echo esc_html($address); ?></span>
        <button class="ukr-map-load" type="button"><?php echo esc_html(ukr_copy('map_load', 'Показати карту')); ?></button>
        <small>Google Maps завантажиться після натискання.</small>
      </div>
    </div>
  </div>
</section>
