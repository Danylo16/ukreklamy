/* UKreklamy 1.11 — scroll-direction header, controlled visual depth, opt-in map. */
(() => {
  'use strict';
  const header = document.querySelector('.ukr-smart-header');
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
  const desktop = window.matchMedia('(min-width: 801px)');
  const photos = [];
  const hero = document.querySelector('.ukr-hero-v17');
  const heroPhoto = hero?.querySelector('.heroart.has-image .ukr-hero-photo');
  document.querySelectorAll('.ukr-category .visual.has-image img, .workvisual img').forEach(image => {
    const container = image.closest('.ukr-category, .work');
    if (container) photos.push({image, container});
  });
  const depths = [
    {selector: '.about', near: 145, far: -76},
    {selector: '.process', near: 190, far: -113},
    {selector: '.contact', near: 185, far: -106}
  ].map(item => ({...item, element: document.querySelector(item.selector)})).filter(x => x.element);
  depths.forEach(item => item.element.classList.add('ukr-depth-section'));

  let keyboardNavigation = false;
  window.addEventListener('keydown', (event) => { if (['Tab','ArrowUp','ArrowDown'].includes(event.key)) keyboardNavigation = true; });
  window.addEventListener('pointerdown', () => { keyboardNavigation = false; }, {passive: true});
  let lastY = Math.max(0, window.scrollY);
  let raf = 0;
  const clamp = (value, min, max) => Math.max(min, Math.min(max, value));
  const active = () => desktop.matches && !reduced.matches && !document.hidden;
  function resetMotion() {
    heroPhoto?.style.removeProperty('--ukr-v111-hero-shift');
    photos.forEach(({image}) => image.style.removeProperty('--ukr-v111-card-shift'));
    depths.forEach(({element}) => {
      element.style.removeProperty('--ukr-v111-near');
      element.style.removeProperty('--ukr-v111-far');
    });
  }
  function paint() {
    raf = 0;
    const y = Math.max(0, window.scrollY);
    if (header) {
      const delta = y - lastY;
      const navOpen = header.querySelector('.menubtn[aria-expanded="true"]');
      const hasFocus = keyboardNavigation && header.contains(document.activeElement);
      header.classList.toggle('ukr-scrolled', y > 28);
      // Dead-zone prevents flickering on wheel/touch micro-gestures.
      if (y < 112 || navOpen || hasFocus) header.classList.remove('ukr-hidden');
      else if (delta > 6) header.classList.add('ukr-hidden');
      else if (delta < -6) header.classList.remove('ukr-hidden');
    }
    lastY = y;
    if (!active()) { resetMotion(); return; }
    const viewport = window.innerHeight || document.documentElement.clientHeight;
    if (heroPhoto && hero) {
      const rect = hero.getBoundingClientRect();
      if (rect.bottom > -80 && rect.top < viewport + 80) {
        const distance = clamp(-rect.top, 0, Math.max(1, rect.height));
        const pixels = 150 * (distance / Math.max(1, rect.height));
        heroPhoto.style.setProperty('--ukr-v111-hero-shift', `${pixels.toFixed(1)}px`);
      }
    }
    for (const {image, container} of photos) {
      const rect = container.getBoundingClientRect();
      if (rect.bottom < -80 || rect.top > viewport + 80) continue;
      const progress = clamp((viewport * .5 - rect.top - rect.height * .5) / Math.max(1, viewport * .5 + rect.height * .5), -1, 1);
      image.style.setProperty('--ukr-v111-card-shift', `${(progress * 32).toFixed(1)}px`);
    }
    for (const {element, near, far} of depths) {
      const rect = element.getBoundingClientRect();
      if (rect.bottom < -180 || rect.top > viewport + 180) continue;
      const progress = clamp((viewport * .5 - rect.top - rect.height * .5) / Math.max(1, viewport * .5 + rect.height * .5), -1, 1);
      element.style.setProperty('--ukr-v111-near', `${(progress * near).toFixed(1)}px`);
      element.style.setProperty('--ukr-v111-far', `${(progress * far).toFixed(1)}px`);
    }
  }
  const schedule = () => { if (!raf) raf = requestAnimationFrame(paint); };
  window.addEventListener('scroll', schedule, {passive:true});
  window.addEventListener('resize', schedule, {passive:true});
  document.addEventListener('visibilitychange', schedule);
  [reduced, desktop].forEach(query => query.addEventListener?.('change', schedule));
  header?.addEventListener('focusin', () => header.classList.remove('ukr-hidden'));
  // Allow clicking anchor links without causing hidden header to trap focus.
  header?.querySelectorAll('a').forEach(link => link.addEventListener('click', () => {
    header.classList.remove('ukr-hidden');
  }));

  document.querySelectorAll('.ukr-map-shell[data-ukr-map-url]').forEach(shell => {
    const button = shell.querySelector('.ukr-map-load');
    if (!button) return;
    button.addEventListener('click', () => {
      const url = shell.getAttribute('data-ukr-map-url') || '';
      if (!/^https:\/\/maps\.google\.com\/maps\?/.test(url)) return;
      const iframe = document.createElement('iframe');
      iframe.title = shell.getAttribute('aria-label') || 'Карта розташування';
      iframe.src = url;
      iframe.loading = 'lazy';
      iframe.referrerPolicy = 'no-referrer-when-downgrade';
      iframe.allowFullscreen = true;
      shell.replaceChildren(iframe);
    }, {once:true});
  });
  schedule();
})();
