@extends('layouts.app')

@section('content')
    @php
        $products = [
            ['name' => 'Residential Solar Panel 420W', 'sku' => 'SOL-PNL-420', 'category' => 'Solar panels', 'stock' => 'In stock', 'price' => '$289.00'],
            ['name' => 'Hybrid Inverter 5kW', 'sku' => 'INV-HYB-5K', 'category' => 'Inverters', 'stock' => 'In stock', 'price' => '$1,249.00'],
            ['name' => 'Home Battery 10kWh', 'sku' => 'BAT-HOME-10', 'category' => 'Storage', 'stock' => 'Low stock', 'price' => '$4,890.00'],
        ];
    @endphp

    <div class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Ecommerce / {{ $title }}</p>
                <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">{{ $title }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">SolarShare demo catalog · sample data only</p>
            </div>
            @if ($type === 'list')
                <x-ui.button variant="primary">Add product</x-ui.button>
            @endif
        </div>

        @if ($type === 'list')
            <x-common.component-card title="Product inventory">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[640px] text-start text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 text-xs uppercase text-gray-500 dark:border-gray-800 dark:text-gray-400">
                                <th class="px-4 py-3 font-medium">Product</th><th class="px-4 py-3 font-medium">SKU</th><th class="px-4 py-3 font-medium">Category</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3 text-end font-medium">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                                <tr class="border-b border-gray-50 text-gray-700 last:border-0 dark:border-gray-800 dark:text-gray-300">
                                    <td class="px-4 py-4 font-medium text-gray-800 dark:text-white">{{ $product['name'] }}</td>
                                    <td class="px-4 py-4">{{ $product['sku'] }}</td><td class="px-4 py-4">{{ $product['category'] }}</td>
                                    <td class="px-4 py-4"><x-ui.badge variant="light" :color="$product['stock'] === 'Low stock' ? 'warning' : 'success'">{{ $product['stock'] }}</x-ui.badge></td>
                                    <td class="px-4 py-4 text-end font-medium">{{ $product['price'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-common.component-card>
        @elseif ($type === 'detail')
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <x-common.component-card title="Product preview">
                    <div class="flex aspect-[4/3] items-center justify-center rounded-xl bg-gradient-to-br from-brand-50 to-blue-light-50 text-center dark:from-gray-800 dark:to-gray-900">
                        <div><p class="text-6xl" aria-hidden="true">☀</p><p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Solar panel product image</p></div>
                    </div>
                </x-common.component-card>
                <x-common.component-card title="Residential Solar Panel 420W">
                    <x-ui.badge variant="light" color="success">In stock</x-ui.badge>
                    <p class="mt-4 text-3xl font-semibold text-gray-800 dark:text-white">$289.00</p>
                    <p class="mt-3 text-sm leading-6 text-gray-500 dark:text-gray-400">High-efficiency monocrystalline panel for residential installations. Product details shown here are illustrative and are not connected to checkout or inventory services.</p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <x-ui.button variant="primary">Add to cart</x-ui.button>
                        <x-ui.button variant="outline">Save item</x-ui.button>
                    </div>
                </x-common.component-card>
            </div>
        @elseif ($type === 'cart')
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                <div class="space-y-4 xl:col-span-2">
                    @foreach ([$products[0], $products[1]] as $product)
                        <x-common.component-card :title="$product['name']">
                            <div class="flex flex-wrap items-center justify-between gap-4">
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $product['sku'] }} · Qty 1</span>
                                <span class="font-semibold text-gray-800 dark:text-white">{{ $product['price'] }}</span>
                            </div>
                        </x-common.component-card>
                    @endforeach
                </div>
                <x-common.component-card title="Order summary">
                    <div class="space-y-3 text-sm text-gray-500 dark:text-gray-400">
                        <div class="flex justify-between"><span>Subtotal</span><span>$1,538.00</span></div>
                        <div class="flex justify-between"><span>Shipping</span><span>Calculated at checkout</span></div>
                        <div class="flex justify-between border-t border-gray-100 pt-3 font-semibold text-gray-800 dark:border-gray-800 dark:text-white"><span>Total</span><span>$1,538.00</span></div>
                    </div>
                    <x-ui.button variant="primary" className="mt-5 w-full">Continue to checkout</x-ui.button>
                </x-common.component-card>
            </div>
        @else
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                <x-common.component-card title="Shipping details" class="xl:col-span-2">
                    <form class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <label class="text-sm text-gray-600 dark:text-gray-300">First name<input class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 dark:border-gray-700 dark:bg-gray-900 dark:text-white" autocomplete="given-name" /></label>
                        <label class="text-sm text-gray-600 dark:text-gray-300">Last name<input class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 dark:border-gray-700 dark:bg-gray-900 dark:text-white" autocomplete="family-name" /></label>
                        <label class="text-sm text-gray-600 dark:text-gray-300 sm:col-span-2">Email address<input type="email" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 dark:border-gray-700 dark:bg-gray-900 dark:text-white" autocomplete="email" /></label>
                        <label class="text-sm text-gray-600 dark:text-gray-300 sm:col-span-2">Delivery address<input class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 dark:border-gray-700 dark:bg-gray-900 dark:text-white" autocomplete="street-address" /></label>
                    </form>
                </x-common.component-card>
                <x-common.component-card title="Order summary">
                    <p class="text-sm text-gray-500 dark:text-gray-400">2 items</p>
                    <p class="mt-4 flex justify-between font-semibold text-gray-800 dark:text-white"><span>Total</span><span>$1,538.00</span></p>
                    <p class="mt-4 text-xs text-gray-400">Checkout is a UI demonstration; no payment is processed.</p>
                    <x-ui.button variant="primary" className="mt-5 w-full">Review order</x-ui.button>
                </x-common.component-card>
            </div>
        @endif
    </div>
@endsection
