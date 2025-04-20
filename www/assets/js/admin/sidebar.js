// www/assets/js/admin/sidebar.js

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('admin-sidebar');
    if (!container) return;
  
    fetch('/admin/partials/sidebar.html')
      .then(response => response.text())
      .then(html => {
        container.innerHTML = html;
      })
      .catch(err => {
        console.error("Erreur chargement sidebar admin :", err);
      });
  });
  