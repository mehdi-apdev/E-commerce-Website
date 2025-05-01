// www/assets/js/index.js

import { initLayout } from './common.js';
import generateProductCard from './components/productCard.js';

document.addEventListener('DOMContentLoaded', () => {
  initLayout(() => {
    loadHomeProducts();
  });
});

function loadHomeProducts() {
  const newContainer = document.getElementById('product-list-new');
  const topContainer = document.getElementById('product-list-top');
  if (!newContainer || !topContainer) return;

  fetch('/api/products')
    .then(res => res.json())
    .then(data => {
      const products = data.products || [];

      const latest = [...products]
        .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
        .slice(0, 4);
      const top = [...products]
        .sort((a, b) => (b.sales_count || 0) - (a.sales_count || 0))
        .slice(0, 4);

      latest.forEach(p => {
        newContainer.appendChild(generateProductCard(p, 'Nouveau'));
      });
      top.forEach(p => {
        topContainer.appendChild(generateProductCard(p, 'Top vente'));
      });
    });
}
