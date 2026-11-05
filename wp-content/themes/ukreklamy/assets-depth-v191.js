/* UKreklamy 1.9.1 — independent two-plane decorative parallax. */
(() => {
  'use strict';
  if (!window.matchMedia || !window.requestAnimationFrame) return;

  const desktop = matchMedia('(min-width: 801px)');
  const reduced = matchMedia('(prefers-reduced-motion: reduce)');
  const entries = [
    {selector: '.about', near: 128, far: -75},
    {selector: '.process', near: 185, far: -105},
    {selector: '.contact', near: 190, far: -115}
  ].map(({selector, near, far}) => ({element: document.querySelector(selector), near, far}))
    .filter(item => item.element);

  for (const entry of entries) entry.element.classList.add('ukr-depth-section');
  if (!entries.length) return;

  let frame = 0;
  const enabled = () => desktop.matches && !reduced.matches && !document.hidden;
  const clear = () => {
    for (const {element} of entries) {
      element.style.removeProperty('--ukr-depth-near');
      element.style.removeProperty('--ukr-depth-far');
    }
  };
  const paint = () => {
    frame = 0;
    if (!enabled()) { clear(); return; }
    const h = window.innerHeight || document.documentElement.clientHeight;
    for (const {element, near, far} of entries) {
      const bounds = element.getBoundingClientRect();
      // Skip out-of-viewport work: visuals freeze once out of sight.
      if (bounds.bottom < -160 || bounds.top > h + 160) continue;
      // Progress -1 entering from bottom to +1 leaving at top.
      const normalized = Math.max(-1, Math.min(1,
        (h * .5 - bounds.top - bounds.height * .5) / Math.max(1, (h + bounds.height) * .5)));
      element.style.setProperty('--ukr-depth-near', `${(normalized * near).toFixed(1)}px`);
      element.style.setProperty('--ukr-depth-far', `${(normalized * far).toFixed(1)}px`);
    }
  };
  const request = () => { if (!frame) frame = requestAnimationFrame(paint); };
  const settings = () => {
    if (!enabled()) {
      if (frame) cancelAnimationFrame(frame);
      frame = 0;
      clear();
    } else request();
  };
  window.addEventListener('scroll', request, {passive: true});
  window.addEventListener('resize', request, {passive: true});
  document.addEventListener('visibilitychange', settings);
  for (const mq of [desktop, reduced]) {
    if (mq.addEventListener) mq.addEventListener('change', settings);
    else if (mq.addListener) mq.addListener(settings);
  }
  settings();
})();
