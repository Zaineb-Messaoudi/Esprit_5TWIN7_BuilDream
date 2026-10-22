# SolarShare - Complete Test Scenario

> **Flow:** Fresh install → Register 3 users (Buyer, Owner, Admin) → Complete all CRUD + Advanced + AI features in logical order

---

## 🚀 1. Environment Setup

```bash
# Clone & install
git clone https://github.com/Zaineb-Messaoudi/Esprit_5TWIN7_BuilDream.git
cd Esprit_5TWIN7_BuilDream

# Backend
composer install
cp .env.example .env
php artisan key:generate

# Configure .env (required for AI)
# AI_PROVIDER=gemini
# AI_GEMINI_KEY=your-key
# AI_CLOUDFLARE_ACCOUNT_ID=xxx
# AI_CLOUDFLARE_API_TOKEN=xxx
# AI_GROQ_KEY=xxx

# Database
php artisan migrate --seed

# Frontend
npm install
npm run build

# Start all services (Docker recommended)
docker compose up -d

# Or locally:
php artisan serve        # http://localhost:8000
php artisan reverb:start # WebSocket on :8080
npm run dev              # Vite HMR on :5173
```

**Verify:** Open http://localhost:8000/up → should return `OK`

---

## 👥 2. Create Three Personas

| Role | Email | Password | Purpose |
|------|-------|----------|---------|
| **Admin** | admin@solarshare.test | password123 | Platform oversight, analytics, user mgmt |
| **Owner** | owner@solarshare.test | password123 | Publish equipment, manage rentals |
| **Buyer** | buyer@solarshare.test | password123 | Rent equipment, use AI tools |

### Step-by-step:

#### A. Register Admin
1. Open http://localhost:8000/register
2. Fill: **Admin User** / admin@solarshare.test / password123
3. Submit → Redirected to `/choose-role`
4. Select **"Administrateur"** → Continue
4. Enable **2FA** (scan QR with Authenticator app) → Save recovery codes

#### B. Register Owner
1. Logout → Register: **Equipment Owner** / owner@solarshare.test / password123
2. Choose **"Propriétaire d'équipement"**
3. Complete profile: company name, address, phone
4. Enable 2FA

#### C. Register Buyer
1. Logout → Register: **Energy Buyer** / buyer@solarshare.test / password123
2. Choose **"Locataire / Acheteur d'énergie"**
2. Complete profile
3. Enable 2FA

---

## 🔐 3. Authentication Flow (All Users)

