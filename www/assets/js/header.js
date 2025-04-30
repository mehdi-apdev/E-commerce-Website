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
      localStorage.removeItem('user');
    }
  } catch (error) {
    console.error("Erreur API /auth/me :", error);
    localStorage.removeItem('user');
  }

  let desktopLinks = `
    <a href="/index.html" class="hover:text-primary transition">Accueil</a>
    <a href="/products.html" class="hover:text-primary transition">Produits</a>
  `;

  let mobileLinks = `
    <a href="/index.html" class="dropdown-link">Accueil</a>
    <a href="/products.html" class="dropdown-link">Produits</a>
  `;

  if (user) {
    desktopLinks += `
      <div class="relative" id="user-menu-container">
        <button id="user-menu-button" class="hover:text-primary transition">${user.first_name} ${user.last_name}</button>
        <div id="user-dropdown" class="dropdown-menu hidden absolute right-0 mt-2">
          ${user.role === 'admin' ? `<a href="/admin/dashboard.html" class="dropdown-link">Admin</a>` : ''}
          <a href="/profile.html" class="dropdown-link">Profil</a>
          <button onclick="logout()" class="dropdown-link w-full text-left">Déconnexion</button>
        </div>
      </div>
    `;

    mobileLinks += `
      ${user.role === 'admin' ? `<a href="/admin/dashboard.html" class="dropdown-link">Admin</a>` : ''}
      <a href="/profile.html" class="dropdown-link">Profil</a>
      <button onclick="logout()" class="dropdown-link w-full text-left">Déconnexion</button>
    `;
  } else {
    desktopLinks += `
      <a href="/login.html" class="hover:text-primary transition">Connexion</a>
      <a href="/register.html" class="hover:text-primary transition">Inscription</a>
    `;

    mobileLinks += `
      <a href="/login.html" class="dropdown-link">Connexion</a>
      <a href="/register.html" class="dropdown-link">Inscription</a>
    `;
  }

  // Panier (facteur commun)
  const cartLink = `
  <a href="/cart.html" class="relative hover:text-primary transition">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 inline" fill="currentColor" viewBox="0 0 16 16">
      <path d="M0 1.5A.5.5 0 0 1 .5 1h1a.5.5 0 0 1 .485.379L2.89 5H14.5a.5.5 0 0 1 .49.598l-1.5 7A.5.5 0 0 1 13 13H4a.5.5 0 0 1-.49-.402L1.61 2H.5a.5.5 0 0 1-.5-.5zM3.14 6l1.25 5.995h8.22L13.89 6H3.14z"/>
      <path d="M5.5 16a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3zm7 0a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z"/>
    </svg>
    <span id="cart-badge" class="absolute -top-2 -right-4 bg-secondary dark:bg-primary text-xs rounded-full px-2 py-1 leading-none">
      ${cartCount}
    </span>
  </a>
`;

  desktopLinks += cartLink;
  document.querySelector('.md\\:hidden.flex.items-center')?.insertAdjacentHTML('afterbegin', cartLink);

  desktopLinks += `
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

  document.getElementById('nav-desktop').innerHTML = desktopLinks;
  document.getElementById('mobile-menu').innerHTML = mobileLinks;

  if (typeof applyThemeToggle === 'function') applyThemeToggle();

  const toggle = document.getElementById('menu-toggle');
  const mobileMenu = document.getElementById('mobile-menu');

  if (toggle && mobileMenu) {
    toggle.addEventListener('click', () => {
      mobileMenu.classList.toggle('hidden');
    });
  }

  const userBtn = document.getElementById('user-menu-button');
  const userDropdown = document.getElementById('user-dropdown');
  const userContainer = document.getElementById('user-menu-container');

  if (userBtn && userDropdown && userContainer) {
    let hoverTimeout;

    userContainer.addEventListener('mouseenter', () => {
      clearTimeout(hoverTimeout);
      userDropdown.classList.remove('hidden');
    });

    userContainer.addEventListener('mouseleave', () => {
      hoverTimeout = setTimeout(() => {
        userDropdown.classList.add('hidden');
      }, 200);
    });
  }
}

function logout() {
  localStorage.removeItem('user');
  fetch('/api/auth/logout', {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
  }).finally(() => {
    location.reload();
  });
}

renderHeaderLinks();