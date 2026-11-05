/* UKreklamy v1.14.1: modal gallery thumbnail state, no additional dependencies. */
(() => {
  'use strict';
  const dialog = document.getElementById('ukr-work-dialog');
  if (!dialog) return;
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)');
  function sync() {
    const track = dialog.querySelector('.ukr-work-detail-track');
    if (!track) return;
    const thumbs = [...dialog.querySelectorAll('[data-ukr-work-thumb]')];
    const slides = [...track.querySelectorAll('.ukr-work-detail-slide')];
    if (!slides.length || !thumbs.length) return;
    const index = slides.reduce((best, slide, i) =>
      Math.abs(slide.offsetLeft - slides[0].offsetLeft - track.scrollLeft) <
      Math.abs(slides[best].offsetLeft - slides[0].offsetLeft - track.scrollLeft) ? i : best, 0);
    thumbs.forEach((thumb, i) => {
      thumb.classList.toggle('is-active', i === index);
      thumb.setAttribute('aria-current', String(i === index));
    });
  }
  let observedTrack = null;
  let raf = 0;
  const schedule = () => { if (!raf) raf = window.requestAnimationFrame(() => { raf=0; sync(); }); };
  const observe = () => {
    const track = dialog.querySelector('.ukr-work-detail-track');
    if (track === observedTrack) return;
    if (observedTrack) observedTrack.removeEventListener('scroll', schedule);
    observedTrack = track;
    observedTrack?.addEventListener('scroll', schedule, {passive:true});
    sync();
  };
  dialog.addEventListener('click', e => {
    const button = e.target.closest('[data-ukr-work-thumb]');
    if (!button || !dialog.contains(button)) return;
    const idx = Number(button.dataset.ukrWorkThumb);
    const track = dialog.querySelector('.ukr-work-detail-track');
    const slides = [...(track?.querySelectorAll('.ukr-work-detail-slide') || [])];
    if (!track || !Number.isInteger(idx) || !slides[idx]) return;
    const left = slides[idx].offsetLeft - slides[0].offsetLeft;
    track.scrollTo({left, behavior:reduce.matches ? 'instant' : 'smooth'});
    button.focus({preventScroll:true});
  });
  dialog.addEventListener('close', () => {
    observedTrack?.removeEventListener('scroll', schedule);
    observedTrack = null;
    if (raf) window.cancelAnimationFrame(raf);
    raf=0;
  });
  /* showModal runs synchronously from the original v1.13 handler, so observe afterward. */
  dialog.addEventListener('click', e => {
    if (e.target.closest('[data-ukr-work-thumb]')) schedule();
  });
  const content = document.getElementById('ukr-work-dialog-content');
  if (content && typeof MutationObserver === 'function') {
    new MutationObserver(() => { if(dialog.open) observe(); }).observe(content,{childList:true});
  }
  dialog.addEventListener('keydown',e=>{
    if (e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') return;
    if (e.target.closest('.ukr-work-detail-track')) return; // Original carousel already handles these.
    if (!e.target.closest('.ukr-work-detail-images')) return;
    const btn = dialog.querySelector(e.key === 'ArrowLeft' ? '.ukr-work-detail-prev' : '.ukr-work-detail-next');
    if(btn && !btn.disabled) { e.preventDefault(); btn.click(); }
  });
})();
