# SolarShare Module 1 — Catalogue and Energy Characteristics

## Scope

Module 1 manages the equipment catalogue owned by SolarShare members:

- `Category` groups listings such as batteries, solar panels, and wind turbines.
- `Equipment` is the listing visible in the public Front Office.
- `EnergyProfile` stores technical information such as power, voltage, capacity, efficiency, and technology.

The module is intentionally independent from reservations, payments, rentals, and maintenance. Those modules reference `equipment.id`.

## Data model

```text
Category 1 ──── N Equipment N ──── 1 User (owner)
                         │
                         └──────── 1 EnergyProfile
```

`Equipment` uses the existing `equipment` table. Its `owner_id` points to the user who published the listing. `EnergyProfile` is optional: an equipment form may be saved without technical details, and the profile is removed if all energy fields are cleared during an update.

## User-facing pages

### Public Front Office

- `/` — SolarShare landing page.
- `/catalog` — searchable and filterable equipment catalogue.
- `/equipment/{id}` — equipment details page.

The public catalogue reads Eloquent records through `App\Support\FrontDemo`. If the database has not been migrated or seeded yet, it uses the documented demo fallback so the template remains viewable.

### Owner Front Office

These routes require authentication, email verification, the selected owner role, and ownership checks:

- `/my/equipment` — list the authenticated owner's listings.
- `/my/equipment/new` — publish a listing.
- `/my/equipment/{equipment}` — view one owned listing.
- `/my/equipment/{equipment}/edit` — edit a listing.
- `PUT /my/equipment/{equipment}` — save changes.
- `DELETE /my/equipment/{equipment}` — delete a listing.

### Admin Back Office

Administrators can manage all categories and equipment through resource routes under `/admin/categories` and `/admin/equipment`.

## Validation and business rules

1. Only administrators can use Back Office catalogue CRUD.
2. Only owners can use the Owner Front Office workspace.
3. An owner can only view, edit, or delete equipment where `equipment.owner_id` matches the authenticated user.
4. Owners can select active categories only.
5. Equipment status is one of `available`, `unavailable`, `maintenance`, or `inactive`.
6. Equipment condition is one of `new`, `good`, `fair`, or `damaged`.
7. Category deletion is rejected when equipment still references that category.
8. Equipment and its energy profile are written in one database transaction.
9. The reservation module must check date overlap before confirming a booking; this module only provides the equipment record.
10. Category images are managed explicitly by administrators; equipment images accept a validated URL or a local upload.
11. Owner listings enter `pending_review` and become public only after an administrator sets `approval_status` to `published`.
12. Equipment deletion is soft deletion so future rentals, reservations, inspections, and maintenance records keep their references.
13. Category-aware validation requires the minimum useful energy values for batteries, solar panels, wind/hydro equipment, and charge controllers.
14. The public catalogue filters and paginates published equipment through Eloquent queries; the demo fallback remains available only when the database is empty.

## Publication lifecycle

```text
Owner submits listing → pending_review → Admin publishes or rejects
                                     ↓
                                  published
```

The operational `status` (`available`, `maintenance`, `unavailable`, or
`inactive`) remains separate from `approval_status`. Reservations should use
both values when the other module is connected.

## Main implementation files

- Models: `app/Models/Category.php`, `Equipment.php`, `EnergyProfile.php`
- Requests: `app/Http/Requests/Owner` and `app/Http/Requests/Admin`
- Service: `app/Services/EquipmentCatalogService.php`
- Controllers: `app/Http/Controllers/OwnerEquipmentController.php` and `app/Http/Controllers/Admin`
- Views: `resources/views/pages/front/owner-equipment` and `resources/views/pages/admin`
- Database: `database/migrations`, `database/factories`, and `database/seeders/DatabaseSeeder.php`

## Local setup

```bash
php artisan migrate
php artisan db:seed
php artisan serve
```

The seeded demo accounts are documented in `DatabaseSeeder.php`. The demo owner can publish and manage equipment; the demo admin can manage the complete catalogue.

## Testing

The module is covered by:

- `tests/Feature/AdminCatalogTest.php`
- `tests/Feature/OwnerEquipmentTest.php`
- `tests/Feature/FrontOfficeTest.php`

Run the complete suite with:

```bash
php artisan test --compact
```
