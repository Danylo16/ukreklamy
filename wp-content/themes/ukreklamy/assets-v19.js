/** Clients carousel and restrained FAQ interaction. Zero third-party dependencies. */
(() => {
  'use strict';
  const track = document.querySelector('.ukr-clients-track');
  if (track) {
    const cards = [...track.querySelectorAll('.ukr-client-logo')];
    const prev = document.querySelector('.ukr-clients-prev');
    const next = document.querySelector('.ukr-clients-next');
    const label = document.querySelector('.ukr-clients-count');
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
    const refresh = () => {
      if (!cards.length) return;
      const left = track.getBoundingClientRect().left;
      const current = Math.max(0, cards.findIndex(card => card.getBoundingClientRect().right > left + 8));
      if (label) label.textContent = `${current + 1} / ${cards.length}`;
      if (prev) prev.disabled = track.scrollLeft <= 2;
      if (next) next.disabled = track.scrollLeft + track.clientWidth >= track.scrollWidth - 2;
    };
    const scroll = (dir) => {
      const item = cards[0];
      if (!item) return;
      const gap = parseFloat(getComputedStyle(track).columnGap) || 0;
      track.scrollBy({left: dir * (item.getBoundingClientRect().width + gap), behavior: reduced.matches ? 'instant' : 'smooth'});
    };
    prev?.addEventListener('click', () => scroll(-1));
    next?.addEventListener('click', () => scroll(1));
    track.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
        e.preventDefault(); scroll(e.key === 'ArrowRight' ? 1 : -1);
      }
    });
    track.addEventListener('scroll', refresh, {passive: true});
    window.addEventListener('resize', refresh, {passive: true});
    refresh();
  }
})();
