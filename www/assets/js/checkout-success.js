// www/assets/js/checkout-success.js
import { initLayout, showToast } from '/assets/js/common.js';

document.addEventListener('DOMContentLoaded', () => {
  initLayout();
  const toast = sessionStorage.getItem('toast');
  if (toast) {
    showToast(toast);
    sessionStorage.removeItem('toast');
  }

  const order = JSON.parse(sessionStorage.getItem('last_order') || '{}');

  if (!order || !order.id) {
    showToast("Aucune commande récente à afficher.", 'error');
    setTimeout(() => {
      window.location.href = '/products.html';
    }, 2000);
    return;
  }

  // Affichage des infos de commande
  document.getElementById('order-id').textContent = order.id;
  document.getElementById('order-total').textContent = order.total + ' €';
  document.getElementById('order-address').textContent = order.address;
  document.getElementById('order-info').classList.remove('hidden');

  sessionStorage.removeItem('last_order');
});
