<?php
/** Safe, allowlisted site-wide font configuration. */
if (!defined('ABSPATH')) exit;
final class UKR_Typography {
    const OPTION = 'ukr_typography';
    public static function fonts(): array { return [
        'system' => 'Системний шрифт (без зовнішніх запитів)',
        'manrope' => 'Manrope', 'onest' => 'Onest', 'inter' => 'Inter',
    ]; }
    public static function choices(): array {
        $values = get_option(self::OPTION, []);
        if (!is_array($values)) $values = [];
        $allowed = self::fonts();
        return [
            'heading' => isset($allowed[$values['heading'] ?? '']) ? $values['heading'] : 'system',
            'body' => isset($allowed[$values['body'] ?? '']) ? $values['body'] : 'system',
        ];
    }
    public static function boot(): void {
        add_action('admin_menu', [__CLASS__, 'menu']);
        add_action('admin_init', [__CLASS__, 'register']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue'], 110);
    }
    public static function menu(): void {
        add_submenu_page('ukr-homepage', 'Типографіка', 'Типографіка', UKR_Access::EDIT, 'ukr-typography', [__CLASS__, 'page']);
    }
    public static function register(): void {
        register_setting('ukr_typography_group', self::OPTION, ['type'=>'array', 'sanitize_callback'=>[__CLASS__, 'sanitize']]);
    }
    public static function sanitize($raw): array {
        $allowed = self::fonts();
        if(!is_array($raw)) $raw = [];
        $out=[];
        foreach (['heading','body'] as $key) {
            $value = sanitize_key((string)($raw[$key] ?? 'system'));
            $out[$key] = isset($allowed[$value]) ? $value : 'system';
        }
        return $out;
    }
    public static function enqueue(): void {
        $choices = self::choices();
        $families = ['manrope'=>'Manrope', 'onest'=>'Onest', 'inter'=>'Inter'];
        $selected = array_unique(array_filter([$choices['heading'],$choices['body']], static fn($k)=>isset($families[$k])));
        if($selected) {
            $params=[];
            foreach($selected as $key) $params[]='family='.rawurlencode($families[$key]).':wght@400;500;600;700;800';
            wp_enqueue_style('ukr-fonts-google','https://fonts.googleapis.com/css2?'.implode('&',$params).'&display=swap',[],null);
        }
        $stack = [
           'system'=>'system-ui,-apple-system,"Segoe UI",Arial,sans-serif',
           'manrope'=>'"Manrope",system-ui,Arial,sans-serif',
           'onest'=>'"Onest",system-ui,Arial,sans-serif',
           'inter'=>'"Inter",system-ui,Arial,sans-serif',
        ];
        wp_enqueue_style('ukr-v1151',get_template_directory_uri().'/assets-v1151.css',[],(string)filemtime(get_template_directory().'/assets-v1151.css'));
        wp_add_inline_style('ukr-v1151',':root{--ukr-font-body:'.$stack[$choices['body']].';--ukr-font-heading:'.$stack[$choices['heading']].';}');
    }
    public static function page(): void {
        if(!current_user_can(UKR_Access::EDIT)) wp_die('Недостатньо прав.');
        $v=self::choices();
        echo '<div class="wrap"><h1>Типографіка UKreklamy</h1><p>Оберіть шрифт заголовків і основного тексту. Шрифти Manrope, Onest та Inter завантажуються з Google Fonts; системний варіант не робить зовнішніх запитів. Після зміни перевірте мобільні заголовки.</p><form action="options.php" method="post">';
        settings_fields('ukr_typography_group');
        foreach(['heading'=>'Шрифт заголовків', 'body'=>'Шрифт основного тексту'] as $key=>$label) {
            echo '<p><label for="ukr-font-'.esc_attr($key).'"><strong>'.esc_html($label).'</strong></label><br><select id="ukr-font-'.esc_attr($key).'" name="'.esc_attr(self::OPTION).'['.esc_attr($key).']">';
            foreach(self::fonts() as $value=>$name) echo '<option value="'.esc_attr($value).'" '.selected($v[$key],$value,false).'>'.esc_html($name).'</option>';
            echo '</select></p>';
        }
        submit_button('Зберегти типографіку'); echo '</form></div>';
    }
}
UKR_Typography::boot();
