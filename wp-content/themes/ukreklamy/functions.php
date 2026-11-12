<?php
if (!defined('ABSPATH')) exit;
add_action('after_setup_theme',function(){add_theme_support('title-tag');add_theme_support('post-thumbnails');add_theme_support('html5',['search-form','gallery','caption','style','script']);add_theme_support('automatic-feed-links');});
add_action('wp_enqueue_scripts',function(){wp_enqueue_style('ukr-style',get_stylesheet_uri(),[],wp_get_theme()->get('Version'));wp_enqueue_style('ukr-v12',get_template_directory_uri().'/assets-v12.css',['ukr-style'],wp_get_theme()->get('Version'));wp_enqueue_style('ukr-v13',get_template_directory_uri().'/assets-v13.css',['ukr-v12'],wp_get_theme()->get('Version'));wp_enqueue_style('ukr-hero',get_template_directory_uri().'/assets-hero.css',['ukr-v13'],'1.5.0');if(is_front_page()){wp_enqueue_style('ukr-parallax',get_template_directory_uri().'/assets-parallax.css',['ukr-hero'],'1.7.0');wp_enqueue_style('ukr-hero-v17',get_template_directory_uri().'/assets-hero-v17.css',['ukr-parallax'],'1.7.0');}if(is_front_page()){wp_enqueue_style('ukr-v19',get_template_directory_uri().'/assets-v19.css',['ukr-hero-v17'],'1.9.0');wp_enqueue_script('ukr-v19',get_template_directory_uri().'/assets-v19.js',[],'1.9.0',true);wp_enqueue_style('ukr-depth-v191',get_template_directory_uri().'/assets-depth-v191.css',['ukr-v19'],filemtime(get_template_directory().'/assets-depth-v191.css'));wp_enqueue_style('ukr-v110',get_template_directory_uri().'/assets-v110.css',['ukr-depth-v191'],filemtime(get_template_directory().'/assets-v110.css'));wp_enqueue_script('ukr-v110',get_template_directory_uri().'/assets-v110.js',[],filemtime(get_template_directory().'/assets-v110.js'),true);}wp_enqueue_style('ukr-v111',get_template_directory_uri().'/assets-v111.css',['ukr-style'],filemtime(get_template_directory().'/assets-v111.css'));wp_enqueue_style('ukr-v1111',get_template_directory_uri().'/assets-v1111.css',['ukr-v111'],filemtime(get_template_directory().'/assets-v1111.css'));wp_enqueue_style('ukr-mobile-v112',get_template_directory_uri().'/assets-mobile-v112.css',['ukr-v1111'],filemtime(get_template_directory().'/assets-mobile-v112.css'));wp_enqueue_script('ukr-main',get_template_directory_uri().'/assets.js',[],filemtime(get_template_directory().'/assets.js'),true);if(is_front_page())wp_enqueue_script('ukr-services',get_template_directory_uri().'/assets-services.js',[],wp_get_theme()->get('Version'),true);wp_enqueue_script('ukr-v111',get_template_directory_uri().'/assets-v111.js',['ukr-main'],filemtime(get_template_directory().'/assets-v111.js'),true);wp_add_inline_script('ukr-main','window.ukrConfig='.wp_json_encode(['endpoint'=>esc_url_raw(rest_url('ukreklamy/v1/lead')),'tokenEndpoint'=>esc_url_raw(rest_url('ukreklamy/v1/token')),'restNonce'=>wp_create_nonce('wp_rest')]).';','before');});
function ukr_contact($key) { $c=get_option('ukr_contacts',[]);return is_array($c)?(string)($c[$key]??''):''; }
function ukr_posts($type){return get_posts(['post_type'=>$type,'post_status'=>'publish','numberposts'=>-1,'orderby'=>['menu_order'=>'ASC','date'=>'DESC']]);}
require_once get_template_directory() . '/inc/seo.php';
add_action('send_headers',function(){if(is_front_page())header('Cache-Control: no-store, private');});
add_action('wp',function(){if(is_front_page()&&!headers_sent()){setcookie('ukr_form_token',wp_create_nonce('ukr_form'),['expires'=>time()+12*HOUR_IN_SECONDS,'path'=>'/','secure'=>is_ssl(),'httponly'=>true,'samesite'=>'Strict']);}});

/** Resolve an editable phrase; preserve the approved copy if the CMS plugin is disabled. */
function ukr_copy(string $key, string $fallback): string {
    if (class_exists('UKR_Site_Content')) return UKR_Site_Content::get($key, $fallback);
    return $fallback;
}

