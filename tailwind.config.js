/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'class',
  content: [
    "./www/**/*.html",
    "./www/**/*.js"
  ],
  theme: {
    extend: {
      fontFamily: {
        heading: ['Algerian', 'serif'],
        body: ['Inter', 'sans-serif'],
      },
      colors: {
        primary: '#b17a50',
        'primary-dark': '#96663f',
        secondary: '#EEE8E1',
        dark: '#1a1a1a',
        light: '#f9f9f9',
      },
      ringColor: {
        'primary/20': 'rgba(177, 122, 80, 0.2)',
      },
    },
  },
  plugins: [
    function ({ addComponents }) {
      addComponents({
        '.dropdown-menu': {
          '@apply w-56 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg shadow-lg py-2 px-2 z-50 space-y-1': {},
        },
        '.dropdown-link': {
          '@apply flex items-center justify-between w-full text-sm px-4 py-2 rounded hover:bg-gray-100 dark:hover:bg-zinc-700 transition': {},
        },
          '.section-card': {
          '@apply bg-white dark:bg-zinc-800 rounded shadow p-6 border border-gray-200 dark:border-zinc-700': {},
        },
        '.section-title': {
          '@apply text-xl font-semibold mb-4': {},
        },
        '.btn-primary': {
          '@apply px-6 py-2 bg-primary text-white rounded hover:bg-primary-dark transition': {},
        },
        '.input': {
          '@apply w-full rounded border px-3 py-2 bg-white dark:bg-zinc-700 dark:text-white': {},
        },
        '.stat-card': {
          '@apply flex items-center gap-4 bg-white dark:bg-zinc-800 p-6 rounded shadow border border-gray-200 dark:border-zinc-700': {},
        },
        '.stat-icon': {
          '@apply w-12 h-12 flex items-center justify-center rounded-full': {},
        },
      });
    }
  ]
};
