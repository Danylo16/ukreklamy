/** FAQ v1.12.1: accessible accordion with measured-height animation and unlimited answer length. */
(() => {
  'use strict';
  const root = document.querySelector('.ukr-faq');
  if (!root) return;
  const items = [...root.querySelectorAll('.ukr-faq-item')].map(row => ({
    trigger: row.querySelector('.ukr-faq-trigger'),
    shell: row.querySelector('.ukr-faq-answer-shell')
  })).filter(item => item.trigger && item.shell);
  if (!items.length) return;
  const reduced = window.matchMedia('(prefers-reduced-motion:reduce)');
  for (const item of items) {
    item.trigger.setAttribute('aria-expanded','false');
    item.shell.setAttribute('aria-hidden','true');
    item.shell.inert = true;
    item.shell.style.height = '0px';
  }
  root.classList.add('ukr-faq-enhanced');
  function setOpen(item, open) {
    const shell = item.shell;
    const wasOpen = item.trigger.getAttribute('aria-expanded') === 'true';
    if (open === wasOpen) return;
    item.trigger.setAttribute('aria-expanded',String(open));
    shell.setAttribute('aria-hidden',String(!open));
    shell.inert = !open;
    // From the current visible height, including during a rapid double click.
    const currentHeight = shell.getBoundingClientRect().height;
    shell.style.height = `${currentHeight}px`;
    void shell.offsetHeight;
    if (reduced.matches) {
      shell.style.height = open ? 'auto' : '0px';
    } else {
      shell.style.height = open ? `${shell.scrollHeight}px` : '0px';
    }
  }
  for (const item of items) {
    item.trigger.addEventListener('click', () => {
      const shouldOpen = item.trigger.getAttribute('aria-expanded') !== 'true';
      items.forEach(other => setOpen(other, shouldOpen && other === item));
    });
    item.shell.addEventListener('transitionend', event => {
      if (event.target !== item.shell || event.propertyName !== 'height') return;
      if (item.trigger.getAttribute('aria-expanded') === 'true') item.shell.style.height = 'auto';
    });
  }
  reduced.addEventListener?.('change', () => {
    items.forEach(item => {
      item.shell.style.height = item.trigger.getAttribute('aria-expanded') === 'true' ? 'auto' : '0px';
    });
  });
})();
