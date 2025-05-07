// www/assets/js/cart.js

const CART_KEY = 'cart';

/** 
 * 🔄 Récupère le panier depuis localStorage 
 * Renvoie un tableau vide si le panier n'existe pas
 */
function getCart() {
  try {
    return JSON.parse(localStorage.getItem(CART_KEY)) || [];
  } catch (error) {
    console.error("Erreur lors de la récupération du panier :", error);
    return [];
  }
}

/** 
 * 🔄 Sauvegarde le panier dans localStorage 
 */
function saveCart(cart) {
  try {
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
  } catch (error) {
    console.error("Erreur lors de la sauvegarde du panier :", error);
  }
}

/** 
 * ➕ Ajoute un produit au panier (ou augmente la quantité) 
 */
function addToCart(productId, size, quantity = 1) {
  const cart = getCart();

  // Conversion explicite en string pour éviter les erreurs de typage
  productId = String(productId);

  // Recherche du produit par ID ET taille
  const index = cart.findIndex(item => item.product_id === productId && item.size === size);

  if (index > -1) {
    // Si le produit existe déjà avec cette taille, on incrémente
    cart[index].quantity += quantity;
  } else {
    // Sinon, on l'ajoute
    cart.push({ product_id: productId, size, quantity });
  }

  console.log("🛒 Panier mis à jour :", cart);

  saveCart(cart);
  updateCartBadge();
}

/** 
 * 🔄 Met à jour la quantité d'un produit spécifique 
 */
function updateQuantity(productId, size, newQty) {
  productId = String(productId); // Conversion explicite
  const cart = getCart().map(item =>
    item.product_id === productId && item.size === size ? { ...item, quantity: newQty } : item
  );

  console.log(`🛒 Quantité mise à jour pour ${productId} (${size}) : ${newQty}`);
  saveCart(cart);
  updateCartBadge();
}

/** 
 * ❌ Supprime un produit du panier 
 */
function removeFromCart(productId, size) {
  productId = String(productId); // Conversion explicite
  const cart = getCart();
  
  console.log("📌 Contenu avant suppression :", cart);

  const updatedCart = cart.filter(item => {
    const match = item.product_id === productId && item.size === size;
    console.log(`Comparaison : ${item.product_id} === ${productId} && ${item.size} === ${size} -> ${!match}`);
    return !match;
  });

  console.log("📌 Contenu après suppression :", updatedCart);

  saveCart(updatedCart);
  updateCartBadge();
}

/** 
 * 🗑️ Vide complètement le panier 
 */
function clearCart() {
  localStorage.removeItem(CART_KEY);
  updateCartBadge();
}

/** 
 * 🔄 Met à jour le badge de quantité panier 
 */
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
