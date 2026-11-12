<section class="section contact" id="contact" aria-labelledby="ukr-contact-heading">
  <div class="wrap contactgrid">
    <div class="ukr-contact-copy">
      <?php echo ukr_optional_eyebrow('contact_eyebrow', 'Почнімо з вашої задачі'); ?>
      <h2 id="ukr-contact-heading"><?php echo esc_html(ukr_copy('contact_title', 'Розрахуємо ваш проєкт')); ?></h2>
      <p><?php echo nl2br(esc_html(ukr_copy('contact_description', 'Залиште контакт — обговоримо вашу ідею та підберемо рішення.'))); ?></p>
      <nav class="contactlinks ukr-contact-custom-links" aria-label="Способи зв’язку">
        <?php $ukr_links = class_exists('UKR_Contact_Links') ? UKR_Contact_Links::links() : []; ?>
        <?php foreach ($ukr_links as $link):
          $url = (string) $link['url'];
          $label = (string) $link['label'];
          $kind = UKR_Contact_Links::type($link);
          $social = UKR_Contact_Links::is_social($kind);
          $types = UKR_Contact_Links::types();
          $accessible = $label ?: ($types[$kind] ?? 'Посилання');
          $visible = $label;
          $compare = function_exists('mb_strtolower') ? mb_strtolower(trim($label)) : strtolower(trim($label));
          if ($kind === 'phone' && in_array($compare, ['телефон', 'Телефон', 'phone'], true)) $visible = ukr_contact('phone') ?: preg_replace('~^tel:~i','',$url);
          if ($kind === 'email' && in_array($compare, ['email','пошта','Пошта'],true)) $visible = preg_replace('~^mailto:~i','',$url);
        ?>
          <a class="ukr-contact-link ukr-contact-link--<?php echo esc_attr($kind); ?><?php echo $social ? ' ukr-contact-link--social' : ''; ?>" href="<?php echo esc_url($url); ?>"<?php echo preg_match('~^https?://~i',$url) ? ' target="_blank" rel="noopener noreferrer"' : ''; ?><?php if($social): ?> aria-label="<?php echo esc_attr($accessible); ?>" title="<?php echo esc_attr($accessible); ?>"<?php endif; ?>>
            <?php echo UKR_Contact_Links::icon_svg($kind); // SVG from server-side allowlist. ?>
            <?php if (!$social): ?><span><?php echo esc_html($visible); ?></span><?php endif; ?>
          </a>
        <?php endforeach; ?>
      </nav>
      <?php if(ukr_contact('address')): ?><p class="ukr-contact-address"><?php echo esc_html(ukr_contact('address')); ?></p><?php endif; ?>
    </div>
    <form class="form ukr-lead-form" id="lead-form" novalidate>
      <div class="ukr-form-heading"><h3><?php echo esc_html(ukr_copy('form_title', 'Обговорімо ваш проєкт')); ?></h3><p><?php echo esc_html(ukr_copy('form_description', 'Оберіть зручний спосіб зв\'язку.')); ?></p></div>
      <input type="hidden" name="service_id" value="0">
      <div class="ukr-selection" id="ukr-selected-service" hidden>
        <div><span class="ukr-selection-label"><?php echo esc_html(ukr_copy('form_selected_label', 'Вибрана послуга')); ?></span><strong id="ukr-selected-service-name"></strong><span class="ukr-selection-hint"><?php echo esc_html(ukr_copy('form_selected_hint', 'Додамо до заявки менеджеру')); ?></span></div>
        <button type="button" class="ukr-selection-clear" aria-label="Прибрати вибрану послугу">×</button>
      </div>
      <fieldset class="ukr-channel-field"><legend><?php echo esc_html(ukr_copy('form_channel_legend', 'Як із вами зв\'язатися?')); ?></legend>
        <div class="ukr-channel-switch">
          <label class="ukr-channel-option"><input type="radio" name="channel" value="phone" checked><span><svg width="17" height="17" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 16.9v3a2 2 0 0 1-2.2 2A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7l.5 2.9a2 2 0 0 1-.6 1.8L7.4 10a16 16 0 0 0 6.6 6.6l1.6-1.6a2 2 0 0 1 1.8-.6l2.9.5a2 2 0 0 1 1.7 2Z"/></svg><?php echo esc_html(ukr_copy('form_channel_phone', 'Телефон')); ?></span></label>
          <label class="ukr-channel-option"><input type="radio" name="channel" value="telegram"><span><svg width="18" height="18" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m22 2-7 20-4-8-8-4L22 2ZM11 14l11-12"/></svg><?php echo esc_html(ukr_copy('form_channel_telegram', 'Telegram')); ?></span></label>
        </div>
      </fieldset>
      <div class="ukr-form-field"><label for="lead-contact" id="ukr-contact-label" data-phone-label="<?php echo esc_attr(ukr_copy('form_phone_label', 'Номер телефону')); ?>" data-telegram-label="<?php echo esc_attr(ukr_copy('form_telegram_label', 'Telegram @username')); ?>"><?php echo esc_html(ukr_copy('form_phone_label', 'Номер телефону')); ?> <span aria-hidden="true">*</span></label><input id="lead-contact" name="contact" autocomplete="tel" required maxlength="120" placeholder="<?php echo esc_attr(ukr_copy('form_phone_placeholder', '+380 XX XXX XX XX')); ?>" data-phone-placeholder="<?php echo esc_attr(ukr_copy('form_phone_placeholder', '+380 XX XXX XX XX')); ?>" data-telegram-placeholder="<?php echo esc_attr(ukr_copy('form_telegram_placeholder', '@username')); ?>" type="tel"></div>
      <div class="ukr-form-field"><label for="lead-comment"><?php echo esc_html(ukr_copy('form_comment_label', 'Що потрібно виготовити?')); ?> <span class="ukr-optional"><?php echo esc_html(ukr_copy('form_optional', 'необов\'язково')); ?></span></label><textarea id="lead-comment" name="comment" rows="2" maxlength="1500" placeholder="<?php echo esc_attr(ukr_copy('form_comment_placeholder', 'Кілька слів про вашу задачу')); ?>"></textarea></div>
      <div class="trap" aria-hidden="true"><label for="lead-website">Не заповнюйте це поле</label><input type="text" id="lead-website" name="website" tabindex="-1" autocomplete="off"></div>
      <label class="privacy ukr-privacy"><input type="checkbox" name="privacy" required><span><?php echo nl2br(esc_html(ukr_copy('form_privacy', 'Погоджуюся на обробку моїх контактних даних для відповіді на звернення. Фінальний текст повідомлення про приватність має бути погоджений замовником.'))); ?></span></label>
      <input type="hidden" name="started" value="<?php echo esc_attr(time()); ?>">
      <button class="btn ukr-lead-submit" type="submit"><?php echo esc_html(ukr_copy('form_cta', 'Надіслати запит')); ?> <span aria-hidden="true">→</span></button>
      <p id="form-status" class="form-status" aria-live="polite"></p>
    </form>
  </div>
</section>