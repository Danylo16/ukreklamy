<?php
/** Editable texts for the existing landing page. One WordPress option; allow-list validation. */
if (!defined('ABSPATH')) exit;

final class UKR_Site_Content {
    public const OPTION = 'ukr_site_content';
    public const SLUG = 'ukr-site-content';

    public static function sections(): array {
        return [
            'header' => ['label' => 'Шапка та навігація', 'fields' => [
                'brand_subtitle' => ['label' => 'Підпис під назвою', 'default' => 'рекламне виробництво', 'type' => 'text'],
                'nav_services' => ['label' => 'Меню: послуги', 'default' => 'Послуги', 'type' => 'text'],
                'nav_works' => ['label' => 'Меню: роботи', 'default' => 'Роботи', 'type' => 'text'],
                'nav_process' => ['label' => 'Меню: процес', 'default' => 'Як працюємо', 'type' => 'text'],
                'nav_about' => ['label' => 'Меню: про компанію', 'default' => 'Про нас', 'type' => 'text'],
                'nav_location' => ['label' => 'Меню: де ми', 'default' => 'Де ми', 'type' => 'text'],
                'nav_cta' => ['label' => 'Кнопка шапки', 'default' => 'Отримати розрахунок →', 'type' => 'text'],
            ]],
            'hero' => ['label' => 'Перший екран (Hero)', 'fields' => [
                'hero_eyebrow' => ['label' => 'Малий напис', 'default' => 'Виробництво реклами · Чернігів', 'type' => 'text'],
                'hero_title_first' => ['label' => 'Заголовок: перший рядок', 'default' => 'Ваш бізнес', 'type' => 'text'],
                'hero_title_emphasis' => ['label' => 'Заголовок: виділений рядок', 'default' => 'мають помітити.', 'type' => 'text'],
                'hero_description' => ['label' => 'Опис', 'default' => 'Вивіски, об’ємні літери, друк і брендування — від ідеї до готового результату. Виготовляємо в Чернігові, відправляємо по Україні.', 'type' => 'textarea'],
                'hero_cta_primary' => ['label' => 'Основна кнопка', 'default' => 'Розрахувати мій проєкт', 'type' => 'text'],
                'hero_cta_secondary' => ['label' => 'Друга кнопка', 'default' => 'Подивитися роботи ↓', 'type' => 'text'],
            ]],
            'stats' => ['label' => 'Інформаційні показники', 'fields' => [
                'stat_1_value' => ['label' => 'Блок 1: велика назва', 'default' => 'Чернігів', 'type' => 'text'],
                'stat_1_caption' => ['label' => 'Блок 1: підпис', 'default' => 'Рекламне виробництво', 'type' => 'text'],
                'stat_2_value' => ['label' => 'Блок 2: велика назва', 'default' => 'Вивіски', 'type' => 'text'],
                'stat_2_caption' => ['label' => 'Блок 2: підпис', 'default' => 'Для бізнесу й просторів', 'type' => 'text'],
                'stat_3_value' => ['label' => 'Блок 3: велика назва', 'default' => 'Друк', 'type' => 'text'],
                'stat_3_caption' => ['label' => 'Блок 3: підпис', 'default' => 'Рекламна продукція', 'type' => 'text'],
                'stat_4_value' => ['label' => 'Блок 4: велика назва', 'default' => 'Консультація', 'type' => 'text'],
                'stat_4_caption' => ['label' => 'Блок 4: підпис', 'default' => 'Під ваш проєкт', 'type' => 'text'],
            ]],
            'services' => ['label' => 'Напрями послуг', 'fields' => [
                'services_eyebrow' => ['label' => 'Малий напис', 'default' => 'Що виготовляємо', 'type' => 'text'],
                'services_title' => ['label' => 'Заголовок секції', 'default' => 'Рішення для вашого бізнесу', 'type' => 'text'],
                'services_description' => ['label' => 'Опис секції', 'default' => 'П’ять основних напрямів. Оберіть напрям, щоб переглянути конкретні послуги.', 'type' => 'textarea'],
                'services_open' => ['label' => 'Напис на картці з підпослугами', 'default' => 'Переглянути послуги', 'type' => 'text'],
                'services_empty_cat' => ['label' => 'Напис на порожній картці', 'default' => 'Перелік уточнюється', 'type' => 'text'],
                'services_empty_all' => ['label' => 'Повідомлення, коли категорій немає', 'default' => 'Напрями послуг ще не заповнені. Залиште запит у формі нижче.', 'type' => 'textarea'],
                'modal_eyebrow' => ['label' => 'Малий напис модального вікна', 'default' => 'Деталі послуги', 'type' => 'text'],
                'modal_price_label' => ['label' => 'Підпис біля вартості', 'default' => 'Орієнтовна вартість', 'type' => 'text'],
                'modal_price_unknown' => ['label' => 'Якщо ціну не вказано', 'default' => 'Ціна уточнюється', 'type' => 'text'],
                'modal_description_empty' => ['label' => 'Якщо опис не заповнено', 'default' => 'Опис послуги буде додано.', 'type' => 'text'],
                'modal_photo_empty' => ['label' => 'Якщо фото не додано', 'default' => 'Фото послуги буде додано', 'type' => 'text'],
                'modal_cta' => ['label' => 'Кнопка в модальному вікні', 'default' => 'Запитати про послугу', 'type' => 'text'],
            ]],
            'about' => ['label' => 'Про компанію', 'fields' => [
                'about_eyebrow' => ['label' => 'Малий напис', 'default' => 'Хто ми', 'type' => 'text'],
                'about_title' => ['label' => 'Заголовок', 'default' => 'Від виробництва до вивіски, яку запам’ятають', 'type' => 'text'],
                'about_description' => ['label' => 'Опис компанії', 'default' => 'Інформація про виробництво уточнюється у компанії.', 'type' => 'textarea'],
                'about_tag_1' => ['label' => 'Тег / перевага 1', 'default' => 'Уточнюється', 'type' => 'text'],
                'about_tag_2' => ['label' => 'Тег / перевага 2', 'default' => 'Уточнюється', 'type' => 'text'],
                'about_tag_3' => ['label' => 'Тег / перевага 3', 'default' => 'Уточнюється', 'type' => 'text'],
                'about_photo_placeholder' => ['label' => 'Підпис до відсутнього фото', 'default' => 'Фото команди або виробництва очікується', 'type' => 'text'],
            ]],
            'portfolio' => ['label' => 'Портфоліо', 'fields' => [
                'works_eyebrow' => ['label' => 'Малий напис', 'default' => 'Портфоліо', 'type' => 'text'],
                'works_title' => ['label' => 'Заголовок', 'default' => 'Роботи говорять краще', 'type' => 'text'],
                'works_description' => ['label' => 'Опис секції', 'default' => 'Реальні роботи компанії з фотографіями та описами.', 'type' => 'textarea'],
                'works_empty' => ['label' => 'Якщо портфоліо порожнє', 'default' => 'Портфоліо готується до публікації.', 'type' => 'text'],
            ]],
            'process' => ['label' => 'Як працюємо', 'fields' => [
                'process_eyebrow' => ['label' => 'Малий напис', 'default' => 'Прозорий процес', 'type' => 'text'],
                'process_title' => ['label' => 'Заголовок', 'default' => 'Від запиту до готової реклами', 'type' => 'text'],
                'process_1_title' => ['label' => 'Етап 1: назва', 'default' => 'Заявка', 'type' => 'text'],
                'process_1_description' => ['label' => 'Етап 1: опис', 'default' => 'Ви описуєте задачу та зручний спосіб зв’язку.', 'type' => 'textarea'],
                'process_2_title' => ['label' => 'Етап 2: назва', 'default' => 'Консультація', 'type' => 'text'],
                'process_2_description' => ['label' => 'Етап 2: опис', 'default' => 'Уточнюємо розміри, матеріали, терміни й бюджет.', 'type' => 'textarea'],
                'process_3_title' => ['label' => 'Етап 3: назва', 'default' => 'Макет і кошторис', 'type' => 'text'],
                'process_3_description' => ['label' => 'Етап 3: опис', 'default' => 'Узгоджуємо рішення та фінальну ціну.', 'type' => 'textarea'],
                'process_4_title' => ['label' => 'Етап 4: назва', 'default' => 'Виробництво', 'type' => 'text'],
                'process_4_description' => ['label' => 'Етап 4: опис', 'default' => 'Виготовляємо й контролюємо якість.', 'type' => 'textarea'],
                'process_5_title' => ['label' => 'Етап 5: назва', 'default' => 'Передача', 'type' => 'text'],
                'process_5_description' => ['label' => 'Етап 5: опис', 'default' => 'Доставка або монтаж — залежно від замовлення.', 'type' => 'textarea'],
            ]],
            'clients' => ['label' => 'Клієнти', 'fields' => [
                'clients_eyebrow' => ['label' => 'Малий напис', 'default' => 'Наші клієнти', 'type' => 'text'],
                'clients_title' => ['label' => 'Заголовок блоку логотипів', 'default' => 'Нам довіряють', 'type' => 'text'],
            ]],
            'faq' => ['label' => 'Запитання та відповіді', 'fields' => [
                'faq_eyebrow' => ['label' => 'Малий напис', 'default' => 'Запитання', 'type' => 'text'],
                'faq_title' => ['label' => 'Заголовок', 'default' => 'Перш ніж замовити', 'type' => 'text'],
                'faq_description' => ['label' => 'Опис угорі', 'default' => 'Умови проєкту, ціни, терміни й матеріали залежать від вашої задачі.', 'type' => 'textarea'],
                'faq_cta' => ['label' => 'Кнопка', 'default' => 'Поставити запитання →', 'type' => 'text'],
                'faq_empty' => ['label' => 'Якщо питань ще немає', 'default' => 'Питання та відповіді готуються. Поки що запитайте менеджера через форму нижче.', 'type' => 'textarea'],
            ]],
            'contact' => ['label' => 'Контакти та форма заявки', 'fields' => [
                'contact_eyebrow' => ['label' => 'Малий напис', 'default' => 'Почнімо з вашої задачі', 'type' => 'text'],
                'contact_title' => ['label' => 'Заголовок', 'default' => 'Розрахуємо ваш проєкт', 'type' => 'text'],
                'contact_description' => ['label' => 'Опис зліва', 'default' => 'Залиште контакт — обговоримо вашу ідею та підберемо рішення.', 'type' => 'textarea'],
                'form_title' => ['label' => 'Заголовок форми', 'default' => 'Обговорімо ваш проєкт', 'type' => 'text'],
                'form_description' => ['label' => 'Опис під заголовком', 'default' => 'Оберіть зручний спосіб зв\'язку.', 'type' => 'text'],
                'form_selected_label' => ['label' => 'Вибрана послуга: назва поля', 'default' => 'Вибрана послуга', 'type' => 'text'],
                'form_selected_hint' => ['label' => 'Вибрана послуга: підпис', 'default' => 'Додамо до заявки менеджеру', 'type' => 'text'],
                'form_channel_legend' => ['label' => 'Питання про канал зв’язку', 'default' => 'Як із вами зв\'язатися?', 'type' => 'text'],
                'form_channel_phone' => ['label' => 'Варіант «Телефон»', 'default' => 'Телефон', 'type' => 'text'],
                'form_channel_telegram' => ['label' => 'Варіант «Telegram»', 'default' => 'Telegram', 'type' => 'text'],
                'form_phone_label' => ['label' => 'Поле телефону', 'default' => 'Номер телефону', 'type' => 'text'],
                'form_telegram_label' => ['label' => 'Поле Telegram', 'default' => 'Telegram @username', 'type' => 'text'],
                'form_phone_placeholder' => ['label' => 'Підказка телефону', 'default' => '+380 XX XXX XX XX', 'type' => 'text'],
                'form_telegram_placeholder' => ['label' => 'Підказка Telegram', 'default' => '@username', 'type' => 'text'],
                'form_comment_label' => ['label' => 'Коментар: підпис', 'default' => 'Що потрібно виготовити?', 'type' => 'text'],
                'form_optional' => ['label' => 'Необов’язкове поле', 'default' => 'необов\'язково', 'type' => 'text'],
                'form_comment_placeholder' => ['label' => 'Коментар: підказка', 'default' => 'Кілька слів про вашу задачу', 'type' => 'text'],
                'form_privacy' => ['label' => 'Текст згоди на обробку персональних даних', 'default' => 'Погоджуюся на обробку моїх контактних даних для відповіді на звернення. Фінальний текст повідомлення про приватність має бути погоджений замовником.', 'type' => 'textarea'],
                'form_cta' => ['label' => 'Кнопка відправлення', 'default' => 'Надіслати запит', 'type' => 'text'],
            ]],
            'location' => ['label' => 'Карта та адреса', 'fields' => [
                'map_eyebrow' => ['label' => 'Малий напис', 'default' => 'Де нас знайти', 'type' => 'text'],
                'map_title' => ['label' => 'Заголовок секції', 'default' => 'Ми в Чернігові', 'type' => 'text'],
                'map_description' => ['label' => 'Опис', 'default' => 'Адреса виробництва та місце, де можна нас знайти.', 'type' => 'textarea'],
                'map_directions' => ['label' => 'Кнопка маршруту', 'default' => 'Прокласти маршрут', 'type' => 'text'],
                'map_load' => ['label' => 'Кнопка карти', 'default' => 'Показати карту', 'type' => 'text'],
                'map_note' => ['label' => 'Примітка під адресою', 'default' => 'Перед візитом радимо узгодити час із менеджером.', 'type' => 'text'],
            ]],
            'footer' => ['label' => 'Нижня частина сайту', 'fields' => [
                'footer_location' => ['label' => 'Місто', 'default' => 'Чернігів', 'type' => 'text'],
                'footer_contacts' => ['label' => 'Посилання до контактів', 'default' => 'Контакти', 'type' => 'text'],
            ]],
            'seo' => ['label' => 'SEO', 'fields' => [
                'seo_title' => ['label' => 'SEO title головної сторінки', 'default' => 'Вивіски, лайтбокси та рекламна продукція у Чернігові | Кухня реклами', 'type' => 'text'],
                'meta_description' => ['label' => 'Meta description головної сторінки', 'default' => 'Кухня реклами — виготовлення вивісок, об’ємних літер, лайтбоксів, широкоформатний друк і брендування у Чернігові. Залиште заявку на розрахунок.', 'type' => 'textarea'],
                'seo_og_alt' => ['label' => 'Опис фото для соцмереж', 'default' => 'Рекламне виробництво Кухня реклами', 'type' => 'text'],
            ]],
        ];
    }

