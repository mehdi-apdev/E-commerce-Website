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
 * @param {string} productId - L'ID du produit
 * @param {number} sizeId - L'ID de la taille
 * @param {string} sizeLabel - Le label de la taille (ex: "M", "L", "XL")
 * @param {number} quantity - La quantité à ajouter
 */
function addToCart(productId, sizeId, sizeLabel, quantity = 1) {
  const cart = getCart();
  
  productId = String(productId);
  sizeId = parseInt(sizeId);

  // Recherche du produit par ID ET taille
  const index = cart.findIndex(item => item.product_id === productId && item.size_id === sizeId);

  if (index > -1) {
    // Si le produit existe déjà avec cette taille, on incrémente
    cart[index].quantity += quantity;
  } else {
    // Sinon, on l'ajoute
    cart.push({
      product_id: productId,
      size_id: sizeId,      // 🆕 Ici on enregistre l'ID (important pour la base de données)
      size_label: sizeLabel, // 🆕 Ici, on enregistre le label (utile pour l'affichage)
      quantity
    });
  }

  console.log("🛒 Panier mis à jour :", cart);

  saveCart(cart);
  updateCartBadge();
}

/** 
 * 🔄 Met à jour la quantité d'un produit spécifique 
 * @param {string} productId - L'ID du produit
 * @param {number} sizeId - L'ID de la taille
 * @param {number} newQty - La nouvelle quantité
 */
function updateQuantity(productId, sizeId, newQty) {
  productId = String(productId); // Conversion explicite
  sizeId = parseInt(sizeId);

  const cart = getCart().map(item =>
    item.product_id === productId && item.size_id === sizeId 
      ? { ...item, quantity: newQty } 
      : item
  );

  console.log(`🛒 Quantité mise à jour pour ${productId} (${sizeId}) : ${newQty}`);
  saveCart(cart);
  updateCartBadge();
}

/** 
 * ❌ Supprime un produit du panier 
 * @param {string} productId - L'ID du produit
 * @param {number} sizeId - L'ID de la taille
 */
function removeFromCart(productId, sizeId) {
  productId = String(productId); // Conversion explicite
  sizeId = parseInt(sizeId);

  const cart = getCart();

  const updatedCart = cart.filter(item => {
    const match = item.product_id === productId && item.size_id === sizeId;
    console.log(`🗑️ Comparaison : ${item.product_id} === ${productId} && ${item.size_id} === ${sizeId} -> ${!match}`);
    return !match;
  });

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
