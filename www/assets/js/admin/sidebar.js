// www/assets/js/admin/sidebar.js

document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('admin-sidebar');
  if (!container) return;

  fetch('/admin/partials/sidebar.html')
    .then(response => response.text())
    .then(html => {
      container.innerHTML = html;

      // Mise en surbrillance du lien actif
      const currentPath = window.location.pathname;
      document.querySelectorAll('#admin-sidebar nav a').forEach(link => {
        if (link.getAttribute('href') === currentPath) {
          link.classList.add('bg-primary', 'text-white', 'font-semibold');
        }
      });

      // Active Lucide icons
      if (window.lucide) {
        lucide.createIcons();
      }
    })
    .catch(err => {
      console.error("Erreur chargement sidebar admin :", err);
    });
});
