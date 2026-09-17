# AI Tool Recommendation Portal

A clean, modern PHP + MySQL web app to discover, compare, filter, and review AI tools. Rebuilt with a **white, clean UI** accented with **dark green**, and all bugs from the original repository fixed.

---

## What was fixed / rebuilt

| Problem | Fix |
| --- | --- |
| All pages lived inside `api/` with Vercel-only routing | Pages moved to the project root; plain Apache/XAMPP/`php -S` friendly |
| "Similar Tools" button did nothing (`recommend.php` ignored GET) | `recommend.php` now accepts `?category_id=..&pricing=..` via GET |
| Admin "Add Tool" crashed when no category selected (FK violation on `0`) | `category_id` saved as `NULL` instead of `0` |
| Dark-space theme (`#0a0a0b`) | Full white / clean theme with dark-green accents (`assets/styles.css`) |
| Pages depended on the deprecated Tailwind CDN | Replaced with self-contained custom CSS — works fully offline |
| Star-rating picker & empty stars invisible on white | Recolored for the light theme |
| Seeded admin password hash was for `password`, not documented `Admin@1234` | Hash regenerated for `Admin@1234` |
| Deleting a review left the tool rating stale | Tool rating is recomputed (falls back to default 4.5) |

---

## Technology Stack

- **Frontend:** HTML5, CSS3, vanilla JavaScript (no framework/CDN dependency)
- **Backend:** PHP 8.x
- **Database:** MySQL / MariaDB

---

## Local Development Setup (XAMPP)

1. **Copy the project** into your web root:
   ```
   C:\xampp\htdocs\PHP_PROJECT
   ```

2. **Start XAMPP** → Apache and MySQL.

3. **Create and seed the database** (phpMyAdmin → SQL tab, or CLI):
   ```
   mysql -u root < database.sql
   ```
   This creates the `ai_tool_portal` database with 8 categories, 22 AI tools, and the admin account.

4. **Open the app:**
   ```
   http://localhost/PHP_PROJECT/
   ```

> Prefer the built-in server? Run `php -S localhost:8000 -t .` from this folder and open `http://localhost:8000/`.

---

## Database config

`config.php` reads environment variables with sensible local defaults:

| Variable | Default |
| --- | --- |
| `DB_HOST` | `localhost` |
| `DB_USER` | `root` |
| `DB_PASS` | *(empty)* |
| `DB_NAME` | `ai_tool_portal` |

---

## Admin account

| Field | Value |
| --- | --- |
| Email | `admin@gmail.com` |
| Password | `admin123` |

Regular users can register their own accounts from the **Get Started** button.

---

## Pages

- `index.php` — landing page with stats
- `tools.php` — browse, search, and filter tools by category
- `recommend.php` — 4-step wizard that matches tools to your needs
- `tool_detail.php` — tool profile + community reviews
- `login.php` / `register.php` — authentication
- `profile.php` — your profile and reviews (delete reviews here)
- `admin_dashboard.php` — admin-only CRUD for tools

---

## Notes

- Database schema & seed data live in `database.sql`.
- All user inputs are escaped; passwords hashed with `password_hash()`; admin routes protected by role checks.
