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
function addToCart(productId, size, quantity = 1) {
  const cart = getCart();
  
  // Recherche du produit par ID ET taille
  const index = cart.findIndex(item => item.product_id === productId && item.size === size);

  if (index > -1) {
    // Si le produit existe déjà avec cette taille, on incrémente
    cart[index].quantity += quantity;
  } else {
    // Sinon, on l'ajoute
    cart.push({ product_id: productId, size, quantity });
  }

  saveCart(cart);
  updateCartBadge();
}

function updateQuantity(productId, size, newQty) {
  const cart = getCart().map(item =>
    item.product_id === productId && item.size === size ? { ...item, quantity: newQty } : item
  );

  saveCart(cart);
  updateCartBadge();
}

function removeFromCart(productId, size) {
  const cart = getCart().filter(item => !(item.product_id === productId && item.size === size));
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

  // met à jour tous les éléments #cart-badge présents dans le DOM (desktop + mobile)
  document.querySelectorAll('#cart-badge').forEach(badge => {
    badge.textContent = totalItems;
    badge.classList.toggle('hidden', totalItems === 0);
  });
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
