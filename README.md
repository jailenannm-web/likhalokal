# Vinzons LikhaLokal

Integrated tourism, e-commerce showcase, local business directory, and community engagement platform for **Vinzons, Camarines Norte** — *Tuklas, Kultura, Kabuhayan*.

## Features

- Public pages: home, tourism (attractions + maps placeholder), products, vendor profiles, business directory, local business landing, events, cultural info, about.
- **Guests**: browse only; messaging, reviews, and protected actions prompt login (modal + redirects).
- **Local users / tourists**: messaging sellers (poll-based chat), submit reviews (pending admin moderation), profile.
- **Sellers**: business application/profile, products (when approved), promotions, messages, review visibility.
- **Admin / tourism officer**: dashboard counts + chart, approve/reject/suspend businesses, manage attractions/events/announcements/cultural posts, moderate reviews, view messages log, manage users, simple engagement reports.
- **REST-style JSON APIs** under `/api/` for auth, businesses, products, attractions, events, announcements, cultural content, reviews, messages, dashboard stats, maps metadata, uploads.
- **MySQL** schema + seed accounts and demo data; **PDO** prepared statements; **CSRF** on forms; **password_hash** / **password_verify**; file uploads (JPG/PNG/WEBP, max 5MB) to `assets/uploads/`.

## Stack

- HTML5, CSS3, **Bootstrap 5**, JavaScript ES6+
- **PHP 8.x** (plain PHP + shared includes/middleware)
- **MySQL** (InnoDB)

## Installation (XAMPP)

1. Copy the `likhalokal` folder to `C:\xampp\htdocs\likhalokal` (or your Apache document root).
2. Start **Apache** and **MySQL** from the XAMPP control panel.
3. Open phpMyAdmin → create database `likhalokal_db` (or run the SQL file which creates it).
4. Import **`database/likhalokal.sql`**, then **`database/seed.sql`**.
5. Edit **`config/database.php`** if your MySQL user/password is not `root` / empty.
6. Edit **`config/app.php`** and set **`BASE_URL`**, **`ASSET_URL`**, **`ADMIN_URL`**, **`SELLER_URL`**, and **`USER_DASH_URL`** to match your install path (defaults assume `http://localhost/likhalokal/...`).
7. Set **`GOOGLE_MAPS_API_KEY`** in `config/app.php` (also exposed to JS as `window.LIKHA_GOOGLE_KEY`). Until a valid key is set, map blocks show a fallback link to Google Maps.
8. Open **`http://localhost/likhalokal/public/index.php`**.

## Default login accounts (after seed)

| Role       | Email                 | Password    |
|-----------|------------------------|------------|
| Admin     | admin@likhalokal.com   | password123 |
| Seller    | jannah@likhalokal.com  | password123 |
| Seller    | rhumens@likhalokal.com | password123 |
| Local user| user@likhalokal.com    | password123 |
| Local user| tourist@likhalokal.com | password123 |

## Folder structure (high level)

```
likhalokal/
  admin/           Tourism officer UI
  api/             JSON endpoints
  assets/          css/, js/, uploads/, images/
  config/          database.php, app.php
  database/        likhalokal.sql, seed.sql
  includes/        header, navbar, footer, functions
  middleware/      auth, role, csrf
  public/          Public site + login/register/logout
  seller/          Seller dashboard
  user/            Local user dashboard
  bootstrap.php
```

## Google Maps

Add your key in **`config/app.php`** as `GOOGLE_MAPS_API_KEY`. Enable **Maps JavaScript API** (and Geocoding if you extend server-side geocoding). `assets/js/maps.js` loads the script when the key is present.

## Troubleshooting

- **Blank page / 500**: enable `display_errors` in `php.ini` temporarily; check Apache `error.log`.
- **Database connection failed**: verify MySQL is running and `config/database.php` credentials.
- **404 on CSS/JS**: fix `ASSET_URL` in `config/app.php` so it matches the URL path to `assets/`.
- **Reviews or messages fail**: confirm seed ran; check foreign keys and that the user role matches (e.g. only `local_user` can create reviews via public forms/API as implemented).

## Screenshots

Add your UI screenshots under `assets/images/` and link them here for your documentation or capstone report.

---

Copyright notice in footer: *Talisay-Vinzons Team, BSIT 2B - AY 25-26* (per project brief).
