<?php
$settings = get_option('ukr_hero_settings', []);
$settings = is_array($settings) ? $settings : [];
$team_image = absint($settings['team_image_id'] ?? 0);
if ($team_image && !wp_attachment_is_image($team_image)) $team_image = 0;
?>
<section class="section about" id="about">
  <div class="wrap aboutgrid">
    <div>
      <span class="eyebrow"><?php echo esc_html(ukr_copy('about_eyebrow', 'Хто ми')); ?></span>
      <h2><?php echo esc_html(ukr_copy('about_title', 'Від виробництва до вивіски, яку запам’ятають')); ?></h2>
      <p><?php echo nl2br(esc_html(ukr_copy('about_description', 'Інформація про виробництво уточнюється у компанії.'))); ?></p>
      <div class="pillrow">
        <span class="pill"><?php echo esc_html(ukr_copy('about_tag_1', 'Уточнюється')); ?></span>
        <span class="pill"><?php echo esc_html(ukr_copy('about_tag_2', 'Уточнюється')); ?></span>
        <span class="pill"><?php echo esc_html(ukr_copy('about_tag_3', 'Уточнюється')); ?></span>
      </div>
    </div>
    <div class="aboutpic<?php echo $team_image ? ' ukr-team-has-photo' : ''; ?>">
      <?php if ($team_image): ?>
        <?php echo wp_get_attachment_image($team_image, 'large', false, ['loading'=>'lazy','decoding'=>'async','alt'=>get_post_meta($team_image,'_wp_attachment_image_alt',true) ?: 'Команда або виробництво Кухні реклами','sizes'=>'(max-width: 700px) 100vw, 48vw']); ?>
      <?php else: ?>
        <span><?php echo esc_html(ukr_copy('about_photo_placeholder', 'Фото команди або виробництва очікується')); ?></span>
      <?php endif; ?>
    </div>
  </div>
</section>
