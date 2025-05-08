// www/assets/js/products.js

import { initLayout, showToast } from './common.js';
import generateProductCard from './components/productCard.js';



// Sélection des conteneurs HTML (sidebar + zone produits + pagination)
const filtersContainer = document.getElementById('filters-container');
const productsContainer = document.getElementById('products-container');
const paginationContainer = document.getElementById('pagination-container');

document.addEventListener('DOMContentLoaded', () => {
  // On initialise header/footer/themes/panier AVANT le reste
  initLayout(() => {
    renderFilters();
    loadProducts(1);
  });
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
  await loadOptions('/api/regions', 'region', 'region_id');

  // On ne récupère plus les tailles globalement, mais depuis les produits
  await loadSizeOptions();
}

async function loadSizeOptions() {
  const select = document.getElementById('size');
  if (!select) return;

  select.innerHTML = `<option value="">Toutes</option>`;

  try {
    const res = await fetch('/api/products', {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    const data = await res.json();

    const products = data.products || [];

    // Création d'un Set pour éviter les doublons
    const sizes = new Set();

    products.forEach(product => {
      if (product.sizes) {
        product.sizes.forEach(size => sizes.add(size.size_label));
      }
    });

    // 🔎 On trie les tailles de manière naturelle
    const naturalOrder = ["XS", "S", "M", "L", "XL", "XXL"];
    const sortedSizes = Array.from(sizes).sort((a, b) => {
      const indexA = naturalOrder.indexOf(a);
      const indexB = naturalOrder.indexOf(b);

      if (indexA === -1 && indexB === -1) {
        return a.localeCompare(b);
      } else if (indexA === -1) {
        return 1;
      } else if (indexB === -1) {
        return -1;
      }
      return indexA - indexB;
    });

    // Ajout au Select trié
    sortedSizes.forEach(sizeLabel => {
      const option = document.createElement('option');
      option.value = sizeLabel;
      option.textContent = sizeLabel;
      select.appendChild(option);
    });

    // Réagir au changement de taille
    select.addEventListener('change', () => loadProducts(1));

  } catch (err) {
    console.error(`Erreur fetch tailles:`, err);
    showToast(`Erreur lors du chargement des tailles`, 'error');
  }
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
    showToast(`Erreur lors du chargement du filtre ${elementId}`, 'error');
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
  if (region) url.searchParams.set('region', region);
  if (orderBy) url.searchParams.set('orderBy', orderBy);
  if (direction) url.searchParams.set('direction', direction);

  // 🔍 Ajout du filtre par taille
  if (size) url.searchParams.set('size', size);


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
      showToast("Erreur lors du chargement des produits", 'error');
      productsContainer.innerHTML = `
        <p class="text-center text-gray-500 col-span-full">
          Aucune donnée à afficher.
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
    const card = generateProductCard(product, null, { showAddToCart: true });
    productsContainer.appendChild(card);
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