<?php
/** Ordered, editable social/contact links with SVG icons from an allowlist. */
if (!defined('ABSPATH')) exit;
final class UKR_Contact_Links {
    public const OPTION = 'ukr_contact_links';
    public static function types(): array { return [
        'phone'=>'Телефон', 'email'=>'Email', 'instagram'=>'Instagram', 'facebook'=>'Facebook',
        'telegram'=>'Telegram', 'youtube'=>'YouTube', 'tiktok'=>'TikTok', 'linkedin'=>'LinkedIn',
        'whatsapp'=>'WhatsApp', 'viber'=>'Viber', 'pinterest'=>'Pinterest', 'x'=>'X / Twitter',
        'website'=>'Інший сайт',
    ]; }
    public static function boot(): void {
        add_action('admin_menu', [__CLASS__, 'menu']);
        add_action('admin_init', [__CLASS__, 'register']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'assets']);
    }
    public static function menu(): void {
        add_submenu_page('ukr-homepage', 'Контактні посилання', 'Контактні посилання', UKR_Access::EDIT, 'ukr-contact-links', [__CLASS__, 'page']);
    }
    public static function register(): void {
        register_setting('ukr_contact_links_group', self::OPTION, [
            'type'=>'array','sanitize_callback'=>[__CLASS__, 'sanitize'],
        ]);
    }
    public static function guess_type(string $url, string $label = ''): string {
        $l = strtolower($label);
        if (str_starts_with(strtolower($url),'tel:')) return 'phone';
        if (str_starts_with(strtolower($url),'mailto:')) return 'email';
        if (preg_match('~(?:instagram\\.com|instagr\\.am)~i',$url) || stripos($l,'instagram') !== false || stripos($l,'інст') !== false) return 'instagram';
        if (preg_match('~(?:facebook\\.com|fb\\.com|fb\\.me)~i',$url) || stripos($l,'facebook') !== false) return 'facebook';
        if (preg_match('~(?:t\\.me|telegram\\.me|telegram\\.org)~i',$url)) return 'telegram';
        if (preg_match('~(?:youtube\\.com|youtu\\.be)~i',$url)) return 'youtube';
        if (preg_match('~tiktok\\.com~i',$url)) return 'tiktok';
        if (preg_match('~linkedin\\.com~i',$url)) return 'linkedin';
        if (preg_match('~(?:wa\\.me|whatsapp\\.com)~i',$url)) return 'whatsapp';
        if (preg_match('~(?:viber\\.com|vb\\.me|invite\\.viber\\.com)~i',$url)) return 'viber';
        if (preg_match('~(?:pinterest\\.com|pin\\.it)~i',$url)) return 'pinterest';
        if (preg_match('~(?:x\\.com|twitter\\.com)~i',$url)) return 'x';
        return 'website';
    }
    public static function type(array $link): string {
        $given = sanitize_key((string)($link['type'] ?? ''));
        return isset(self::types()[$given]) ? $given : self::guess_type((string)($link['url'] ?? ''), (string)($link['label'] ?? ''));
    }
    public static function sanitize($input): array {
        if (!is_array($input)) return [];
        $output=[];
        foreach (array_slice($input,0,24) as $row) {
            if (!is_array($row)) continue;
            $raw=trim((string)($row['url'] ?? ''));
            $url=esc_url_raw(wp_unslash($raw), ['https','http','tel','mailto']);
            if (!$url || !preg_match('~^(?:https?://|tel:|mailto:)~i',$url)) continue;
            $type = self::type(['type'=>$row['type'] ?? '', 'url'=>$url, 'label'=>(string)($row['label'] ?? '')]);
            // Protocol wins for phone and email. Other platforms must be actual web links.
            if (str_starts_with(strtolower($url),'tel:')) $type='phone';
            elseif (str_starts_with(strtolower($url),'mailto:')) $type='email';
            elseif (in_array($type,['phone','email'],true)) $type='website';
            $label=sanitize_text_field(wp_unslash((string)($row['label'] ?? '')));
            $label = function_exists('mb_substr') ? mb_substr($label,0,60) : substr($label,0,60);
            if ($label==='') $label=self::types()[$type];
            $output[]=['label'=>$label,'url'=>$url,'type'=>$type];
        }
        return $output;
    }
    public static function links(): array {
        $saved=get_option(self::OPTION,null);
        if (is_array($saved)) return self::sanitize($saved);
        $old=get_option('ukr_contacts',[]);
        if (!is_array($old)) return [];
        $result=[];
        foreach (['phone'=>'Телефон','instagram'=>'Instagram','facebook'=>'Facebook','email'=>'Email','telegram'=>'Telegram'] as $key=>$label) {
            $value=trim((string)($old[$key] ?? ''));
            if ($value==='') continue;
            $url = $key==='phone' ? 'tel:'.preg_replace('/[^+0-9]/','',$value) : ($key==='email' ? 'mailto:'.$value : $value);
            if (preg_match('~^(?:https?://|tel:|mailto:)~i',$url)) $result[]=['label'=>$key==='phone' ? $value : $label,'url'=>$url,'type'=>$key];
        }
        return self::sanitize($result);
    }
    /** Only trusted, built-in SVG fragments are rendered. */
    private static function paths(): array { return [
        'phone'=>'<path d="M22 16.9v3a2 2 0 0 1-2.2 2A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7l.5 2.9a2 2 0 0 1-.6 1.8L7.4 10a16 16 0 0 0 6.6 6.6l1.6-1.6a2 2 0 0 1 1.8-.6l2.9.5a2 2 0 0 1 1.7 2Z"/>',
        'email'=>'<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 7 9-7"/>',
        'instagram'=>'<rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.7" cy="6.3" r="1" fill="currentColor" stroke="none"/>',
        'facebook'=>'<path fill="currentColor" stroke="none" d="M16.8 3.2h-2.5c-3 0-4.7 1.8-4.7 5v2H7.1v3.5h2.5V21h3.8v-7.3h2.9l.5-3.5h-3.4V8.6c0-1 .3-1.5 1.5-1.5h1.9V3.2Z"/>',
        'telegram'=>'<path d="M22 2 2 10l7.3 2.3L19 5l-7.2 9.1L12.3 21l4.5-5 3.3 2z"/>',
        'youtube'=>'<rect x="2" y="5" width="20" height="14" rx="4"/><path d="m10 9 5 3-5 3z"/>',
        'tiktok'=>'<path d="M14 3v11.5a4.5 4.5 0 1 1-4.6-4.5M14 3c.5 3 2.5 4.7 6 5"/>',
        'linkedin'=>'<rect x="2" y="2" width="20" height="20" rx="2"/><path d="M7 10v8M7 6v.5M12 18v-8m0 3a3 3 0 0 1 6 0v5"/>',
        'whatsapp'=>'<path d="M20 11.5a8 8 0 0 1-11.6 7.2L4 20l1.3-4.3A8 8 0 1 1 20 11.5Z"/><path d="M9 9c1.4 3 3 4.5 6.1 5.8l1.2-1.2"/>',
        'viber'=>'<path d="M5 17 3 21l5-2A8 8 0 1 0 5 17Z"/><path d="M9 8c1 3 3 5 6 6m-1-8a5 5 0 0 1 4 4"/>',
        'pinterest'=>'<circle cx="12" cy="12" r="10"/><path d="M9 18c1-2 2-5 3-9m-3 3a4 4 0 1 1 3 4"/>',
        'x'=>'<path d="M4 3h4l12 18h-4zM20 3 4 21"/>',
        'website'=>'<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c-4 4-4 14 0 18M12 3c4 4 4 14 0 18"/>',
    ]; }
    public static function icon_svg(string $type, string $class=''): string {
        $paths=self::paths();
        $type=isset($paths[$type]) ? $type : 'website';
        return '<svg class="'.esc_attr($class).'" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'.$paths[$type].'</svg>';
    }
    public static function is_social(string $type): bool { return !in_array($type,['phone','email','website'],true); }
    public static function assets(string $hook): void {
        if ($hook !== 'ukreklamy_page_ukr-contact-links' && (!isset($_GET['page']) || sanitize_key((string)wp_unslash($_GET['page'])) !== 'ukr-contact-links')) return;
        wp_enqueue_style('ukr-links-admin',plugin_dir_url(__FILE__).'contact-links-admin.css',[], '1.15.2');
        wp_enqueue_script('ukr-contact-links-admin',plugin_dir_url(__FILE__).'contact-links-admin.js',[], '1.15.2',true);
        wp_add_inline_script('ukr-contact-links-admin','window.UKRContactPlatforms='.wp_json_encode(self::types()).';','before');
    }
    private static function field_row(int $i,array $item): void {
        $type=self::type($item);
        ?>
        <tr class="ukr-link-row">
          <td><span class="ukr-link-icon-preview" aria-hidden="true"><?php echo self::icon_svg($type); ?></span></td>
          <td><select data-kind="type" name="<?php echo esc_attr(self::OPTION); ?>[<?php echo $i; ?>][type]" aria-label="Тип контакту">
            <?php foreach (self::types() as $key=>$name): ?><option value="<?php echo esc_attr($key); ?>" <?php selected($type,$key); ?>><?php echo esc_html($name); ?></option><?php endforeach; ?>
          </select></td>
          <td><input data-kind="label" class="regular-text" maxlength="60" name="<?php echo esc_attr(self::OPTION); ?>[<?php echo $i; ?>][label]" value="<?php echo esc_attr($item['label']); ?>" aria-label="Назва або підпис"></td>
          <td><input data-kind="url" class="large-text" name="<?php echo esc_attr(self::OPTION); ?>[<?php echo $i; ?>][url]" value="<?php echo esc_attr($item['url']); ?>" placeholder="https://..." aria-label="Адреса контакту"></td>
          <td class="ukr-link-actions"><button type="button" class="button ukr-row-up" aria-label="Підняти вище">↑</button> <button type="button" class="button ukr-row-down" aria-label="Опустити нижче">↓</button> <button type="button" class="button ukr-row-remove">Видалити</button></td>
        </tr>
        <?php
    }
    public static function page(): void {
        if(!current_user_can(UKR_Access::EDIT)) wp_die('Недостатньо прав');
        $links=self::links(); ?>
        <div class="wrap ukr-contact-admin">
          <h1>Контактні посилання</h1>
          <p>Додайте будь-яку соцмережу, виберіть її тип — іконка з’явиться одразу. На сайті соціальні мережі відображаються іконками без тексту; телефон і email — з підписом.</p>
          <p class="description">Для телефону використовуйте <code>tel:+380...</code>, для пошти — <code>mailto:info@example.com</code>, для соцмереж — повне <code>https://...</code> посилання. До 24 контактів.</p>
          <?php settings_errors(); ?>
          <form action="options.php" method="post" id="ukr-links-form">
            <?php settings_fields('ukr_contact_links_group'); ?>
            <input type="hidden" name="ukr_contact_links" value="">
            <div class="ukr-links-scroll"><table class="widefat striped ukr-links-table"><thead><tr><th>Іконка</th><th>Платформа</th><th>Назва / підказка</th><th>Адреса</th><th>Порядок</th></tr></thead>
            <tbody id="ukr-links-rows"><?php foreach($links as $i=>$item) self::field_row((int)$i,$item); ?></tbody></table></div>
            <p><button type="button" id="ukr-add-link" class="button button-secondary">+ Додати посилання</button></p>
            <?php submit_button('Зберегти контакти'); ?>
          </form>
          <p class="description">Контакти для адреси та карти, як і раніше, редагуються у <a href="<?php echo esc_url(admin_url('admin.php?page=ukr-contacts')); ?>">Налаштування → Контакти UKreklamy</a>.</p>
          <div hidden id="ukr-link-icons" aria-hidden="true">
            <?php foreach(self::types() as $type=>$label): ?><template data-ukr-icon="<?php echo esc_attr($type); ?>"><?php echo self::icon_svg($type); ?></template><?php endforeach; ?>
          </div>
        </div>
        <?php
    }
}
UKR_Contact_Links::boot();
