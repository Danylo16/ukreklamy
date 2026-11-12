<?php
/**
 * Plugin Name: UKreklamy Core
 * Description: Послуги, портфоліо, контакти, захищені заявки та сповіщення Telegram.
 * Version: 1.13.0
 * Requires PHP: 8.1
 * Text Domain: ukreklamy
 */
if (!defined('ABSPATH')) exit;
require_once __DIR__ . '/class-ukr-service-catalog.php';
require_once __DIR__ . '/class-ukr-hero-settings.php';
require_once __DIR__ . '/class-ukr-site-content.php';
require_once __DIR__ . '/class-ukr-editorial-sections.php';
require_once __DIR__ . '/class-ukr-telegram-diagnostics.php';
require_once __DIR__ . '/class-ukr-contact-links.php';
require_once __DIR__ . '/class-ukr-typography.php';

final class UKR_Core {
    const OPT = 'ukr_contacts';
    const HOOK = 'ukr_retry_leads';
    static function boot(): void {
        add_action('init', [__CLASS__, 'types']);
        add_action('add_meta_boxes', [__CLASS__, 'boxes']);
        add_action('save_post_ukr_service', [__CLASS__, 'save_meta']);
        add_action('save_post_ukr_work', [__CLASS__, 'save_meta']);
        add_action('admin_menu', [__CLASS__, 'menu']);
        add_action('admin_init', [__CLASS__, 'settings']);
        add_action('rest_api_init', [__CLASS__, 'routes']);
        add_action(self::HOOK, [__CLASS__, 'retry']);
        add_filter('manage_ukr_lead_posts_columns', [__CLASS__, 'lead_columns']);
        add_action('manage_ukr_lead_posts_custom_column', [__CLASS__, 'lead_column'], 10, 2);
        add_action('admin_post_ukr_retry_lead', [__CLASS__, 'manual_retry']);
        add_action('admin_notices', [__CLASS__, 'admin_notice']);
        add_filter('wp_insert_post_data', [__CLASS__, 'lock_lead'], 10, 2);
    }
    static function activate(): void {
        self::types(); flush_rewrite_rules();
        if (!wp_next_scheduled(self::HOOK)) wp_schedule_event(time() + 300, 'hourly', self::HOOK);
    }
    static function deactivate(): void { wp_clear_scheduled_hook(self::HOOK); flush_rewrite_rules(); }
    static function types(): void {
        UKR_Service_Catalog::register_taxonomy();
        register_post_type('ukr_service', [
            'labels'=>self::labels('Послуга','Послуги'), 'public'=>false, 'show_ui'=>true,'show_in_menu'=>true,
            'supports'=>['title','editor','thumbnail','page-attributes'],'menu_icon'=>'dashicons-hammer','show_in_rest'=>false,
        ]);
        register_post_type('ukr_work', [
            'labels'=>self::labels('Робота','Портфоліо'), 'public'=>false,'show_ui'=>true,
            'supports'=>['title','editor','thumbnail','page-attributes'],'menu_icon'=>'dashicons-format-gallery','show_in_rest'=>false,
        ]);
        register_post_type('ukr_lead', [
            'labels'=>self::labels('Заявка','Заявки'),'public'=>false,'show_ui'=>true,'show_in_menu'=>true,
            'capabilities'=>['create_posts'=>'do_not_allow'],'map_meta_cap'=>true,
            'supports'=>['title'],'menu_icon'=>'dashicons-email-alt', 'show_in_rest'=>false,
        ]);
    }
    static function labels(string $single,string $plural): array {
        return ['name'=>$plural,'singular_name'=>$single,'add_new_item'=>'Додати: '.$single,
            'edit_item'=>'Редагувати: '.$single,'all_items'=>$plural,'menu_name'=>$plural];
    }
    static function boxes(): void {
        add_meta_box('ukr_service_details','Параметри послуги',[__CLASS__,'service_box'],'ukr_service','normal');
        add_meta_box('ukr_work_details','Фотографії проєкту',[__CLASS__,'work_box'],'ukr_work','normal');
        add_meta_box('ukr_lead_details','Деталі заявки',[__CLASS__,'lead_box'],'ukr_lead','normal');
    }
    static function service_box(WP_Post $post): void {
        wp_nonce_field('ukr_meta','ukr_meta_nonce');
        $visible = get_post_meta($post->ID,'_ukr_visible',true);
        echo '<p>Назва — поле «Заголовок», опис — основний редактор, фото — «Зображення запису», порядок — атрибут «Порядок».</p>';
        echo '<label><input type="checkbox" name="ukr_visible" value="1" '.checked($visible!=='0',true,false).'> Показувати на сайті</label>';
        echo '<p class="description">Для нових послуг показ увімкнено за замовчуванням. Зніміть прапорець, щоб приховати.</p>';
    }
    static function work_box(WP_Post $post): void {
        wp_nonce_field('ukr_meta','ukr_meta_nonce');
        $ids = array_filter(array_map('absint',explode(',',(string)get_post_meta($post->ID,'_ukr_gallery',true))));
        echo '<p>Назва — заголовок; короткий опис — редактор; обкладинка — «Зображення запису». Порядок — атрибут «Порядок».</p>';
        echo '<label for="ukr_gallery">ID додаткових фотографій з Медіатеки (через кому)</label><input class="widefat" id="ukr_gallery" name="ukr_gallery" value="'.esc_attr(implode(',',$ids)).'">';
        echo '<p class="description">Відкрийте фото в Медіатеці та скопіюйте його ID. Необов’язково; обкладинка є основною фотографією.</p>';
    }
    static function save_meta(int $post_id): void {
        if (!isset($_POST['ukr_meta_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ukr_meta_nonce'])),'ukr_meta') ||
            (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !current_user_can('edit_post',$post_id)) return;
        if (get_post_type($post_id)==='ukr_service') {
            update_post_meta($post_id,'_ukr_visible',isset($_POST['ukr_visible'])?'1':'0');
        } else {
            $raw = sanitize_text_field(wp_unslash($_POST['ukr_gallery'] ?? ''));
            $ids = array_values(array_unique(array_filter(array_map('absint',explode(',',$raw)))));
            $ids = array_filter($ids,fn($id)=>wp_attachment_is_image($id));
            update_post_meta($post_id,'_ukr_gallery',implode(',',$ids));
        }
    }
    static function menu(): void { add_options_page('Контакти UKreklamy','Контакти UKreklamy','manage_options','ukr-contacts',[__CLASS__,'contacts_page']); }
    static function fields(): array { return ['phone'=>'Телефон','instagram'=>'Instagram URL','telegram'=>'Telegram URL','address'=>'Адреса','email'=>'Email','facebook'=>'Facebook URL']; }
    static function settings(): void {
        register_setting('ukr_contacts_group',self::OPT,['type'=>'array','sanitize_callback'=>[__CLASS__,'sanitize_contacts'],'default'=>[]]);
    }
    static function sanitize_contacts($raw): array {
        if (!is_array($raw)) return [];
        $out=[];
        foreach (self::fields() as $key=>$label) {
            $value = (string)($raw[$key] ?? '');
            $out[$key] = $key==='email' ? sanitize_email($value) : (in_array($key,['instagram','telegram','facebook'],true) ? esc_url_raw($value,['https']) : sanitize_text_field($value));
        }
        return $out;
    }
    static function contacts_page(): void {
        if (!current_user_can('manage_options')) return;
        $values=get_option(self::OPT,[]);
        echo '<div class="wrap"><h1>Контакти UKreklamy</h1><form action="options.php" method="post">';
        settings_fields('ukr_contacts_group'); echo '<table class="form-table"><tbody>';
        foreach (self::fields() as $key=>$label) {
            echo '<tr><th><label for="ukr_'.esc_attr($key).'">'.esc_html($label).'</label></th><td><input class="regular-text" id="ukr_'.esc_attr($key).'" name="'.esc_attr(self::OPT).'['.esc_attr($key).']" value="'.esc_attr($values[$key]??'').'"></td></tr>';
        }
        echo '</tbody></table>'; submit_button('Зберегти контакти'); echo '</form></div>';
    }
    static function routes(): void {
        register_rest_route('ukreklamy/v1','/lead', ['methods'=>'POST','callback'=>[__CLASS__,'lead'],
            'permission_callback'=>'__return_true']);
        register_rest_route('ukreklamy/v1','/token',['methods'=>'GET','callback'=>fn()=>new WP_REST_Response(['token'=>wp_create_nonce('ukr_form'),'issued'=>time()],200), 'permission_callback'=>'__return_true']);
    }
    static function rate_limit(string $ip): bool {
        $key='ukr_rate_'.hash_hmac('sha256',$ip,wp_salt('auth'));
        $hits=(int)get_transient($key);
        if ($hits>=5) return false;
        set_transient($key,$hits+1,10*MINUTE_IN_SECONDS);
        return true;
    }
    static function lead(WP_REST_Request $request) {
        $data = $request->get_json_params();
        if (!is_array($data)) return new WP_Error('invalid_json','Некоректний запит',['status'=>400]);
        // Public REST endpoints cannot rely solely on WordPress' logged-in REST nonce.
        // Double-submit custom nonce, Origin check, SameSite cookie, honeypot, and limits.
        $origin=$request->get_header('origin');
        if ($origin && untrailingslashit($origin)!==untrailingslashit(home_url())) return new WP_Error('origin','Запит з іншого сайту заборонено',['status'=>403]);
        $cookie=isset($_COOKIE['ukr_form_token'])?sanitize_text_field(wp_unslash($_COOKIE['ukr_form_token'])):'';
        $submitted=sanitize_text_field((string)($data['token']??''));
        if (!$cookie || !$submitted || !hash_equals($cookie,$submitted) || !wp_verify_nonce($submitted,'ukr_form')) return new WP_Error('csrf','Оновіть сторінку та спробуйте знову',['status'=>403]);
        if (!empty($data['website'])) return new WP_Error('spam','Запит не прийнято',['status'=>400]);
        if (!empty($data['started']) && time()-(int)$data['started']<3) return new WP_Error('too_fast','Перевірте форму та повторіть спробу',['status'=>429]);
        $ip=(string)($_SERVER['REMOTE_ADDR']??'unknown');
        $channel=sanitize_key((string)($data['channel']??''));
        $contact=sanitize_text_field((string)($data['contact']??''));
        $comment=sanitize_textarea_field((string)($data['comment']??''));
        $service_id=absint($data['service_id']??0);
        if ($service_id && (get_post_type($service_id)!=='ukr_service' || get_post_status($service_id)!=='publish' || get_post_meta($service_id,'_ukr_visible',true)==='0' || !has_term('', UKR_Service_Catalog::TAX, $service_id))) return new WP_Error('invalid_service','Обрана послуга недоступна',['status'=>422]);
        $privacy=($data['privacy']??false)===true;
        if (!in_array($channel,['phone','telegram'],true) || !$privacy || mb_strlen($contact)>120 || mb_strlen($comment)>1500 || !$contact) return new WP_Error('invalid','Заповніть канал, контакт і згоду',['status'=>422]);
        if ($channel==='phone' && !preg_match('/^\+?[0-9\s().-]{7,25}$/u',$contact)) return new WP_Error('invalid_phone','Укажіть коректний номер телефону',['status'=>422]);
        if ($channel==='telegram' && !preg_match('/^@[a-zA-Z0-9_]{5,32}$/',$contact)) return new WP_Error('invalid_telegram','Укажіть Telegram у форматі @username',['status'=>422]);
        $idempotency=sanitize_text_field((string)($data['request_id']??''));
        if (!preg_match('/^[a-f0-9-]{36}$/i',$idempotency)) return new WP_Error('request_id','Некоректний ідентифікатор запиту',['status'=>422]);
        $dedupe='ukr_dup_'.hash_hmac('sha256',$idempotency,wp_salt('auth'));
        $existing=(int)get_option($dedupe,0);
        if ($existing) return new WP_REST_Response(['saved'=>true,'id'=>$existing,'message'=>'Заявку вже збережено. Повторно відправляти не потрібно.'],200);
        if (!self::rate_limit($ip)) return new WP_Error('rate_limit','Забагато запитів. Спробуйте пізніше',['status'=>429]);
        if (!add_option($dedupe,'pending','','no')) {
            return new WP_Error('processing','Заявка вже обробляється. Не надсилайте її повторно',['status'=>409]);
        }
        $id=wp_insert_post(['post_type'=>'ukr_lead','post_status'=>'private','post_title'=>'Заявка '.wp_date('d.m.Y H:i:s')],true);
        if (is_wp_error($id)) { delete_option($dedupe); return new WP_Error('storage','Не вдалося зберегти заявку. Спробуйте знову',['status'=>500]); }
        update_post_meta($id,'_ukr_channel',$channel); update_post_meta($id,'_ukr_contact',$contact);
        update_post_meta($id,'_ukr_comment',$comment); update_post_meta($id,'_ukr_service_id',$service_id);
        update_post_meta($id,'_ukr_service_name',$service_id ? get_the_title($service_id) : '');
        update_post_meta($id,'_ukr_status','pending');
        update_post_meta($id,'_ukr_attempts',0); update_post_meta($id,'_ukr_created',current_time('mysql',true));
        update_option($dedupe,$id,false);
        self::send($id);
        return new WP_REST_Response(['saved'=>true,'id'=>$id,'message'=>'Заявку збережено. Ми опрацюємо її; якщо сповіщення затримується, система спробує знову.'],201);
    }
    static function send(int $id): bool {
        if (get_post_type($id)!=='ukr_lead' || get_post_meta($id,'_ukr_status',true)==='sent') return false;
        $attempts=(int)get_post_meta($id,'_ukr_attempts',true);
        update_post_meta($id,'_ukr_attempts',$attempts+1);
        $token=defined('UKR_TELEGRAM_TOKEN')?UKR_TELEGRAM_TOKEN:'';
        $chats=defined('UKR_TELEGRAM_CHAT_IDS')?array_filter(array_map('trim',explode(',',UKR_TELEGRAM_CHAT_IDS))):[];
        $pending=(string)get_post_meta($id,'_ukr_pending_chats',true);
        if ($pending!=='') $chats=array_filter(array_map('trim',explode(',',$pending))); 
        if (!$token || !$chats) { update_post_meta($id,'_ukr_status','pending'); update_post_meta($id,'_ukr_last_error','Telegram ще не налаштований'); return false; }
        $channel=get_post_meta($id,'_ukr_channel',true)==='telegram'?'Telegram':'Телефон';
        $service_id=(int)get_post_meta($id,'_ukr_service_id',true);
        $service_name=(string)get_post_meta($id,'_ukr_service_name',true);
        if (!$service_name && $service_id && get_post_type($service_id)==='ukr_service') $service_name=get_the_title($service_id);
        $service_line=$service_name ? 'Послуга: '.$service_name."\n" : '';
        $msg="Нова заявка #{$id}\n{$service_line}Канал зв'язку: {$channel}\nКонтакт: ".get_post_meta($id,'_ukr_contact',true)."\nКоментар: ".(get_post_meta($id,'_ukr_comment',true)?:'—')."\nДата: ".get_the_date('d.m.Y H:i',$id);
        $failed=[];
        foreach ($chats as $chat) {
            $res=wp_remote_post('https://api.telegram.org/bot'.rawurlencode($token).'/sendMessage',[
                'timeout'=>12,'redirection'=>0,'body'=>['chat_id'=>$chat,'text'=>$msg],
            ]);
            $json=!is_wp_error($res)?json_decode(wp_remote_retrieve_body($res),true):null;
            if (is_wp_error($res)||wp_remote_retrieve_response_code($res)!==200||!is_array($json)||empty($json['ok'])) $failed[]=$chat;
        }
        if ($failed) {
            update_post_meta($id,'_ukr_status','pending');
            update_post_meta($id,'_ukr_last_error','Не доставлено у '.count($failed).' чат(и); буде повторено');
            // Retry only failed destinations: never resend to managers already notified.
            update_post_meta($id,'_ukr_pending_chats',implode(',',$failed));
            return false;
        }
        update_post_meta($id,'_ukr_pending_chats','');
        update_post_meta($id,'_ukr_status','sent'); delete_post_meta($id,'_ukr_last_error');
        update_post_meta($id,'_ukr_sent_at',current_time('mysql',true));return true;
    }
    static function retry(): void {
        $leads=get_posts(['post_type'=>'ukr_lead','post_status'=>'private','posts_per_page'=>30,
            'meta_query'=>[['key'=>'_ukr_status','value'=>'pending']],'orderby'=>'date','order'=>'ASC']);
        foreach ($leads as $post) {
            if ((int) get_post_meta($post->ID, '_ukr_attempts', true) >= 24) {
                // Do not let exhausted old leads starve newer ones in the next WP-Cron run.
                update_post_meta($post->ID, '_ukr_status', 'failed');
                update_post_meta($post->ID, '_ukr_last_error', 'Вичерпано автоматичні спроби. Потрібне ручне повторне надсилання.');
            } else {
                self::send($post->ID);
            }
        }
    }
    static function lead_columns(array $cols): array {return ['cb'=>$cols['cb'],'title'=>'Заявка','ukr_contact'=>'Контакт','ukr_status'=>'Telegram','date'=>'Дата'];}
    static function lead_column(string $col,int $id): void {
        if($col==='ukr_contact') echo esc_html(get_post_meta($id,'_ukr_channel',true).' · '.get_post_meta($id,'_ukr_contact',true));
        if ($col === 'ukr_status') {
            $status = (string) get_post_meta($id, '_ukr_status', true);
            echo esc_html($status === 'sent' ? 'Надіслано' : ($status === 'failed' ? 'Потрібен ручний повтор' : 'Очікує повтору'));
        }
    }
    static function lead_box(WP_Post $post): void {
        $service_name = (string) get_post_meta($post->ID, '_ukr_service_name', true);
        $service_id = (int) get_post_meta($post->ID, '_ukr_service_id', true);
        if (!$service_name && $service_id) $service_name = (string) get_the_title($service_id);
        echo '<p><strong>Обрана послуга:</strong> '.esc_html($service_name ?: 'Не вибрано').'</p>';
        foreach(['_ukr_channel'=>'Канал','_ukr_contact'=>'Контакт','_ukr_comment'=>'Коментар','_ukr_status'=>'Статус','_ukr_attempts'=>'Спроби','_ukr_created'=>'Створено (UTC)','_ukr_sent_at'=>'Надіслано (UTC)','_ukr_last_error'=>'Остання помилка'] as $key=>$label) {
            echo '<p><strong>'.esc_html($label).':</strong> '.esc_html((string)get_post_meta($post->ID,$key,true)).'</p>';
        }
        if(get_post_meta($post->ID,'_ukr_status',true)!=='sent') {
            $url=wp_nonce_url(admin_url('admin-post.php?action=ukr_retry_lead&id='.$post->ID),'ukr_retry_'.$post->ID);
            echo '<p><a class="button" href="'.esc_url($url).'">Повторити надсилання</a></p>';
        }
    }
    static function manual_retry(): void {
        $id=absint($_GET['id']??0);
        if (!current_user_can('edit_post',$id)||!check_admin_referer('ukr_retry_'.$id)) wp_die('Недостатньо прав');
        self::send($id);wp_safe_redirect(get_edit_post_link($id,'raw'));exit;
    }
    static function admin_notice(): void {
        if (!current_user_can('manage_options')) return;
        if(!wp_next_scheduled(self::HOOK)) echo '<div class="notice notice-warning"><p>UKreklamy: WP-Cron не заплановано. Повторне надсилання заявок може не працювати.</p></div>';
    }
    static function lock_lead(array $data,array $postarr): array { return $data; }
}
UKR_Core::boot();
UKR_Service_Catalog::boot();
register_activation_hook(__FILE__,['UKR_Core','activate']);
register_deactivation_hook(__FILE__,['UKR_Core','deactivate']);

/** Visibility status in the services list: a published post can still be hidden. */
add_filter('manage_ukr_service_posts_columns', static function(array $columns): array {
    $updated = [];
    foreach ($columns as $key => $label) {
        if ($key === 'date') $updated['ukr_front_visibility'] = 'На сайті';
        $updated[$key] = $label;
    }
    return $updated;
});
add_action('manage_ukr_service_posts_custom_column', static function(string $column, int $post_id): void {
    if ($column !== 'ukr_front_visibility') return;
    if (get_post_status($post_id) !== 'publish') {echo 'Не опубліковано'; return;}
    if (get_post_meta($post_id,'_ukr_visible',true) === '0') {
        echo '<strong style="color:#a51d27">Приховано</strong><br><small>Відкрийте запис і ввімкніть «Показувати на сайті»</small>';
    } else {
        echo '<span style="color:#16733b">Показується</span>';
    }
}, 10, 2);
