<?php

/*
 * Front Office template content (static, no backend).
 * Each page = path + title + list of sections rendered by <x-front.section>.
 * Section types: hero, features, steps, stats, faq, plans, quotes, cta,
 * text, form, table, equipment.
 */

$cta = ['type' => 'cta', 'title' => 'Ready to power your next adventure?', 'text' => 'Join SolarShare and rent clean energy by the day, or earn from the gear you already own.', 'label' => 'Create your free account', 'href' => 'register'];

$hero = fn (string $eyebrow, string $title, string $sub, array $extra = []) => ['type' => 'hero', 'eyebrow' => $eyebrow, 'title' => $title, 'sub' => $sub] + $extra;

$faq = ['type' => 'faq', 'title' => 'Frequently asked questions', 'items' => [
    ['q' => 'How is renting intended to work?', 'a' => 'The planned flow is to choose equipment and dates, review the terms, then confirm. This frontend is a visual prototype: it does not create reservations, process payments or issue invoices.'],
    ['q' => 'Can two people request the same dates?', 'a' => 'Preventing overlapping reservations is a core product requirement. Availability checks are not connected in this frontend preview.'],
    ['q' => 'Can I keep the equipment longer?', 'a' => 'Rental extensions are part of the planned product flow. Requests and owner decisions are not saved in this visual prototype.'],
    ['q' => 'What happens if equipment is damaged?', 'a' => 'The intended workflow includes a return inspection and maintenance report. This preview does not record inspections or service work.'],
    ['q' => 'How do owners get paid?', 'a' => 'Payouts are not active. Any fees shown on the Plans page are proposed examples, not real charges.'],
    ['q' => 'Which payment methods are accepted?', 'a' => 'Payments are not enabled in this prototype, so no payment method is currently accepted.'],
]];

$plans = ['type' => 'plans', 'id' => 'pricing', 'title' => 'A fair exchange, by design.', 'intro' => 'Pricing concept for the SolarShare prototype. Fees and plans shown here are illustrative, not active charges.', 'items' => [
    ['name' => 'Renter', 'price' => '0 TND', 'period' => 'proposed', 'tagline' => 'For campers, makers and event planners.', 'features' => ['Browse the example catalog', 'Availability checks (planned)', 'Illustrative service fee: 8%', 'Invoice and contract flow (planned)'], 'label' => 'Explore the catalog', 'href' => 'front.catalog'],
    ['name' => 'Owner', 'price' => '0 TND', 'period' => 'proposed', 'tagline' => 'Put idle equipment to work.', 'features' => ['Equipment listing flow (planned)', 'Illustrative commission: 10%', 'Inspection records (planned)', 'Extension requests (planned)'], 'label' => 'See owner features', 'href' => 'front.owners', 'highlight' => true],
    ['name' => 'Pro owner', 'price' => '29 TND', 'period' => '/ month · proposed', 'tagline' => 'A concept for fleets and small businesses.', 'features' => ['Owner features (proposed)', 'Illustrative commission: 5%', 'Priority placement concept', 'Technical report templates'], 'label' => 'Talk to us', 'href' => 'front.contact'],
]];

$guarantees = ['type' => 'features', 'title' => 'Designed around trust', 'items' => [
    ['icon' => 'calendar', 'title' => 'One calendar per item', 'text' => 'The planned booking flow checks date conflicts before a request can be confirmed.'],
    ['icon' => 'wallet', 'title' => 'Clear costs', 'text' => 'The product concept puts price, deposit and payment details before confirmation.'],
    ['icon' => 'document', 'title' => 'Written agreements', 'text' => 'Rental terms and equipment condition are part of the planned hand-off.'],
    ['icon' => 'tool', 'title' => 'Care after use', 'text' => 'The product concept includes inspections and maintenance records between rentals.'],
]];

$steps = ['type' => 'steps', 'id' => 'how-it-works', 'title' => 'How SolarShare works', 'items' => [
    ['title' => 'Publish', 'text' => 'The planned owner flow captures category, daily price and energy specifications.'],
    ['title' => 'Search & request', 'text' => 'Explore example listings and select dates. Availability checks are planned, not active.'],
    ['title' => 'Review the terms', 'text' => 'Payment, invoices and rental contracts belong in the proposed booking flow.'],
    ['title' => 'Use & extend', 'text' => 'The concept includes a rental period and a request path for extra time.'],
    ['title' => 'Return & inspect', 'text' => 'The proposed process records condition and routes damaged equipment to maintenance.'],
]];

