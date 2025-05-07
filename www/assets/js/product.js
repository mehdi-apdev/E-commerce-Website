import { initLayout, showToast } from './common.js';

document.addEventListener('DOMContentLoaded', () => {
    initLayout(loadProductDetails);
});

async function loadProductDetails() {
    const productId = window.location.pathname.split('/')[2];

    if (!productId) {
        showToast('Produit non trouvé !', 'error');
        window.location.href = '/404.html';
        return;
    }

    try {
        const response = await fetch(`/api/products/${productId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        if (!response.ok) {
            showToast('Produit non disponible.', 'error');
            window.location.href = '/not-available.html';
            return;
        }

        const { product } = await response.json();

        if (!product) {
            showToast('Produit introuvable.', 'error');
            window.location.href = '/not-available.html';
            return;
        }

        // ✅ Remplissage des informations du produit
        document.getElementById('product-name').textContent = product.name;
        document.getElementById('product-price').textContent = `${product.price} €`;
        document.getElementById('product-description').textContent = product.description;

        // ✅ Chargement du carousel
        const slidesContainer = document.getElementById('carousel-slides');
        slidesContainer.innerHTML = '';

        if (product.images && product.images.length > 0) {
            product.images.forEach(image => {
                const li = document.createElement('li');
                li.classList.add("glide__slide");
                li.innerHTML = `
                    <img src="/uploads/products/${productId}/${image.filename}" 
                         class="product-image" 
                         alt="${product.name}">
                `;
                slidesContainer.appendChild(li);
            });

            // Initialisation du carousel
            new Glide('.glide', {
                type: 'carousel',
                startAt: 0,
                perView: 1,
                gap: 10,
                autoplay: 4000
            }).mount();
        }

        // ✅ Chargement des tailles depuis l'API
        const sizeSelector = document.getElementById('size-selector');
        sizeSelector.innerHTML = '<option disabled selected>Choisir une taille</option>';

        if (product.sizes && product.sizes.length > 0) {
            product.sizes.forEach(size => {
                const option = document.createElement('option');
                option.value = size.size_label;
                option.textContent = `${size.size_label} - ${size.size_description} (Stock: ${size.stock_qty})`;
                sizeSelector.appendChild(option);
            });
        } else {
            showToast('Aucune taille disponible pour ce produit.', 'error');
            sizeSelector.setAttribute('disabled', 'disabled');
        }

    } catch (err) {
        showToast('Erreur de connexion avec le serveur.', 'error');
        window.location.href = '/not-available.html';
    }
}
