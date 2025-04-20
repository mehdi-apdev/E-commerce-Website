// www/assets/js/register.js
document.getElementById('registerForm').addEventListener('submit', async function (e) {
  e.preventDefault();

  // Réinitialiser les erreurs précédentes
  document.querySelectorAll('.text-red-500').forEach(el => el.textContent = '');
  document.querySelectorAll('input').forEach(input => input.classList.remove('border-red-500'));

  const formData = new FormData(e.target);

  const data = {
    first_name: formData.get('first_name'),
    last_name: formData.get('last_name'),
    email: formData.get('email'),
    phone: formData.get('phone'),
    password: formData.get('password'),
    password_confirm: formData.get('password_confirm'),
  };

  try {
    const response = await fetch('/api/auth/registerPost', {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: new URLSearchParams(data),
    });

    const result = await response.json();

    if (result.success) {
      if (result.user) {
        localStorage.setItem('user', JSON.stringify(result.user));
      }
      window.location.href = result.redirect;
    } else {
      Object.entries(result.errors).forEach(([key, message]) => {
        const input = document.querySelector(`#${key}`);
        if (input) input.classList.add('border-red-500');

        const errorEl = document.querySelector(`#${key}-error`);
        if (errorEl) errorEl.textContent = message;
      });
    }
  } catch (error) {
    alert("Erreur réseau ou serveur. Veuillez réessayer.");
  }
});
