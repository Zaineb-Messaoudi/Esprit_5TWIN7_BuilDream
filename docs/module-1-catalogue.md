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
- `/catalog` — searchable and filterable equipment catalogue with **AI-powered search**:
  - **Natural Language Search**: Describe needs in plain language (e.g., "Battery for 2 days camping with fridge, under 20 TND")
  - **Semantic Search**: Toggle AI embeddings-based search for conceptually similar equipment
- `/equipment/{id}` — equipment details page with:
  - **AI Chatbot (RAG)**: Ask questions about the equipment; AI answers using only the equipment's data
  - **Solar Production Estimate**: Real-time Open-Meteo radiation forecast × panel efficiency
  - **CO₂ Avoided**: Estimated daily CO₂ avoidance with formula disclosure
- `/energy-calculator` — estimates daily watt-hours from a renter's appliances and recommends compatible published equipment with CO₂ avoided estimates
- `/equipment/compare` — compares up to three published listings using their `EnergyProfile` values, including CO₂ avoided for solar

### Owner Front Office

These routes require authentication, email verification, the selected owner role, and ownership checks:

- `/my/equipment` — list the authenticated owner's listings.
- `/my/equipment/new` — publish a listing with **AI assistance**:
  - **Description Generator**: Auto-generate professional French description from name, brand, model, category, and specs
  - **Price Suggestion**: AI-suggested daily rental price based on median of similar equipment + LLM explanation
  - **Auto-categorization**: AI-suggested category from name and description with confidence score
  - **Image Validation**: Verify uploaded images match equipment type using vision model (Ollama LLaVA for FREE local, or OpenAI gpt-4o)
- `/my/equipment/{equipment}` — view one owned listing.
- `/my/equipment/{equipment}/edit` — edit a listing.
- `PUT /my/equipment/{equipment}` — save changes.
- `DELETE /my/equipment/{equipment}` — delete a listing.

### Admin Back Office

- Administrators manage all categories and equipment through resource routes under `/admin/categories` and `/admin/equipment`.
- Category detail pages show the related equipment. Energy profiles have a dedicated nested CRUD under `/admin/equipment/{equipment}/energy-profile`.
- **AI Configuration** at `/admin/ai-settings`:
  - Provider selection: Ollama (FREE local), OpenAI, Anthropic
  - Model configuration: chat model, vision model, embedding model
  - Temperature and max tokens sliders
  - Test connection button
  - Ollama model management: pull new models, list local models
  - Quick start guide for Ollama setup

### Energy profile Back Office routes

- `GET /admin/equipment/{equipment}/energy-profile` — view the profile or its empty state.
- `GET /admin/equipment/{equipment}/energy-profile/create` — create a profile.
- `POST /admin/equipment/{equipment}/energy-profile` — save a profile.
- `GET /admin/equipment/{equipment}/energy-profile/show` — view the profile detail.
- `GET /admin/equipment/{equipment}/energy-profile/edit` — edit a profile.
- `PUT /admin/equipment/{equipment}/energy-profile` — save changes.
- `DELETE /admin/equipment/{equipment}/energy-profile` — remove the profile.

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
15. The energy calculator computes `quantity × watts × hours` and recommends available equipment whose `capacity_wh` and `power_watts` or `max_output` meet the estimate.
16. The comparison screen reads power, maximum output, capacity, voltage, efficiency, operating duration, and technology directly from `EnergyProfile`.

## AI Features (7 endpoints)

All AI features use the `AiService` with configurable provider:

### 1. Natural Language Search (`POST /api/ai/natural-language-search`)
Converts plain language queries to structured filters:
```
Input: "Battery for 2 days camping with fridge, under 20 TND"
Output: {category: "battery", max_price: 20, min_capacity: 2000, ...}
```

### 2. Description Generator (`POST /api/ai/generate-description`)
Generates professional French rental descriptions from equipment specs.

### 3. Price Suggestion (`POST /api/ai/suggest-price`)
Calculates median price of similar equipment from SQL, then uses LLM to explain the suggestion.

### 4. Auto-categorization (`POST /api/ai/auto-categorize`)
Suggests category from name + description with confidence score (0-100).

### 5. Chatbot RAG (`POST /api/ai/chatbot`)
Answers questions using ONLY provided equipment data as context. Responds in French.

### 6. Semantic Search (`POST /api/ai/semantic-search`)
Generates embeddings for query, falls back to keyword search if pgvector unavailable.

