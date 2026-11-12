(() => {
  'use strict';
  const table = document.getElementById('ukr-links-rows');
  if (!table) return;
  const types = window.UKRContactPlatforms || {};
  const maxRows = 24;
  const platform = (url) => {
    const s = url.toLowerCase();
    if (s.startsWith('tel:')) return 'phone';
    if (s.startsWith('mailto:')) return 'email';
    if (/instagram\.com|instagr\.am/.test(s)) return 'instagram';
    if (/facebook\.com|fb\.com|fb\.me/.test(s)) return 'facebook';
    if (/t\.me\//.test(s) || /telegram\.me/.test(s)) return 'telegram';
    if (/youtube\.com|youtu\.be/.test(s)) return 'youtube';
    if (/tiktok\.com/.test(s)) return 'tiktok';
    if (/linkedin\.com/.test(s)) return 'linkedin';
    if (/wa\.me|whatsapp\.com/.test(s)) return 'whatsapp';
    if (/viber\.com|vb\.me/.test(s)) return 'viber';
    if (/pinterest\.com|pin\.it/.test(s)) return 'pinterest';
    if (/x\.com|twitter\.com/.test(s)) return 'x';
    return 'website';
  };
  function renumber() {
    [...table.rows].forEach((row,i) => row.querySelectorAll('[data-kind]').forEach(field => {
      field.name = `ukr_contact_links[${i}][${field.dataset.kind}]`;
    }));
  }
  function updateIcon(row) {
    const kind = row.querySelector('[data-kind="type"]')?.value || 'website';
    const template = document.querySelector(`template[data-ukr-icon="${kind}"]`);
    const preview = row.querySelector('.ukr-link-icon-preview');
    if (preview && template) preview.replaceChildren(template.content.cloneNode(true));
  }
  function updatePlaceholder(row) {
    const type = row.querySelector('[data-kind="type"]')?.value || 'website';
    const url = row.querySelector('[data-kind="url"]');
    if (!url) return;
    url.placeholder = type === 'phone' ? 'tel:+380...' : type === 'email' ? 'mailto:info@example.com' : 'https://...';
  }
  function rowFor(type = 'facebook') {
    const row = document.createElement('tr'); row.className='ukr-link-row';
    const icon = document.createElement('td');
    const preview = document.createElement('span'); preview.className='ukr-link-icon-preview'; preview.setAttribute('aria-hidden','true'); icon.append(preview);
    const typeCell = document.createElement('td');
    const typeSelect = document.createElement('select'); typeSelect.dataset.kind='type'; typeSelect.setAttribute('aria-label','Тип контакту');
    for (const [value,label] of Object.entries(types)) { const option=document.createElement('option'); option.value=value; option.textContent=label; typeSelect.append(option); }
    typeSelect.value=type; typeCell.append(typeSelect);
    const labelCell = document.createElement('td'); const label=document.createElement('input');
    label.dataset.kind='label'; label.className='regular-text'; label.maxLength=60; label.setAttribute('aria-label','Назва або підказка'); label.value=types[type] || ''; labelCell.append(label);
    const urlCell=document.createElement('td'); const url=document.createElement('input');
    url.dataset.kind='url'; url.className='large-text'; url.setAttribute('aria-label','Адреса контакту'); urlCell.append(url);
    const actions=document.createElement('td'); actions.className='ukr-link-actions';
    for (const [cls,labelText,aria] of [['ukr-row-up','↑','Підняти вище'],['ukr-row-down','↓','Опустити нижче'],['ukr-row-remove','Видалити','Видалити']]) {
      const btn=document.createElement('button'); btn.type='button'; btn.className=`button ${cls}`; btn.textContent=labelText; btn.setAttribute('aria-label',aria); actions.append(btn, ' ');
    }
    row.append(icon,typeCell,labelCell,urlCell,actions);
    row.dataset.selectedType=type;
    updatePlaceholder(row); updateIcon(row);
    return row;
  }
  document.getElementById('ukr-add-link')?.addEventListener('click', () => {
    if (table.rows.length >= maxRows) return;
    const row = rowFor('facebook'); table.append(row); renumber(); row.querySelector('[data-kind="url"]').focus();
  });
  table.addEventListener('change', e => {
    const el=e.target; if (!el.matches('[data-kind="type"]')) return;
    const row=el.closest('tr'); const label=row.querySelector('[data-kind="label"]');
    const old=row.dataset.selectedType || '';
    if (!label.value.trim() || label.value === types[old]) label.value=types[el.value] || '';
    row.dataset.selectedType=el.value;
    updateIcon(row); updatePlaceholder(row);
  });
  table.addEventListener('input', e => {
    const input=e.target; if(!input.matches('[data-kind="url"]')) return;
    const row=input.closest('tr'); const select=row.querySelector('[data-kind="type"]');
    if (!input.value.trim()) return;
    const detected=platform(input.value);
    if (detected !== 'website' && select.value !== detected) {
      const old=select.value;
      select.value=detected;
      const label=row.querySelector('[data-kind="label"]');
      if (!label.value.trim() || label.value===types[old]) label.value=types[detected] || '';
      row.dataset.selectedType=detected; updateIcon(row); updatePlaceholder(row);
    }
  });
  table.addEventListener('click',e=>{
    const btn=e.target.closest('button'); if(!btn) return;
    const row=btn.closest('tr'); if(!row) return;
    if(btn.classList.contains('ukr-row-remove')) row.remove();
    else if(btn.classList.contains('ukr-row-up') && row.previousElementSibling) table.insertBefore(row,row.previousElementSibling);
    else if(btn.classList.contains('ukr-row-down') && row.nextElementSibling) table.insertBefore(row.nextElementSibling,row);
    renumber();
  });
  [...table.rows].forEach(row => {
    row.dataset.selectedType=row.querySelector('[data-kind="type"]')?.value || 'website';
    updatePlaceholder(row); updateIcon(row);
  });
})();
