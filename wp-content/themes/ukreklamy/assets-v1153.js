/* UKreklamy v1.15.3 — reveal extra service categories without concealing content when JS fails. */
(() => {
  'use strict';
  function init() {
    const section = document.getElementById('services');
    const wrapper = document.getElementById('ukr-services-more-wrap');
    const button = document.getElementById('ukr-services-more');
    if (!section || !wrapper || !button) return;
    const items = Array.from(section.querySelectorAll('.servicegrid > .ukr-category--extra'));
    if (!items.length) return;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    // Make hiding conditional on this script actually initializing successfully.
    section.classList.add('ukr-catalog-ready');
    wrapper.hidden = false;
    button.addEventListener('click', () => {
      if (button.disabled) return;
      button.disabled = true;
      items.forEach((item, i) => {
        item.classList.add('is-visible');
        if (!reducedMotion.matches && typeof item.animate === 'function') {
          item.animate([
            { opacity: 0, transform: 'translateY(22px) scale(.975)' },
            { opacity: 1, transform: 'translateY(0) scale(1)' }
          ], { duration: 440, delay: Math.min(i, 8) * 65, easing: 'cubic-bezier(.22,1,.36,1)', fill: 'both' });
        }
      });
      button.setAttribute('aria-expanded', 'true');
      wrapper.hidden = true;
    }, { once: true });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once: true });
  else init();
})();
