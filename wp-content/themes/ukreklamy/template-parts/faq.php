<?php
$questions = class_exists('UKR_Editorial_Sections') ? UKR_Editorial_Sections::entries('ukr_faq') : [];
?>
<section class="section ukr-faq" id="faq" aria-labelledby="ukr-faq-title">
  <div class="wrap ukr-faq-grid">
    <div class="ukr-faq-intro">
      <span class="eyebrow"><?php echo esc_html(ukr_copy('faq_eyebrow', 'Запитання')); ?></span>
      <h2 id="ukr-faq-title"><?php echo esc_html(ukr_copy('faq_title', 'Перш ніж замовити')); ?></h2>
      <p><?php echo nl2br(esc_html(ukr_copy('faq_description', 'Умови проєкту, ціни, терміни й матеріали залежать від вашої задачі.'))); ?></p>
      <a class="ukr-faq-contact" href="#contact"><?php echo esc_html(ukr_copy('faq_cta', 'Поставити запитання →')); ?></a>
    </div>
    <div class="ukr-faq-list">
      <?php if ($questions): foreach ($questions as $faq): ?>
        <article class="ukr-faq-item">
          <h3 class="ukr-faq-question">
            <button class="ukr-faq-trigger" type="button" aria-expanded="true" aria-controls="ukr-faq-answer-<?php echo (int) $faq->ID; ?>">
              <span><?php echo esc_html(get_the_title($faq)); ?></span><span class="ukr-faq-indicator" aria-hidden="true"></span>
            </button>
          </h3>
          <div class="ukr-faq-answer-shell" id="ukr-faq-answer-<?php echo (int) $faq->ID; ?>" role="region" aria-label="<?php echo esc_attr(get_the_title($faq)); ?>">
            <div class="ukr-faq-answer"><div><?php echo wpautop(esc_html(trim(wp_strip_all_tags($faq->post_content)))); ?></div></div>
          </div>
        </article>
      <?php endforeach; else: ?>
        <p class="ukr-faq-empty"><?php echo esc_html(ukr_copy('faq_empty', 'Питання та відповіді готуються. Поки що запитайте менеджера через форму нижче.')); ?></p>
        <?php if (current_user_can('edit_posts')): ?><p class="ukr-editorial-empty">Питання не відображаються? Перевірте публікацію, відповідь та видимість.
          <a href="<?php echo esc_url(admin_url('admin.php?page=ukr-editorial-status')); ?>">Діагностика FAQ</a></p><?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</section>
