# my-eshop

A custom-built PHP e-commerce site for traditional Algerian men's clothing, targeting the diaspora in Belgium and beyond.

---

## 🔧 Build & Development Commands

- Required tools:
  - XAMPP (or any stack with PHP 8+ and MySQL)
  - phpMyAdmin (included with XAMPP)
  - Browser (Chrome, Firefox, etc.)

- Folder placement:
  - Project should be located inside HTDOCS
    Example: C:\xampp\htdocs\my-eshop

- Start Apache and MySQL via XAMPP control panel

- Access the site at:
  http://localhost/my-eshop/public/

- Database setup:
  1. Open phpMyAdmin: http://localhost/phpmyadmin
  2. Import `/database/eshop_clothing_schema.sql`
  3. DB name: `eshop_clothing`
  4. MySQL credentials (default):
     - host: localhost
     - user: root
     - password: (empty)

- Configurations in `/config/config.php`:
```php
define('BASE_URL', '/my-eshop/public');
define('APP_ROOT', dirname(__DIR__) . '/app');
define('PUBLIC_PATH', dirname(__DIR__) . '/public');
define('UPLOADS_DIR', 'uploads/products');
```

- Required folders:
  - /public/uploads/products/
  - /public/uploads/outfits/

---

## 🧪 Testing Guidelines

- Manual browser testing
- Validate:
  - Homepage loads properly
  - Product list appears from database
  - Images load from: /uploads/products/{product_id}/{filename}
  - Custom MVC routing is functional
  - AJAX interactions (e.g., cart updates) work and return valid JSON
- Dummy data can be inserted from optional `/database/eshop_clothing_data.sql`

---

## 🧼 Code Style & Guidelines

- PHP 8+, PDO for DB
- Custom MVC architecture
- All includes use constants:
  - `APP_ROOT`, `PUBLIC_PATH`, `BASE_URL`, `UPLOADS_DIR`
- Views use Bootstrap 5 for layout
- No logic inside views
- Controllers = routing logic
- Models = DB logic
- All comments in English
- Use `url()` helper for routing
- AJAX is mandatory for interactive UI (filters, cart, etc.)
- JSON is the default format for dynamic responses

---

## 📚 Documentation Guidelines

- All functions/classes must use PHPDoc-style comments:
```php
/**
 * Description
 * @param type $param
 * @return type
 */
```

- Use centralized constants from config.php
- No hardcoded paths or strings
- Public image paths = BASE_URL + UPLOADS_DIR + product_id + filename
- Inline comments + documentation in `/docs/structure.md`

---

## 📁 Project Structure

See [docs/structure.md](docs/structure.md) for a full breakdown of folders, logic, and responsibilities.
