// www/assets/js/admin/dashboard.js

document.addEventListener('DOMContentLoaded', async () => {
    try {
      const response = await fetch('/api/admin/dashboard', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
  
      if (!response.ok) throw new Error("Erreur lors du chargement des statistiques");
  
      const stats = await response.json();
  
      document.getElementById('total-products').textContent = stats.total_products;
      document.getElementById('total-categories').textContent = stats.total_categories;
      document.getElementById('total-suppliers').textContent = stats.total_suppliers;
      document.getElementById('total-sales').textContent = stats.total_sales + ' ventes';
    } catch (err) {
      console.error(err);
      alert("Impossible de charger les données du dashboard.");
    }
  });
  