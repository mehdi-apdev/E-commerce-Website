// www/assets/js/common.js

// Charge header + footer + thèmes + panier
export function initLayout(callback = null) {
  // Injecte HEADER
  fetch('/partials/header.html')
    .then(r => r.text())
    .then(html => {
      const temp = document.createElement('div');
      temp.innerHTML = html;
      const newHeader = temp.querySelector('header');
      document.querySelector('header').replaceWith(newHeader);

      // Charge les scripts principaux ensuite
      loadScript('/assets/js/theme-toggle.js', () => {
        loadScript('/assets/js/header.js', async () => {
          try {
            const { updateCartBadge } = await import('./cart.js');
            updateCartBadge();
            if (typeof callback === 'function') callback();
          } catch (err) {
            console.error("Erreur lors du chargement de cart.js :", err);
          }
        });
      });
    });

  // Injecte FOOTER
  fetch('/partials/footer.html')
    .then(res => res.text())
    .then(html => {
      const temp = document.createElement('div');
      temp.innerHTML = html;
      const newFooter = temp.querySelector('footer');
      document.querySelector('footer').replaceWith(newFooter);
    });
}

function loadScript(src, callback) {
  const s = document.createElement('script');
  s.src = src;
  s.onload = callback || null;
  document.body.appendChild(s);
}

export function showToast(message, type = 'success') {
  const container = document.getElementById('toast-container');
  if (!container) return;

  const toast = document.createElement('div');
  toast.className = `
    px-4 py-3 rounded-xl shadow-md transition-opacity duration-300 text-sm max-w-xs
    ${type === 'success'
      ? 'bg-green-500 text-white'
      : 'bg-red-500 text-white'}
  `;
  toast.textContent = message;

  container.appendChild(toast);

  setTimeout(() => {
    toast.classList.add('opacity-0');
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}
