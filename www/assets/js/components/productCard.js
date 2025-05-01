// www/assets/js/components/productCard.js

/**
 * Génère dynamiquement une carte produit (HTML) à insérer dans la page.
 * @param {Object} product - Données du produit (doit contenir product_id, name, price, etc.)
 * @param {string|null} badgeText - Texte à afficher dans le badge (ex: "Nouveau", "Top vente", etc.)
 * @param {Object} options - Options d'affichage (ex: { showAddToCart: true })
 * @returns {HTMLElement} - Élément HTML <a> représentant la carte produit
 */
export default function generateProductCard(product, badgeText = null, options = {}) {
  const { showAddToCart = true } = options;

  // Chemin vers l'image principale du produit (si disponible)
  const imagePath = product.main_image
    ? `/uploads/products/${product.product_id}/${product.main_image.trim()}`
    : null;

  // Badge ("Nouveau", "Top vente", etc.)
  const badgeHTML = badgeText
    ? `<span class="absolute top-2 left-2 bg-primary text-white text-xs px-2 py-1 rounded shadow-md z-10">${badgeText}</span>`
    : '';

  // Bouton "Ajouter au panier" sous forme de "+" en haut à droite
  const addToCartHTML = showAddToCart
    ? `
      <button
        class="absolute top-2 right-2 bg-white/80 dark:bg-zinc-900/70 border border-gray-300 dark:border-zinc-700 p-1.5 rounded-full shadow-md z-10 hover:bg-primary hover:text-white transition"
        onclick="event.preventDefault(); addToCart(${product.product_id})"
        title="Ajouter au panier"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
      </button>`
    : '';

  // Création de la carte entière cliquable (vers product.html?id=...)
  const card = document.createElement('a');
  card.href = `/product.html?id=${product.product_id}`;
  card.className = `
    relative group rounded-2xl shadow-sm hover:shadow-xl transition overflow-hidden
    bg-white dark:bg-zinc-800 flex flex-col justify-between hover:ring-2 hover:ring-primary/30`;

  // Contenu HTML de la carte produit
  card.innerHTML = `
    <div class="relative w-full h-48 md:h-56 overflow-hidden">
      ${imagePath
        ? `<img src="${imagePath}" alt="${product.name}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">`
        : `<div class="h-full flex items-center justify-center bg-secondary dark:bg-primary text-white">Aucune image</div>`}
      ${badgeHTML}
      ${addToCartHTML}
    </div>
    <div class="p-4">
      <h3 class="text-base md:text-lg font-semibold text-gray-800 dark:text-white mb-1 truncate">${product.name}</h3>
      <p class="text-sm text-gray-500 dark:text-gray-300 mb-2 line-clamp-2">${product.short_description || ''}</p>
      <p class="text-sm font-bold text-primary">${product.price} €</p>
    </div>
  `;

  return card;
}