    public static function fields(): array {
        $fields = [];
        foreach (self::sections() as $section) {
            foreach ($section['fields'] as $key => $definition) $fields[$key] = $definition;
        }
        return $fields;
    }

    public static function get(string $key, string $fallback = ''): string {
        $fields = self::fields();
        if (!isset($fields[$key])) return $fallback;
        $options = get_option(self::OPTION, []);
        if (!is_array($options) || !array_key_exists($key, $options)) return $fallback !== '' ? $fallback : $fields[$key]['default'];
        return (string)$options[$key];
    }

    public static function sanitize($input): array {
        $previous = get_option(self::OPTION, []);
        if (!is_array($previous)) $previous = [];
        if (!is_array($input)) return $previous;
        $out = $previous;
        foreach (self::fields() as $key => $definition) {
            if (!array_key_exists($key, $input) || !is_scalar($input[$key])) continue;
            $text = wp_unslash((string)$input[$key]);
            $text = $definition['type'] === 'checkbox' ? ($text === '1' ? '1' : '0') : ($definition['type'] === 'textarea' ? sanitize_textarea_field($text) : sanitize_text_field($text));
            $out[$key] = $text;
        }
        return $out;
    }

    public static function boot(): void {
        add_action('admin_menu', [__CLASS__, 'menu']);
        add_action('admin_init', [__CLASS__, 'register']);
    }

