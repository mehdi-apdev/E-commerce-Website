// www/assets/js/checkout.js
import { initLayout, showToast } from './common.js';
import { getCart, clearCart } from './cart.js';

async function prefillUserData() {
  try {
    const res = await fetch('/api/auth/getProfile', {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });

    const data = await res.json();
    if (data.success && data.user) {
      const user = data.user;

      document.querySelector('[name="first_name"]').value = user.first_name || '';
      document.querySelector('[name="last_name"]').value = user.last_name || '';
      document.querySelector('[name="email"]').value = user.email || '';
      document.querySelector('[name="phone"]').value = user.phone || '';
    }
  } catch (err) {
    console.warn('Utilisateur non connecté ou erreur /getProfile');
  }
}


document.addEventListener('DOMContentLoaded', () => {
  initLayout(() => {
    renderCartSummary();
  });
  prefillUserData();
  document.getElementById('checkoutForm')?.addEventListener('submit', handleCheckoutSubmit);
});

/** Affiche le contenu du panier dans le résumé */
function renderCartSummary() {
  const cart = getCart();
  const container = document.getElementById('cart-summary');
  const totalDisplay = document.getElementById('total-price');
  if (!container || !totalDisplay) return;

  if (cart.length === 0) {
    container.innerHTML = `<p class="text-gray-500 dark:text-gray-400">Votre panier est vide.</p>`;
    totalDisplay.textContent = '0 €';
    return;
  }

  fetch('/api/products', {
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
    .then(res => res.json())
    .then(data => {
      const allProducts = data.products || [];
      let total = 0;
      container.innerHTML = '';

      getCart().forEach(item => {
        const product = allProducts.find(p => p.product_id === item.product_id);
        if (!product) return;

        const price = parseFloat(product.price) * item.quantity;
        total += price;

        container.innerHTML += `
          <div class="py-3 flex justify-between items-center">
            <div>
              <p class="font-medium">${product.name}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Quantité : ${item.quantity}</p>
            </div>
            <p class="font-bold">${price.toFixed(2)} €</p>
          </div>
        `;
      });

      totalDisplay.textContent = `${total.toFixed(2)} €`;
    })
    .catch(err => {
      console.error("Erreur chargement produits :", err);
      container.innerHTML = `<p class="text-red-500">Erreur lors de l'affichage du panier.</p>`;
    });
}

/** Gère la soumission du formulaire */
async function handleCheckoutSubmit(e) {
  e.preventDefault();

  const form = e.target;
  const submitBtn = form.querySelector('button[type="submit"]');

  submitBtn.disabled = true;
  submitBtn.textContent = "Traitement...";

  if (!form.checkValidity()) {
    form.reportValidity();
    submitBtn.disabled = false;
    submitBtn.textContent = "Valider la commande";
    return;
  }

  const formData = new FormData(form);

  const userInfo = {
    first_name: formData.get('first_name'),
    last_name: formData.get('last_name'),
    email: formData.get('email'),
    phone: formData.get('phone'),
    street: formData.get('street'),
    number: parseInt(formData.get('number'), 10),
    postal_code: formData.get('postal_code'),
    city: formData.get('city'),
    region: formData.get('region'),
    country: formData.get('country'),
  };

  const cart = getCart();
  if (cart.length === 0) {
    showToast("Votre panier est vide.", 'error');
    submitBtn.disabled = false;
    submitBtn.textContent = "Valider la commande";
    return;
  }

  const payload = { user: userInfo, cart };

  try {
    const res = await fetch('/api/checkout', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'include',
      body: JSON.stringify(payload)
    });
    

    const data = await res.json();

    if (data.success) {
      sessionStorage.setItem('toast', '🎉 Commande validée avec succès !');
      clearCart();
      sessionStorage.setItem('last_order', JSON.stringify({
        id: data.order_id,
        total: data.total_amount.toFixed(2),
        address: `${userInfo.street} ${userInfo.number}, ${userInfo.postal_code} ${userInfo.city}`
      }));
      window.location.href = '/checkout-success.html';      
    } else {
      showToast((data.message || 'Erreur inconnue'), 'error');
    }
  } catch (err) {
    console.error('Erreur checkout :', err);
    showToast("Erreur réseau. Veuillez réessayer.", 'error');
  } finally {
    submitBtn.disabled = false;
    submitBtn.textContent = "Valider la commande";
  }
}

// 📍 Remplissage automatique de l'adresse via Geoapify
const GEOAPIFY_KEY = '9cd7983aed0b42e4abbbbdfe73582bf4';

document.getElementById('detect-location')?.addEventListener('click', () => {
  if (!navigator.geolocation) {
    showToast("La géolocalisation n'est pas prise en charge par votre navigateur.", 'error');
    return;
  }

  navigator.geolocation.getCurrentPosition(async position => {
    const { latitude, longitude } = position.coords;

    try {
      const res = await fetch(`https://api.geoapify.com/v1/geocode/reverse?lat=${latitude}&lon=${longitude}&apiKey=${GEOAPIFY_KEY}`);
      const data = await res.json();
      const info = data.features[0]?.properties;

      if (!info) {
        showToast("Impossible de localiser votre adresse.", 'error');
        return;
      }

      // Remplissage automatique des champs
      document.querySelector('[name="street"]').value = info.street || '';
      document.querySelector('[name="number"]').value = info.housenumber || '';
      document.querySelector('[name="postal_code"]').value = info.postcode || '';
      document.querySelector('[name="city"]').value = info.city || info.town || info.village || '';
      document.querySelector('[name="region"]').value = info.state || '';
      document.querySelector('[name="country"]').value = info.country || '';

      showToast("📍 Adresse détectée automatiquement !");
    } catch (err) {
      console.error('Erreur Geoapify :', err);
      showToast("Erreur lors de la détection de l'adresse.", 'error');
    }
  }, () => {
    showToast("Permission de localisation refusée.", 'error');
  });
});


