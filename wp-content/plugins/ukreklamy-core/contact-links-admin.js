(() => {
  'use strict';
  const list = document.getElementById('ukr-links-rows');
  if (!list) return;
  const option = 'ukr_contact_links';
  const renumber = () => [...list.rows].forEach((row, index) => {
    row.querySelectorAll('input').forEach(input => {
      const name = input.dataset.kind;
      if (name) input.name = `${option}[${index}][${name}]`;
    });
  });
  const add = (label = '', url = '') => {
    if (list.rows.length >= 16) return;
    const tr = document.createElement('tr');
    const title = document.createElement('td');
    const address = document.createElement('td');
    for (const [cell, type, value] of [[title, 'label', label], [address, 'url', url]]) {
      const input = document.createElement('input');
      input.className = type === 'label' ? 'regular-text' : 'large-text';
      input.dataset.kind = type;
      input.value = value;
      input.setAttribute('aria-label', type === 'label' ? 'Назва посилання' : 'URL посилання');
      cell.append(input);
    }
    const actions = document.createElement('td');
    for (const [cls, text] of [['ukr-row-up','↑'],['ukr-row-down','↓'],['ukr-row-remove','Видалити']]) {
      const b = document.createElement('button'); b.type = 'button'; b.className = `button ${cls}`; b.textContent = text; actions.append(b, ' ');
    }
    tr.append(title,address,actions); list.append(tr); renumber();
  };
  // Persisted rows from PHP are assigned data-kind without altering their values.
  [...list.rows].forEach(row => {
    row.querySelectorAll('input').forEach(input => { input.dataset.kind = input.name.endsWith('[label]') ? 'label' : 'url'; });
  });
  document.getElementById('ukr-add-link')?.addEventListener('click', () => add());
  list.addEventListener('click', event => {
    const button = event.target.closest('button'); if (!button) return;
    const row = button.closest('tr'); if (!row) return;
    if (button.classList.contains('ukr-row-remove')) row.remove();
    else if (button.classList.contains('ukr-row-up') && row.previousElementSibling) list.insertBefore(row, row.previousElementSibling);
    else if (button.classList.contains('ukr-row-down') && row.nextElementSibling) list.insertBefore(row.nextElementSibling, row);
    renumber();
  });
})();
