/* Service accordions, accessible modal and scroll-snap media gallery (v1.3). */
(()=>{'use strict';
  const dialog=document.getElementById('ukr-service-dialog');
  const content=document.getElementById('ukr-service-dialog-content');
  const closeButton=dialog?.querySelector('.ukr-dialog-close');
  const reducedMotion=matchMedia('(prefers-reduced-motion: reduce)');
  const closeFallbackMs=380;
  let opener=null,closeTimer=null,previousOverflow='',previousPadding='',scrollLocked=false;
  let openingFrame=0,openingFrame2=0;
  let closing=false;
  const countScrollGap=()=>Math.max(0,window.innerWidth-document.documentElement.clientWidth);
  function lockScroll(){
    if(scrollLocked)return;
    previousOverflow=document.body.style.overflow;
    previousPadding=document.body.style.paddingRight;
    // A stable root scrollbar gutter keeps the exact same layout width open and closed.
    // Older browsers still get a pixel-compensated body padding fallback.
    const hasStableGutter=!!(window.CSS?.supports?.('scrollbar-gutter: stable') &&
      getComputedStyle(document.documentElement).scrollbarGutter.includes('stable'));
    if(!hasStableGutter){
      const gap=countScrollGap();
      if(gap){const oldPadding=parseFloat(getComputedStyle(document.body).paddingRight)||0;document.body.style.paddingRight=`${oldPadding+gap}px`;}
    }
    document.body.style.overflow='hidden';scrollLocked=true;
  }
  function unlockScroll(){
    if(!scrollLocked)return;
    document.body.style.overflow=previousOverflow;
    document.body.style.paddingRight=previousPadding;
    scrollLocked=false;
  }
  function setExpanded(trigger,open){
    const panel=document.getElementById(trigger.getAttribute('aria-controls'));if(!panel)return;
    // Read the *current* rendered height. Handles a rapid second click mid-transition.
    const measured=panel.getBoundingClientRect().height;
    panel.style.height=`${measured}px`;
    panel.getBoundingClientRect(); // Commit current pixels before animating.
    trigger.setAttribute('aria-expanded',String(open));
    panel.setAttribute('aria-hidden',String(!open));
    panel.inert=!open;
    panel.classList.toggle('is-expanded',open);
    panel.style.height=open?`${panel.scrollHeight}px`:'0px';
  }
  document.querySelectorAll('.ukr-category-trigger').forEach(trigger=>{
    const panel=document.getElementById(trigger.getAttribute('aria-controls'));if(!panel)return;
    panel.addEventListener('transitionend',e=>{
      if(e.propertyName==='height'&&panel.classList.contains('is-expanded'))panel.style.height='auto';
    });
    trigger.addEventListener('click',()=>{
      const open=trigger.getAttribute('aria-expanded')!=='true';
      document.querySelectorAll('.ukr-category-trigger[aria-expanded="true"]').forEach(other=>{if(other!==trigger)setExpanded(other,false);});
      setExpanded(trigger,open);
    });
  });
  if(!dialog||!content||!closeButton||typeof dialog.showModal!=='function')return;

  function finalizeClose(restoreFocus=true){
    if(closeTimer!==null){clearTimeout(closeTimer);closeTimer=null;}
    if(openingFrame)cancelAnimationFrame(openingFrame);
    if(openingFrame2)cancelAnimationFrame(openingFrame2);
    openingFrame=0;openingFrame2=0;
    dialog.removeEventListener('transitionend',onExitTransition);
    closing=false;
    // Closing and unlocking happen in the same JavaScript task, with a fixed gutter.
    if(dialog.open)dialog.close();
    dialog.classList.remove('is-visible');
    unlockScroll();
    if(restoreFocus&&opener?.isConnected)opener.focus({preventScroll:true});
    opener=null;
    content.replaceChildren();
  }
  let restoreFocusAfterExit=true;
  function onExitTransition(event){
    if(event.target!==dialog || event.propertyName!=='transform' || !closing)return;
    finalizeClose(restoreFocusAfterExit);
  }
  function closeModal(restoreFocus=true,immediate=false){
    if(!dialog.open||closing)return;
    restoreFocusAfterExit=restoreFocus;
    if(immediate||reducedMotion.matches){finalizeClose(restoreFocus);return;}
    closing=true;
    dialog.addEventListener('transitionend',onExitTransition);
    dialog.classList.remove('is-visible');
    // Safety net for hidden / background tabs, which can skip transitionend.
    closeTimer=setTimeout(()=>finalizeClose(restoreFocusAfterExit),closeFallbackMs);
  }
  dialog.addEventListener('close',()=>{if(scrollLocked)finalizeClose(restoreFocusAfterExit);});
  dialog.addEventListener('cancel',e=>{e.preventDefault();closeModal();});
  dialog.addEventListener('click',e=>{if(e.target===dialog)closeModal();});
  closeButton.addEventListener('click',()=>closeModal());
  // Top-layer <dialog> traps focus natively; this also handles edge-case embedded browsers.
  dialog.addEventListener('keydown',e=>{
    if(e.key!=='Tab')return;
    const items=[...dialog.querySelectorAll('a[href],button:not([disabled]),input:not([disabled]),[tabindex]:not([tabindex="-1"])')]
      .filter(el=>el.getClientRects().length&&getComputedStyle(el).visibility!=='hidden');
    if(!items.length){e.preventDefault();closeButton.focus();return;}
    const first=items[0],last=items[items.length-1],active=document.activeElement;
    if(e.shiftKey&&(active===first||!dialog.contains(active))){e.preventDefault();last.focus();}
    else if(!e.shiftKey&&(active===last||!dialog.contains(active))){e.preventDefault();first.focus();}
  });

  function bindGallery(){
    const gallery=content.querySelector('.ukr-gallery');if(!gallery)return;
    const track=gallery.querySelector('.ukr-gallery-track');
    const slides=[...track.querySelectorAll('.ukr-gallery-slide')];
    const prev=gallery.querySelector('.ukr-gallery-prev');
    const next=gallery.querySelector('.ukr-gallery-next');
    const thumbs=[...content.querySelectorAll('.ukr-gallery-thumb')];
    const counter=content.querySelector('.ukr-gallery-count');
    let index=0,raf=0;
    const update=i=>{
      index=Math.max(0,Math.min(slides.length-1,i));
      if(prev)prev.disabled=index===0;
      if(next)next.disabled=index===slides.length-1;
      if(counter)counter.textContent=`${String(index+1).padStart(2,'0')} / ${String(slides.length).padStart(2,'0')}`;
      thumbs.forEach((t,n)=>{t.classList.toggle('is-current',n===index);t.setAttribute('aria-current',n===index?'true':'false');});
    };
    const go=i=>{
      const nextIndex=Math.max(0,Math.min(slides.length-1,i));
      track.scrollTo({left:nextIndex*track.clientWidth,behavior:reducedMotion.matches?'instant':'smooth'});
      update(nextIndex);
    };
    prev?.addEventListener('click',()=>go(index-1));
    next?.addEventListener('click',()=>go(index+1));
    thumbs.forEach((thumb,i)=>thumb.addEventListener('click',()=>go(i)));
    track.addEventListener('scroll',()=>{if(raf)cancelAnimationFrame(raf);raf=requestAnimationFrame(()=>update(Math.round(track.scrollLeft/Math.max(1,track.clientWidth))));},{passive:true});
    gallery.addEventListener('keydown',e=>{
      if(e.key==='ArrowRight'||e.key==='ArrowLeft'){e.preventDefault();go(index+(e.key==='ArrowRight'?1:-1));}
    });
    update(0);
  }
  document.querySelectorAll('.ukr-subservice-btn').forEach(trigger=>{
    trigger.addEventListener('click',()=>{
      const template=document.getElementById(trigger.dataset.ukrTemplate);
      if(!(template instanceof HTMLTemplateElement))return;
      if(closeTimer!==null){clearTimeout(closeTimer);closeTimer=null;}
      if(openingFrame)cancelAnimationFrame(openingFrame);
      if(openingFrame2)cancelAnimationFrame(openingFrame2);
      closing=false;
      opener=trigger;
      content.replaceChildren(template.content.cloneNode(true));
      bindGallery();
      lockScroll();
      try{dialog.showModal();}catch(err){unlockScroll();return;}
      // Two rAF frames guarantee a painted initial state after showModal().
      // A single rAF may be coalesced with dialog layout, skipping the entrance.
      dialog.classList.remove('is-visible');
      if(reducedMotion.matches)dialog.classList.add('is-visible');
      else {
        openingFrame=requestAnimationFrame(()=>{
          openingFrame=0;
          openingFrame2=requestAnimationFrame(()=>{
            openingFrame2=0;
            if(dialog.open && !closing)dialog.classList.add('is-visible');
          });
        });
      }
      closeButton.focus({preventScroll:true});
    });
  });
  content.addEventListener('click',e=>{
    const ask=e.target.closest('.ukr-service-inquiry');if(!ask)return;
    e.preventDefault();
    const id=ask.dataset.ukrAskId||'';
    const label=ask.dataset.ukrAskName||'';
    const form=document.getElementById('lead-form');
    if(form?.elements?.service_id){
      form.elements.service_id.value=id;
      const selection=document.getElementById('ukr-selected-service');
      const selectedName=document.getElementById('ukr-selected-service-name');
      if(selection&&selectedName){selectedName.textContent=label;selection.hidden=false;}
    }
    closeModal(false,true);
    document.getElementById('contact')?.scrollIntoView({behavior:reducedMotion.matches?'instant':'smooth'});
    setTimeout(()=>form?.elements?.contact?.focus({preventScroll:true}),reducedMotion.matches?0:420);
  });
})();
