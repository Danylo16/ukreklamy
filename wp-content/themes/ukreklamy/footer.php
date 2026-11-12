</main>
<footer class="foot ukr-footer-polished" role="contentinfo">
  <div class="wrap ukr-footer-main">
    <div class="ukr-footer-brand">
      <a class="ukr-footer-logo" href="<?php echo esc_url(home_url('/')); ?>">КУХНЯ РЕКЛАМИ</a>
      <p>Рекламне виробництво у Чернігові.<br>Від ідеї до готового результату.</p>
    </div>
    <nav class="ukr-footer-nav" aria-label="Навігація в підвалі">
      <strong>Навігація</strong>
      <a href="#services">Послуги</a><a href="#works">Наші роботи</a><a href="#faq">Запитання та відповіді</a>
    </nav>
    <div class="ukr-footer-contact">
      <strong>Контакти</strong>
      <?php if(ukr_contact('address')): ?><span><?php echo esc_html(ukr_contact('address')); ?></span><?php endif; ?>
      <a href="#contact">Зв'язатися з нами <span aria-hidden="true">↗</span></a>
    </div>
  </div>
  <div class="ukr-footer-meta">
    <div class="wrap ukr-footer-meta-inner">
      <span>© <?php echo esc_html(wp_date('Y')); ?> Кухня реклами. Усі права захищено.</span>
      <span class="ukr-developer-credit">Розробка сайту — <a href="https://www.linkedin.com/in/danylo-zubrii-bb0758299/?locale=uk" target="_blank" rel="noopener noreferrer">Danylo Zubrii <span aria-hidden="true">↗</span></a></span>
    </div>
  </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
