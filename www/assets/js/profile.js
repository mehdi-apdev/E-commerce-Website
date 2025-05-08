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

/**
 * Récupère les informations de l'utilisateur connecté
 */
function fetchUserInfo() {
  fetch('/api/auth/getProfile', {
    method: 'GET',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
    },
    credentials: 'include' // 🔥 Permet d'envoyer les cookies de session
  })
    .then(res => {
      if (!res.ok) throw new Error(`Erreur ${res.status}: ${res.statusText}`);
      return res.json();
    })
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
      console.error('Erreur lors de la récupération du profil:', err.message);
      showToast('Erreur lors de la récupération des informations.', 'error');
    });
}

/**
 * Mise à jour du profil utilisateur
 */
function updateProfile() {
  const form = {
    first_name: document.getElementById('first_name').value.trim(),
    last_name: document.getElementById('last_name').value.trim(),
    phone: document.getElementById('phone').value.trim(),
    password: document.getElementById('password').value.trim(),
  };

  // Construction du FormData
  const formData = new FormData();
  for (let key in form) {
    if (form[key]) formData.append(key, form[key]);
  }

  const submitButton = document.getElementById('update-profile-btn');
  submitButton.disabled = true;
  submitButton.textContent = "Mise à jour...";

  fetch('/api/auth/updateProfile', {
    method: 'POST',
    body: formData,
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
    },
    credentials: 'include' // 🔥 Permet d'envoyer les cookies de session
  })
    .then(async res => {
      if (res.status === 401) {
        throw new Error("Non autorisé. Veuillez vous reconnecter.");
      }
      if (!res.ok) {
        const errorData = await res.json();
        throw new Error(errorData.message || "Erreur inconnue");
      }
      return res.json();
    })
    .then(data => {
      if (data.success) {
        showToast('Profil mis à jour avec succès', 'success');
        fetchUserInfo(); // 🔄 On rafraîchit les données
      } else if (data.errors) {
        showToast(Object.values(data.errors).join(' / '), 'error');
      } else {
        showToast('Erreur inconnue lors de la mise à jour', 'error');
      }
    })
    .catch(err => {
      console.error('Erreur lors de la mise à jour du profil:', err.message);
      showToast(err.message, 'error');
    })
    .finally(() => {
      submitButton.disabled = false;
      submitButton.textContent = "Mettre à jour le profil";
    });
}

/**
 * Déconnexion utilisateur
 */
function logout() {
  fetch('/api/auth/logout', {
    method: 'POST',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
    },
    credentials: 'include' // 🔥 Envoie les cookies pour bien détruire la session
  })
    .then(res => {
      if (!res.ok) throw new Error('Erreur lors de la déconnexion');
      localStorage.removeItem('user');
      window.location.href = '/login.html';
    })
    .catch(err => {
      console.error('Erreur logout():', err.message);
      showToast('Erreur lors de la déconnexion.', 'error');
    });
}
