// public/assets/js/index.js

document.addEventListener('DOMContentLoaded', () => {
  // 1) Injecte HEADER
  fetch('/my-eshop/public/html/partials/header.html')
  .then(r => r.text())
  .then(html => {
    const temp = document.createElement('div');
    temp.innerHTML = html;
    const newHeader = temp.querySelector('header'); 
    document.querySelector('header').replaceWith(newHeader);

      // Charger theme-toggle puis header.js
      loadScript('/my-eshop/public/assets/js/theme-toggle.js', () => {
        loadScript('/my-eshop/public/assets/js/header.js', () => {
          // Ensuite, si on est sur la page d'accueil, on charge les “nouveaux” / “top ventes”
          loadHomeProducts();
        });
      });
    });

  // 2) Injecte FOOTER
  fetch('/my-eshop/public/html/partials/footer.html')
    .then(res => res.text())
    .then(html => {
      const temp = document.createElement('div');
      temp.innerHTML = html;
      const newFooter = temp.querySelector('footer'); 
    document.querySelector('footer').replaceWith(newFooter);
    });

  // === Fonctions internes ===

  // Charge le script JS en asynchrone, puis exécute un callback
  function loadScript(src, callback) {
    const s = document.createElement('script');
    s.src = src;
    s.onload = callback || null;
    document.body.appendChild(s);
  }

  // Charge et insère les produits “nouveautés” et “top ventes” (spécifique page d’accueil)
  function loadHomeProducts() {
    const newContainer = document.getElementById('product-list-new');
    const topContainer = document.getElementById('product-list-top');
    // Si on n’est pas sur la home (ces div n’existent pas), on s’arrête
    if (!newContainer || !topContainer) return;

    // Récupère tous les produits depuis l’API, puis on trie
    fetch('/my-eshop/public/api/products')
      .then(res => res.json())
      .then(data => {
        const products = data.products || [];
      
        const latest = [...products].sort((a, b) => new Date(b.created_at) - new Date(a.created_at)).slice(0, 4);
        const top = [...products].sort((a, b) => (b.sales_count || 0) - (a.sales_count || 0)).slice(0, 4);
      
        latest.forEach(p => {
          newContainer.innerHTML += generateProductCard(p, 'Nouveau');
        });
        top.forEach(p => {
          topContainer.innerHTML += generateProductCard(p, 'Top vente', true);
        });      
      });
  }

  // Génère le HTML d’une carte produit
  function generateProductCard(product, badgeText, dark = false) {
    const imagePath = product.main_image
      ? `/my-eshop/public/uploads/products/${product.product_id}/${product.main_image.trim()}`
      : null;

    const badgeClass = dark
      ? 'bg-secondary dark:bg-primary text-primary dark:text-white'
      : 'bg-primary text-white';

    return `
      <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-md hover:shadow-2xl transition duration-300 overflow-hidden group">
        <div class="relative">
          ${imagePath
            ? `<img src="${imagePath}" alt="${product.name}" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">`
            : `<div class="h-48 flex items-center justify-center bg-secondary dark:bg-primary text-white">Aucune image</div>`
          }
          <span class="absolute top-2 left-2 ${badgeClass} text-xs px-2 py-1 rounded">${badgeText}</span>
        </div>
        <div class="p-4">
          <h3 class="text-lg font-semibold mb-1">${product.name}</h3>
          <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">${product.short_description || ''}</p>
          <p class="text-sm font-bold text-primary mb-3">${product.price} €</p>
          <a href="/my-eshop/public/html/product.html?id=${product.product_id}" class="text-sm underline text-primary">Voir</a>
        </div>
      </div>
    `;
  }

});
