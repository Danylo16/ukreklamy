/* Site navigation, portfolio and lead form (v1.2). */
(()=>{'use strict';
  const menu=document.querySelector('.menubtn'),nav=document.querySelector('.navlinks');
  if(menu&&nav){
    menu.addEventListener('click',()=>{
      const open=menu.getAttribute('aria-expanded')!=='true';
      menu.setAttribute('aria-expanded',String(open));
      menu.setAttribute('aria-label',open?'Закрити меню':'Відкрити меню');
      nav.classList.toggle('open',open);menu.textContent=open?'×':'☰';
    });
    nav.querySelectorAll('a').forEach(a=>a.addEventListener('click',()=>{
      nav.classList.remove('open');menu.setAttribute('aria-expanded','false');menu.textContent='☰';
    }));
  }
  const track=document.querySelector('.worktrack'),prev=document.querySelector('.prev'),next=document.querySelector('.next'),count=document.querySelector('.slide-count');
  if(track&&prev&&next){
    const refresh=()=>{
      const cards=[...track.querySelectorAll('.work')];
      const width=cards[0]?.getBoundingClientRect().width||1;
      const idx=Math.min(cards.length,Math.round(track.scrollLeft/(width+24))+1);
      if(count)count.textContent=cards.length?`${idx} / ${cards.length}`:'0 / 0';
      prev.disabled=track.scrollLeft<5;
      next.disabled=track.scrollLeft+track.clientWidth>=track.scrollWidth-5;
    };
    const move=dir=>{
      const card=track.querySelector('.work');if(!card)return;
      track.scrollBy({left:dir*(card.getBoundingClientRect().width+24),behavior:matchMedia('(prefers-reduced-motion: reduce)').matches?'instant':'smooth'});
    };
    prev.addEventListener('click',()=>move(-1));next.addEventListener('click',()=>move(1));
    track.addEventListener('keydown',e=>{if(['ArrowRight','ArrowLeft'].includes(e.key)){e.preventDefault();move(e.key==='ArrowRight'?1:-1);}});
    track.addEventListener('scroll',refresh,{passive:true});window.addEventListener('resize',refresh);refresh();
  }
  const form=document.getElementById('lead-form');if(!form)return;
  const status=document.getElementById('form-status'),contact=form.elements.contact;
  const channelOptions=[...form.querySelectorAll('input[name="channel"]')];
  const selected=document.getElementById('ukr-selected-service');
  const makeId=()=>crypto.randomUUID?crypto.randomUUID():'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g,c=>(c==='x'?Math.floor(Math.random()*16):8+Math.floor(Math.random()*4)).toString(16));
  let id=makeId(),saved=false;
  const channel=()=>form.querySelector('input[name="channel"]:checked')?.value||'phone';
  const sync=()=>{
    const tg=channel()==='telegram';
    contact.type=tg?'text':'tel';contact.placeholder=tg?(contact.dataset.telegramPlaceholder||'@username'):(contact.dataset.phonePlaceholder||'+380 XX XXX XX XX');
    contact.autocomplete=tg?'off':'tel';contact.inputMode=tg?'text':'tel';
    const contactLabel=document.getElementById('ukr-contact-label');
    const label=tg?(contactLabel.dataset.telegramLabel||'Telegram @username'):(contactLabel.dataset.phoneLabel||'Номер телефону');
    if(contactLabel.firstChild?.nodeType===3) contactLabel.firstChild.nodeValue=label+' '; // Keep required-field marker and its styling intact.
    contact.setAttribute('aria-label',label);
  };
  channelOptions.forEach(option=>option.addEventListener('change',sync));sync();
  selected?.querySelector('.ukr-selection-clear')?.addEventListener('click',()=>{
    form.elements.service_id.value='0';selected.hidden=true;
    document.getElementById('ukr-selected-service-name').textContent='';
  });
  form.addEventListener('submit',async e=>{
    e.preventDefault();if(saved)return;
    status.removeAttribute('role');status.classList.remove('is-success');
    if(!form.reportValidity())return;
    const value=contact.value.trim(),chosenChannel=channel();
    if(chosenChannel==='telegram'&&!/^@[a-zA-Z0-9_]{5,32}$/.test(value)){
      status.textContent='Вкажіть Telegram у форматі @username.';status.setAttribute('role','alert');contact.focus();return;
    }
    if(chosenChannel==='phone'&&!/^\+?[0-9\s().-]{7,25}$/.test(value)){
      status.textContent='Перевірте номер телефону.';status.setAttribute('role','alert');contact.focus();return;
    }
    const button=form.querySelector('button[type=submit]');
    button.disabled=true;status.textContent='Зберігаємо заявку…';
    try{
      const tokenResponse=await fetch(window.ukrConfig.tokenEndpoint,{credentials:'same-origin',cache:'no-store',headers:{'X-WP-Nonce':window.ukrConfig.restNonce}});
      if(!tokenResponse.ok)throw Error('Не вдалося отримати захисний токен.');
      const token=(await tokenResponse.json()).token;
      const payload={channel:chosenChannel,contact:value,comment:form.elements.comment.value,service_id:Number(form.elements.service_id?.value||0),website:form.elements.website.value,privacy:form.elements.privacy.checked,started:Number(form.elements.started.value),token,request_id:id};
      const response=await fetch(window.ukrConfig.endpoint,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-WP-Nonce':window.ukrConfig.restNonce},body:JSON.stringify(payload)});
      const body=await response.json();
      if(!response.ok)throw Error(body.message||'Сервер тимчасово недоступний.');
      if(!body.saved)throw Error('Неочікувана відповідь сервера.');
      saved=true;status.textContent=body.message;status.classList.add('is-success');button.textContent='Заявку збережено';
    }catch(err){status.setAttribute('role','alert');status.textContent=err.message||'Не вдалося підтвердити збереження. Перевірте у менеджера перед повтором.';button.disabled=false;}
  });
})();
