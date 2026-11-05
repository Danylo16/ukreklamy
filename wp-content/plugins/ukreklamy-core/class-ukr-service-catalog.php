<?php
/** Admin fields and taxonomy for the hierarchical services catalogue. */
if (!defined('ABSPATH')) exit;

final class UKR_Service_Catalog {
    const TAX = 'ukr_service_category';
    const SCHEMA_VERSION = '1.1.0';

    public static function boot(): void {
        add_action('admin_init', [__CLASS__, 'seed_categories']);
        add_action('add_meta_boxes_ukr_service', [__CLASS__, 'register_service_box']);
        add_action('save_post_ukr_service', [__CLASS__, 'save_service_fields']);
        add_action(self::TAX . '_add_form_fields', [__CLASS__, 'add_fields']);
        add_action(self::TAX . '_edit_form_fields', [__CLASS__, 'edit_fields']);
        add_action('created_' . self::TAX, [__CLASS__, 'save_category_fields']);
        add_action('edited_' . self::TAX, [__CLASS__, 'save_category_fields']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'admin_assets']);
        add_action('admin_notices', [__CLASS__, 'unassigned_notice']);
        add_action(self::TAX . '_edit_form_fields', [__CLASS__, 'edit_service_order'], 20);
        add_filter('wp_insert_post_data', [__CLASS__, 'require_category_before_publish'], 20, 2);
        add_action('admin_notices', [__CLASS__, 'missing_category_notice']);
    }

    public static function register_taxonomy(): void {
        register_taxonomy(self::TAX, ['ukr_service'], [
            'labels' => [
                'name' => 'Напрями послуг', 'singular_name' => 'Напрям послуг',
                'menu_name' => 'Напрями послуг', 'all_items' => 'Усі напрями',
                'add_new_item' => 'Додати напрям', 'edit_item' => 'Редагувати напрям',
                'search_items' => 'Шукати напрями', 'parent_item' => 'Батьківський напрям',
            ],
            'public' => false, 'show_ui' => true, 'show_admin_column' => true,
            'show_in_rest' => false, 'hierarchical' => true,
            'meta_box_cb' => [__CLASS__, 'category_select_box'],
            'rewrite' => false,
        ]);
    }

    /** Populate five agreed headings; never overwrite editorial data. */
    public static function seed_categories(): void {
        if (!current_user_can('manage_options') || get_option('ukr_catalog_schema') === self::SCHEMA_VERSION) return;
        $names = [
            ['Вивіски', 'vyvisky', 'Об’ємні літери, світлові вивіски, вивіски з композиту та лайтбокси.'],
            ['Поліграфія', 'polihrafiia', 'Блокноти, буклети, календарі, папки та етикетки.'],
            ['Широкоформатний друк', 'shyrokoformatnyi-druk', 'Банери, плівка, плакати, афіші та оформлення вітрин.'],
            ['Сувенірна продукція', 'suvenirna-produktsiia', 'Календарі, ручки, чашки, нагородна продукція та принти на одязі.'],
            ['Брендування', 'brenduvannia', 'Оформлення автомобілів та інтер’єрів.'],
        ];
        foreach ($names as $order => [$name, $slug, $description]) {
            $existing = get_term_by('slug', $slug, self::TAX);
            if ($existing) continue;
            $created = wp_insert_term($name, self::TAX, ['slug' => $slug, 'description' => $description]);
            if (is_wp_error($created)) continue;
            $term_id = (int) $created['term_id'];
            update_term_meta($term_id, '_ukr_cat_visible', '1');
            update_term_meta($term_id, '_ukr_cat_order', $order * 10);
        }
        update_option('ukr_catalog_schema', self::SCHEMA_VERSION, false);
    }

    public static function register_service_box(): void {
        add_meta_box('ukr_service_catalog', 'Деталі підпослуги', [__CLASS__, 'service_fields'], 'ukr_service', 'normal', 'high');
    }

    public static function service_fields(WP_Post $post): void {
        wp_nonce_field('ukr_service_catalog', 'ukr_service_catalog_nonce');
        $short = get_post_meta($post->ID, '_ukr_short_description', true);
        $price = get_post_meta($post->ID, '_ukr_price_from', true);
        $unit = get_post_meta($post->ID, '_ukr_price_unit', true);
        $gallery = (string) get_post_meta($post->ID, '_ukr_service_gallery', true);
        echo '<p><strong>Напрям послуг</strong> — обов’язкове поле праворуч. Основне фото — у блоці «Головне зображення». Повний опис — у головному редакторі.</p>';
        echo '<p><label for="ukr_short_description"><strong>Короткий опис</strong></label><br><textarea class="widefat" rows="2" id="ukr_short_description" name="ukr_short_description" maxlength="280">'.esc_textarea((string) $short).'</textarea></p>';
        echo '<p><label for="ukr_price_from"><strong>Ціна від</strong> (наприклад, «1 500 грн»; залиште порожнім, якщо прайсу немає)</label><br><input class="regular-text" id="ukr_price_from" name="ukr_price_from" maxlength="100" value="'.esc_attr((string) $price).'"></p>';
        echo '<p><label for="ukr_price_unit"><strong>Одиниця</strong> (наприклад, «за м²», «за шт.»)</label><br><input class="regular-text" id="ukr_price_unit" name="ukr_price_unit" maxlength="100" value="'.esc_attr((string) $unit).'"></p>';
        echo '<div class="ukr-service-gallery-admin"><p><strong>Додаткові фотографії послуги</strong></p>';
        echo '<input type="hidden" id="ukr_service_gallery" name="ukr_service_gallery" value="'.esc_attr($gallery).'">';
        echo '<div id="ukr-service-gallery-list" class="ukr-service-gallery-list" aria-live="polite"></div>';
        echo '<p><button type="button" class="button ukr-gallery-pick">Додати / вибрати фото</button> <button type="button" class="button ukr-gallery-clear">Очистити галерею</button></p>';
        echo '<p class="description">Головне зображення — перший слайд. Тут можна додати до 12 інших фото, змінити їхній порядок стрілками. Фото не потрібно дублювати у текстовому описі.</p></div>';
        echo '<p class="description">Без підтвердженої ціни на сайті буде «Ціна уточнюється». Не публікуйте непогоджені тарифи.</p>';
    }

    /** Radio-equivalent dropdown: a service belongs to exactly one parent direction. */
    public static function category_select_box(WP_Post $post): void {
        $selected = wp_get_post_terms($post->ID, self::TAX, ['fields'=>'ids']);
        $selected = !is_wp_error($selected) && $selected ? (int)$selected[0] : 0;
        $terms = get_terms(['taxonomy'=>self::TAX, 'hide_empty'=>false, 'parent'=>0]);
        echo '<p><label for="ukr_service_category_select"><strong>Напрям послуг <span style="color:#b32d2e">*</span></strong></label></p>';
        echo '<select name="ukr_service_category" id="ukr_service_category_select" class="widefat" required aria-required="true">';
        echo '<option value="">— Оберіть напрям —</option>';
        if (!is_wp_error($terms)) foreach ($terms as $term) {
            echo '<option value="'.(int)$term->term_id.'" '.selected($selected, (int)$term->term_id, false).'>'.esc_html($term->name).'</option>';
        }
        echo '</select><p class="description">Без напряму послугу не можна опублікувати. Порядок підпослуг задається в «Послуги → Напрями послуг → Редагувати».</p>';
    }

    private static function valid_direction($term_id): bool {
        $id = absint($term_id);
        if (!$id) return false;
        $term = get_term($id, self::TAX);
        return $term instanceof WP_Term && (int)$term->parent === 0;
    }

    /** Backend validation also catches bypassing browser-side `required`. */
    public static function require_category_before_publish(array $data, array $postarr): array {
        if (($data['post_type'] ?? '') !== 'ukr_service' || !in_array(($data['post_status'] ?? ''), ['publish', 'future'], true)) return $data;
        if (isset($_POST['ukr_service_category'])) {
            $raw = wp_unslash($_POST['ukr_service_category']);
            $valid = is_scalar($raw) && self::valid_direction($raw);
        } else {
            $id = absint($postarr['ID'] ?? 0);
            $terms = $id ? wp_get_post_terms($id, self::TAX, ['fields'=>'ids']) : [];
            $valid = !is_wp_error($terms) && count($terms) === 1 && self::valid_direction($terms[0]);
        }
        if (!$valid) {
            $data['post_status'] = 'draft';
            if (is_admin() && get_current_user_id()) set_transient('ukr_missing_category_'.get_current_user_id(), '1', 60);
        }
        return $data;
    }

    public static function missing_category_notice(): void {
        $id = get_current_user_id();
        if (!$id || !get_transient('ukr_missing_category_'.$id)) return;
        delete_transient('ukr_missing_category_'.$id);
        echo '<div class="notice notice-error is-dismissible"><p><strong>Послугу збережено як чернетку:</strong> для публікації потрібно вибрати один напрям послуг.</p></div>';
    }

    public static function save_service_fields(int $id): void {
        if (!isset($_POST['ukr_service_catalog_nonce']) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ukr_service_catalog_nonce'])), 'ukr_service_catalog') ||
            (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || wp_is_post_revision($id) ||
            !current_user_can('edit_post', $id)) return;
        foreach (['_ukr_short_description' => ['ukr_short_description', 280], '_ukr_price_from' => ['ukr_price_from', 100], '_ukr_price_unit' => ['ukr_price_unit', 100]] as $key => [$field, $limit]) {
            $raw = $_POST[$field] ?? '';
            $value = is_string($raw) ? sanitize_text_field(wp_unslash($raw)) : '';
            update_post_meta($id, $key, mb_substr($value, 0, $limit));
        }
        if (array_key_exists('ukr_service_category', $_POST)) {
            $raw = wp_unslash($_POST['ukr_service_category']);
            $term_id = is_scalar($raw) ? absint($raw) : 0;
            wp_set_object_terms($id, self::valid_direction($term_id) ? [$term_id] : [], self::TAX, false);
        }
        if (array_key_exists('ukr_service_gallery', $_POST)) {
            $raw = wp_unslash($_POST['ukr_service_gallery']);
            $ids = is_string($raw) ? explode(',', $raw) : [];
            $ids = array_slice(array_values(array_unique(array_filter(array_map('absint', $ids)))), 0, 12);
            $ids = array_values(array_filter($ids, 'wp_attachment_is_image'));
            update_post_meta($id, '_ukr_service_gallery', implode(',', $ids));
        }
    }

    private static function category_form_fields(int $term_id = 0): void {
        $image = $term_id ? (int) get_term_meta($term_id, '_ukr_cat_image', true) : 0;
        $order = $term_id ? (int) get_term_meta($term_id, '_ukr_cat_order', true) : 0;
        $visible = $term_id ? (string) get_term_meta($term_id, '_ukr_cat_visible', true) : '1';
        wp_nonce_field('ukr_category_fields', 'ukr_category_nonce');
        echo '<p><label for="ukr_cat_order">Порядок відображення</label><br><input id="ukr_cat_order" name="ukr_cat_order" type="number" step="1" value="'.esc_attr((string) $order).'"></p>';
        echo '<p><label><input type="checkbox" name="ukr_cat_visible" value="1" '.checked($visible, '1', false).'> Показувати напрям на сайті</label></p>';
        echo '<p><label><strong>Фото категорії</strong></label><br><input type="hidden" id="ukr_cat_image" name="ukr_cat_image" value="'.esc_attr((string) $image).'">';
        echo '<span class="ukr-cat-preview">'.($image ? wp_get_attachment_image($image, 'thumbnail') : '').'</span><br>';
        echo '<button type="button" class="button ukr-cat-pick">Обрати фото</button> <button type="button" class="button ukr-cat-clear">Прибрати фото</button></p>';
        echo '<p class="description">Короткий опис категорії заповніть у полі «Опис» вище. Для послуг використовуйте окремі записи з призначеним напрямом.</p>';
    }
    public static function add_fields(): void { echo '<div class="form-field">'; self::category_form_fields(); echo '</div>'; }
    public static function edit_fields(WP_Term $term): void { echo '<tr class="form-field"><th scope="row">Оформлення категорії</th><td>'; self::category_form_fields($term->term_id); echo '</td></tr>'; }
    /** Order all subservices from the parent direction's edit screen. */
    public static function edit_service_order(WP_Term $term): void {
        if ((int)$term->parent !== 0) return;
        $items = get_posts([
            'post_type'=>'ukr_service', 'post_status'=>['publish','draft','pending','future','private'],
            'numberposts'=>-1, 'orderby'=>['menu_order'=>'ASC','title'=>'ASC'],
            'tax_query'=>[['taxonomy'=>self::TAX,'field'=>'term_id','terms'=>[(int)$term->term_id]]],
        ]);
        echo '<tr class="form-field"><th scope="row"><label>Порядок підпослуг</label></th><td>';
        if (!$items) {
            echo '<p>У цьому напрямі ще немає підпослуг. Створіть їх у розділі «Послуги».</p>';
        } else {
            echo '<p>Переміщуйте підпослуги кнопками. Після цього натисніть <strong>«Оновити»</strong> внизу сторінки.</p>';
            echo '<ol id="ukr-subservice-order" class="ukr-subservice-order" style="max-width:620px;margin:16px 0;padding:0;list-style:none">';
            foreach ($items as $item) {
                echo '<li style="display:flex;align-items:center;gap:10px;padding:10px 12px;margin-bottom:6px;background:white;border:1px solid #dcdcde;border-radius:6px">';
                echo '<input type="hidden" name="ukr_subservice_order[]" value="'.(int)$item->ID.'">';
                echo '<span class="ukr-sort-name" style="flex:1">'.esc_html(get_the_title($item)).'</span>';
                echo '<button type="button" class="button ukr-order-up" aria-label="Підняти '.esc_attr(get_the_title($item)).'">↑</button>';
                echo '<button type="button" class="button ukr-order-down" aria-label="Опустити '.esc_attr(get_the_title($item)).'">↓</button>';
                echo '</li>';
            }
            echo '</ol>';
        }
        echo '</td></tr>';
    }

    public static function save_category_fields(int $term_id): void {
        if (!current_user_can('manage_categories') || !isset($_POST['ukr_category_nonce']) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ukr_category_nonce'])), 'ukr_category_fields')) return;
        $image = absint($_POST['ukr_cat_image'] ?? 0);
        update_term_meta($term_id, '_ukr_cat_image', $image && wp_attachment_is_image($image) ? $image : 0);
        update_term_meta($term_id, '_ukr_cat_order', (int) ($_POST['ukr_cat_order'] ?? 0));
        update_term_meta($term_id, '_ukr_cat_visible', isset($_POST['ukr_cat_visible']) ? '1' : '0');
        if (isset($_POST['ukr_subservice_order']) && is_array($_POST['ukr_subservice_order']) && current_user_can('edit_posts')) {
            $posted = array_values(array_unique(array_filter(array_map('absint', wp_unslash($_POST['ukr_subservice_order'])))));
            // Reject forged IDs. Sort all services in the direction; don't let a partial payload reorder unseen entries.
            $existing = get_posts(['post_type'=>'ukr_service','post_status'=>['publish','draft','pending','future','private'],
                'numberposts'=>-1,'fields'=>'ids','tax_query'=>[['taxonomy'=>self::TAX,'field'=>'term_id','terms'=>[$term_id]]]]);
            $expected = array_map('intval', $existing);
            $left = $posted; $right = $expected; sort($left); sort($right);
            if ($left === $right) foreach ($posted as $index => $post_id) {
                if (!current_user_can('edit_post', $post_id)) continue;
                wp_update_post(['ID'=>$post_id,'menu_order'=>($index+1)*10]);
            }
        }
    }
    public static function admin_assets(string $hook): void {
        $screen = get_current_screen();
        if ($screen && $screen->post_type === 'ukr_service' && in_array($hook, ['post.php', 'post-new.php'], true)) {
            wp_enqueue_media();
            wp_enqueue_script('ukr-service-gallery-admin', plugins_url('service-gallery-admin.js', __FILE__), ['media-views'], '1.2.0', true);
        }
        if (!in_array($hook, ['edit-tags.php', 'term.php'], true) || sanitize_key($_GET['taxonomy'] ?? '') !== self::TAX) return;
        if ($hook === 'term.php') wp_enqueue_script('ukr-category-order', plugins_url('category-order-admin.js', __FILE__), [], '1.14.0', true);
        wp_enqueue_media();
        wp_enqueue_script('ukr-category-media', plugins_url('category-media.js', __FILE__), ['jquery', 'media-views'], '1.1.0', true);
    }
    public static function unassigned_notice(): void {
        $screen = get_current_screen();
        if (!$screen || $screen->post_type !== 'ukr_service' || !current_user_can('edit_posts')) return;
        echo '<div class="notice notice-info"><p>UKreklamy: у кожної опублікованої послуги має бути один «Напрям послуг». Послуги без напряму не відображаються на сайті.</p></div>';
    }
}
