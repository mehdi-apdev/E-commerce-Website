// www/assets/js/common.js

// Charge header + footer + thèmes + panier
export function initLayout(callback = null) {
    // Injecte HEADER
    fetch('/partials/header.html')
      .then(r => r.text())
      .then(html => {
        const temp = document.createElement('div');
        temp.innerHTML = html;
        const newHeader = temp.querySelector('header');
        document.querySelector('header').replaceWith(newHeader);
  
        // Charge les scripts principaux ensuite
        loadScript('/assets/js/theme-toggle.js', () => {
          loadScript('/assets/js/header.js', () => {
            loadScript('/assets/js/cart.js', () => {
              updateCartBadge();
              if (typeof callback === 'function') callback();
            });
          });
        });
      });
  
    // Injecte FOOTER
    fetch('/partials/footer.html')
      .then(res => res.text())
      .then(html => {
        const temp = document.createElement('div');
        temp.innerHTML = html;
        const newFooter = temp.querySelector('footer');
        document.querySelector('footer').replaceWith(newFooter);
      });
  }
  
  function loadScript(src, callback) {
    const s = document.createElement('script');
    s.src = src;
    s.onload = callback || null;
    document.body.appendChild(s);
  }
  