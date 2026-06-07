# Getas Reality — Real Estate Website

**PHP 8 + MySQL + Bootstrap 5.3 | Mobile-First**

## Setup

### 1. Import Database
```bash
mysql -u root -p < config/install.sql
```

### 2. Configure `config/database.php`
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_user');
define('DB_PASS', 'your_password');
define('DB_NAME', 'getas_realty');
define('SITE_URL', 'http://localhost');
```

### 3. Admin Login
- URL: `/login.php`
- Email: `admin@getasreality.com`
- Password: `password`

Change password after first login!

---

## Available Apartments

| Type | Size   | Summit 72      | Kazanchis      |
|------|--------|----------------|----------------|
| 1BR  | 61 m²  | ETB 2,850,000  | ETB 2,700,000  |
| 1BR  | 65 m²  | ETB 3,100,000  | ETB 2,950,000  |
| 2BR  | 109 m² | ETB 4,950,000  | ETB 4,750,000  |
| 2BR  | 115 m² | ETB 5,200,000  | ETB 4,950,000  |
| 3BR  | 144 m² | ETB 6,800,000  | ETB 6,500,000  |
| 3BR  | 151 m² | ETB 7,200,000  | ETB 6,900,000  |

## Pages
- `/index.php` — Homepage (hero, search, listings, testimonials)
- `/properties.php` — All apartments with filters
- `/property.php?slug=...` — Apartment detail + inquiry form
- `/about.php` — About page
- `/contact.php` — Contact form
- `/login.php` — Admin login
- `/admin/` — Admin dashboard

## Admin Panel
- Dashboard with live stats
- Add/Edit/Delete apartments
- Inquiry management (view, reply, status)
- Testimonials management
- User management
- Site settings
