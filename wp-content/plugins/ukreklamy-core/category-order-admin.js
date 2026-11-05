/* Accessible up/down ordering in the direction editing screen. */
(() => {
  const root = document.getElementById('ukr-subservice-order');
  if (!root) return;
  function update() {
    const items = [...root.children];
    items.forEach((li, index) => {
      const up = li.querySelector('.ukr-order-up');
      const down = li.querySelector('.ukr-order-down');
      if (up) up.disabled = index === 0;
      if (down) down.disabled = index === items.length - 1;
    });
  }
  root.addEventListener('click', event => {
    const button = event.target.closest('button');
    if (!button || (!button.classList.contains('ukr-order-up') && !button.classList.contains('ukr-order-down'))) return;
    const item = button.closest('li');
    if (!item) return;
    const up = button.classList.contains('ukr-order-up');
    const sibling = up ? item.previousElementSibling : item.nextElementSibling;
    if (!sibling) return;
    if (up) root.insertBefore(item, sibling);
    else root.insertBefore(sibling, item);
    update();
    item.querySelector(up ? '.ukr-order-up' : '.ukr-order-down')?.focus();
  });
  update();
})();
