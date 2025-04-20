// www/assets/js/products.js

/**
 * Gère l'affichage d'un catalogue de produits avec filtres dynamiques,
 * tri (date, prix, nom), pagination, etc.
 *
 * Hypothèse : Le header/footer sont injectés par index.js
 * products.html contient un <aside> pour la sidebar, et ce script.
 */

// Sélection des conteneurs HTML (sidebar + zone produits + pagination)
const filtersContainer = document.getElementById('filters-container');
const productsContainer = document.getElementById('products-container');
const paginationContainer = document.getElementById('pagination-container');

// 1) Crée la structure HTML de base pour les filtres
function renderFilters() {
  // On insère des <select> vides pour catégorie, couleur, tissu, taille, région, + tri
  filtersContainer.innerHTML = `
    <div>
      <label for="category" class="block font-semibold mb-1 text-sm md:text-base">Catégorie</label>
      <select id="category" class="w-full border rounded px-2 py-1 dark:bg-zinc-800"></select>
    </div>

    <div>
      <label for="color" class="block font-semibold mb-1 text-sm md:text-base">Couleur</label>
      <select id="color" class="w-full border rounded px-2 py-1 dark:bg-zinc-800"></select>
    </div>

    <div>
      <label for="fabric" class="block font-semibold mb-1 text-sm md:text-base">Tissu</label>
      <select id="fabric" class="w-full border rounded px-2 py-1 dark:bg-zinc-800"></select>
    </div>

    <div>
      <label for="size" class="block font-semibold mb-1 text-sm md:text-base">Taille</label>
      <select id="size" class="w-full border rounded px-2 py-1 dark:bg-zinc-800"></select>
    </div>

    <div>
      <label for="region" class="block font-semibold mb-1 text-sm md:text-base">Région culturelle</label>
      <select id="region" class="w-full border rounded px-2 py-1 dark:bg-zinc-800" autocomplete="off"></select>
    </div>
  `;

  // Charger les options dynamiquement depuis les endpoints
  loadFilterOptions();

  // Écouteur sur le tri (si l'utilisateur change le tri => loadProducts)
  document.getElementById('sort')?.addEventListener('change', () => loadProducts(1));
}

// 2) Charge dynamiquement les options depuis l’API
async function loadFilterOptions() {
  // Example endpoints :
  // /api/categories -> [ { category_id, name }... ]
  // /api/colors -> [ { color_id, name }... ]
  // /api/fabrics -> [ { fabric_id, name }... ]
  // /api/sizes -> [ { size_label: 'S' }... ]
  // /api/regions -> [ { region_id, name }... ]

  // Charger catégories
  const catSelect = document.getElementById('category');
  if (catSelect) {
    catSelect.innerHTML = '<option value="">Toutes</option>';
    try {
      const categories = await fetch('/api/categories').then(r => r.json());
      categories.forEach(cat => {
        const opt = document.createElement('option');
        opt.value = cat.category_id;
        opt.textContent = cat.name;
        catSelect.appendChild(opt);
      });
      catSelect.addEventListener('change', () => loadProducts(1));
    } catch (err) {
      console.error('Erreur fetch categories:', err);
    }
  }

  // Charger couleurs
  const colorSelect = document.getElementById('color');
  if (colorSelect) {
    colorSelect.innerHTML = '<option value="">Toutes</option>';
    try {
      const colors = await fetch('/api/colors').then(r => r.json());
      colors.forEach(col => {
        const opt = document.createElement('option');
        opt.value = col.color_id;
        opt.textContent = col.name; 
        colorSelect.appendChild(opt);
      });
      colorSelect.addEventListener('change', () => loadProducts(1));
    } catch (err) {
      console.error('Erreur fetch colors:', err);
    }
  }

  // Charger tissus
  const fabricSelect = document.getElementById('fabric');
  if (fabricSelect) {
    fabricSelect.innerHTML = '<option value="">Tous</option>';
    try {
      const fabrics = await fetch('/api/fabrics').then(r => r.json());
      fabrics.forEach(fab => {
        const opt = document.createElement('option');
        opt.value = fab.fabric_id;
        opt.textContent = fab.name;
        fabricSelect.appendChild(opt);
      });
      fabricSelect.addEventListener('change', () => loadProducts(1));
    } catch (err) {
      console.error('Erreur fetch fabrics:', err);
    }
  }

  // Charger tailles
  const sizeSelect = document.getElementById('size');
  if (sizeSelect) {
    sizeSelect.innerHTML = '<option value="">Toutes</option>';
    try {
      const sizes = await fetch('/api/sizes').then(r => r.json());
      sizes.forEach(sz => {
        // Suppose: { size_label: 'M' }
        const opt = document.createElement('option');
        opt.value = sz.size_label;
        opt.textContent = sz.size_label;
        sizeSelect.appendChild(opt);
      });
      sizeSelect.addEventListener('change', () => loadProducts(1));
    } catch (err) {
      console.error('Erreur fetch sizes:', err);
    }
  }

  // Charger régions
  const regionSelect = document.getElementById('region');
  if (regionSelect) {
    regionSelect.innerHTML = '<option value="">Toutes</option>';
    try {
      const regions = await fetch('/api/regions').then(r => r.json());
      regions.forEach(rg => {
        // Suppose: { region_id, name }
        const opt = document.createElement('option');
        opt.value = rg.region_id;
        opt.textContent = rg.name;
        regionSelect.appendChild(opt);
      });
      regionSelect.addEventListener('change', () => loadProducts(1));
    } catch (err) {
      console.error('Erreur fetch regions:', err);
    }
  }
}

