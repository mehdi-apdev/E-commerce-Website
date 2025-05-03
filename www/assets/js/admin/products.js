// www/assets/js/admin/products.js
import { showToast } from '/assets/js/common.js';

document.addEventListener('DOMContentLoaded', async () => {
  // Affiche un toast stocké via localStorage (ex: après création/modification)
  const toastData = localStorage.getItem('toastMessage');
  if (toastData) {
    const { message, type } = JSON.parse(toastData);
    showToast(message, type || 'success');
    localStorage.removeItem('toastMessage');
  }

  try {
    const response = await fetch('/api/products', {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });

    if (!response.ok) throw new Error('Échec du chargement des produits');

    const { products } = await response.json();

    const tbody = document.getElementById('product-table');
    tbody.innerHTML = ''; // nettoyage

    products.forEach(product => {
      const imageUrl = product.main_image
        ? `/uploads/products/${product.product_id}/${product.main_image}`
        : null;

      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="px-4 py-3 text-sm">${product.product_id}</td>
        <td class="px-4 py-3">
          <div class="w-20 h-20 rounded-md overflow-hidden border dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800 flex items-center justify-center">
            ${imageUrl
              ? `<img src="${imageUrl}" alt="${product.name}" class="w-full h-full object-cover">`
              : `<span class="text-xs text-gray-400">Aucune</span>`}
          </div>
        </td>
        <td class="px-4 py-3 text-sm font-medium">${product.name}</td>
        <td class="px-4 py-3 text-sm">${product.price} €</td>
        <td class="px-4 py-3 text-sm">${product.category_name || '-'}</td>
        <td class="px-4 py-3 text-sm space-x-2">
          <a href="/admin/products-form.html?id=${product.product_id}" class="text-blue-500 hover:underline">Modifier</a>
          <button class="text-red-500 hover:underline" onclick="deleteProduct(${product.product_id})">Supprimer</button>
        </td>
      `;

      tbody.appendChild(tr);
    });

  } catch (err) {
    console.error(err);
    showToast("Erreur lors du chargement des produits.", "error");
  }
});

/**
 * Supprime un produit après confirmation
 */
async function deleteProduct(productId) {
  if (!confirm("Êtes-vous sûr de vouloir supprimer ce produit ?")) return;

  try {
    const response = await fetch(`/api/admin/products/${productId}`, {
      method: 'DELETE',
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });

    const result = await response.json();

    if (result.success) {
      showToast('🗑️ Produit supprimé.', 'success');
      setTimeout(() => location.reload(), 1000);
    } else {
      showToast(result.message || 'Suppression impossible.', 'error');
    }
  } catch (err) {
    console.error(err);
    showToast('Erreur réseau ou serveur.', 'error');
  }
}
