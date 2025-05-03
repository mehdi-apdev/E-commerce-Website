// www/assets/js/login.js
import { initLayout, showToast } from './common.js';


document.addEventListener('DOMContentLoaded', () => {
  // Injecte le header/footer + thème + panier
  initLayout();

  // Ajoute le listener après injection
  const form = document.getElementById('loginForm');
  if (!form) return;

  form.addEventListener('submit', async function (e) {
    e.preventDefault();

    // Nettoyer les erreurs précédentes
    document.querySelectorAll('.text-red-500').forEach(el => el.textContent = '');
    document.querySelectorAll('input').forEach(input => input.classList.remove('border-red-500'));

    const formData = new FormData(e.target);
    const data = {
      email: formData.get('email'),
      password: formData.get('password'),
      remember: formData.get('remember') ? '1' : '',
    };

    try {
      const response = await fetch('/api/auth/loginPost', {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: new URLSearchParams(data),
      });

      const raw = await response.text();
      console.log("Réponse brute : ", raw);

      try {
        const result = JSON.parse(raw);
        if (result.success) {
          showToast("Connexion réussie 🎉");
          window.location.href = result.redirect;
        } else {
          Object.entries(result.errors).forEach(([key, message]) => {
            const input = document.querySelector(`#${key}`);
            if (input) input.classList.add('border-red-500');

            const errorEl = document.querySelector(`#${key}-error`);
            if (errorEl) errorEl.textContent = message;
          });

          if (result.errors.auth) {
            showToast(result.errors.auth, 'error');
          }
        }
      } catch (err) {
        console.error("Erreur de parsing JSON :", err);
        showToast("Erreur de réponse du serveur", 'error');
      }
    } catch (err) {
      console.error("Erreur réseau :", err);
      showToast("Erreur réseau. Veuillez réessayer.", 'error');
    }
  });
});
