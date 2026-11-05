/** v1.9 color-section decorative parallax: visible depth without moving content. */
(() => {
  'use strict';
  if (!('requestAnimationFrame' in window) || !window.matchMedia) return;
  const width = window.matchMedia('(min-width: 801px)');
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
  const sections = [
    ['.hero', 95], ['.about', 110], ['.process', 135], ['.contact', 140]
  ].map(([selector, range]) => ({node: document.querySelector(selector), range, visible: true}))
    .filter(item => item.node);
  sections.forEach(item => item.node.classList.add('ukr-color-depth'));
  if (!sections.length) return;
  let raf = 0;
  const enabled = () => width.matches && !reduced.matches && !document.hidden;
  const clear = () => sections.forEach(item => item.node.style.removeProperty('--ukr-color-depth'));
  const paint = () => {
    raf = 0;
    if (!enabled()) { clear(); return; }
    const middle = window.innerHeight * 0.5;
    sections.forEach(({node, range, visible}) => {
      if (!visible) return;
      const rect = node.getBoundingClientRect();
      const offset = (middle - rect.top - rect.height / 2) / Math.max(1, middle + rect.height / 2);
      const bounded = Math.max(-1, Math.min(1, offset));
      node.style.setProperty('--ukr-color-depth', `${(bounded*range).toFixed(1)}px`);
    });
  };
  const request = () => { if (!raf) raf = requestAnimationFrame(paint); };
  const settings = () => { if (!enabled()) { if (raf) cancelAnimationFrame(raf); raf = 0; clear(); } else request(); };
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        const target = sections.find(item => item.node === entry.target);
        if (target) target.visible = entry.isIntersecting;
      });
      request();
    }, {rootMargin:'200px 0px'});
    sections.forEach(item => observer.observe(item.node));
  }
  window.addEventListener('scroll', request, {passive:true});
  window.addEventListener('resize', request, {passive:true});
  document.addEventListener('visibilitychange', settings);
  [width,reduced].forEach(query => {
    if (query.addEventListener) query.addEventListener('change', settings);
    else if (query.addListener) query.addListener(settings);
  });
  request();
})();