### 7. Image Validation (`POST /api/ai/validate-image`)
Vision model validates uploaded images match equipment name/category:
- **Ollama (FREE)**: LLaVA, LLaVA-Llama3, LLaVA-Mistral, BakLLaVA
- **OpenAI**: gpt-4o-mini, gpt-4o

## CO₂ Avoided Calculation

Displayed for solar equipment in:
- Equipment detail page (Solar insight section)
- Energy Calculator recommendations
- Equipment Compare page

**Formula:**
```
Production (Wh/day) = Radiation (kWh/m²/day) × Power (W) × Efficiency
CO₂ avoided (kg/day) = Production (Wh) × 0.42 / 1000
```

**Assumptions (clearly disclosed to users):**
- Grid emission factor: **0.42 kg CO₂/kWh** (Tunisia/France electricity mix average)
- Solar radiation: **Open-Meteo `shortwave_radiation_sum`** forecast (3-day average)
- Panel efficiency: From `EnergyProfile.efficiency` (default 20%)
- Production = Daily average radiation × Panel power × Efficiency

## Publication lifecycle

```text
Owner submits listing → pending_review → Admin publishes or rejects
                                      ↓
                                   published
```

The operational `status` (`available`, `maintenance`, `unavailable`, or `inactive`) remains separate from `approval_status`. Reservations should use both values when the other module is connected.

## Main implementation files

- Models: `app/Models/Category.php`, `Equipment.php`, `EnergyProfile.php`, `EquipmentImage.php`
- AI Service: `app/Services/AiService.php` (multi-provider: Ollama/OpenAI/Anthropic)
- AI Controller: `app/Http/Controllers/AiController.php` (7 endpoints)
- Requests: `app/Http/Requests/Owner` and `app/Http/Requests/Admin`
- Service: `app/Services/EquipmentCatalogService.php`
- Controllers: `app/Http/Controllers/OwnerEquipmentController.php`, `FrontController.php`, `CatalogueToolsController.php`, `Admin/*`
- Views: `resources/views/pages/front/owner-equipment`, `resources/views/pages/front/energy-calculator.blade.php`, `equipment.blade.php`, `catalog.blade.php`, `equipment-compare.blade.php`, `admin/ai-settings.blade.php`
- Database: `database/migrations` (includes `equipment_favorites`, `equipment_images`, vector column for pgvector), `database/factories`, `database/seeders/DatabaseSeeder.php`

The factories support relation-aware fixtures. Use `Equipment::factory()->withEnergyProfile()->create()` when a test needs a complete catalogue listing, or create a profile through `EnergyProfile::factory()->for($equipment)`.

## Local setup

```bash
php artisan migrate
php artisan db:seed
php artisan serve
```

**For AI features with Ollama (FREE local LLM):**
```bash
# Install Ollama
curl -fsSL https://ollama.com/install.sh | sh

# Pull models
ollama pull llama3.2          # Chat model
ollama pull nomic-embed-text  # Embeddings for semantic search
ollama pull llava             # Vision model for image validation

# Configure .env
AI_PROVIDER=ollama
AI_BASE_URL=http://localhost:11434
AI_MODEL=llama3.2
AI_VISION_MODEL=llava
AI_EMBEDDING_MODEL=nomic-embed-text
```

The seeded demo accounts are documented in `DatabaseSeeder.php`. The demo owner can publish and manage equipment; the demo admin can manage the complete catalogue.

## Testing

The catalogue feature tests cover:
- Category create, update, detail, and delete.
- Equipment create, detail, update, soft delete, and validation.
- EnergyProfile create, detail, update, delete, and factory relations.
- Owner access restrictions and category deletion protection.
- Public catalogue filtering, pagination, and seeded relations.
- **AI features**: All 7 endpoints require authentication, UI pages load with AI components.

Run the focused module tests with:

```bash
php artisan test --filter=AdminCatalogTest
php artisan test --filter=FrontOfficeTest
php artisan test --filter=OwnerEquipmentTest
php artisan test --filter=AiFeaturesTest
```

Run the complete suite with:

```bash
php artisan test --compact
```

## Rental module integration

The rentals table now references real catalogue equipment through a database foreign key. The Equipment model exposes the inverse rentals relation, while the Rental back office loads the equipment and category and supports searching by equipment name, brand, or model. Rental demo data is seeded after the catalogue, so every seeded rental points to an existing equipment listing.

The module is covered by:

- `tests/Feature/AdminCatalogTest.php`
- `tests/Feature/OwnerEquipmentTest.php`
- `tests/Feature/FrontOfficeTest.php`
- `tests/Feature/AiFeaturesTest.php`

Run the complete suite with:

```bash
php artisan test --compact
```