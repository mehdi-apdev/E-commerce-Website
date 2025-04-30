document.addEventListener('DOMContentLoaded', async () => {
  try {
    const response = await fetch('/api/admin/dashboard', {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });

    if (!response.ok) throw new Error("Erreur lors du chargement des statistiques");

    const stats = await response.json();

    // Remplissage des indicateurs
    document.getElementById('total-products').textContent = stats.total_products;
    document.getElementById('total-categories').textContent = stats.total_categories;
    document.getElementById('total-suppliers').textContent = stats.total_suppliers;
    document.getElementById('total-sales').textContent = `${stats.total_sales} €`;
    document.getElementById('total-orders').textContent = stats.total_orders;

    // Si les données mensuelles existent, on trace le graphique
    if (stats.monthly_sales && Array.isArray(stats.monthly_sales)) {
      renderMonthlySalesChart(stats.monthly_sales);
    }

  } catch (err) {
    console.error(err);
    alert("Impossible de charger les données du dashboard.");
  }
});

/**
 * Affiche un graphique des ventes mensuelles
 */
function renderMonthlySalesChart(monthlySales) {
  const ctx = document.getElementById('sales-chart');

  const labels = monthlySales.map(item => item.month);
  const values = monthlySales.map(item => item.total);

  new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Ventes mensuelles (€)',
        data: values,
        backgroundColor: 'rgba(34, 197, 94, 0.7)', // vert Tailwind
        borderColor: 'rgba(34, 197, 94, 1)',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: (val) => `${val} €`
          }
        }
      }
    }
  });
}
