# BD3 Backend API

Laravel 13 REST API for the BD3 luxury eyewear storefront. Serves product catalog, PDP gallery/copy helpers, and cart/checkout stubs that match the React frontend data shape.

## Requirements

- PHP 8.3+
- Composer
- SQLite (default) — enable `pdo_sqlite` and `sqlite3` in `php.ini` on Windows if missing

## Setup

```bash
cd D:\work\BD3\Backend
composer install
copy .env.example .env   # if .env does not exist
php artisan key:generate
type nul > database\database.sqlite   # Windows — or touch database/database.sqlite on macOS/Linux
php artisan migrate --seed
php artisan serve
```

API base URL: `http://localhost:8000/api`

## Environment

| Variable | Description |
|----------|-------------|
| `APP_URL` | Backend URL (default `http://localhost:8000`) |
| `CORS_ALLOWED_ORIGINS` | Comma-separated frontend origins |
| `FRONTEND_URL` | React storefront URL (admin dashboard link) |
| `DB_CONNECTION` | `sqlite` (default) or `mysql` / `pgsql` |

## API Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/health` | Service health check |
| GET | `/api/catalog` | Filter options (shapes, series, lenses) |
| GET | `/api/products` | Product list (`?shape=`, `?series=`, `?lens=`, `?min=`, `?max=`, `?sort=price-asc\|price-desc\|name`) |
| GET | `/api/products/{slug}` | Single product |
| GET | `/api/products/{slug}/gallery?color=black` | PDP gallery images for a color |
| GET | `/api/products/{slug}/copy` | Tagline, description, specs |
| POST | `/api/cart/items` | Add/update cart line (`productId`, `colorId`, `quantity`, optional `cartId`) |
| GET | `/api/cart/{cartId}` | Cart summary |
| POST | `/api/checkout/pay` | Checkout stub (`cartId`) |

### Product JSON shape

Matches the frontend `Product` type:

```json
{
  "id": "axiom-midnight",
  "name": "Axiom Midnight",
  "price": 890,
  "shape": "aviator",
  "series": "axiom",
  "lens": "polarized",
  "image": "/images/products/swatch-aviator-black.webp",
  "colors": [
    { "id": "black", "label": "Black", "hex": "#1A1A1A", "image": "..." }
  ],
  "rating": 4.9,
  "reviews": 42,
  "isNew": true,
  "createdAt": "2026-06-01"
}
```

## Admin dashboard (Laravel)

Blade admin panel at **`http://localhost:8000/admin`** (session auth, not React).

| URL | Purpose |
|-----|---------|
| `/admin/login` | Sign in |
| `/admin` | Dashboard stats |
| `/admin/products` | Product list + CRUD |
| `/admin/colors` | Color list + CRUD |

**Default admin credentials** (change in production):

| Email | Password |
|-------|----------|
| `admin@bd3.ae` | `bd3-admin-2026` |

The `/api/admin/*` JSON endpoints remain available for Postman and programmatic access (Sanctum token).

## Postman

Import:

- `postman/BD3-API.postman_collection.json`
- `postman/BD3-Local.postman_environment.json`

Run **Admin → Login** first; the collection saves `adminToken` automatically.

## Frontend integration

Point the React app at this API:

```env
VITE_API_URL=http://localhost:8000/api
```

Product images remain on the frontend static host (`/images/...` paths).

## Development

```bash
php artisan migrate:fresh --seed   # reset catalog
php artisan test
composer run dev                   # Laravel dev server + queue + vite (if configured)
```

## License

MIT
