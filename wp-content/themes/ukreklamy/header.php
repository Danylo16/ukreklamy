<!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="site-header ukr-smart-header" id="ukr-site-header">
  <div class="wrap nav">
    <a class="brand ukr-header-logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="Кухня реклами — на початок">
      <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo-header.png'); ?>"
           alt="Кухня реклами" width="624" height="138" decoding="async">
    </a>
    <button class="menubtn" type="button" aria-expanded="false" aria-controls="main-nav" aria-label="Відкрити меню">☰</button>
    <nav class="navlinks" id="main-nav" aria-label="Основна навігація">
      <a href="#services"><?php echo esc_html(ukr_copy('nav_services', 'Послуги')); ?></a>
      <a href="#works"><?php echo esc_html(ukr_copy('nav_works', 'Роботи')); ?></a>
      <a href="#process"><?php echo esc_html(ukr_copy('nav_process', 'Як працюємо')); ?></a>
      <a href="#about"><?php echo esc_html(ukr_copy('nav_about', 'Про нас')); ?></a>
      <a href="#location"><?php echo esc_html(ukr_copy('nav_location', 'Де ми')); ?></a>
    </nav>
    <?php if (ukr_contact('phone')): ?>
      <a class="navphone" href="<?php echo esc_url('tel:' . preg_replace('/[^+0-9]/','',ukr_contact('phone'))); ?>"><?php echo esc_html(ukr_contact('phone')); ?></a>
    <?php endif; ?>
    <a class="btn ukr-nav-cta" href="#contact"><span><?php echo esc_html(preg_replace('/[\s→↗]+$/u', '', ukr_copy('nav_cta', 'Отримати розрахунок →'))); ?></span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 12h14m-7-7 7 7-7 7"/></svg></a>
  </div>
</header>
<main id="top">
