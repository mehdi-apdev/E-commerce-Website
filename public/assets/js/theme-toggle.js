// public/assets/js/theme-toggle.js

function applyThemeToggle() {
  const html = document.documentElement;

  const toggles = [
    {
      button: document.getElementById('toggle-dark'),
      sun: document.getElementById('sun-icon'),
      moon: document.getElementById('moon-icon'),
    },
    {
      button: document.getElementById('toggle-dark-desktop'),
      sun: document.getElementById('sun-icon-desktop'),
      moon: document.getElementById('moon-icon-desktop'),
    }
  ];

  const savedTheme = localStorage.getItem('theme');
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  const isDark = savedTheme === 'dark' || (!savedTheme && prefersDark);

  if (isDark) html.classList.add('dark');
  else html.classList.remove('dark');

  // Init icons
  toggles.forEach(({ sun, moon }) => {
    if (sun && moon) {
      if (isDark) {
        sun.classList.remove('hidden');
        moon.classList.add('hidden');
      } else {
        sun.classList.add('hidden');
        moon.classList.remove('hidden');
      }
    }
  });

  // Click handlers
  toggles.forEach(({ button, sun, moon }) => {
    button?.addEventListener('click', () => {
      html.classList.toggle('dark');
      const nowDark = html.classList.contains('dark');
      localStorage.setItem('theme', nowDark ? 'dark' : 'light');
      toggles.forEach(({ sun, moon }) => {
        sun?.classList.toggle('hidden', !nowDark);
        moon?.classList.toggle('hidden', nowDark);
      });
    });
  });
}

// Appel automatique à l’ouverture de la page
document.addEventListener('DOMContentLoaded', applyThemeToggle);
