<?php
/**
 * UKreklamy v1.16: separate content-management permission from WordPress administration.
 * No manage_options, plugin/theme installation, or user-management privileges are granted.
 */
if (!defined('ABSPATH')) exit;

final class UKR_Access {
    public const ROLE = 'ukreklamy_manager';
    public const EDIT = 'ukr_edit_content';
    public const VIEW_LEADS = 'ukr_view_leads';
    private const VERSION = '1.16.0';

    public static function boot(): void {
        add_action('init', [__CLASS__, 'sync_roles'], 5);
        add_action('admin_menu', [__CLASS__, 'leads_menu']);
        foreach (['ukr_hero_group','ukr_site_content_group','ukr_contact_links_group','ukr_contacts_group','ukr_typography_group'] as $group) {
            add_filter('option_page_capability_' . $group, static fn() => self::EDIT);
        }
    }

    private static function content_caps(): array {
        $caps = ['read','upload_files',self::EDIT,self::VIEW_LEADS,
            'read_private_ukr_leads', // Read-only; never grants edit_ukr_leads.
            'ukr_manage_service_categories','ukr_assign_service_categories'];
        foreach (['ukr_services','ukr_works','ukr_clients','ukr_faqs'] as $plural) {
            foreach (['edit_', 'edit_others_', 'edit_published_', 'edit_private_',
                      'publish_', 'read_private_', 'delete_', 'delete_others_',
                      'delete_published_', 'delete_private_'] as $prefix) {
                $caps[] = $prefix . $plural;
            }
        }
        return $caps;
    }

    public static function sync_roles(): void {
        if (get_option('ukr_access_schema') === self::VERSION && get_role(self::ROLE)) return;
        if (!get_role(self::ROLE)) add_role(self::ROLE, 'Менеджер UKreklamy', ['read' => true]);
        $manager = get_role(self::ROLE);
        $admin = get_role('administrator');
        if ($manager) foreach (self::content_caps() as $cap) $manager->add_cap($cap);
        if ($admin) {
            foreach (self::content_caps() as $cap) $admin->add_cap($cap);
            // Applications are created by the site, never by managers.
            foreach (['edit_','edit_others_','edit_published_','edit_private_',
                      'publish_','read_private_','delete_','delete_others_',
                      'delete_published_','delete_private_'] as $prefix) {
                $admin->add_cap($prefix . 'ukr_leads');
            }
        }
        update_option('ukr_access_schema', self::VERSION, false);
    }

    public static function leads_menu(): void {
        add_menu_page('Заявки UKreklamy', 'Заявки (перегляд)', self::VIEW_LEADS,
            'ukr-leads-view', [__CLASS__, 'leads_page'], 'dashicons-email-alt2', 62);
    }

    public static function leads_page(): void {
        if (!current_user_can(self::VIEW_LEADS)) wp_die('Недостатньо прав.');
        $page = max(1, min(500, absint($_GET['paged'] ?? 1)));
        $query = new WP_Query([
            'post_type' => 'ukr_lead', 'post_status' => 'private', 'posts_per_page' => 30,
            'paged' => $page, 'orderby' => 'date', 'order' => 'DESC',
            'no_found_rows' => false,
        ]);
        echo '<div class="wrap"><h1>Заявки UKreklamy — тільки перегляд</h1>';
        echo '<p>Контакти заявників — конфіденційні дані. Не передавайте їх стороннім.</p>';
        echo '<table class="widefat striped"><thead><tr><th>Дата</th><th>Послуга</th><th>Канал</th><th>Контакт</th><th>Коментар</th><th>Telegram</th></tr></thead><tbody>';
        if (!$query->have_posts()) echo '<tr><td colspan="6">Заявок немає.</td></tr>';
        foreach ($query->posts as $post) {
            $id = (int) $post->ID;
            $row = [
                get_the_date('d.m.Y H:i', $id),
                (string) get_post_meta($id, '_ukr_service_name', true),
                (string) get_post_meta($id, '_ukr_channel', true),
                (string) get_post_meta($id, '_ukr_contact', true),
                (string) get_post_meta($id, '_ukr_comment', true),
                (string) get_post_meta($id, '_ukr_status', true),
            ];
            echo '<tr>';
            foreach ($row as $cell) echo '<td style="overflow-wrap:anywhere">'.esc_html($cell).'</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        $total = (int) $query->max_num_pages;
        if ($total > 1) {
            echo '<p class="tablenav-pages">';
            for ($i = max(1, $page - 2); $i <= min($total, $page + 2); $i++) {
                $url = add_query_arg(['page' => 'ukr-leads-view', 'paged' => $i], admin_url('admin.php'));
                echo ' <a class="button'.($i === $page ? ' button-primary' : '').'" href="'.esc_url($url).'">'.(int)$i.'</a> ';
            }
            echo '</p>';
        }
        echo '</div>';
        wp_reset_postdata();
    }
}
