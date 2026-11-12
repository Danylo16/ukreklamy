<?php
/**
 * UKreklamy v1.16: server-side antiflood and Cloudflare Turnstile verification.
 * Turnstile is optional until BOTH keys are configured in wp-config.php.
 */
if (!defined('ABSPATH')) exit;

final class UKR_Form_Security {
    public static function client_ip(): string {
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
        // Never trust client-supplied forwarding headers without an origin firewall.
        if (defined('UKR_TRUST_CF_IP') && UKR_TRUST_CF_IP === true) {
            $candidate = trim((string) ($_SERVER['HTTP_CF_CONNECTING_IP'] ?? ''));
            if (filter_var($candidate, FILTER_VALIDATE_IP)) $ip = $candidate;
        }
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'unknown';
    }

    public static function valid_started($started): bool {
        if (!is_int($started) && !(is_string($started) && ctype_digit($started))) return false;
        $age = time() - (int) $started;
        return $age >= 3 && $age <= DAY_IN_SECONDS;
    }

    public static function check(WP_REST_Request $request, array $data, string $ip, string $channel, string $contact) {
        if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 16384)
            return new WP_Error('request_too_large', 'Запит завеликий', ['status' => 413]);
        $site = strtolower(trim((string) $request->get_header('sec-fetch-site')));
        if ($site && !in_array($site, ['same-origin', 'none'], true))
            return new WP_Error('cross_site', 'Запит з іншого сайту заборонено', ['status' => 403]);
        if (!self::valid_started($data['started'] ?? null))
            return new WP_Error('form_time', 'Оновіть сторінку та повторіть спробу', ['status' => 422]);

        $checked = self::turnstile($data, $ip);
        if (is_wp_error($checked)) return $checked;

        // Rate-limit repeated submissions to the same contact even across IPs.
        // Store only a salted HMAC, not contact information.
        $key = 'ukr_contact_rate_' . hash_hmac('sha256', $channel . '|' . mb_strtolower(trim($contact)), wp_salt('auth'));
        $hits = (int) get_transient($key);
        if ($hits >= 3)
            return new WP_Error('contact_rate', 'Забагато заявок на цей контакт. Спробуйте пізніше', ['status' => 429]);
        set_transient($key, $hits + 1, HOUR_IN_SECONDS);
        return true;
    }

    private static function turnstile(array $data, string $ip) {
        $site_key = defined('UKR_TURNSTILE_SITE_KEY') ? trim((string) UKR_TURNSTILE_SITE_KEY) : '';
        $secret = defined('UKR_TURNSTILE_SECRET_KEY') ? trim((string) UKR_TURNSTILE_SECRET_KEY) : '';
        if ($site_key === '' && $secret === '') return true; // Explicitly documented optional integration.
        if ($site_key === '' || $secret === '')
            return new WP_Error('turnstile_config', 'Захист форми налаштовано не повністю', ['status' => 503]);
        $token = isset($data['turnstile_token']) && is_string($data['turnstile_token'])
            ? trim($data['turnstile_token']) : '';
        if ($token === '' || strlen($token) > 2048)
            return new WP_Error('turnstile_required', 'Підтвердьте перевірку захисту від спаму', ['status' => 422]);
        $body = ['secret' => $secret, 'response' => $token];
        if (filter_var($ip, FILTER_VALIDATE_IP)) $body['remoteip'] = $ip;
        $response = wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'timeout' => 8, 'redirection' => 0, 'body' => $body,
        ]);
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200)
            return new WP_Error('turnstile_unavailable', 'Не вдалося перевірити захист форми. Спробуйте ще раз', ['status' => 503]);
        $result = json_decode(wp_remote_retrieve_body($response), true);
        $expected_host = strtolower((string) wp_parse_url(home_url('/'), PHP_URL_HOST));
        $verified_host = strtolower((string) ($result['hostname'] ?? ''));
        if (!is_array($result) || empty($result['success']) ||
            ($expected_host !== '' && $verified_host !== $expected_host) ||
            ($result['action'] ?? '') !== 'lead_form')
            return new WP_Error('turnstile_failed', 'Перевірку захисту не пройдено. Спробуйте знову', ['status' => 403]);
        return true;
    }
}
