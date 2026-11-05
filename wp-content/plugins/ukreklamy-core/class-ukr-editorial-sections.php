<?php
/** Editable client logos and FAQ entries. Existing content remains untouched. */
if (!defined('ABSPATH')) exit;

final class UKR_Editorial_Sections {
    public static function boot(): void {
        add_action('init', [__CLASS__, 'register_types']);
        add_action('add_meta_boxes', [__CLASS__, 'meta_boxes']);
        add_action('save_post_ukr_client', [__CLASS__, 'save_client']);
        add_action('save_post_ukr_faq', [__CLASS__, 'save_faq']);
        add_action('admin_notices', [__CLASS__, 'content_notice']);
        add_action('admin_menu', [__CLASS__, 'admin_menu']);
        add_action('admin_post_ukr_prepare_faq', [__CLASS__, 'prepare_faq']);
        add_filter('manage_ukr_client_posts_columns', [__CLASS__, 'admin_columns']);
        add_filter('manage_ukr_faq_posts_columns', [__CLASS__, 'admin_columns']);
        add_action('manage_ukr_client_posts_custom_column', [__CLASS__, 'admin_column'], 10, 2);
        add_action('manage_ukr_faq_posts_custom_column', [__CLASS__, 'admin_column'], 10, 2);
    }

    public static function register_types(): void {
        register_post_type('ukr_client', [
            'labels' => [
                'name' => 'Клієнти', 'singular_name' => 'Клієнт', 'menu_name' => 'Клієнти',
                'add_new_item' => 'Додати клієнта', 'edit_item' => 'Редагувати клієнта',
                'all_items' => 'Усі клієнти',
            ],
            'public' => false, 'show_ui' => true, 'show_in_rest' => false,
            'supports' => ['title', 'thumbnail', 'page-attributes'],
            'menu_icon' => 'dashicons-groups',
        ]);
        register_post_type('ukr_faq', [
            'labels' => [
                'name' => 'Питання та відповіді', 'singular_name' => 'Питання',
                'menu_name' => 'FAQ', 'add_new_item' => 'Додати питання',
                'edit_item' => 'Редагувати питання', 'all_items' => 'Усі питання',
            ],
            'public' => false, 'show_ui' => true, 'show_in_rest' => false,
            'supports' => ['title', 'editor', 'page-attributes'],
            'menu_icon' => 'dashicons-editor-help',
        ]);
    }

    public static function meta_boxes(): void {
        add_meta_box('ukr_client_settings', 'Показ логотипа на сайті', [__CLASS__, 'client_box'], 'ukr_client', 'normal', 'high');
        add_meta_box('ukr_faq_settings', 'Публікація відповіді', [__CLASS__, 'faq_box'], 'ukr_faq', 'side');
    }

    public static function client_box(WP_Post $post): void {
        wp_nonce_field('ukr_client_fields', 'ukr_client_nonce');
        $url = (string) get_post_meta($post->ID, '_ukr_client_url', true);
        $visible = (string) get_post_meta($post->ID, '_ukr_client_visible', true);
        if ($visible === '') $visible = '1'; // New entries are shown when published with a logo.
        echo '<p>Назва клієнта — у заголовку, логотип — у «Головне зображення» праворуч. Вибирайте справжній логотип з прозорим фоном (PNG або WebP). WordPress стандартно не дозволяє SVG з міркувань безпеки. Згоду на використання бренду потрібно отримати від замовника.</p>';
        echo '<p><label for="ukr_client_url"><strong>Сайт клієнта (необов’язково, https://)</strong></label><br>';
        echo '<input class="widefat" id="ukr_client_url" type="url" name="ukr_client_url" value="'.esc_attr($url).'" placeholder="https://example.com"></p>';
        echo '<p><label><input type="checkbox" name="ukr_client_visible" value="1" '.checked($visible, '1', false).'> Показувати на сайті (за наявності логотипа)</label></p>';
        echo '<p class="description">Задайте порядок у блоці «Властивості запису». Прибирати з показу можна без видалення.</p>';
    }

    public static function faq_box(WP_Post $post): void {
        wp_nonce_field('ukr_faq_fields', 'ukr_faq_nonce');
        $visible = (string) get_post_meta($post->ID, '_ukr_faq_visible', true);
        if ($visible === '') $visible = '1'; // New entries are shown when published with an answer.
        echo '<p>Заголовок — питання; редактор — відповідь. Без тексту відповіді питання не показується.</p>';
        echo '<p><label><input type="checkbox" name="ukr_faq_visible" value="1" '.checked($visible, '1', false).'> Показувати на сайті</label></p>';
        echo '<p class="description">Публікація та порядок керуються стандартними полями WordPress.</p>';
    }

