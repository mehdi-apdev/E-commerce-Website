import { initLayout, showToast } from './common.js';

document.addEventListener('DOMContentLoaded', () => {
  initLayout(() => {
    fetchUserInfo();
    fetchOrders();
    fetchAddresses(); // 🚀 Ajout de la récupération des adresses
    
    document.getElementById('update-profile-btn')?.addEventListener('click', updateProfile);
    document.getElementById('logout-btn')?.addEventListener('click', logout);
    document.getElementById('add-address-btn')?.addEventListener('click', openAddAddressModal);
  });
});

/**
 * 🔄 Récupération des adresses de l'utilisateur
 */
function fetchAddresses() {
  fetch('/api/profile/addresses', {
    method: 'GET',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
    },
    credentials: 'include'
  })
    .then(res => res.json())
    .then(data => {
      const addressContainer = document.getElementById('address-list');
      if (data.success && data.addresses.length > 0) {
        addressContainer.innerHTML = '';
        data.addresses.forEach(address => {
          addressContainer.innerHTML += generateAddressCard(address);
        });
      } else {
        addressContainer.innerHTML = `<p class="text-gray-600 dark:text-gray-400">Aucune adresse enregistrée.</p>`;
      }
    })
    .catch(err => {
      console.error('Erreur lors de la récupération des adresses:', err.message);
      showToast('Erreur lors de la récupération des adresses.', 'error');
    });
}

/**
 * 🎯 Génère une carte pour une adresse
 */
function generateAddressCard(address) {
  return `
    <div class="bg-white dark:bg-zinc-800 p-4 rounded shadow mb-4">
      <p class="font-semibold mb-1">${address.recipient_name}</p>
      <p>${address.street} ${address.number}, ${address.postal_code} ${address.city}</p>
      <p>${address.region}, ${address.country}</p>

      <div class="flex justify-start mt-4 gap-4">
        <button onclick="editAddress(${address.address_id})" 
                class="px-4 py-1 text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary">
          Modifier
        </button>
        <button onclick="deleteAddress(${address.address_id})" 
                class="px-4 py-1 text-sm font-medium text-white bg-red-500 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-400">
          Supprimer
        </button>
      </div>
    </div>
  `;
}

/**
 * 🆕 Ajouter une adresse
 */
function openAddAddressModal() {
  // Ouvrir une modal (tu peux intégrer un formulaire ici)
  const formData = {
    recipient_name: prompt("Nom du destinataire"),
    street: prompt("Rue"),
    number: prompt("Numéro"),
    postal_code: prompt("Code postal"),
    city: prompt("Ville"),
    region: prompt("Région"),
    country: prompt("Pays"),
    is_default: confirm("Définir comme adresse principale ?")
  };

  fetch('/api/profile/addresses', {
    method: 'POST',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'Content-Type': 'application/json'
    },
    credentials: 'include',
    body: JSON.stringify(formData)
  })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        showToast('Adresse ajoutée avec succès', 'success');
        fetchAddresses(); // Rafraîchir la liste
      } else {
        showToast('Erreur lors de l\'ajout de l\'adresse.', 'error');
      }
    })
    .catch(err => {
      console.error('Erreur lors de l\'ajout de l\'adresse:', err.message);
      showToast('Erreur lors de l\'ajout de l\'adresse.', 'error');
    });
}

/**
 * ✏️ Modifier une adresse
 */
window.editAddress = function (addressId) {
  // Récupérer les données actuelles et les mettre dans des prompts
  const recipient_name = prompt("Nom du destinataire");
  const street = prompt("Rue");
  const number = prompt("Numéro");
  const postal_code = prompt("Code postal");
  const city = prompt("Ville");
  const region = prompt("Région");
  const country = prompt("Pays");
  const is_default = confirm("Définir comme adresse principale ?");

  const formData = {
    recipient_name,
    street,
    number,
    postal_code,
    city,
    region,
    country,
    is_default
  };

  fetch(`/api/profile/addresses/${addressId}`, {
    method: 'PUT',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'Content-Type': 'application/json'
    },
    credentials: 'include',
    body: JSON.stringify(formData)
  })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        showToast('Adresse modifiée avec succès', 'success');
        fetchAddresses();
      } else {
        showToast('Erreur lors de la modification de l\'adresse.', 'error');
      }
    })
    .catch(err => {
      console.error('Erreur lors de la modification de l\'adresse:', err.message);
      showToast('Erreur lors de la modification de l\'adresse.', 'error');
    });
}

