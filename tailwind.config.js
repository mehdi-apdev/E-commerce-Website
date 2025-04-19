// tailwind.config.js
/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'class', // important pour activer le mode sombre via class="dark"
  content: [
    './app/views/**/*.php',
    './public/**/*.html',
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
  plugins: [],
};
