<?php
/** Site hero content settings; saved in a single WordPress option. */
if (!defined('ABSPATH')) exit;

final class UKR_Hero_Settings {
    public const OPT = 'ukr_hero_settings';
    private const PAGE = 'ukr-homepage';

    public static function boot(): void {
        add_action('admin_menu', [__CLASS__, 'menu']);
        add_action('admin_init', [__CLASS__, 'register']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'assets']);
    }

    public static function menu(): void {
        add_menu_page('UKreklamy — головна сторінка', 'UKreklamy', UKR_Access::EDIT, self::PAGE, [__CLASS__, 'page'], 'dashicons-format-image', 61);
        add_submenu_page(self::PAGE, 'Налаштування головної сторінки', 'Головна сторінка', UKR_Access::EDIT, self::PAGE, [__CLASS__, 'page']);
    }

    public static function register(): void {
        register_setting('ukr_hero_group', self::OPT, [
            'type' => 'array',
            'sanitize_callback' => [__CLASS__, 'sanitize'],
            'default' => ['image_id' => 0, 'position' => 'center', 'team_image_id' => 0],
        ]);
    }

    public static function sanitize($submitted): array {
        $previous = get_option(self::OPT, []);
        if (!is_array($previous)) $previous = [];
        if (!is_array($submitted)) return $previous;
        $id = absint($submitted['image_id'] ?? 0);
        if ($id && !wp_attachment_is_image($id)) {
            add_settings_error(self::OPT, 'invalid_image', 'Будь ласка, виберіть зображення з Медіатеки WordPress.');
            $id = absint($previous['image_id'] ?? 0);
        }
        $position = sanitize_key($submitted['position'] ?? 'center');
        if (!in_array($position, ['left', 'center', 'right'], true)) $position = 'center';
        $team_id = absint($submitted['team_image_id'] ?? ($previous['team_image_id'] ?? 0));
        if ($team_id && !wp_attachment_is_image($team_id)) {
            add_settings_error(self::OPT, 'invalid_team_image', 'Фото команди має бути зображенням із медіатеки.');
            $team_id = absint($previous['team_image_id'] ?? 0);
        }
        return ['image_id' => $id, 'position' => $position, 'team_image_id' => $team_id];
    }

    public static function assets(string $hook): void {
        if ($hook !== 'toplevel_page_' . self::PAGE) return;
        wp_enqueue_media();
        $url = plugin_dir_url(__FILE__);
        $version = '1.13.0';
        wp_enqueue_style('ukr-hero-admin', $url . 'hero-admin.css', [], $version);
        wp_enqueue_script('ukr-hero-admin', $url . 'hero-admin.js', ['media-views'], $version, true);
    }

    public static function page(): void {
        if (!current_user_can(UKR_Access::EDIT)) wp_die('Недостатньо прав.');
        $settings = get_option(self::OPT, []);
        if (!is_array($settings)) $settings = [];
        $id = absint($settings['image_id'] ?? 0);
        $position = $settings['position'] ?? 'center';
        $preview = $id ? wp_get_attachment_image_url($id, 'large') : '';
        $alt = $id ? get_post_meta($id, '_wp_attachment_image_alt', true) : '';
        $team_id = absint($settings['team_image_id'] ?? 0);
        $team_preview = $team_id ? wp_get_attachment_image_url($team_id, 'large') : '';
        ?>
        <div class="wrap ukr-hero-admin">
            <h1>Налаштування головної сторінки</h1>
            <p class="description">Зображення в першому екрані сайту. Якщо його не вибрано, залишиться початкова заглушка.</p>
            <?php settings_errors(self::OPT); ?>
            <form method="post" action="options.php">
                <?php settings_fields('ukr_hero_group'); ?>
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row"><label for="ukr_hero_image_id">Головне зображення</label></th>
                            <td>
                                <input type="hidden" name="<?php echo esc_attr(self::OPT); ?>[image_id]" id="ukr_hero_image_id" value="<?php echo esc_attr($id); ?>">
                                <div class="ukr-hero-preview" id="ukr_hero_preview">
                                    <img src="<?php echo esc_url($preview); ?>" alt="<?php echo esc_attr($alt); ?>" id="ukr_hero_preview_img" <?php echo $preview ? '' : 'hidden'; ?>>
                                    <p id="ukr_hero_empty" <?php echo $preview ? 'hidden' : ''; ?>>Фото ще не вибрано</p>
                                </div>
                                <p class="ukr-hero-actions">
                                    <button class="button button-secondary" type="button" id="ukr_hero_select">Вибрати / змінити фото</button>
                                    <button class="button" type="button" id="ukr_hero_remove" <?php echo $preview ? '' : 'disabled'; ?>>Прибрати фото</button>
                                </p>
                                <p class="description">Бажано WebP або JPEG, горизонтальне фото щонайменше 1200 px завширшки. Максимальний розмір файлу локально — 64 МБ, але для швидкого сайту краще значно менше.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="ukr_hero_position">Положення зображення</label></th>
                            <td>
                                <select id="ukr_hero_position" name="<?php echo esc_attr(self::OPT); ?>[position]">
                                    <?php foreach (['center'=>'По центру','left'=>'Ліворуч','right'=>'Праворуч'] as $value=>$label): ?>
                                        <option value="<?php echo esc_attr($value); ?>" <?php selected($position,$value); ?>><?php echo esc_html($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description">Змінює кадрування фотографії, а не розташування тексту та секцій.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="ukr_team_image_id">Фото команди / виробництва</label></th>
                            <td>
                                <input type="hidden" name="<?php echo esc_attr(self::OPT); ?>[team_image_id]" id="ukr_team_image_id" value="<?php echo esc_attr($team_id); ?>">
                                <div class="ukr-hero-preview" id="ukr_team_preview">
                                    <img id="ukr_team_preview_img" src="<?php echo esc_url($team_preview); ?>" alt="Попередній перегляд фото команди" <?php echo $team_preview ? '' : 'hidden'; ?>>
                                    <p id="ukr_team_empty" <?php echo $team_preview ? 'hidden' : ''; ?>>Фото команди ще не вибрано</p>
                                </div>
                                <p class="ukr-hero-actions">
                                    <button class="button button-secondary" type="button" id="ukr_team_select">Вибрати фото команди</button>
                                    <button class="button" type="button" id="ukr_team_remove" <?php echo $team_preview ? '' : 'disabled'; ?>>Прибрати фото</button>
                                </p>
                                <p class="description">Зображення з'явиться в розділі «Про нас». Рекомендується реальне горизонтальне фото команди або виробництва.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <?php submit_button('Зберегти зміни'); ?>
            </form>
        </div>
        <?php
    }
}
UKR_Hero_Settings::boot();