/**
 * ❌ Supprimer une adresse
 */
window.deleteAddress = function (addressId) {
  if (confirm("Êtes-vous sûr de vouloir supprimer cette adresse ?")) {
    fetch(`/api/profile/addresses/${addressId}`, {
      method: 'DELETE',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'include'
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          showToast('Adresse supprimée avec succès', 'success');
          fetchAddresses();
        } else {
          showToast('Erreur lors de la suppression de l\'adresse.', 'error');
        }
      })
      .catch(err => {
        console.error('Erreur lors de la suppression de l\'adresse:', err.message);
        showToast('Erreur lors de la suppression de l\'adresse.', 'error');
      });
  }
};

/**
 * Récupère les informations de l'utilisateur connecté
 */
function fetchUserInfo() {
  fetch('/api/auth/getProfile', {
    method: 'GET',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
    },
    credentials: 'include'
  })
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
      console.error('Erreur lors de la récupération du profil:', err.message);
      showToast('Erreur lors de la récupération des informations.', 'error');
    });
}

/**
 * 🔄 Récupération des commandes de l'utilisateur
 */
function fetchOrders() {
  fetch('/api/profile/orders', {
    method: 'GET',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
    },
    credentials: 'include'
  })
    .then(res => res.json())
    .then(data => {
      const ordersContainer = document.getElementById('orders-list');
      if (data.success && data.orders.length > 0) {
        ordersContainer.innerHTML = '';
        data.orders.forEach(order => {
          ordersContainer.innerHTML += generateOrderCard(order);
        });
      } else {
        ordersContainer.innerHTML = `<p class="text-gray-600 dark:text-gray-400">Aucune commande trouvée.</p>`;
      }
    })
    .catch(err => {
      console.error('Erreur lors de la récupération des commandes:', err.message);
      showToast('Erreur lors de la récupération des commandes.', 'error');
    });
}

/**
 * 🛍️ Génération de la carte de commande
 */
function generateOrderCard(order) {
  const itemsHtml = order.items.map(item => `
    <div class="flex items-center space-x-4">
      <img src="/uploads/products/${item.product_id}/${item.product_image.trim()}" 
           alt="${item.product_name}" 
           class="w-16 h-16 object-cover rounded">
      <div>
        <p class="font-semibold">${item.product_name}</p>
        <p class="text-sm text-gray-500">Taille : ${item.size_label}</p>
        <p class="text-sm text-gray-500">Quantité : ${item.quantity}</p>
      </div>
      <div class="ml-auto font-semibold">${parseFloat(item.price).toFixed(2)} €</div>
    </div>
  `).join('');

  return `
    <div class="bg-white dark:bg-zinc-800 p-4 rounded shadow mb-4">
      <div class="flex justify-between items-center">
        <div>
          <p class="font-semibold">Commande #${order.order_id}</p>
          <p class="text-sm text-gray-500">${new Date(order.created_at).toLocaleDateString()}</p>
        </div>
        <div class="text-primary font-bold">${parseFloat(order.total_amount).toFixed(2)} €</div>
      </div>
      <div class="mt-4 space-y-2">
        ${itemsHtml}
      </div>
      <div class="mt-4 text-right">
        <span class="px-3 py-1 rounded-full text-sm ${order.status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'}">
          ${order.status === 'completed' ? 'Complétée' : 'En cours'}
        </span>
      </div>
    </div>
  `;
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
    credentials: 'include'
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
        fetchUserInfo();
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
    credentials: 'include'
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
