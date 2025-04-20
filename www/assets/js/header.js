// www/assets/js/header.js
async function renderHeaderLinks() {
  const cart = JSON.parse(localStorage.getItem('cart')) || [];
  const cartCount = cart.reduce((total, item) => total + item.quantity, 0);

  let user = null;

  try {
    const response = await fetch('/api/auth/me', {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      }
    });

    if (response.ok) {
      const data = await response.json();
      user = data.user;
      localStorage.setItem('user', JSON.stringify(user));
    } else {
      // Pas connecté
      localStorage.removeItem('user');
    }
  } catch (error) {
    console.error("Erreur API /auth/me :", error);
    localStorage.removeItem('user');
  }

  let links = `
    <a href="/index.html" class="hover:text-primary transition">Accueil</a>
    <a href="/products.html" class="hover:text-primary transition">Produits</a>
  `;

  if (user) {
    links += `
      <div class="relative group">
        <button class="hover:text-primary transition">${user.first_name} ${user.last_name}</button>
        <div class="absolute hidden group-hover:block right-0 bg-white dark:bg-zinc-800 rounded shadow-lg z-10">
          ${user.role === 'admin' ? `
          <a href="/admin/dashboard.html" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-zinc-700">Admin</a>` : ''}
          <a href="/profile.html" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-zinc-700">Profil</a>
          <button onclick="logout()" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-zinc-700 w-full text-left">Déconnexion</button>
        </div>
      </div>
    `;
  } else {
    links += `
      <a href="/login.html" class="hover:text-primary transition">Connexion</a>
      <a href="/register.html" class="hover:text-primary transition">Inscription</a>
    `;
  }

  links += `
    <a href="/cart.html" class="relative hover:text-primary transition">
      <i class="bi bi-cart"></i>
      <span class="ml-1">Panier</span>
      <span class="absolute -top-2 -right-4 bg-secondary dark:bg-primary text-xs rounded-full px-2 py-1 leading-none">
        ${cartCount}
      </span>
    </a>

    <button id="toggle-dark-desktop" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-zinc-700 transition" title="Changer de thème">
      <svg id="sun-icon-desktop" class="h-5 w-5 hidden text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M12 3v1m0 16v1m8.66-8.66h-1M4.34 12H3m15.36 4.24l-.71-.71M6.34 6.34l-.71-.71m12.02 0l-.71.71M6.34 17.66l-.71.71M12 8a4 4 0 100 8 4 4 0 000-8z"/>
      </svg>
      <svg id="moon-icon-desktop" class="h-5 w-5 hidden text-gray-800 dark:text-gray-100" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M21 12.79A9 9 0 1111.21 3a7 7 0 109.79 9.79z"/>
      </svg>
    </button>
  `;

  document.getElementById('nav-desktop').innerHTML = links;
  document.getElementById('mobile-menu').innerHTML = links;

  if (typeof applyThemeToggle === 'function') applyThemeToggle();
}

function logout() {
  localStorage.removeItem('user');

  // Déconnexion côté serveur
  fetch('/api/auth/logout', {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
  }).finally(() => {
    location.reload();
  });
}

renderHeaderLinks();
