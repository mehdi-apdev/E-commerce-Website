## Project Structure: my-eshop

This document describes the folder and file structure of the `my-eshop` project, which follows a custom MVC architecture built with PHP, MySQL, Bootstrap, and AJAX.

---

### Root Overview

```
my-eshop/
├── app/
│   ├── Controllers/
│   ├── Core/
│   ├── Models/
│   ├── middleware
│   └── views/
│       ├── home/
│       └── products/
├── config/
├── database/
├── public/
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── images/
│   ├── uploads/
│   │   ├── products/
│   │   └── outfits/
│   └── index.php
├── docs/
│   └── structure.md (this file)
└── .htaccess
```

---

### Folder Descriptions

#### `app/`
Contains the core logic of the application structured by the MVC pattern.

- `controllers/` : Handles incoming requests, processes input, and interacts with models.
- `models/` : Contains classes that interact with the database using PDO.
- `views/` : HTML files grouped by feature or page type.
  - `home/` : Views for the homepage.
  - `products/` : Views related to products listing or details.
- `core/` : Core application logic (Router, base Controller class, helper functions).

#### `config/`
Contains configuration files and constants.

- `config.php` : Defines constants like BASE_URL, PUBLIC_PATH, APP_ROOT, UPLOADS_DIR, database settings, etc.

#### `database/`
Database scripts for setting up and populating the database.

- `eshop_clothing_schema.sql` : SQL file to recreate the entire database schema.
- `eshop_clothing_data.sql` (optional) : Test dataset for development purposes.

#### `public/`
Public-facing directory. Only this folder should be accessible by the web server.

- `index.php` : Entry point for all HTTP requests. Handles routing.
- `assets/` : Contains static frontend files like CSS, JS, and images.
  - `css/` : Bootstrap overrides or custom styles.
  - `js/` : Custom scripts, including AJAX handlers.
  - `images/` : Static UI images (not user uploads).
- `uploads/` : Stores images uploaded by admins/users.
  - `products/` : Product images, organized by `product_id`.
  - `outfits/` : Outfit images, organized similarly.

#### `docs/`
Documentation folder.

- `structure.md` : This file. Describes the architecture and structure.

#### `.htaccess`
(Optional) Apache configuration file used for clean URL rewriting.

---

### Notes
- The router is custom-built and resolves controllers dynamically from the URL.
- `BASE_URL`, `APP_ROOT`, and `PUBLIC_PATH` are defined in `config/config.php` and should be used instead of hardcoding paths.
- Views are meant to be purely presentational (no logic).
- AJAX is used via jQuery to handle asynchronous interactions (e.g., cart, filters).

---

This structure ensures clarity, maintainability, and adherence to MVC principles in a pure PHP environment.
