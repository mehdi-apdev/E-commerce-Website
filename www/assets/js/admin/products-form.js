// www/assets/js/admin/products-form.js
import { showToast } from '/assets/js/common.js';

const form = document.getElementById('product-form');
const productId = new URLSearchParams(window.location.search).get('id');
const isEditMode = Boolean(productId);

/** Initialise le formulaire (chargement des options + préremplissage éventuel) */
document.addEventListener('DOMContentLoaded', async () => {
  if (!form) return;

  await Promise.all([
    populateSelect('/api/categories', 'category_id'),
    populateSelect('/api/colors', 'color_id'),
    populateSelect('/api/fabrics', 'fabric_id'),
    populateSelect('/api/regions', 'cultural_region_id'),
    populateSelect('/api/suppliers', 'supplier_id'),
  ]);

  if (isEditMode) {
    try {
      const response = await fetch(`/api/products/${productId}`);
      const { product } = await response.json();

      form.name.value = product.name;
      form.short_description.value = product.short_description;
      form.description.value = product.description;
      form.price.value = product.price;
      form.stock.value = product.stock ?? 0;
      form.category_id.value = product.category_id;
      form.color_id.value = product.color_id;
      form.fabric_id.value = product.fabric_id;
      form.cultural_region_id.value = product.cultural_region_id;
      form.supplier_id.value = product.supplier_id;
    } catch (err) {
      console.error('Erreur chargement produit', err);
      showToast('Erreur lors du chargement du produit.', 'error');
    }
  }
});

/** Soumission du formulaire (POST pour create ou faux PUT pour update) */
form?.addEventListener('submit', async (e) => {
  e.preventDefault();

  const formData = new FormData(form);
  const url = isEditMode ? `/api/admin/products/${productId}` : '/api/admin/products';

  if (isEditMode) {
    formData.append('_method', 'PUT');
  }

  try {
    const response = await fetch(url, {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    });

    const result = await response.json();

    if (response.ok && result.success) {
      // ✅ Stocke le toast avant redirection
      localStorage.setItem('toastMessage', JSON.stringify({
        message: result.message || (isEditMode ? 'Produit modifié.' : 'Produit créé.'),
        type: 'success'
      }));
      window.location.href = '/admin/products.html';
    } else {
      showToast(result.message || 'Erreur inconnue.', 'error');
    }
  } catch (err) {
    console.error('Erreur lors de la soumission du produit :', err);
    showToast('Erreur serveur ou réseau.', 'error');
  }
});

/** Fonction utilitaire pour remplir un select depuis une API */
async function populateSelect(url, selectName) {
  try {
    const response = await fetch(url);
    const data = await response.json();

    const select = document.querySelector(`select[name=${selectName}]`);
    select.innerHTML = '<option value="">---</option>';

    const items = data[Object.keys(data)[0]] || [];

    items.forEach(item => {
      const idKey = Object.keys(item).find(k => k.endsWith('_id')) || 'id';
      const option = document.createElement('option');
      option.value = item[idKey];
      option.textContent = item.name;
      select.appendChild(option);
    });
  } catch (err) {
    console.error(`Erreur chargement ${selectName} :`, err);
  }
}
