// www/assets/js/profile.js
import { initLayout, showToast } from './common.js';

document.addEventListener('DOMContentLoaded', () => {
  // Injecte header, footer, thème et panier, puis exécute le reste
  initLayout(() => {
    fetchUserInfo();

    document.getElementById('update-profile-btn')?.addEventListener('click', updateProfile);
    document.getElementById('logout-btn')?.addEventListener('click', logout);
  });
});

function fetchUserInfo() {
  fetch('/api/auth/getProfile')
    .then(res => res.json())
    .then(data => {
      if (data.success && data.user) {
        const { first_name, last_name, email, phone } = data.user;
        document.getElementById('first_name').value = first_name || '';
        document.getElementById('last_name').value = last_name || '';
        document.getElementById('email').value = email || '';
        document.getElementById('phone').value = phone || '';
      } else {
        showToast('Utilisateur non connecté ou non trouvé', 'error');
        setTimeout(() => {
          window.location.href = '/login.html';
        }, 1500);        
      }
    })
    .catch(err => {
      console.error('Erreur API getProfile():', err);
    });
}

function updateProfile() {
  const form = {
    first_name: document.getElementById('first_name').value.trim(),
    last_name: document.getElementById('last_name').value.trim(),
    phone: document.getElementById('phone').value.trim(),
    password: document.getElementById('password').value.trim(),
  };

  const formData = new FormData();
  for (let key in form) {
    if (form[key]) formData.append(key, form[key]);
  }

  fetch('/api/auth/updateProfile', {
    method: 'POST',
    body: formData
  })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        showToast('Profil mis à jour avec succès');
        fetchUserInfo(); // refresh
      } else if (data.errors) {
        showToast(Object.values(data.errors).join(' / '), 'error');
      } else {
        alert('Erreur inconnue lors de la mise à jour');
      }
    })
    .catch(err => {
      showToast('Erreur inconnue lors de la mise à jour', 'error');
    });
}

function logout() {
  fetch('/api/auth/logout', {
    method: 'POST',
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
    .then(() => {
      localStorage.removeItem('user');
      window.location.href = '/login.html';
    })
    .catch(err => {
      console.error('Erreur logout():', err);
    });
}
