<?php
/** Safe Telegram diagnostics and explicit, authorized test delivery. Never exposes secrets. */
if (!defined('ABSPATH')) exit;

final class UKR_Telegram_Diagnostics {
    public const SLUG = 'ukr-telegram-diagnostics';
    public static function boot(): void {
        add_action('admin_menu', [__CLASS__, 'menu']);
        add_action('admin_post_ukr_telegram_test', [__CLASS__, 'test_delivery']);
    }
    public static function menu(): void {
        add_submenu_page('ukr-homepage', 'Telegram та заявки', 'Telegram та заявки', 'manage_options', self::SLUG, [__CLASS__, 'page']);
    }
    public static function config(): array {
        $token = defined('UKR_TELEGRAM_TOKEN') ? trim((string) UKR_TELEGRAM_TOKEN) : '';
        $ids = defined('UKR_TELEGRAM_CHAT_IDS') ? explode(',', (string) UKR_TELEGRAM_CHAT_IDS) : [];
        $ids = array_values(array_unique(array_filter(array_map('trim', $ids), static fn($x) => preg_match('/^-?[0-9]+$/', $x))));
        return ['token' => $token, 'ids' => $ids];
    }
    public static function send_test(): bool {
        $cfg = self::config();
        if (!$cfg['token'] || !$cfg['ids']) return false;
        $text = 'UKreklamy — тестове сповіщення. Перевірка доставки з адмінпанелі WordPress. Це не заявка клієнта.';
        $ok = true;
        foreach ($cfg['ids'] as $id) {
            $res = wp_remote_post('https://api.telegram.org/bot'.rawurlencode($cfg['token']).'/sendMessage', [
                'timeout' => 12, 'redirection' => 0, 'body' => ['chat_id' => $id, 'text' => $text],
            ]);
            $payload = !is_wp_error($res) ? json_decode(wp_remote_retrieve_body($res), true) : null;
            if (is_wp_error($res) || wp_remote_retrieve_response_code($res) !== 200 || !is_array($payload) || empty($payload['ok'])) $ok = false;
        }
        return $ok;
    }
    public static function test_delivery(): void {
        if (!current_user_can('manage_options')) wp_die('Недостатньо прав.');
        check_admin_referer('ukr_telegram_test');
        $result = self::send_test() ? 'sent' : 'failed';
        wp_safe_redirect(add_query_arg('ukr_test', $result, admin_url('admin.php?page='.self::SLUG)));
        exit;
    }
    public static function page(): void {
        if (!current_user_can('manage_options')) wp_die('Недостатньо прав.');
        $cfg = self::config();
        $cron = wp_next_scheduled(UKR_Core::HOOK);
        $pending = get_posts([
            'post_type' => 'ukr_lead', 'post_status' => 'private', 'numberposts' => 25,
            'meta_query' => [['key' => '_ukr_status', 'value' => 'pending']],
        ]);
        $failed = get_posts([
            'post_type' => 'ukr_lead', 'post_status' => 'private', 'numberposts' => 25,
            'meta_query' => [['key' => '_ukr_status', 'value' => 'failed']],
        ]);
        ?>
        <div class="wrap" style="max-width:920px">
          <h1>Telegram — стан інтеграції UKreklamy</h1>
          <p>Секретний токен і Chat ID не відображаються. Тест відправляється лише після натискання кнопки адміністратором.</p>
          <?php $result = sanitize_key($_GET['ukr_test'] ?? ''); if (in_array($result, ['sent', 'failed'], true)): ?>
            <div class="notice <?php echo $result==='sent'?'notice-success':'notice-error'; ?>"><p><?php echo esc_html($result==='sent'?'Telegram підтвердив доставку тестового повідомлення до всіх налаштованих чатів.':'Telegram не підтвердив доставку до всіх чатів. Перевірте токен, Chat ID, права бота й журнали Docker.'); ?></p></div>
          <?php endif; ?>
          <table class="widefat striped"><tbody>
            <tr><th>Токен у серверній конфігурації</th><td><?php echo $cfg['token'] ? 'Налаштований' : 'Відсутній'; ?></td></tr>
            <tr><th>Chat ID</th><td><?php echo $cfg['ids'] ? esc_html(count($cfg['ids']).' чат(ів) налаштовано') : 'Не налаштовано'; ?></td></tr>
            <tr><th>Повторна відправка WP-Cron</th><td><?php echo $cron ? esc_html('Заплановано: '.wp_date('d.m.Y H:i', $cron)) : 'Не заплановано — перевірте активацію плагіна'; ?></td></tr>
            <tr><th>Заявки зі статусом «Очікує» (перші 25)</th><td><?php echo (int)count($pending); ?><?php echo count($pending)===25?' (може бути більше)':''; ?></td></tr>
            <tr><th>Заявки, що потребують ручного повтору (перші 25)</th><td><?php echo (int)count($failed); ?><?php echo count($failed)===25?' (може бути більше)':''; ?></td></tr>
          </tbody></table>
          <p>Наявність токена не означає, що Telegram його приймає. Для перевірки натисніть кнопку.</p>
          <?php if ($cfg['token'] && $cfg['ids']): ?>
          <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="ukr_telegram_test">
            <?php wp_nonce_field('ukr_telegram_test'); ?>
            <?php submit_button('Надіслати одне тестове повідомлення у Telegram', 'secondary'); ?>
          </form>
          <?php endif; ?>
          <p><strong>Важливо:</strong> WP-Cron запускається від відвідувань сайту. На production рекомендовано налаштувати системний cron для стабільних повторних спроб. У розділі «Заявки» є кнопка ручного повтору для недоставлених заявок.</p>
        </div>
        <?php
    }
}
UKR_Telegram_Diagnostics::boot();