| Action | URL | Test |
|--------|-----|------|
| Login | `/login` | Email + password + 2FA code |
| Remember me | Checkbox | Close browser → reopen → still logged in |
| Password reset | `/forgot-password` | Request → check Mailpit (http://localhost:8025) → reset |
| Email verify | Mailpit link | Click → verified badge appears |
| Logout | Top-right dropdown | Session destroyed, redirected to home |
| Lock screen | `/lock-screen` | Auto-lock after idle → unlock with password |

---

## 👑 4. Admin Dashboard (Admin User)

**Login as Admin** → http://localhost:8000/dashboard

### 4.1 Dashboard Overview
- [ ] **E-commerce** tab: Revenue, orders, conversion
- [ ] **Stocks** tab: Equipment inventory
- [ ] **Finance** tab: Transactions, payouts
- [ ] **Sales** tab: Rental metrics
- [ ] **Logistics** tab: Active rentals, overdue

### 4.2 Equipment Management (CRUD)
| Action | Route | Test Data |
|--------|-------|-----------|
| List | `/admin/equipment` | Pagination, filters (status, category) |
| Create | `/admin/equipment/create` | Name: "Test Panel 400W", Category: Solar, Price: 15€/day, Location: Tunis, Specs: 400W, 24V |
| View | `/admin/equipment/{id}` | Shows energy profile, images, rental history |
| Edit | `/admin/equipment/{id}/edit` | Change price → 18€/day |
| Approve | POST `/admin/equipment/{id}/approve` | Owner equipment → Published |
| Reject | POST `/admin/equipment/{id}/reject` | With reason |
| Delete | DELETE `/admin/equipment/{id}` | Soft delete → restore from trash |

### 4.3 Categories & Energy Profiles
- [ ] Create category: "Batteries", "Inverters", "Panels"
- [ ] Add energy profile to equipment: Power 5000W, Capacity 10kWh, Efficiency 95%

### 4.4 Rentals & Contracts (Supervision Only)
| Route | Test |
|-------|------|
| `/admin/rentals` | Filter by status, export CSV |
| `/admin/rental-contracts` | View PDF, verify signatures |
| `/admin/rental-contracts/{id}/export-pdf` | Download contract |
| `/admin/rental-insights` | Dashboard: overdue, utilization, revenue |

### 4.5 Users & Roles
- [ ] `/admin/users` - List, filter by role, impersonate
- [ ] Assign roles: Promote buyer → owner
- [ ] View audit logs

### 4.6 AI Settings (Admin Only)
- [ ] `/admin/ai-settings` - Configure Gemini/Cloudflare/Groq keys
- [ ] Test connection buttons
- [ ] Pull Ollama models

---

## 🏢 5. Owner Portal (Owner User)

**Login as Owner** → http://localhost:8000/owner

### 5.1 My Equipment (Full CRUD)
| Action | Route | Test |
|--------|-------|------|
| List | `/my/equipment` | Table with status badges |
| Create | `/my/equipment/new` | "Solar Kit 5kW", 25€/day, photos, specs |
| View | `/my/equipment/{id}` | Preview public page |
| Edit | `/my/equipment/{id}/edit` | Update availability calendar |
| Delete | DELETE | Only if no active rentals |

### 5.2 Service Provider Profile
- [ ] `/owner/service-provider-profile` - Company info, certifications, coverage areas
- [ ] Upload logo, description, contact details

### 5.3 Rental Management
| Route | Actions |
|-------|---------|
| `/owner/reservations` | **Accept** / **Reject** pending requests |
| `/owner/rentals` | View active, mark "Equipment handed over" |
| `/owner/rental-contracts` | Sign digitally, download PDF |
| `/owner/rental-contracts/{id}/deposit/hold` | Stripe hold deposit |
| `/owner/rental-contracts/{id}/deposit/release` | Release after return |

### 5.4 Extensions & Disputes
- [ ] Handle extension requests (approve/reject)
- [ ] View cancellation requests with evidence

---

## 🛒 6. Buyer Marketplace (Buyer User)

**Login as Buyer** → http://localhost:8000/

### 6.1 Browse & Discover
| Feature | Test |
|---------|------|
| Homepage | Hero, categories, featured equipment |
| `/equipment` | Grid/list toggle, filters (category, price, location, dates) |
| `/equipment/{id}` | Gallery, specs, reviews, calendar availability |
| `/equipment/compare?ids=1,2` | Side-by-side specs table |
| `/equipment-map` | Leaflet map with markers |

### 6.2 Favorites & Cart
- [ ] Click ♡ on 3 equipment → `/profile/favorites` shows them
- [ ] Add to cart → `/cart` shows summary

### 6.3 Reservation Flow (Complete)
1. **Select dates** on equipment page → Check availability
2. **Click "Reserver"** → `/booking/reservations` (POST)
3. **Review summary** → Dates, price, deposit, insurance
4. **Pay** → Stripe test card `4242 4242 4242 4242` → Success
5. **Contract** → Auto-generated → Sign digitally (canvas)
6. **Confirmation** → Email + dashboard notification

### 6.4 My Rentals
| Route | Test |
|-------|------|
| `/my/reservations` | Upcoming, past, cancelled tabs |
| `/my/rentals` | Active rental → "Request extension" |
| `/my/rentals/{id}/return` | Mark returned, rate owner |

### 6.5 Cancellation & Refunds
- [ ] Cancel >48h → Full refund
- [ ] Cancel <24h → 50% refund
- [ ] Owner disputes → Admin mediation

---

## ⚡ 7. Advanced Features

### 7.1 Reservation Assistant (AI-Powered)
**URL:** `/reservation-assistant` (Buyer only)

| Step | Action | Expected |
|------|--------|----------|
| 1 | Describe need: *"Need 3kW for weekend camping near Tunis"* | AI analyzes intent |
| 2 | Click **"Analyser mes besoins"** | Structured requirements extracted |
| 3 | Click **"Recommander équipements"** | 3-5 matching equipment listed |
| 4 | Click **"Trouver artisans"** | Nearby installers with ratings |
| 5 | Click **"Confirmer et envoyer"** | Creates work request → notifications sent |

### 7.2 Energy Calculator
**URL:** `/energy-calculator`

| Test Case | Input | Expected Output |
|-----------|-------|-----------------|
| Basic | Fridge (1) + LED (4) + Phone (2) | ~1,640 Wh/day, panel + battery recommendations |
| With autonomy | + 2 days autonomy, 5h sun | Battery ≥ 3,280 Wh |
| Quick calc | `?calculate=1` | Pre-filled with demo appliances |
| Equipment match | After calc | Shows compatible equipment from catalogue |
| AI Explain | Click "Ask AI" | Opens `/ai/chat` with context |

### 7.3 Equipment Comparison
- [ ] Select 2-3 equipment → `/equipment/compare`
- [ ] Compare: Power, Capacity, Efficiency, Price/day, Technology

### 7.4 Installment Payments
- [ ] Enable on rental > 100€
- [ ] Stripe: 3x, 4x, 6x monthly
- [ ] Webhook handles failed payments

---

## 🤖 8. AI Studio (All Authenticated Users)

**URL:** `/ai` → Dashboard with usage stats

### 8.1 Text Generator (`/ai/text-generator`)
| Prompt | Expected |
|--------|----------|
| *"Write a solar equipment rental description for a 400W panel"* | Professional listing with specs, benefits, rental terms |
| *"Explain MPPT vs PWM in 100 words"* | Clear technical comparison |

### 8.2 Code Generator (`/ai/code-generator`)
| Prompt | Expected |
|--------|----------|
| *"Laravel migration for solar_equipment table with energy_profile relation"* | Valid migration code |

### 8.3 Image Generator (`/ai/image-generator`)
| Prompt | Expected |
|--------|----------|
| *"Solar panel on modern roof, sunny day, photorealistic"* | PNG/JPEG saved to storage, URL returned |

### 8.4 Video Generator (`/ai/video-generator`)
| Prompt | Expected |
|--------|----------|
| *"Drone shot of solar farm at sunrise"* | MP4 generated via Gemini |

### 8.5 Chat (`/ai/chat`)
| Test | Flow |
|------|------|
| Contextual | Ask: *"What equipment for 5kWh/day?"* → Uses catalogue RAG |
| Multi-turn | Follow-up: *"Cheaper option?"* → Remembers context |
| Fallback | If Gemini fails → Auto-switch to Groq → Response returned |

### 8.6 History & Settings
- [ ] `/ai/history` - Paginated, filter by tool, download media
- [ ] `/ai/settings` - Change model, temperature, max tokens
- [ ] `/ai/usage` - Stats: requests, tokens, success rate, charts

---

## 📊 9. Cross-Cutting Features

### 9.1 Notifications (Real-time via Reverb)
| Trigger | Channel | Test |
|---------|---------|------|
| New reservation | `owner.{id}` | Bell icon → dropdown → mark read |
| Payment success | `buyer.{id}` | Toast + email |
| Contract signed | Both | Signed PDF attached |
| AI generation complete | `user.{id}` | Link to result |

### 9.2 RTL / i18n
- [ ] Top-right dropdown → **Français** → UI flips RTL
- [ ] Top-right → **English** → LTR
- [ ] Persists in localStorage

### 9.3 Dark Mode
- [ ] Toggle in header → Persists
- [ ] System preference detected

### 9.4 PWA
- [ ] Chrome → Install SolarShare → Offline works
- [ ] Manifest: icons, screenshots, shortcuts

---

## 🔧 10. DevOps Verification

### 10.1 CI Pipeline (GitHub Actions)
```bash
# Trigger: Push to any branch
gh workflow run test.yml --ref <branch>

# Stages (parallel):
# 1. frontend-tests: npm ci → npm run build → eslint
# 2. php-tests: composer → migrate → pest --parallel
# 3. lint: pint --test
# 4. security: composer audit + npm audit
```

### 10.2 CD Pipeline
```bash
# Trigger: Push to main OR manual
gh workflow run "Deploy Production" --ref main

# Stages:
# 1. test.yml (reuse)
# 2. docker-build: Buildx → push to Docker Hub → Trivy scan
# 3. staging-smoke: Full stack → migrate → health check
# 4. deploy-production: WSL runner → local-compose.sh → rollback on fail
```

### 10.3 Local Docker Test
```bash
docker compose up -d
# Verify all healthy:
docker compose ps
# App: http://localhost:8000/up
# Reverb: ws://localhost:8080
# Mailpit: http://localhost:8025
# Meilisearch: http://localhost:7700
```

---

## ✅ 11. Complete Scenario Checklist

### Authentication & Roles
- [ ] Register 3 users with different roles
- [ ] 2FA setup + recovery codes work
- [ ] Email verification flow
- [ ] Password reset via Mailpit
- [ ] Role-based redirects (Admin→Back, Owner→Front Owner, Buyer→Front)

### Admin CRUD
- [ ] Equipment: Create → Approve → Edit → Delete
- [ ] Categories & Energy Profiles
- [ ] Rentals/Contracts: View only (supervision)
- [ ] Users: Impersonate, role change
- [ ] AI Settings configuration

### Owner CRUD
- [ ] Equipment: Full lifecycle (draft → published → rented → returned)
- [ ] Profile management
- [ ] Reservation decisions (accept/reject)
- [ ] Contract signing + deposit handling
- [ ] Extensions & cancellations

### Buyer Journey
- [ ] Browse → Filter → Compare → Favorite
- [ ] Reserve → Pay (Stripe) → Sign contract
- [ ] Active rental → Extend → Return → Rate
- [ ] Cancellation with refund rules

### Advanced
- [ ] Reservation Assistant: Describe → Analyze → Recommend → Find artisans → Submit
- [ ] Energy Calculator: Add appliances → Calculate → Match equipment → Ask AI
- [ ] Equipment Compare
- [ ] Installment payments

### AI Studio
- [ ] Text, Code, Image, Video, Chat generators
- [ ] Chat with catalogue RAG
- [ ] Multi-turn context
- [ ] Provider fallback (Gemini → Groq)
- [ ] History, Settings, Usage analytics

### Platform
- [ ] Real-time notifications
- [ ] RTL/French + Dark mode
- [ ] PWA installable
- [ ] Health endpoint `/up`

---

## 🎯 Demo Script (5-minute presentation)

1. **Login as Buyer** (30s) → Show dashboard, notifications
2. **Energy Calculator** (60s) → Add fridge+TV → Calculate → Match equipment → "Ask AI"
3. **Reserve Equipment** (60s) → Calendar → Stripe test → Contract sign
4. **Switch to Owner** (30s) → Accept reservation → Handover → Deposit hold
5. **Switch to Admin** (30s) → Dashboard insights → Equipment approval
6. **AI Studio** (60s) → Generate text + image + chat with RAG
7. **RTL + Dark + PWA** (30s) → Toggle all three

---

## 🐛 Known Edge Cases to Test

| Scenario | Expected |
|----------|----------|
| Double-booking same dates | 2nd user sees "Unavailable" |
| Expired Stripe payment | Reservation auto-cancelled |
| Owner rejects reservation | Buyer refunded automatically |
| AI provider down | Fallback to next provider |
| WebSocket disconnect | Auto-reconnect, notifications queued |
| RTL + long Arabic text | No layout break |
| Offline PWA | Cached pages work, sync on reconnect |

---

## 📁 Key URLs Reference

| Feature | URL |
|---------|-----|
| Home | `/` |
| Login | `/login` |
| Register | `/register` |
| Role Setup | `/choose-role` |
| Admin Dashboard | `/dashboard` |
| Owner Portal | `/owner` |
| Buyer Marketplace | `/` |
| Equipment List | `/equipment` |
| Equipment Detail | `/equipment/{id}` |
| Compare | `/equipment/compare` |
| Map | `/equipment-map` |
| Energy Calculator | `/energy-calculator` |
| Reservation Assistant | `/reservation-assistant` |
| AI Studio | `/ai` |
| AI Chat | `/ai/chat` |
| AI History | `/ai/history` |
| Profile | `/profile` |
| Settings | `/settings` |
| Health Check | `/up` |
| Mailpit | `http://localhost:8025` |

---

## 🔑 Test Credentials (After Seeding)

```bash
# If using seeders:
Admin:    admin@solarshare.test    / password
Owner:    owner@solarshare.test    / password
Buyer:    buyer@solarshare.test    / password
# All with 2FA disabled for quick testing
```

---

*Generated for SolarShare v1.1.2 — Laravel 12 + Tailwind 4 + Alpine.js + AI Studio*