    public static function menu(): void {
        add_submenu_page('ukr-homepage', 'Тексти сайту', 'Тексти сайту', UKR_Access::EDIT, self::SLUG, [__CLASS__, 'page']);
    }

    public static function register(): void {
        register_setting('ukr_site_content_group', self::OPTION, [
            'type' => 'array', 'sanitize_callback' => [__CLASS__, 'sanitize'], 'default' => [],
        ]);
    }

    public static function page(): void {
        if (!current_user_can(UKR_Access::EDIT)) wp_die('Недостатньо прав.');
        $values = get_option(self::OPTION, []);
        if (!is_array($values)) $values = [];
        ?>
        <div class="wrap ukr-content-admin">
            <h1>Тексти головної сторінки</h1>
            <p>Редагуйте заголовки, описи та підписи кнопок. <strong>Очистіть поле «Малий напис» і збережіть — напис повністю зникне із сайту.</strong> Послуги й портфоліо змінюються у власних розділах WordPress, а фотографія Hero — у «UKreklamy → Головна сторінка».</p>
            <p><strong>Важливо:</strong> не публікуйте непідтверджені ціни, результати, обіцянки чи юридичні тексти. Поле згоди на обробку даних має бути узгоджене із замовником.</p>
            <?php settings_errors(); ?>
            <form action="options.php" method="post">
              <?php settings_fields('ukr_site_content_group'); ?>
              <div class="ukr-section-menu" aria-label="Перейти до секції">
              <?php foreach (self::sections() as $slug => $section): ?>
                <a class="button button-secondary" href="#ukr-section-<?php echo esc_attr($slug); ?>"><?php echo esc_html($section['label']); ?></a>
              <?php endforeach; ?>
              </div>
              <?php foreach (self::sections() as $slug => $section): ?>
                <details id="ukr-section-<?php echo esc_attr($slug); ?>" class="ukr-content-section" <?php echo $slug === 'header' ? 'open' : ''; ?>>
                  <summary><?php echo esc_html($section['label']); ?></summary>
                  <table class="form-table" role="presentation"><tbody>
                  <?php foreach ($section['fields'] as $key => $definition):
                    $value = array_key_exists($key, $values) ? (string)$values[$key] : $definition['default'];
                    $id = 'ukr-copy-' . $key;
                  ?>
                    <tr><th scope="row"><label for="<?php echo esc_attr($id); ?>"><?php echo esc_html($definition['label']); ?></label></th>
                      <td><?php if ($definition['type'] === 'checkbox'): ?>
                        <input type="hidden" name="<?php echo esc_attr(self::OPTION); ?>[<?php echo esc_attr($key); ?>]" value="0">
                        <label><input type="checkbox" id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr(self::OPTION); ?>[<?php echo esc_attr($key); ?>]" value="1" <?php checked($value, '1'); ?>> Увімкнено</label>
                        <?php elseif ($definition['type'] === 'textarea'): ?>
                        <textarea id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr(self::OPTION); ?>[<?php echo esc_attr($key); ?>]" class="large-text" rows="3" maxlength="2500"><?php echo esc_textarea($value); ?></textarea>
                        <?php else: ?>
                        <input id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr(self::OPTION); ?>[<?php echo esc_attr($key); ?>]" type="text" class="regular-text" maxlength="300" value="<?php echo esc_attr($value); ?>">
                        <?php endif; ?></td></tr>
                  <?php endforeach; ?>
                  </tbody></table>
                  <?php if ($slug === 'seo'): ?>
                    <div class="ukr-seo-help" style="padding:14px;background:#f5f7fa;border:1px solid #dde2e6;border-radius:6px">
                      <strong>Попередній перегляд пошукового результату</strong>
                      <p id="ukr-seo-preview-title" style="font-size:19px;color:#1558b0;margin:12px 0 0"></p>
                      <p style="color:#3d784c;margin:0"><?php echo esc_html(home_url('/')); ?></p>
                      <p id="ukr-seo-preview-description" style="font-size:13px;margin:3px 0 0;max-width:620px"></p>
                      <p class="description">Рекомендована довжина: заголовок орієнтовно 50–60 символів, опис орієнтовно 120–160. Це не жорсткий ліміт: Google може показувати інший фрагмент, сформований зі сторінки. Публікуйте лише підтверджені факти.</p>
                    </div>
                  <?php endif; ?>
                </details>
              <?php endforeach; ?>
              <?php submit_button('Зберегти тексти'); ?>
            </form>
        </div>
        <style>
            .ukr-content-admin{max-width:1120px}.ukr-content-admin .ukr-section-menu{display:flex;gap:8px;flex-wrap:wrap;margin:18px 0 24px}.ukr-content-admin .ukr-content-section{background:#fff;border:1px solid #c3c4c7;border-radius:8px;padding:8px 20px 14px;margin:0 0 22px;scroll-margin-top:55px}.ukr-content-admin .ukr-content-section summary{cursor:pointer;font-size:20px;font-weight:600;padding:16px 0}.ukr-content-admin .ukr-content-section[open] summary{border-bottom:1px solid #eee}.ukr-content-admin .form-table th{width:250px}.ukr-content-admin .form-table td input{width:min(100%,600px)}.ukr-content-admin textarea{max-width:760px}.ukr-content-admin .submit{position:sticky;bottom:0;background:#f0f0f1;padding:14px 0;border-top:1px solid #ddd;z-index:2}.ukr-content-admin .submit input{margin:0}
        </style>
        <script>
        document.querySelectorAll('.ukr-content-admin .ukr-section-menu a').forEach(function(link) {
          link.addEventListener('click', function () {
            var section = document.getElementById(link.getAttribute('href').slice(1));
            if (section) section.open = true;
          });
        });
        document.addEventListener('DOMContentLoaded', function () {
          const title = document.getElementById('ukr-copy-seo_title');
          const description = document.getElementById('ukr-copy-meta_description');
          if (!title || !description) return;
          const previewTitle = document.getElementById('ukr-seo-preview-title');
          const previewDescription = document.getElementById('ukr-seo-preview-description');
          const render = function () {
            previewTitle.textContent = title.value + ' (' + Array.from(title.value).length + ' знаків)';
            previewDescription.textContent = description.value + ' (' + Array.from(description.value).length + ' знаків)';
          };
          title.addEventListener('input', render);
          description.addEventListener('input', render);
          render();
        });
        </script>
        <?php
    }
}
UKR_Site_Content::boot();
