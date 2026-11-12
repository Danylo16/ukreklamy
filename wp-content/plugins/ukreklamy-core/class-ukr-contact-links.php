<?php
/** Editable, ordered front-page links. No secrets, no raw HTML allowed. */
if (!defined('ABSPATH')) exit;
final class UKR_Contact_Links {
    public const OPTION = 'ukr_contact_links';
    public static function boot(): void {
        add_action('admin_menu', [__CLASS__, 'menu']);
        add_action('admin_init', [__CLASS__, 'register']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'assets']);
    }
    public static function menu(): void {
        add_submenu_page('ukr-homepage', 'Контактні посилання', 'Контактні посилання', UKR_Access::EDIT, 'ukr-contact-links', [__CLASS__, 'page']);
    }
    public static function register(): void {
        register_setting('ukr_contact_links_group', self::OPTION, ['type' => 'array', 'sanitize_callback' => [__CLASS__, 'sanitize']]);
    }
    public static function sanitize($input): array {
        if (!is_array($input)) return [];
        $output = [];
        foreach (array_slice($input, 0, 16) as $item) {
            if (!is_array($item)) continue;
            $label = sanitize_text_field(wp_unslash((string)($item['label'] ?? '')));
            $label = function_exists('mb_substr') ? mb_substr($label, 0, 60) : $label;
            $raw = trim((string)($item['url'] ?? ''));
            $url = esc_url_raw(wp_unslash($raw), ['https', 'http', 'tel', 'mailto']);
            if ($label === '' || $url === '' || !preg_match('~^(?:https?://|tel:|mailto:)~i', $url)) continue;
            $output[] = ['label' => $label, 'url' => $url];
        }
        return $output;
    }
    public static function links(): array {
        // Existing installations retain their previously configured contacts until edited here.
        $saved = get_option(self::OPTION, null);
        if (is_array($saved)) return self::sanitize_stored($saved);
        $old = get_option('ukr_contacts', []);
        if (!is_array($old)) return [];
        $defaults = ['phone'=>'Телефон', 'instagram'=>'Instagram', 'facebook'=>'Facebook', 'email'=>'Email', 'telegram'=>'Telegram'];
        $result = [];
        foreach ($defaults as $key => $label) {
            $value = trim((string)($old[$key] ?? ''));
            if ($value === '') continue;
            $url = $key === 'phone' ? 'tel:'.preg_replace('/[^+0-9]/', '', $value) : ($key === 'email' ? 'mailto:'.$value : $value);
            if (preg_match('~^(?:https?://|tel:|mailto:)~i', $url)) $result[] = ['label' => $label, 'url' => $url];
        }
        return $result;
    }
    private static function sanitize_stored(array $list): array {
        return array_values(array_filter(array_map(static function($item) {
            if (!is_array($item)) return null;
            $label = sanitize_text_field((string)($item['label'] ?? ''));
            $url = esc_url_raw((string)($item['url'] ?? ''), ['https', 'http', 'tel', 'mailto']);
            return $label !== '' && preg_match('~^(?:https?://|tel:|mailto:)~i', $url) ? ['label'=>$label,'url'=>$url] : null;
        }, array_slice($list, 0, 16))));
    }
    public static function assets(string $hook): void {
        if (isset($_GET['page']) && sanitize_key((string) wp_unslash($_GET['page'])) === 'ukr-contact-links') {
            wp_enqueue_script('ukr-contact-links-admin', plugin_dir_url(__FILE__).'contact-links-admin.js', [], '1.10.0', true);
        }
    }
    public static function page(): void {
        if (!current_user_can(UKR_Access::EDIT)) wp_die('Недостатньо прав');
        $links = self::links();
        ?>
        <div class="wrap"><h1>Контактні посилання UKreklamy</h1>
        <p>Кнопки під заголовком у червоному блоці консультації. Укажіть назву та справжній URL, телефон у форматі <code>tel:+380...</code>, email — <code>mailto:address@example.com</code>. Порядок рядків відповідає порядку на сайті. Порожні рядки не публікуються.</p>
        <p>Основні телефон, адресу й соцмережі у footer, як і раніше, можна змінити в <a href="<?php echo esc_url(admin_url('admin.php?page=ukr-contacts')); ?>">Налаштування → Контакти UKreklamy</a>.</p>
        <?php settings_errors(); ?>
        <form method="post" action="options.php" id="ukr-links-form">
        <?php settings_fields('ukr_contact_links_group'); ?>
        <input type="hidden" name="ukr_contact_links" value="">
        <table class="widefat striped" style="max-width:900px"><thead><tr><th>Назва</th><th>URL</th><th>Дії</th></tr></thead>
        <tbody id="ukr-links-rows">
          <?php foreach ($links as $i=>$item): ?>
          <tr><td><input class="regular-text" name="<?php echo esc_attr(self::OPTION); ?>[<?php echo (int)$i; ?>][label]" value="<?php echo esc_attr($item['label']); ?>" maxlength="60" aria-label="Назва посилання"></td>
          <td><input class="large-text" name="<?php echo esc_attr(self::OPTION); ?>[<?php echo (int)$i; ?>][url]" value="<?php echo esc_attr($item['url']); ?>" aria-label="URL посилання"></td>
          <td><button type="button" class="button ukr-row-up" aria-label="Вище">↑</button> <button type="button" class="button ukr-row-down" aria-label="Нижче">↓</button> <button type="button" class="button ukr-row-remove">Видалити</button></td></tr>
          <?php endforeach; ?>
        </tbody></table>
        <p><button type="button" id="ukr-add-link" class="button button-secondary">Додати посилання</button></p>
        <?php submit_button('Зберегти посилання'); ?>
        </form></div>
        <?php
    }
}
UKR_Contact_Links::boot();