    public static function save_client(int $id): void {
        if (!isset($_POST['ukr_client_nonce']) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ukr_client_nonce'])), 'ukr_client_fields') ||
            (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !current_user_can('edit_post', $id)) return;
        $raw = isset($_POST['ukr_client_url']) && is_string($_POST['ukr_client_url']) ? wp_unslash($_POST['ukr_client_url']) : '';
        $url = esc_url_raw(trim($raw), ['https']);
        update_post_meta($id, '_ukr_client_url', $url);
        update_post_meta($id, '_ukr_client_visible', isset($_POST['ukr_client_visible']) ? '1' : '0');
    }

    public static function save_faq(int $id): void {
        if (!isset($_POST['ukr_faq_nonce']) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ukr_faq_nonce'])), 'ukr_faq_fields') ||
            (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !current_user_can('edit_post', $id)) return;
        update_post_meta($id, '_ukr_faq_visible', isset($_POST['ukr_faq_visible']) ? '1' : '0');
    }

    public static function entries(string $type): array {
        if (!in_array($type, ['ukr_client', 'ukr_faq'], true)) return [];
        $posts = get_posts([
            'post_type' => $type, 'post_status' => 'publish', 'numberposts' => 100,
            'orderby' => ['menu_order' => 'ASC', 'date' => 'DESC'],
        ]);
        return array_values(array_filter($posts, static function ($post) use ($type): bool {
            if ($type === 'ukr_client') {
                return get_post_meta($post->ID, '_ukr_client_visible', true) !== '0' &&
                    (bool) get_post_thumbnail_id($post->ID);
            }
            return get_post_meta($post->ID, '_ukr_faq_visible', true) !== '0' &&
                trim(wp_strip_all_tags($post->post_content)) !== '';
        }));
    }

    /** Show the specific reason each record is absent, rather than silently hiding it. */
    public static function status($post): string {
        if ($post->post_status !== 'publish') return 'Не опубліковано';
        if ($post->post_type === 'ukr_client' && !get_post_thumbnail_id($post->ID)) return 'Немає логотипа';
        if ($post->post_type === 'ukr_faq' && trim(wp_strip_all_tags($post->post_content)) === '') return 'Немає відповіді';
        if (get_post_meta($post->ID, $post->post_type === 'ukr_client' ? '_ukr_client_visible' : '_ukr_faq_visible', true) === '0') return 'Приховано';
        return 'Відображається';
    }

    public static function admin_menu(): void {
        add_submenu_page('ukr-homepage', 'Клієнти та FAQ — стан наповнення', 'Клієнти та FAQ', 'manage_options', 'ukr-editorial-status', [__CLASS__, 'status_page']);
    }

    public static function admin_columns(array $columns): array {
        $columns['ukr_site_status'] = 'На головній сторінці';
        return $columns;
    }

    public static function admin_column(string $column, int $post_id): void {
        if ($column !== 'ukr_site_status') return;
        $post = get_post($post_id);
        if ($post) echo esc_html(self::status($post));
    }

    public static function status_page(): void {
        if (!current_user_can('manage_options')) wp_die('Недостатньо прав.');
        echo '<div class="wrap"><h1>Клієнти та FAQ — перевірка відображення</h1>';
        echo '<p>Сайт показує лише <strong>опубліковані</strong> елементи. Для клієнта обов’язковий логотип у «Головне зображення», для FAQ — відповідь у редакторі. Вимкнена видимість також приховує запис.</p>';
        foreach (['ukr_client' => ['Клієнти', 'Клієнти'], 'ukr_faq' => ['FAQ', 'FAQ']] as $type => $labels) {
            $posts = get_posts(['post_type' => $type, 'post_status' => 'any', 'numberposts' => -1, 'orderby' => 'date', 'order' => 'DESC']);
            $public = self::entries($type);
            $url = admin_url('edit.php?post_type='.$type);
            $new = admin_url('post-new.php?post_type='.$type);
            echo '<h2>'.esc_html($labels[0]).' — на сайті '.count($public).' із '.count($posts).'</h2>';
            echo '<p><a class="button button-primary" href="'.esc_url($new).'">Додати '.esc_html($type === 'ukr_client' ? 'клієнта' : 'питання').'</a> <a class="button" href="'.esc_url($url).'">Усі записи</a></p>';
            if (!$posts) { echo '<p><strong>Записів ще немає.</strong> Секція не міститиме вигаданих логотипів чи відповідей.</p>'; continue; }
            echo '<table class="widefat striped"><thead><tr><th>Запис</th><th>Статус</th></tr></thead><tbody>';
            foreach ($posts as $post) {
                echo '<tr><td><a href="'.esc_url(get_edit_post_link($post->ID)).'">'.esc_html(get_the_title($post)).'</a></td><td>'.esc_html(self::status($post)).'</td></tr>';
            }
            echo '</tbody></table>';
        }
        echo '<h2>Підготовка FAQ</h2><p>Можна створити чотири <strong>чернетки</strong> запитань із погодженого скриншота. Відповіді не вигадуються: їх потрібно узгодити й додати перед публікацією.</p>';
        echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'"><input type="hidden" name="action" value="ukr_prepare_faq">';
        wp_nonce_field('ukr_prepare_faq');
        submit_button('Створити чернетки FAQ без відповідей', 'secondary', 'submit', false);
        echo '</form></div>';
    }

    public static function prepare_faq(): void {
        if (!current_user_can('manage_options')) wp_die('Недостатньо прав.');
        check_admin_referer('ukr_prepare_faq');
        $questions = ['Як розраховується ціна?', 'Який мінімальний тираж?', 'Чи доставляєте по Україні?', 'Чи допомагаєте з макетом?'];
        $existing = get_posts(['post_type' => 'ukr_faq', 'post_status' => 'any', 'numberposts' => -1]);
        $titles = array_map(static fn($post) => trim(get_the_title($post)), $existing);
        foreach ($questions as $order => $title) {
            if (in_array($title, $titles, true)) continue;
            wp_insert_post(['post_type' => 'ukr_faq', 'post_status' => 'draft', 'post_title' => $title,
                'post_content' => '', 'menu_order' => $order, 'meta_input' => ['_ukr_faq_visible' => '1']]);
        }
        wp_safe_redirect(admin_url('admin.php?page=ukr-editorial-status'));
        exit;
    }

    public static function content_notice(): void {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || !in_array($screen->post_type, ['ukr_client', 'ukr_faq'], true)) return;
        echo '<div class="notice notice-info"><p>UKreklamy: щоб елемент з’явився на сайті, опублікуйте запис і ввімкніть видимість. Для клієнта потрібен логотип, для FAQ — текст відповіді.</p></div>';
    }
}
UKR_Editorial_Sections::boot();