/** Favicon: original client's red emblem, cropped without the wordmark. */
function ukr_brand_favicon_links(): void {
    $base = get_template_directory_uri() . '/assets/images/';
    echo '<link rel="icon" type="image/png" sizes="32x32" href="' . esc_url($base . 'favicon-32.png?v=1111') . '">' . PHP_EOL;
    echo '<link rel="icon" type="image/png" sizes="16x16" href="' . esc_url($base . 'favicon-16.png?v=1111') . '">' . PHP_EOL;
    echo '<link rel="shortcut icon" href="' . esc_url($base . 'favicon.ico?v=1111') . '">' . PHP_EOL;
    echo '<link rel="apple-touch-icon" sizes="180x180" href="' . esc_url($base . 'favicon-180.png?v=1111') . '">' . PHP_EOL;
}
add_action('wp_head','ukr_brand_favicon_links',1000);
add_action('admin_head','ukr_brand_favicon_links',1000);
add_action('login_head','ukr_brand_favicon_links',1000);

/** Mobile-only refinement and full-height FAQ animation: loaded last for safe overrides. */
add_action('wp_enqueue_scripts', static function(): void {
    if (!is_front_page()) return;
    $dir = get_template_directory();
    $uri = get_template_directory_uri();
    wp_enqueue_style('ukr-faq-v113', $uri . '/assets-faq-v113.css', ['ukr-v110'], (string) filemtime($dir . '/assets-faq-v113.css'));
    wp_enqueue_style('ukr-mobile-v113', $uri . '/assets-mobile-v113.css', ['ukr-faq-v113', 'ukr-mobile-v112'], (string) filemtime($dir . '/assets-mobile-v113.css'));
}, 50);

/** UKreklamy v1.13 visual refinements: loaded after all previous layers. */
add_action('wp_enqueue_scripts', static function(): void {
    if (!is_front_page()) return;
    $dir = get_template_directory();
    $uri = get_template_directory_uri();
    wp_enqueue_style('ukr-v113', $uri . '/assets-v113.css', ['ukr-mobile-v113'], (string) filemtime($dir . '/assets-v113.css'));
    wp_enqueue_script('ukr-v113', $uri . '/assets-v113.js', ['ukr-main', 'ukr-v19'], (string) filemtime($dir . '/assets-v113.js'), true);
}, 60);

/** v1.14: category UX, compact flip portfolio, client logo alignment. */
add_action('wp_enqueue_scripts', static function(): void {
    if (!is_front_page()) return;
    $dir = get_template_directory(); $uri = get_template_directory_uri();
    wp_enqueue_style('ukr-v114', $uri.'/assets-v114.css', ['ukr-v113'], (string)filemtime($dir.'/assets-v114.css'));
    wp_enqueue_script('ukr-v114', $uri.'/assets-v114.js', ['ukr-v113'], (string)filemtime($dir.'/assets-v114.js'), true);
}, 70);

/** v1.14.1: unified-height portfolio with landscape photo gallery and thumbnails. */
add_action('wp_enqueue_scripts', static function(): void {
    if (!is_front_page()) return;
    $dir = get_template_directory(); $uri = get_template_directory_uri();
    wp_enqueue_style('ukr-v115', $uri.'/assets-v115.css', ['ukr-v114'], (string)filemtime($dir.'/assets-v115.css'));
    wp_enqueue_script('ukr-v115', $uri.'/assets-v115.js', ['ukr-v114'], (string)filemtime($dir.'/assets-v115.js'), true);
}, 80);

/** v1.16: Safari-safe portfolio and optional, server-validated Turnstile. */
add_action('wp_enqueue_scripts', static function (): void {
    if (!is_front_page()) return;
    $dir = get_template_directory();
    $uri = get_template_directory_uri();
    wp_enqueue_style('ukr-v116', $uri.'/assets-v116.css', ['ukr-v115'], (string) filemtime($dir.'/assets-v116.css'));
    $site_key = defined('UKR_TURNSTILE_SITE_KEY') ? trim((string) UKR_TURNSTILE_SITE_KEY) : '';
    $secret = defined('UKR_TURNSTILE_SECRET_KEY') ? trim((string) UKR_TURNSTILE_SECRET_KEY) : '';
    if ($site_key !== '' && $secret !== '') {
        wp_enqueue_script('ukr-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', [], null, ['strategy' => 'defer', 'in_footer' => true]);
    }
}, 90);
