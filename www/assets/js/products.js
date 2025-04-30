// www/assets/js/products.js

// Sélection des conteneurs HTML (sidebar + zone produits + pagination)
const filtersContainer = document.getElementById('filters-container');
const productsContainer = document.getElementById('products-container');
const paginationContainer = document.getElementById('pagination-container');

document.addEventListener('DOMContentLoaded', () => {
  renderFilters();
  loadProducts(1);
});

function renderFilters() {
  if (!filtersContainer) return;

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

  loadFilterOptions();
  document.getElementById('sort')?.addEventListener('change', () => loadProducts(1));
}

async function loadFilterOptions() {
  await loadOptions('/api/categories', 'category', 'category_id');
  await loadOptions('/api/colors', 'color', 'color_id');
  await loadOptions('/api/fabrics', 'fabric', 'fabric_id');
  await loadOptions('/api/sizes', 'size', 'size_label');
  await loadOptions('/api/regions', 'region', 'region_id');
}

async function loadOptions(endpoint, elementId, valueKey) {
  const select = document.getElementById(elementId);
  if (!select) return;

  const defaultLabel = elementId === 'fabric' ? 'Tous' : 'Toutes';
  select.innerHTML = `<option value="">${defaultLabel}</option>`;

  try {
    const res = await fetch(endpoint, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    const json = await res.json();
    const key = Object.keys(json)[0];
    const items = json[key];

    items.forEach(item => {
      const option = document.createElement('option');
      option.value = item[valueKey];
      option.textContent = item.name ?? item[valueKey];
      select.appendChild(option);
    });

    select.addEventListener('change', () => loadProducts(1));
  } catch (err) {
    console.error(`Erreur fetch ${elementId}:`, err);
  }
}

function getFilters() {
  const category = document.getElementById('category')?.value || '';
  const color = document.getElementById('color')?.value || '';
  const fabric = document.getElementById('fabric')?.value || '';
  const size = document.getElementById('size')?.value || '';
  const region = document.getElementById('region')?.value || '';

  const [orderBy, direction] = (document.getElementById('sort')?.value || 'created_at_DESC').split('_');

  return { category, color, fabric, size, region, orderBy, direction };
}

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
      if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
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

function renderProducts(products) {
  productsContainer.innerHTML = '';

  if (!products.length) {
    productsContainer.className = 'flex justify-center items-center min-h-[40vh] px-4';
    productsContainer.innerHTML = `
      <p class="text-center text-gray-500 text-lg max-w-md w-full">
        Aucun produit ne correspond à vos critères.
      </p>
    `;
    return;
  }

  productsContainer.className = 'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6';

  products.forEach(product => {
    const imagePath = product.main_image
      ? `/uploads/products/${product.product_id}/${product.main_image.trim()}`
      : null;

    const card = `
      <div class="bg-white dark:bg-zinc-800 rounded-xl shadow hover:shadow-lg transition overflow-hidden group">
        <div class="relative">
          ${imagePath
            ? `<img src="${imagePath}" alt="${product.name}" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">`
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
          <a href="/product.html?id=${product.product_id}" class="text-sm underline text-primary">
            Voir
          </a>
        </div>
      </div>
    `;
    productsContainer.innerHTML += card;
  });
}

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