$rentals = [['RNT-2031', 'Portable battery 1000 Wh', '9 – 11 Oct 2026', '54 TND', ['text' => 'Upcoming', 'tone' => 'info']], ['RNT-2024', 'Foldable solar panel 200 W', '2 – 4 Oct 2026', '27 TND', ['text' => 'Active', 'tone' => 'success']], ['RNT-2009', 'Compact battery 500 Wh', '12 – 14 Sep 2026', '30 TND', ['text' => 'Returned', 'tone' => 'success']]];

return [
    'nav' => ['how-it-works' => 'How it works', 'plans' => 'Plans', 'owners' => 'For owners', 'help' => 'Help'],

    'account' => ['my-dashboard' => 'Dashboard', 'owner-dashboard' => 'Owner dashboard', 'my-reservations' => 'Reservations', 'my-rentals' => 'Rentals', 'my-contract' => 'Contract', 'my-extensions' => 'Extensions', 'my-equipment' => 'My equipment', 'my-publish' => 'Publish equipment', 'my-inspections' => 'Inspections', 'my-notifications' => 'Notifications'],

    'footer' => [
        'Platform' => ['front.catalog' => 'Catalog', 'front.how-it-works' => 'How it works', 'front.plans' => 'Plans', 'front.safety' => 'Trust & safety'],
        'Owners' => ['front.owners' => 'Become an owner', 'front.my-publish' => 'Publish equipment', 'front.my-equipment' => 'My equipment'],
        'Company' => ['front.about' => 'About', 'front.contact' => 'Contact', 'front.help' => 'Help center'],
        'Legal' => ['front.terms' => 'Terms of use', 'front.privacy' => 'Privacy policy'],
    ],

    'home' => [
        ['type' => 'features', 'title' => 'One platform. More ways to share clean energy.', 'intro' => 'Whether you need reliable power, own equipment worth sharing or want useful resources to go further, SolarShare brings the journey into one place.', 'items' => [
            ['icon' => 'search', 'title' => 'For renters: find the right power', 'text' => 'Explore example listings by equipment type and energy specifications, then see how a rental journey is designed to work.'],
            ['icon' => 'leaf', 'title' => 'For owners: put good gear to work', 'text' => 'Preview how a listing can present daily price, location, power and capacity to people nearby.'],
            ['icon' => 'tool', 'title' => 'For everyone: keep gear in use', 'text' => 'See the planned hand-off from reservation and contract through return, inspection and maintenance.'],
        ]],
        $steps,
        ['type' => 'equipment', 'title' => 'Good gear. Ready for another life.', 'intro' => 'A few examples from the SolarShare concept catalog. Listings shown here are illustrative.', 'limit' => 4, 'action' => ['label' => 'Explore the catalog', 'href' => 'front.catalog']],
        ['type' => 'features', 'title' => 'Designed around trust', 'intro' => 'A shared-energy marketplace should make the details feel as dependable as the equipment.', 'items' => [
            ['icon' => 'calendar', 'title' => 'One calendar per item', 'text' => 'The reservation flow is designed to prevent overlapping requests for the same equipment.'],
            ['icon' => 'wallet', 'title' => 'Clear costs', 'text' => 'Price, deposit and payment method are shown before a rental is confirmed.'],
            ['icon' => 'shield', 'title' => 'A safer hand-off', 'text' => 'Rental terms and equipment condition have a place in every exchange.'],
        ]],
        $faq,
        $cta,
    ],

    'pages' => [
        // ---------- Marketing ----------
        'about' => ['path' => '/about', 'title' => 'About', 'sections' => [
            $hero('About SolarShare', 'We make clean energy shareable.', 'A peer-to-peer platform to rent portable solar panels, batteries and small wind turbines.'),
            ['type' => 'text', 'title' => 'Our story', 'paragraphs' => ['Sami had a 1000 Wh battery sleeping in his garage. Leila needed power for a weekend of camping but did not want to buy gear she would use twice a year.', 'SolarShare connects people like them. Owners earn from idle equipment, renters access clean energy on demand, and fewer devices end up unused.']],
            ['type' => 'stats', 'items' => [['value' => '3', 'label' => 'equipment categories'], ['value' => '5', 'label' => 'steps from search to return'], ['value' => '4', 'label' => 'team members']]],
            ['type' => 'features', 'title' => 'What we believe', 'items' => [['icon' => 'handshake', 'title' => 'Share more', 'text' => 'Access beats ownership for gear used a few days a year.'], ['icon' => 'eye', 'title' => 'Be transparent', 'text' => 'Clear prices, clear contracts, clear inspections.'], ['icon' => 'leaf', 'title' => 'Think green', 'text' => 'Help useful equipment stay in use for longer.']]],
            $cta,
        ]],
        'how-it-works' => ['path' => '/how-it-works', 'title' => 'How it works', 'sections' => [
            $hero('How it works', 'From search to return in five steps.', 'Whether you rent or lend, the flow is simple and fully tracked.'),
            $steps, $guarantees, $faq, $cta,
        ]],
        'plans' => ['path' => '/plans', 'title' => 'Plans', 'sections' => [
            $hero('A fair exchange', 'Pricing should be the easy part.', 'Explore a proposed pricing model for the SolarShare marketplace. All rates below are illustrative interface content, not live charges.'),
            $plans, $faq, $cta,
        ]],
        'help' => ['path' => '/help', 'title' => 'Help center', 'sections' => [
            $hero('Help center', 'Answers to common questions.', 'Cannot find what you need? Contact our team.', ['compact' => true]),
            $faq,
            ['type' => 'cta', 'title' => 'Still need help?', 'text' => 'Our team usually replies within one business day.', 'label' => 'Contact us', 'href' => 'front.contact'],
        ]],
        'contact' => ['path' => '/contact', 'title' => 'Contact', 'sections' => [
            $hero('Contact', 'Talk to the SolarShare team.', 'Questions, partnerships or feedback: we would love to hear from you.', ['compact' => true]),
            ['type' => 'form', 'title' => 'Send us a message', 'submit' => 'Send message', 'success' => 'Thanks! This demo form is not connected yet.', 'fields' => [
                ['label' => 'Full name', 'name' => 'name', 'type' => 'text'],
                ['label' => 'Email', 'name' => 'email', 'type' => 'email'],
                ['label' => 'Topic', 'name' => 'topic', 'type' => 'select', 'options' => ['Renting equipment', 'Listing equipment', 'Billing', 'Other']],
                ['label' => 'Message', 'name' => 'message', 'type' => 'textarea'],
            ]],
        ]],
        'safety' => ['path' => '/safety', 'title' => 'Trust & safety', 'sections' => [
            $hero('Trust & safety', 'Share energy with confidence.', 'Contracts, deposits, inspections and maintenance protect owners and renters.'),
            ['type' => 'features', 'items' => [
                ['icon' => 'shield', 'title' => 'Account controls', 'text' => 'Each person has a profile and a defined role in the marketplace.'],
                ['icon' => 'wallet', 'title' => 'Deposits', 'text' => 'A deposit amount is stated in the proposed rental contract.'],
                ['icon' => 'eye', 'title' => 'Return inspections', 'text' => 'Condition before and after can be recorded for every rental.'],
                ['icon' => 'tool', 'title' => 'Maintenance reports', 'text' => 'Diagnosis, actions taken and parts replaced have a documented place.'],
                ['icon' => 'document', 'title' => 'Payment records', 'text' => 'Transaction references and invoices belong in the rental record.'],
                ['icon' => 'message', 'title' => 'Human support', 'text' => 'A clear support path helps people resolve questions.'],
            ]],
            $cta,
        ]],
        'owners' => ['path' => '/owners', 'title' => 'For owners', 'sections' => [
            $hero('For owners', 'Turn idle equipment into income.', 'List your panels, batteries or turbines in minutes and rent them to neighbours.', ['cta' => ['label' => 'Publish equipment', 'href' => 'front.my-publish']]),
            ['type' => 'steps', 'title' => 'Start earning in three steps', 'items' => [['title' => 'Publish', 'text' => 'Add photos, price per day, power and capacity.'], ['title' => 'Approve', 'text' => 'Accept reservations and extension requests.'], ['title' => 'Earn', 'text' => 'Get paid after a clean return and inspection.']]],
            ['type' => 'stats', 'items' => [['value' => '10%', 'label' => 'commission, owner plan'], ['value' => '5%', 'label' => 'commission, pro plan'], ['value' => '0 TND', 'label' => 'listing fee']]],
            $plans, $cta,
        ]],
        'terms' => ['path' => '/terms', 'title' => 'Terms of use', 'sections' => [
            $hero('Legal', 'Terms of use', 'Demo content for the SolarShare academic project.', ['compact' => true]),
            ['type' => 'text', 'paragraphs' => ['1. Purpose. SolarShare lets individuals rent and share small renewable-energy equipment.', '2. Reservations. A reservation is confirmed once payment succeeds. Overlapping reservations for the same equipment are not allowed.', '3. Rentals. Each rental is covered by a contract stating the deposit and the terms of use.', '4. Returns. Equipment is inspected on return. Damage may lead to maintenance and deposit deductions.', '5. Liability. Users must use equipment according to its specifications.']],
        ]],
        'privacy' => ['path' => '/privacy', 'title' => 'Privacy policy', 'sections' => [
            $hero('Legal', 'Privacy policy', 'Demo content for the SolarShare academic project.', ['compact' => true]),
            ['type' => 'text', 'paragraphs' => ['We collect only the data needed to run the service: name, email, phone number, address and rental activity.', 'Payment references are stored for invoicing. We never sell personal data.', 'You can ask to access, correct or delete your data at any time through the contact page.']],
        ]],

        // ---------- Booking flow ----------
        'reserve' => ['path' => '/reserve', 'title' => 'Reserve', 'sections' => [
            $hero('Step 1 of 3', 'Reserve your equipment', 'Pick your dates. We check nobody else booked them.', ['compact' => true]),
            ['type' => 'form', 'title' => 'Reservation details', 'submit' => 'Continue to payment', 'href' => 'front.payment', 'fields' => [
                ['label' => 'Equipment', 'name' => 'equipment', 'type' => 'select', 'options' => ['Portable battery 1000 Wh', 'Foldable solar panel 200 W', 'Portable wind turbine 400 W', 'Compact battery 500 Wh']],
                ['label' => 'Start date', 'name' => 'start', 'type' => 'date'],
                ['label' => 'End date', 'name' => 'end', 'type' => 'date'],
                ['label' => 'Notes for the owner (optional)', 'name' => 'notes', 'type' => 'textarea'],
            ]],
        ]],
        'booking-summary' => ['path' => '/book/summary', 'title' => 'Reservation summary', 'sections' => []],
        'payment' => ['path' => '/book/payment', 'title' => 'Payment', 'sections' => [
            $hero('Step 2 of 3', 'Pay securely', 'Total for Portable battery 1000 Wh, 9 – 11 Oct 2026: 54 TND.', ['compact' => true]),
            ['type' => 'form', 'title' => 'Payment method', 'submit' => 'Pay 54 TND', 'href' => 'front.confirmed', 'fields' => [
                ['label' => 'Method', 'name' => 'method', 'type' => 'radio', 'options' => ['Card', 'Bank transfer', 'Cash on pickup']],
                ['label' => 'Cardholder name', 'name' => 'holder', 'type' => 'text'],
                ['label' => 'Card number', 'name' => 'number', 'type' => 'text', 'placeholder' => '0000 0000 0000 0000'],
                ['label' => 'Expiry', 'name' => 'expiry', 'type' => 'text', 'placeholder' => 'MM/YY'],
            ]],
        ]],
        'confirmed' => ['path' => '/book/confirmed', 'title' => 'Reservation confirmed', 'sections' => [
            $hero('Step 3 of 3', 'Reservation confirmed 🎉', 'Your payment went through and your invoice is ready.', ['compact' => true, 'cta' => ['label' => 'View invoice', 'href' => 'front.invoice']]),
            ['type' => 'steps', 'title' => 'What happens next', 'items' => [['title' => 'Sign the contract', 'text' => 'Your reservation becomes a rental with a contract.'], ['title' => 'Pick up the gear', 'text' => 'Meet the owner on the first day.'], ['title' => 'Return & inspect', 'text' => 'The equipment is checked on return.']]],
        ]],
        'invoice' => ['path' => '/book/invoice', 'title' => 'Invoice', 'sections' => [
            $hero('Invoice INV-2026-0142', 'Your invoice', 'Issued on 3 Oct 2026 for reservation RES-1042.', ['compact' => true]),
            ['type' => 'table', 'title' => 'Details', 'cols' => ['Description', 'Days', 'Price/day', 'Amount'], 'rows' => [['Portable battery 1000 Wh', '3', '18 TND', '54 TND']]],
            ['type' => 'stats', 'items' => [['value' => '54.00 TND', 'label' => 'Subtotal'], ['value' => '10.26 TND', 'label' => 'VAT 19%'], ['value' => '64.26 TND', 'label' => 'Total paid']]],
        ]],

        // ---------- My space ----------
        'my-dashboard' => ['path' => '/my/dashboard', 'title' => 'Buyer dashboard', 'shell' => 'account', 'role' => 'buyer', 'sections' => [
            ['type' => 'stats', 'items' => [['value' => '1', 'label' => 'Active rentals'], ['value' => '2', 'label' => 'Upcoming reservations'], ['value' => '1', 'label' => 'Pending reservations'], ['value' => '126 TND', 'label' => 'Total spent'], ['value' => '9 days', 'label' => 'Total rental days'], ['value' => '1', 'label' => 'Extension requests waiting']]],
        ]],
        'buyer-reservation-detail' => ['path' => '/my/reservations/RES-1042', 'title' => 'Reservation details', 'shell' => 'account', 'role' => 'buyer', 'sections' => []],
        'buyer-rental-detail' => ['path' => '/my/rentals/RNT-2031', 'title' => 'Rental details', 'shell' => 'account', 'role' => 'buyer', 'sections' => []],
        'my-payments' => ['path' => '/my/payments', 'title' => 'Payments & invoices', 'shell' => 'account', 'role' => 'buyer', 'sections' => []],
        'owner-dashboard' => ['path' => '/owner/dashboard', 'title' => 'Owner dashboard', 'shell' => 'account', 'role' => 'owner', 'sections' => []],
        'my-reservations' => ['path' => '/my/reservations', 'title' => 'Reservations', 'shell' => 'account', 'sections' => [
            ['type' => 'table', 'title' => 'My reservations', 'cols' => ['Ref', 'Equipment', 'Dates', 'Total', 'Status'], 'action' => ['label' => 'New reservation', 'href' => 'front.reserve'], 'rows' => [
                ['RES-1042', 'Portable battery 1000 Wh', '9 – 11 Oct 2026', '54 TND', ['text' => 'Paid', 'tone' => 'success']],
                ['RES-1038', 'Portable wind turbine 400 W', '16 – 17 Oct 2026', '28 TND', ['text' => 'Pending payment', 'tone' => 'warning']],
                ['RES-1021', 'Power station 2000 Wh', '1 – 2 Sep 2026', '60 TND', ['text' => 'Cancelled', 'tone' => 'error']],
            ]],
        ]],
        'my-rentals' => ['path' => '/my/rentals', 'title' => 'Rentals', 'shell' => 'account', 'sections' => [
            ['type' => 'table', 'title' => 'My rentals', 'cols' => ['Ref', 'Equipment', 'Dates', 'Total', 'Status'], 'rows' => $rentals],
        ]],
        'my-contract' => ['path' => '/my/contract', 'title' => 'Rental contract', 'shell' => 'account', 'sections' => [
            ['type' => 'text', 'title' => 'Contract CTR-2031', 'paragraphs' => ['Equipment: Portable battery 1000 Wh. Period: 9 – 11 Oct 2026. Deposit: 100 TND.', 'The renter agrees to use the equipment according to its specifications and to return it in the same condition.']],
            ['type' => 'form', 'title' => 'Sign the contract', 'submit' => 'Sign contract', 'fields' => [['label' => 'Full name', 'name' => 'signature', 'type' => 'text'], ['label' => 'I accept the terms and the deposit', 'name' => 'accept', 'type' => 'checkbox']]],
        ]],
        'my-extensions' => ['path' => '/my/extensions', 'title' => 'Extensions', 'shell' => 'account', 'sections' => [
            ['type' => 'table', 'title' => 'Extension requests', 'cols' => ['Rental', 'Old end', 'New end', 'Extra', 'Status'], 'rows' => [
                ['RNT-2024', '4 Oct', '5 Oct', '9 TND', ['text' => 'Requested', 'tone' => 'warning']],
                ['RNT-2009', '14 Sep', '15 Sep', '10 TND', ['text' => 'Approved', 'tone' => 'success']],
            ]],
            ['type' => 'form', 'title' => 'Request an extension', 'submit' => 'Send request', 'fields' => [['label' => 'Rental', 'name' => 'rental', 'type' => 'select', 'options' => ['RNT-2031', 'RNT-2024']], ['label' => 'New end date', 'name' => 'new_end', 'type' => 'date'], ['label' => 'Reason', 'name' => 'reason', 'type' => 'textarea']]],
        ]],
        'my-equipment' => ['path' => '/my/equipment', 'title' => 'My equipment', 'shell' => 'account', 'role' => 'owner', 'sections' => [
            ['type' => 'table', 'title' => 'My equipment', 'cols' => ['Name', 'Category', 'Price/day', 'Bookings', 'Status'], 'action' => ['label' => 'Publish equipment', 'href' => 'front.my-publish'], 'rows' => [
                ['Portable battery 1000 Wh', 'Batteries', '18 TND', '12', ['text' => 'Available', 'tone' => 'success']],
                ['Foldable solar panel 200 W', 'Solar panels', '9 TND', '7', ['text' => 'Available', 'tone' => 'success']],
                ['Solar kit 2 × 100 W', 'Solar panels', '11 TND', '4', ['text' => 'Maintenance', 'tone' => 'warning']],
            ]],
        ]],
        'my-publish' => ['path' => '/my/equipment/new', 'title' => 'Publish equipment', 'shell' => 'account', 'role' => 'owner', 'sections' => [
            ['type' => 'form', 'title' => 'Publish equipment', 'submit' => 'Publish', 'fields' => [
                ['label' => 'Name', 'name' => 'name', 'type' => 'text'],
                ['label' => 'Category', 'name' => 'category', 'type' => 'select', 'options' => ['Portable solar panels', 'Batteries', 'Small wind turbines']],
                ['label' => 'Brand', 'name' => 'brand', 'type' => 'text'],
                ['label' => 'Model', 'name' => 'model', 'type' => 'text'],
                ['label' => 'Price per day (TND)', 'name' => 'price', 'type' => 'number'],
                ['label' => 'Condition', 'name' => 'condition', 'type' => 'select', 'options' => ['New', 'Good', 'Fair']],
                ['label' => 'Power (W)', 'name' => 'power', 'type' => 'number'],
                ['label' => 'Capacity (Wh)', 'name' => 'capacity', 'type' => 'number'],
                ['label' => 'City', 'name' => 'city', 'type' => 'text'],
                ['label' => 'Description', 'name' => 'description', 'type' => 'textarea'],
            ]],
        ]],
        'my-inspections' => ['path' => '/my/inspections', 'title' => 'Inspections', 'shell' => 'account', 'role' => 'owner', 'sections' => [
            ['type' => 'table', 'title' => 'Return inspections', 'cols' => ['Equipment', 'Date', 'Before', 'After', 'Result'], 'rows' => [
                ['Compact battery 500 Wh', '14 Sep', 'Good', 'Good', ['text' => 'No damage', 'tone' => 'success']],
                ['Solar kit 2 × 100 W', '20 Sep', 'Good', 'Fair', ['text' => 'Sent to maintenance', 'tone' => 'warning']],
            ]],
        ]],
        'my-calendar' => ['path' => '/owner/availability', 'title' => 'Availability calendar', 'shell' => 'account', 'role' => 'owner', 'sections' => []],
        'my-earnings' => ['path' => '/owner/earnings', 'title' => 'Earnings & invoices', 'shell' => 'account', 'role' => 'owner', 'sections' => []],
        'my-maintenance' => ['path' => '/owner/maintenance', 'title' => 'Maintenance', 'shell' => 'account', 'role' => 'owner', 'sections' => []],
        'my-equipment-detail' => ['path' => '/owner/equipment/1', 'title' => 'Equipment details', 'shell' => 'account', 'role' => 'owner', 'sections' => []],
        'my-equipment-edit' => ['path' => '/owner/equipment/1/edit', 'title' => 'Edit equipment', 'shell' => 'account', 'role' => 'owner', 'sections' => []],
        'my-reservation-detail' => ['path' => '/owner/reservations/1042', 'title' => 'Reservation details', 'shell' => 'account', 'role' => 'owner', 'sections' => []],
        'my-rental-detail' => ['path' => '/owner/rentals/2031', 'title' => 'Rental details', 'shell' => 'account', 'role' => 'owner', 'sections' => []],
        'my-notifications' => ['path' => '/my/notifications', 'title' => 'Notifications', 'shell' => 'account', 'sections' => [
            ['type' => 'table', 'title' => 'Latest notifications', 'cols' => ['When', 'Message'], 'rows' => [
                ['Today', 'Your reservation RES-1042 is paid. Invoice INV-2026-0142 is ready.'],
                ['Yesterday', 'Sami approved your extension request for RNT-2024.'],
                ['2 days ago', 'Solar kit 2 × 100 W is back from maintenance.'],
            ]],
        ]],
    ],
];
