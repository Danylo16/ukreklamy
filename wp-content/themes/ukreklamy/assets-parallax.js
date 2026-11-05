/* UKreklamy v1.7: selective, scroll-driven depth (no dependencies). */
(() => {
  'use strict';
  if (!window.matchMedia || !window.requestAnimationFrame) return;

  const desktop = window.matchMedia('(min-width: 801px)');
  const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
  const targets = [];
  const add = (anchor, layer, distance) => {
    if (anchor && layer) targets.push({anchor, layer, distance, visible: true});
  };

  const hero = document.querySelector('.hero');
  add(hero, hero?.querySelector('.heroart.has-image .ukr-hero-photo'), 100);

  document.querySelectorAll('.ukr-category .visual.has-image img').forEach(img => {
    add(img.closest('.ukr-category'), img, 32);
  });

  document.querySelectorAll('.worktrack .workvisual img').forEach(img => {
    add(img.closest('.work'), img, 42);
  });

  const process = document.querySelector('.process');
  add(process, process, 55);
  if (!targets.length) return;

  let frame = 0;
  const enabled = () => desktop.matches && !motion.matches && !document.hidden;
  const reset = () => {
    targets.forEach(t => t.layer.style.removeProperty('--ukr-parallax-offset'));
  };
  const paint = () => {
    frame = 0;
    if (!enabled()) { reset(); return; }
    const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
    const halfViewport = viewportHeight / 2;
    for (const target of targets) {
      if (!target.visible) continue;
      const rect = target.anchor.getBoundingClientRect();
      // -1 when entering at bottom, +1 when leaving at top; hard bounded.
      const denominator = Math.max(1, halfViewport + rect.height / 2);
      const progress = Math.max(-1, Math.min(1,
        (halfViewport - rect.top - rect.height / 2) / denominator));
      const pixels = progress * target.distance;
      target.layer.style.setProperty('--ukr-parallax-offset', `${pixels.toFixed(2)}px`);
    }
  };
  const request = () => {
    if (!frame) frame = window.requestAnimationFrame(paint);
  };
  const onSettingsChange = () => {
    if (enabled()) request();
    else { if (frame) window.cancelAnimationFrame(frame); frame = 0; reset(); }
  };

  // Skip offscreen work. Without IntersectionObserver, correct behavior remains.
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(entries => {
      for (const entry of entries) {
        const target = targets.find(t => t.anchor === entry.target);
        if (target) target.visible = entry.isIntersecting;
      }
      request();
    }, {rootMargin: '180px 0px'});
    targets.forEach(t => observer.observe(t.anchor));
  }

  window.addEventListener('scroll', request, {passive: true});
  window.addEventListener('resize', request, {passive: true});
  document.addEventListener('visibilitychange', onSettingsChange);
  for (const query of [desktop, motion]) {
    if (query.addEventListener) query.addEventListener('change', onSettingsChange);
    else if (query.addListener) query.addListener(onSettingsChange);
  }
  request();
})();
