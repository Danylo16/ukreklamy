/* UKreklamy v1.13 — no dependencies: side controls, accessible portfolio lightbox. */
(() => {
  'use strict';
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)');
  const behavior = () => reduce.matches ? 'instant' : 'smooth';
  function setupCarousel(root, trackSel, itemSel, prevSel, nextSel, counterSel) {
    if (!root) return;
    const track = root.querySelector(trackSel);
    const items = [...(track?.querySelectorAll(itemSel) || [])];
    const prev = root.querySelector(prevSel);
    const next = root.querySelector(nextSel);
    const counter = root.querySelector(counterSel);
    if (!track || !items.length) return;
    const coords = () => {
      const {left} = track.getBoundingClientRect();
      const pos = items.map(el => el.getBoundingClientRect().left - left + track.scrollLeft);
      return pos;
    };
    const position = () => {
      const points = coords();
      let idx = 0;
      for (let i = 0; i < points.length; i++) {
        if (points[i] <= track.scrollLeft + 6) idx = i;
      }
      return idx;
    };
    let scheduled = 0;
    const refresh = () => {
      scheduled = 0;
      const noOverflow = track.scrollWidth <= track.clientWidth + 3;
      root.classList.toggle('is-static', noOverflow);
      const idx = position();
      if (prev) prev.disabled = noOverflow || track.scrollLeft <= 3;
      if (next) next.disabled = noOverflow || track.scrollLeft + track.clientWidth >= track.scrollWidth - 3;
      if (counter) counter.textContent = `${idx+1} / ${items.length}`;
    };
    const schedule = () => { if (!scheduled) scheduled = requestAnimationFrame(refresh); };
    const go = dir => {
      const points = coords();
      const index = position();
      const target = Math.max(0, Math.min(items.length - 1, index + dir));
      track.scrollTo({left:points[target],behavior:behavior()});
    };
    prev?.addEventListener('click', () => go(-1));
    next?.addEventListener('click', () => go(1));
    track.addEventListener('keydown', e => {
      if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
        e.preventDefault(); go(e.key === 'ArrowRight' ? 1 : -1);
      }
    });
    track.addEventListener('scroll', schedule, {passive:true});
    window.addEventListener('resize', schedule, {passive:true});
    const observer = window.ResizeObserver ? new ResizeObserver(schedule) : null;
    observer?.observe(track);
    track.querySelectorAll('img').forEach(img => img.addEventListener('load', schedule, {once:true}));
    refresh();
    return () => { window.removeEventListener('resize', schedule); observer?.disconnect(); if (scheduled) cancelAnimationFrame(scheduled); };
  }
  // Clients already have handlers in assets-v19.js; only update no-overflow layout.
  const clients = document.querySelector('[data-ukr-carousel="clients"]');
  if (clients) {
    const track = clients.querySelector('.ukr-clients-track');
    const update = () => clients.classList.toggle('is-static', !!track && track.scrollWidth <= track.clientWidth + 3);
    update();
    window.addEventListener('resize', update, {passive:true});
    if (window.ResizeObserver && track) new ResizeObserver(update).observe(track);
  }
  setupCarousel(document.querySelector('[data-ukr-carousel="works"]'), '.worktrack', '.ukr-work-card', '.ukr-works-prev', '.ukr-works-next', '.ukr-works-count');

  const dialog = document.getElementById('ukr-work-dialog');
  const content = document.getElementById('ukr-work-dialog-content');
  const close = dialog?.querySelector('.ukr-work-dialog-close');
  if (!dialog || !content || !close || typeof dialog.showModal !== 'function') return;
  let opener = null;
  let previousOverflow = '';
  let scrollLocked = false;
  let disconnectDialogCarousel = null;
  function exit() {
    if (dialog.open) dialog.close();
    if (scrollLocked) {
      document.body.style.overflow = previousOverflow;
      scrollLocked = false;
    }
    disconnectDialogCarousel?.();
    disconnectDialogCarousel = null;
    content.replaceChildren();
    if (opener?.isConnected) opener.focus({preventScroll:true});
    opener = null;
  }
  close.addEventListener('click', exit);
  dialog.addEventListener('cancel', e => { e.preventDefault(); exit(); });
  dialog.addEventListener('click', e => { if (e.target === dialog) exit(); });
  dialog.addEventListener('close', () => { if (scrollLocked) exit(); });
  document.querySelectorAll('.ukr-work-open').forEach(button => {
    button.addEventListener('click', () => {
      const id = button.dataset.ukrWork;
      if (!/^\d+$/.test(id || '')) return;
      const template = document.getElementById(`ukr-work-${id}`);
      if (!(template instanceof HTMLTemplateElement)) return;
      opener = button;
      content.replaceChildren(template.content.cloneNode(true));
      previousOverflow = document.body.style.overflow;
      document.body.style.overflow = 'hidden';
      scrollLocked = true;
      try { dialog.showModal(); } catch (_) {
        document.body.style.overflow = previousOverflow;
        scrollLocked = false;
        content.replaceChildren();
        return;
      }
      const images = content.querySelector('.ukr-work-detail-images');
      disconnectDialogCarousel = setupCarousel(images, '.ukr-work-detail-track', '.ukr-work-detail-slide', '.ukr-work-detail-prev', '.ukr-work-detail-next', '.ukr-work-detail-count');
      close.focus({preventScroll:true});
    });
  });
})();
