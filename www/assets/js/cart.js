// www/assets/js/cart.js

const CART_KEY = 'cart';

/** Récupère le panier depuis localStorage */
function getCart() {
  return JSON.parse(localStorage.getItem(CART_KEY)) || [];
}

/** Sauvegarde le panier dans localStorage */
function saveCart(cart) {
  localStorage.setItem(CART_KEY, JSON.stringify(cart));
}

/** Ajoute un produit au panier (ou augmente la quantité) */
function addToCart(productId, quantity = 1) {
  const cart = getCart();
  const index = cart.findIndex(item => item.product_id === productId);

  if (index > -1) {
    cart[index].quantity += quantity;
  } else {
    cart.push({ product_id: productId, quantity });
  }

  saveCart(cart);
  updateCartBadge();
}

/** Met à jour la quantité d’un produit */
function updateQuantity(productId, newQty) {
  const cart = getCart().map(item =>
    item.product_id === productId ? { ...item, quantity: newQty } : item
  );

  saveCart(cart);
  updateCartBadge();
}

/** Supprime un produit du panier */
function removeFromCart(productId) {
  const cart = getCart().filter(item => item.product_id !== productId);
  saveCart(cart);
  updateCartBadge();
}

/** Vide complètement le panier */
function clearCart() {
  localStorage.removeItem(CART_KEY);
  updateCartBadge();
}

/** Met à jour le badge de quantité panier */
function updateCartBadge() {
  const cart = getCart();
  const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
  const badge = document.getElementById('cart-badge');
  if (badge) {
    // Affiche toujours un chiffre, même 0
    badge.textContent = totalItems;
  }
}

export {
  getCart,
  saveCart,
  addToCart,
  updateQuantity,
  removeFromCart,
  clearCart,
  updateCartBadge
};
