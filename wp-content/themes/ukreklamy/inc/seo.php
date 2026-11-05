<?php
/** Minimal landing-page SEO; honors WordPress indexing settings and avoids plugin duplicates. */
if (!defined('ABSPATH')) exit;

function ukr_seo_plugin_active(): bool {
    return defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION') || defined('AIOSEO_VERSION');
}

function ukr_seo_title(): string {
    return trim(wp_strip_all_tags(ukr_copy('seo_title', 'Вивіски, лайтбокси та рекламна продукція у Чернігові | Кухня реклами')));
}

function ukr_seo_description(): string {
    return trim(wp_strip_all_tags(ukr_copy('meta_description', 'Кухня реклами — виготовлення вивісок, об’ємних літер, лайтбоксів, широкоформатний друк і брендування у Чернігові. Залиште заявку на розрахунок.')));
}

add_filter('pre_get_document_title', static function ($title) {
    if (is_front_page() && !ukr_seo_plugin_active()) return ukr_seo_title();
    return $title;
}, 30);

add_action('wp', static function () {
    if (is_front_page() && !ukr_seo_plugin_active()) {
        remove_action('wp_head', 'rel_canonical'); // Avoid duplicate canonical on a static front page.
    }
});

add_action('wp_head', static function (): void {
    if (!is_front_page() || ukr_seo_plugin_active()) return;
    $title = ukr_seo_title();
    $desc = ukr_seo_description();
    $base = home_url('/');
    $image_id = absint((get_option('ukr_hero_settings', [])['image_id'] ?? 0));
    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'full') : '';
    if (!$image_url && has_site_icon()) $image_url = get_site_icon_url(512);
    echo '<meta name="description" content="'.esc_attr($desc).'">' . "\n";
    echo '<link rel="canonical" href="'.esc_url($base).'">' . "\n";
    foreach (['og:type'=>'website', 'og:locale'=>'uk_UA', 'og:site_name'=>get_bloginfo('name'),
        'og:title'=>$title, 'og:description'=>$desc, 'og:url'=>$base] as $key=>$val) {
        echo '<meta property="'.esc_attr($key).'" content="'.esc_attr($val).'">' . "\n";
    }
    if ($image_url) {
        echo '<meta property="og:image" content="'.esc_url($image_url).'">' . "\n";
        echo '<meta property="og:image:alt" content="'.esc_attr(ukr_copy('seo_og_alt', 'Рекламне виробництво Кухня реклами')).'">' . "\n";
        if ($image_id) {
            $data = wp_get_attachment_metadata($image_id);
            if (is_array($data) && isset($data['width'], $data['height'])) {
                echo '<meta property="og:image:width" content="'.(int)$data['width'].'">' . "\n";
                echo '<meta property="og:image:height" content="'.(int)$data['height'].'">' . "\n";
            }
        }
    }
    echo '<meta name="twitter:card" content="'.($image_url?'summary_large_image':'summary').'">' . "\n";
    echo '<meta name="twitter:title" content="'.esc_attr($title).'">' . "\n";
    echo '<meta name="twitter:description" content="'.esc_attr($desc).'">' . "\n";
    if ($image_url) echo '<meta name="twitter:image" content="'.esc_url($image_url).'">' . "\n";

    $organization = [
        '@context'=>'https://schema.org', '@type'=>'Organization', '@id'=>$base.'#organization',
        'name'=>'Кухня реклами', 'url'=>$base, 'description'=>$desc,
    ];
    // Include verified local address only when the customer has supplied one in CMS.
    $address_text = trim(ukr_contact('address'));
    if ($address_text !== '') {
        $organization['@type'] = 'LocalBusiness';
        $organization['address'] = [
            '@type' => 'PostalAddress',
            'streetAddress' => $address_text,
            'addressLocality' => 'Чернігів',
            'addressCountry' => 'UA',
        ];
    }
    $phone = ukr_contact('phone');
    $email = ukr_contact('email');
    if ($phone) $organization['telephone'] = $phone;
    if ($email && is_email($email)) $organization['email'] = $email;
    $same_as = [];
    foreach (['instagram','facebook','telegram'] as $field) {
        $link = ukr_contact($field);
        if ($link && wp_http_validate_url($link)) $same_as[] = esc_url_raw($link, ['https']);
    }
    if ($same_as) $organization['sameAs'] = $same_as;
    if (has_site_icon()) $organization['logo'] = get_site_icon_url(512);
    echo '<script type="application/ld+json">'.wp_json_encode($organization, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT).'</script>' . "\n";
}, 10);
