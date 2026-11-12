<?php
/** Hero: display the selected Media Library image, or the original design placeholder. */
if (!defined('ABSPATH')) exit;
$hero_settings = get_option('ukr_hero_settings', []);
$hero_settings = is_array($hero_settings) ? $hero_settings : [];
$hero_image_id = absint($hero_settings['image_id'] ?? 0);
$hero_position = sanitize_key($hero_settings['position'] ?? 'center');
if (!in_array($hero_position, ['left', 'center', 'right'], true)) $hero_position = 'center';
$hero_image = $hero_image_id ? wp_get_attachment_image($hero_image_id, 'full', false, [
    'class' => 'ukr-hero-photo',
    'alt' => get_post_meta($hero_image_id, '_wp_attachment_image_alt', true) ?: 'Приклад рекламної продукції UKreklamy',
    'loading' => 'eager',
    'fetchpriority' => 'high',
    'decoding' => 'async',
    'sizes' => '(max-width: 800px) 100vw, 71vw',
]) : '';
?>
<section class="hero ukr-hero-v17">
    <div class="wrap heroinner">
        <div class="herocopy">
            <?php $hero_eyebrow = trim(ukr_copy('hero_eyebrow', 'Виробництво реклами · Чернігів')); ?>
            <?php if ($hero_eyebrow !== ''): ?><div class="label"><i class="dot"></i> <?php echo esc_html($hero_eyebrow); ?></div><?php endif; ?>
            <h1><?php echo esc_html(ukr_copy('hero_title_first', 'Ваш бізнес')); ?> <em><?php echo esc_html(ukr_copy('hero_title_emphasis', 'мають помітити.')); ?></em></h1>
            <p><?php echo nl2br(esc_html(ukr_copy('hero_description', 'Вивіски, об’ємні літери, друк і брендування — від ідеї до готового результату. Виготовляємо в Чернігові, відправляємо по Україні.'))); ?></p>
            <div class="heroactions">
              <a class="btn ukr-hero-cta-primary" href="#contact"><span><?php echo esc_html(preg_replace('/[\s→↗↓]+$/u', '', ukr_copy('hero_cta_primary', 'Розрахувати мій проєкт'))); ?></span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 12h14m-7-7 7 7-7 7"/></svg></a>
              <a class="btn secondary ukr-hero-cta-works" href="#works"><span><?php echo esc_html(preg_replace('/[\s→↗↓]+$/u', '', ukr_copy('hero_cta_secondary', 'Подивитися роботи ↓'))); ?></span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 4v16m-6-6 6 6 6-6"/></svg></a>
            </div>
        </div>
        <div class="heroart<?php echo $hero_image ? ' has-image' : ''; ?> ukr-hero-position-<?php echo esc_attr($hero_position); ?>" <?php echo $hero_image ? '' : 'aria-hidden="true"'; ?>>
            <?php if ($hero_image) : ?>
                <?php echo $hero_image; // Trusted markup rendered by WordPress. ?>
            <?php else : ?>
                <span>Фото виробництва або виконаного проєкту — очікуємо матеріали</span>
            <?php endif; ?>
        </div>
    </div>
</section>