// 3) Récupère les valeurs de tous les filtres (category, color, fabric, size, region) + tri
function getFilters() {
  const category = document.getElementById('category')?.value || '';
  const color = document.getElementById('color')?.value || '';
  const fabric = document.getElementById('fabric')?.value || '';
  const size = document.getElementById('size')?.value || '';
  const region = document.getElementById('region')?.value || '';

  const [orderBy, direction] = (document.getElementById('sort')?.value || 'created_at_DESC').split('_');

  return { category, color, fabric, size, region, orderBy, direction };
}

// 4) Charge les produits depuis /api/products?category=...&color=... etc.
function loadProducts(page = 1) {
  const { category, color, fabric, size, region, orderBy, direction } = getFilters();

  const url = new URL('/api/products', window.location.origin);
  url.searchParams.set('page', page);
  if (category) url.searchParams.set('category', category);
  if (color) url.searchParams.set('color', color);
  if (fabric) url.searchParams.set('fabric', fabric);
  if (size) url.searchParams.set('size', size);
  if (region) url.searchParams.set('region', region);
  if (orderBy) url.searchParams.set('orderBy', orderBy);
  if (direction) url.searchParams.set('direction', direction);

  fetch(url)
    .then(res => {
      if (!res.ok) {
        throw new Error(`HTTP error! status: ${res.status}`);
      }
      return res.json();
    })
    .then(data => {
      renderProducts(data.products || []);
      renderPagination(data.totalPages || 1, data.currentPage || 1);
    })
    .catch(err => {
      console.error('Erreur lors du chargement des produits :', err);
      productsContainer.innerHTML = `
        <p class="text-center text-red-500 col-span-full">
          Impossible de charger les produits pour le moment.
        </p>
      `;
    });
}

// 5) Affiche la liste des produits
function renderProducts(products) {
  productsContainer.innerHTML = '';

  if (!products.length) {
    // Affichage d’un message centré SANS grille
    productsContainer.className = 'flex justify-center items-center min-h-[40vh] px-4';

    productsContainer.innerHTML = `
      <p class="text-center text-gray-500 text-lg max-w-md w-full">
        Aucun produit ne correspond à vos critères.
      </p>
    `;
    return;
  }

  // Sinon on remet la grille responsive
  productsContainer.className = 'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6';

  products.forEach(product => {
    const imagePath = product.main_image
      ? `/uploads/products/${product.product_id}/${product.main_image.trim()}`
      : null;

    const card = `
      <div class="bg-white dark:bg-zinc-800 rounded-xl shadow hover:shadow-lg transition overflow-hidden group">
        <div class="relative">
          ${imagePath
            ? `<img
                src="${imagePath}"
                alt="${product.name}"
                class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300"
              >`
            : `<div class="h-48 flex items-center justify-center bg-secondary dark:bg-primary text-white">Aucune image</div>`}
        </div>
        <div class="p-4">
          <h3 class="text-lg font-semibold mb-1">${product.name}</h3>
          <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">
            ${product.short_description || ''}
          </p>
          <p class="text-sm text-primary font-bold mb-3">
            ${product.price} €
          </p>
          <a
            href="/product.html?id=${product.product_id}"
            class="text-sm underline text-primary"
          >
            Voir
          </a>
        </div>
      </div>
    `;
    productsContainer.innerHTML += card;
  });
}




// 6) Affiche la pagination
function renderPagination(totalPages, currentPage) {
  paginationContainer.innerHTML = '';

  if (totalPages <= 1) return;

  for (let i = 1; i <= totalPages; i++) {
    const btn = document.createElement('button');
    btn.textContent = i;
    btn.className = [
      'mx-1', 'px-3', 'py-1', 'rounded', 'border',
      i === currentPage ? 'bg-primary text-white' : 'hover:bg-gray-200 dark:hover:bg-zinc-700'
    ].join(' ');

    btn.addEventListener('click', () => loadProducts(i));
    paginationContainer.appendChild(btn);
  }
}

// 7) Au chargement, on construit le squelette des filtres, puis on loadProducts
document.addEventListener('DOMContentLoaded', () => {
  renderFilters();   // 1) crée structure + loadFilterOptions
  loadProducts(1);   // 2) affiche la première page
});
