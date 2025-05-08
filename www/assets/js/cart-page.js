// www/assets/js/cart-page.js

import { initLayout } from './common.js';
import { getCart, saveCart, updateCartBadge, removeFromCart as removeItem, updateQuantity } from './cart.js';

initLayout(() => {
  renderCart();
});

async function renderCart() {
  const container = document.getElementById('cart-container');
  const cart = getCart();

  if (cart.length === 0) {
    container.innerHTML = `<p class="text-center text-gray-500 text-lg">Votre panier est vide.</p>`;
    updateCartBadge();
    return;
  }

  try {
    const res = await fetch('/api/products');
    const data = await res.json();
    const products = data.products || [];

    let total = 0;

    const itemsHtml = cart.map(item => {
      const product = products.find(p => parseInt(p.product_id) === parseInt(item.product_id));
      if (!product) return '';
    
      // 🔎 On récupère les informations de taille depuis le produit
      const sizeData = product.sizes ? product.sizes.find(s => parseInt(s.size_id) === parseInt(item.size_id)) : null;
    
      // ✅ On récupère le prix directement depuis le produit (pas la taille)
      const price = parseFloat(product.price);
    
      const imagePath = product.main_image
        ? `/uploads/products/${product.product_id}/${product.main_image.trim()}`
        : '/uploads/products/default-image.png';
    
      const subtotal = price * item.quantity;
      total += subtotal;
    
      // ✅ Génération de l'affichage produit avec le bon label de taille
      return `
        <div class="flex items-center gap-4 border-b py-4">
          <img src="${imagePath}" alt="${product.name}" class="w-20 h-20 object-cover rounded" />
          <div class="flex-1">
            <a href="/product/${product.product_id}" class="font-medium hover:underline">${product.name}</a>
            <p class="text-sm text-gray-500">${price.toFixed(2)} € x ${item.quantity} (${sizeData ? sizeData.size_label : item.size})</p>
            <p class="text-sm font-bold text-primary">${subtotal.toFixed(2)} €</p>
          </div>
          <div>
            <input 
              type="number" 
              min="1" 
              value="${item.quantity}" 
              class="w-16 p-1 text-center rounded border dark:bg-zinc-800" 
              onchange="updateCartQuantity(${product.product_id}, ${item.size_id}, this.value)" 
            />
            <button 
              onclick="removeFromCart(${product.product_id}, ${item.size_id})" 
              class="text-sm text-red-500 hover:underline ml-2">
              Retirer
            </button>
          </div>
        </div>
      `;
    }).join('');
    
    
    
    // ✅ Insertion dans le DOM
    container.innerHTML = `
      <div class="space-y-4">
        ${itemsHtml}
      </div>
      <div class="mt-6 flex justify-between items-center border-t pt-4">
        <span class="text-lg font-bold">Total : ${total.toFixed(2)} €</span>
        <a href="/checkout.html" class="bg-primary text-white px-4 py-2 rounded hover:bg-opacity-90 transition">Passer commande</a>
      </div>
    `;

    updateCartBadge();
  } catch (err) {
    console.error('Erreur chargement panier :', err);
    container.innerHTML = `<p class="text-center text-red-500">Erreur lors du chargement du panier.</p>`;
  }
}

// ✅ Fonctions globales accessibles inline (car appelées dans le HTML dynamiquement)
window.updateCartQuantity = function (productId, sizeId, newQty) {
  updateQuantity(productId, sizeId, parseInt(newQty));
  renderCart();
};

window.removeFromCart = function (productId, sizeId) {
  removeItem(productId, sizeId);
  renderCart();
};

