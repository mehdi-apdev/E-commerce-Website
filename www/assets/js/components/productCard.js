// www/assets/js/components/productCard.js

/**
 * Gère l'affichage d'une carte produit moderne et interactive.
 * @param {Object} product - Données du produit.
 * @param {string|null} badgeText - Texte du badge (optionnel).
 * @param {Object} options - Options d'affichage (ex: showAddToCart).
 * @returns {HTMLElement} - Élément DOM de la carte produit.
 */
export default function generateProductCard(product, badgeText = null, options = {}) {
    const { showAddToCart = true } = options;
    const imagePath = product.main_image
      ? `/uploads/products/${product.product_id}/${product.main_image.trim()}`
      : null;
  
    const badgeClass = badgeText
      ? 'absolute top-2 left-2 bg-primary text-white text-xs px-2 py-1 rounded shadow'
      : '';
  
    const card = document.createElement('a');
    card.href = `/product.html?id=${product.product_id}`;
    card.className = `relative flex flex-col justify-between bg-white dark:bg-zinc-800 rounded-2xl shadow-md hover:shadow-xl transition overflow-hidden group hover:scale-[1.02] duration-300`;
  
    card.innerHTML = `
      <div class="relative w-full h-48 md:h-56 overflow-hidden">
        ${imagePath
          ? `<img src="${imagePath}" alt="${product.name}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">`
          : `<div class="w-full h-full flex items-center justify-center bg-secondary dark:bg-primary text-white">Aucune image</div>`
        }
        ${badgeText ? `<span class="${badgeClass}">${badgeText}</span>` : ''}
        ${showAddToCart ? `
          <button
            class="absolute top-2 right-2 bg-white/90 dark:bg-zinc-900/80 border border-gray-300 dark:border-zinc-700 p-1 rounded-full shadow hover:bg-primary hover:text-white transition"
            onclick="event.preventDefault(); addToCart(${product.product_id})"
            title="Ajouter au panier"
          >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
          </button>
        ` : ''}
      </div>
      <div class="p-4">
        <h3 class="text-base md:text-lg font-semibold text-gray-800 dark:text-white mb-1 line-clamp-1">${product.name}</h3>
        <p class="text-sm text-gray-500 dark:text-gray-300 line-clamp-2 mb-2">${product.short_description || ''}</p>
        <p class="text-sm font-bold text-primary">${product.price} €</p>
      </div>
    `;
  
    return card;
  }
  