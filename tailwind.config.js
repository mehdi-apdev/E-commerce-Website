// tailwind.config.js
/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'class', // important pour activer le mode sombre via class="dark"
  content: [
    "./www/**/*.html",
    "./www/**/*.js"
  ],
  theme: {
    extend: {
      fontFamily: {
        heading: ['Algerian', 'serif'], // Font locale
        body: ['Inter', 'sans-serif'],  // Google Font
      },
      colors: {
        primary: '#b17a50',            // couleur cuivre personnalisée
        'primary-dark': '#96663f',     // variante hover
        secondary: '#EEE8E1',
        dark: '#1a1a1a',
        light: '#f9f9f9',
      },
      ringColor: {
        'primary/20': 'rgba(177, 122, 80, 0.2)', // focus:ring-primary/20
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
        }
      }); 
    }
  ]
};
