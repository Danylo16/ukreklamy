/* WordPress native Media Library multi-image selector; no external dependencies. */
(()=>{'use strict';
  const field=document.getElementById('ukr_service_gallery');
  const list=document.getElementById('ukr-service-gallery-list');
  const picker=document.querySelector('.ukr-gallery-pick');
  const clear=document.querySelector('.ukr-gallery-clear');
  if(!field||!list||!picker||!clear||!window.wp?.media)return;
  let ids=field.value.split(',').map(Number).filter(Number.isInteger).filter(x=>x>0).slice(0,12);
  const sync=()=>{field.value=ids.join(',');draw();};
  function draw(){
    list.replaceChildren();
    ids.forEach((id,i)=>{
      const item=document.createElement('div');item.className='ukr-admin-gallery-row';
      item.style.cssText='display:flex;align-items:center;gap:10px;margin:8px 0;padding:6px;border:1px solid #ddd;border-radius:5px';
      const photo=document.createElement('img');photo.alt='';photo.width=72;photo.height=52;photo.style.cssText='object-fit:cover;background:#eee';
      const attachment=wp.media.attachment(id);
      const show=()=>{const data=attachment.toJSON();photo.src=data.sizes?.thumbnail?.url||data.url||'';};
      attachment.fetch().then(show).catch(()=>{photo.alt='Зображення не знайдено';});
      const label=document.createElement('span');label.textContent=`Фото ${i+1} · ID ${id}`;label.style.flex='1';
      item.append(photo,label);
      [['↑','Перемістити вище',-1],['↓','Перемістити нижче',1],['×','Прибрати фотографію',0]].forEach(([symbol,title,diff])=>{
        const button=document.createElement('button');button.type='button';button.className='button';button.textContent=symbol;button.setAttribute('aria-label',`${title}: фото ${i+1}`);
        button.disabled=(diff===-1&&i===0)||(diff===1&&i===ids.length-1);
        button.addEventListener('click',()=>{if(diff===0)ids.splice(i,1);else{[ids[i],ids[i+diff]]=[ids[i+diff],ids[i]];}sync();});item.append(button);
      });
      list.append(item);
    });
  }
  picker.addEventListener('click',()=>{
    const frame=wp.media({title:'Фотографії послуги',button:{text:'Застосувати галерею'},multiple:true,library:{type:'image'}});
    frame.on('open',()=>{const selection=frame.state().get('selection');ids.forEach(id=>selection.add(wp.media.attachment(id)));});
    frame.on('select',()=>{ids=frame.state().get('selection').toArray().map(a=>a.id).filter(Number.isInteger).slice(0,12);sync();});
    frame.open();
  });
  clear.addEventListener('click',()=>{ids=[];sync();});draw();
})();
