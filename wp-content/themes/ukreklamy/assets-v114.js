/* UKreklamy 1.14 – flip to summary, then explicitly open this work's photo dialog. */
(() => {
  'use strict';
  const cards = [...document.querySelectorAll('[data-ukr-work-card]')];
  if (!cards.length) return;
  const modal = document.getElementById('ukr-work-dialog');
  function flip(card, open, focus = true) {
    const front = card.querySelector('.ukr-work-face-front');
    const back = card.querySelector('.ukr-work-face-back');
    const trigger = card.querySelector('.ukr-work-flip-trigger');
    const undo = card.querySelector('.ukr-work-unflip');
    if (!front || !back || !trigger) return;
    card.classList.toggle('is-flipped', open);
    trigger.setAttribute('aria-pressed', String(open));
    front.toggleAttribute('inert', open);
    front.setAttribute('aria-hidden', String(open));
    back.toggleAttribute('inert', !open);
    back.setAttribute('aria-hidden', String(!open));
    if (focus) (open ? undo : trigger)?.focus({preventScroll:true});
  }
  cards.forEach(card => {
    const flipBtn = card.querySelector('.ukr-work-flip-trigger');
    const undo = card.querySelector('.ukr-work-unflip');
    flipBtn?.addEventListener('click', () => {
      cards.forEach(other => { if (other !== card && other.classList.contains('is-flipped')) flip(other, false, false); });
      flip(card, true);
    });
    undo?.addEventListener('click', () => flip(card, false));
  });
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && !modal?.open) {
      cards.forEach(card => { if (card.classList.contains('is-flipped')) flip(card, false, true); });
    }
  });
})();
