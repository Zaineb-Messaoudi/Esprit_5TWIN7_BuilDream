<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class EcommerceController extends Controller
{
    private const PAGES = [
        'products' => [
            'title' => 'Products',
            'description' => 'Solar equipment catalog · illustrative demo data',
            'columns' => ['sku' => 'SKU', 'category' => 'Category', 'stock' => 'Inventory', 'price' => 'Price'],
            'create_route' => 'products.create',
            'detail_route' => 'products.show',
            'records' => [
                ['id' => 'sol-panel-420', 'name' => 'Residential Solar Panel 420W', 'subtitle' => 'Monocrystalline · 420 W', 'sku' => 'SOL-PNL-420', 'category' => 'Solar panels', 'stock' => 'In stock · 184', 'price' => '$289.00', 'status' => 'Active', 'tone' => 'success'],
                ['id' => 'inv-hybrid-5k', 'name' => 'Hybrid Inverter 5kW', 'subtitle' => 'Single phase · Wi-Fi enabled', 'sku' => 'INV-HYB-5K', 'category' => 'Inverters', 'stock' => 'In stock · 42', 'price' => '$1,249.00', 'status' => 'Active', 'tone' => 'success'],
                ['id' => 'bat-home-10', 'name' => 'Home Battery 10kWh', 'subtitle' => 'LiFePO4 · 10 year warranty', 'sku' => 'BAT-HOME-10', 'category' => 'Storage', 'stock' => 'Low stock · 6', 'price' => '$4,890.00', 'status' => 'Low stock', 'tone' => 'warning'],
                ['id' => 'mount-roof-rail', 'name' => 'Universal Roof Mounting Kit', 'subtitle' => 'Rail set · 4 panel capacity', 'sku' => 'MNT-ROOF-04', 'category' => 'Mounting', 'stock' => 'In stock · 96', 'price' => '$164.00', 'status' => 'Active', 'tone' => 'success'],
            ],
        ],
        'orders' => [
            'title' => 'Orders',
            'description' => 'Order processing and fulfillment · illustrative demo data',
            'columns' => ['customer' => 'Customer', 'date' => 'Order date', 'items' => 'Items', 'total' => 'Total'],
            'detail_route' => 'orders.show',
            'records' => [
                ['id' => 'SS-2084', 'name' => 'Order #SS-2084', 'subtitle' => 'Residential system', 'customer' => 'Amira Ben Salem', 'date' => 'Oct 3, 2026', 'items' => '4 items', 'total' => '$6,240.00', 'status' => 'Processing', 'tone' => 'primary'],
                ['id' => 'SS-2081', 'name' => 'Order #SS-2081', 'subtitle' => 'Inverter replacement', 'customer' => 'Karim Mansour', 'date' => 'Oct 2, 2026', 'items' => '2 items', 'total' => '$1,840.00', 'status' => 'Shipped', 'tone' => 'info'],
                ['id' => 'SS-2079', 'name' => 'Order #SS-2079', 'subtitle' => 'Home storage upgrade', 'customer' => 'Leila Trabelsi', 'date' => 'Oct 1, 2026', 'items' => '3 items', 'total' => '$5,420.00', 'status' => 'Delivered', 'tone' => 'success'],
                ['id' => 'SS-2076', 'name' => 'Order #SS-2076', 'subtitle' => 'Mounting accessories', 'customer' => 'Omar Gharbi', 'date' => 'Sep 29, 2026', 'items' => '6 items', 'total' => '$984.00', 'status' => 'Pending payment', 'tone' => 'warning'],
            ],
        ],
        'customers' => [
            'title' => 'Customers',
            'description' => 'Customer directory · illustrative demo data',
            'columns' => ['email' => 'Email', 'location' => 'Location', 'orders' => 'Orders', 'spent' => 'Lifetime value'],
            'detail_route' => 'customers.show',
            'records' => [
                ['id' => 'amira-ben-salem', 'name' => 'Amira Ben Salem', 'subtitle' => 'Residential customer', 'email' => 'amira.bensalem@example.test', 'location' => 'Tunis', 'orders' => '8', 'spent' => '$12,480.00', 'status' => 'Active', 'tone' => 'success'],
                ['id' => 'karim-mansour', 'name' => 'Karim Mansour', 'subtitle' => 'Installer partner', 'email' => 'karim.mansour@example.test', 'location' => 'Sousse', 'orders' => '14', 'spent' => '$28,640.00', 'status' => 'Active', 'tone' => 'success'],
                ['id' => 'leila-trabelsi', 'name' => 'Leila Trabelsi', 'subtitle' => 'Residential customer', 'email' => 'leila.trabelsi@example.test', 'location' => 'Sfax', 'orders' => '3', 'spent' => '$8,920.00', 'status' => 'New', 'tone' => 'primary'],
                ['id' => 'omar-gharbi', 'name' => 'Omar Gharbi', 'subtitle' => 'Commercial customer', 'email' => 'omar.gharbi@example.test', 'location' => 'Gabes', 'orders' => '6', 'spent' => '$18,360.00', 'status' => 'Active', 'tone' => 'success'],
            ],
        ],
        'categories' => [
            'title' => 'Categories',
            'description' => 'Organize products in the SolarShare demo catalog',
            'columns' => ['description' => 'Description', 'products' => 'Products', 'updated' => 'Last updated'],
            'records' => [
                ['id' => 'solar-panels', 'name' => 'Solar panels', 'subtitle' => 'PV modules for residential and commercial installations', 'description' => 'Panels and PV modules', 'products' => '24 products', 'updated' => 'Oct 1, 2026', 'status' => 'Published', 'tone' => 'success'],
                ['id' => 'inverters', 'name' => 'Inverters', 'subtitle' => 'Grid-tied and hybrid inverter systems', 'description' => 'Grid-tied and hybrid', 'products' => '18 products', 'updated' => 'Sep 28, 2026', 'status' => 'Published', 'tone' => 'success'],
                ['id' => 'energy-storage', 'name' => 'Energy storage', 'subtitle' => 'Home and commercial battery systems', 'description' => 'Battery storage systems', 'products' => '12 products', 'updated' => 'Sep 24, 2026', 'status' => 'Published', 'tone' => 'success'],
                ['id' => 'accessories', 'name' => 'Accessories', 'subtitle' => 'Mounting, cabling, and safety equipment', 'description' => 'Mounting and accessories', 'products' => '36 products', 'updated' => 'Sep 20, 2026', 'status' => 'Draft', 'tone' => 'warning'],
            ],
        ],
        'invoices' => [
            'title' => 'Invoices',
            'description' => 'Billing documents · illustrative demo data',
            'columns' => ['customer' => 'Customer', 'issued' => 'Issued', 'due' => 'Due date', 'total' => 'Amount'],
            'create_route' => 'invoices.create',
            'detail_route' => 'invoices.show',
            'records' => [
                ['id' => 'INV-2026-084', 'name' => 'Invoice INV-2026-084', 'subtitle' => 'For order SS-2084', 'customer' => 'Amira Ben Salem', 'issued' => 'Oct 3, 2026', 'due' => 'Oct 17, 2026', 'total' => '$6,240.00', 'status' => 'Unpaid', 'tone' => 'warning'],
                ['id' => 'INV-2026-081', 'name' => 'Invoice INV-2026-081', 'subtitle' => 'For order SS-2081', 'customer' => 'Karim Mansour', 'issued' => 'Oct 2, 2026', 'due' => 'Oct 16, 2026', 'total' => '$1,840.00', 'status' => 'Paid', 'tone' => 'success'],
                ['id' => 'INV-2026-079', 'name' => 'Invoice INV-2026-079', 'subtitle' => 'For order SS-2079', 'customer' => 'Leila Trabelsi', 'issued' => 'Oct 1, 2026', 'due' => 'Oct 15, 2026', 'total' => '$5,420.00', 'status' => 'Overdue', 'tone' => 'error'],
                ['id' => 'INV-2026-076', 'name' => 'Invoice INV-2026-076', 'subtitle' => 'For order SS-2076', 'customer' => 'Omar Gharbi', 'issued' => 'Sep 29, 2026', 'due' => 'Oct 13, 2026', 'total' => '$984.00', 'status' => 'Paid', 'tone' => 'success'],
            ],
        ],
        'transactions' => [
            'title' => 'Transactions',
            'description' => 'Payment activity · illustrative demo data, no payment processing',
            'columns' => ['customer' => 'Customer', 'method' => 'Method', 'date' => 'Date', 'amount' => 'Amount'],
            'detail_route' => 'transactions.show',
            'records' => [
                ['id' => 'TXN-48321', 'name' => 'Transaction TXN-48321', 'subtitle' => 'Invoice INV-2026-081', 'customer' => 'Karim Mansour', 'method' => 'Bank transfer', 'date' => 'Oct 3, 2026 · 10:42 AM', 'amount' => '$1,840.00', 'status' => 'Succeeded', 'tone' => 'success'],
                ['id' => 'TXN-48318', 'name' => 'Transaction TXN-48318', 'subtitle' => 'Invoice INV-2026-079', 'customer' => 'Leila Trabelsi', 'method' => 'Card ···· 4242', 'date' => 'Oct 2, 2026 · 3:18 PM', 'amount' => '$5,420.00', 'status' => 'Refunded', 'tone' => 'gray'],
                ['id' => 'TXN-48304', 'name' => 'Transaction TXN-48304', 'subtitle' => 'Invoice INV-2026-076', 'customer' => 'Omar Gharbi', 'method' => 'Card ···· 1824', 'date' => 'Oct 1, 2026 · 9:06 AM', 'amount' => '$984.00', 'status' => 'Succeeded', 'tone' => 'success'],
                ['id' => 'TXN-48296', 'name' => 'Transaction TXN-48296', 'subtitle' => 'Invoice INV-2026-074', 'customer' => 'Nour Ben Ali', 'method' => 'Bank transfer', 'date' => 'Sep 30, 2026 · 1:52 PM', 'amount' => '$2,760.00', 'status' => 'Pending', 'tone' => 'warning'],
            ],
        ],
    ];

    private const PLANS = [
        ['name' => 'Starter', 'price' => '$19', 'description' => 'For small installation teams getting started.', 'features' => ['Up to 3 team members', 'Product and order overview', 'Email support']],
        ['name' => 'Professional', 'price' => '$49', 'description' => 'For growing solar businesses managing more projects.', 'features' => ['Up to 15 team members', 'Advanced analytics', 'Priority support']],
        ['name' => 'Business', 'price' => '$99', 'description' => 'For established teams coordinating multiple regions.', 'features' => ['Unlimited team members', 'Multi-location dashboards', 'Dedicated onboarding']],
    ];

    public function index(string $resource): View
    {
        $page = self::PAGES[$resource] ?? abort(404);

        return view('pages.ecommerce.records', [
            'resource' => $resource,
            'page' => $page,
            'type' => 'list',
        ]);
    }

    public function show(string $id, string $resource): View
    {
        $page = self::PAGES[$resource] ?? abort(404);
        $record = collect($page['records'])->firstWhere('id', $id);
        abort_unless($record, 404);

        return view('pages.ecommerce.records', [
            'resource' => $resource,
            'page' => $page,
            'record' => $record,
            'type' => 'detail',
        ]);
    }

    public function create(string $resource): View
    {
        abort_unless(in_array($resource, ['products', 'invoices'], true), 404);

        return view('pages.ecommerce.records', [
            'resource' => $resource,
            'page' => self::PAGES[$resource],
            'type' => 'create',
        ]);
    }

    public function edit(string $id, string $resource): View
    {
        abort_unless($resource === 'products', 404);

        $page = self::PAGES['products'];
        $record = collect($page['records'])->firstWhere('id', $id);
        abort_unless($record, 404);

        $page['title'] = 'Edit product';
        $page['description'] = 'Review product details in this non-persistent demo form.';

        return view('pages.ecommerce.records', [
            'resource' => $resource,
            'page' => $page,
            'record' => $record,
            'type' => 'edit',
        ]);
    }

    public function pricing(): View
    {
        return view('pages.ecommerce.records', [
            'resource' => 'pricing',
            'page' => [
                'title' => 'Pricing plans',
                'description' => 'Subscription plan examples for the SolarShare admin demo.',
                'records' => self::PLANS,
            ],
            'type' => 'pricing',
        ]);
    }

    public function billing(): View
    {
        return view('pages.ecommerce.records', [
            'resource' => 'billing',
            'page' => [
                'title' => 'Billing overview',
                'description' => 'Sample payment methods and billing summary · no charges are made.',
                'records' => [],
            ],
            'type' => 'billing',
        ]);
    }
}
