<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Equipment;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Persona 1: Super Admin
        User::firstOrCreate(['email' => 'admin@solarshare.com'], [
            'name' => 'Solar Admin',
            'password' => 'password',
            'phone_number' => '+216 70 000 001',
            'address' => 'Tunis, Tunisia',
            'role' => UserRole::ADMIN,
            'role_setup_completed' => true,
            'email_verified_at' => now(),
        ]);

        // Persona 2: Buyer
        User::firstOrCreate(['email' => 'user@solarshare.com'], [
            'name' => 'Leila Buyer',
            'password' => 'password',
            'phone_number' => '+216 70 000 002',
            'address' => 'Sousse, Tunisia',
            'role' => UserRole::BUYER,
            'role_setup_completed' => true,
            'email_verified_at' => now(),
        ]);

        // Persona 3: Equipment Owner
        $owner = User::firstOrCreate(['email' => 'owner@solarshare.com'], [
            'name' => 'Sami Owner',
            'password' => 'password',
            'phone_number' => '+216 70 000 003',
            'address' => 'Sfax, Tunisia',
            'role' => UserRole::OWNER,
            'role_setup_completed' => true,
            'email_verified_at' => now(),
        ]);

        // Persona 4: New account that still needs to select a buyer or owner role.
        User::firstOrCreate(['email' => 'new-member@solarshare.com'], [
            'name' => 'New SolarShare Member',
            'password' => 'password',
            'role' => UserRole::USER,
            'role_setup_completed' => false,
            'email_verified_at' => now(),
        ]);

        // Stable additional buyer accounts avoid growing the database on each seed.
        foreach (range(1, 10) as $number) {
            $suffix = str_pad((string) $number, 2, '0', STR_PAD_LEFT);

            User::firstOrCreate(["email" => "buyer{$suffix}@solarshare.com"], [
                'name' => "SolarShare Buyer {$suffix}",
                'password' => 'password',
                'role' => UserRole::BUYER,
                'role_setup_completed' => true,
                'email_verified_at' => now(),
            ]);
        }

        $categories = [
            'Portable solar panels' => 'Foldable panels for camping, vans and off-grid weekends.',
            'Portable power stations' => 'Rechargeable power stations for fridges, lights, tools and weekend trips.',
            'Solar charge controllers' => 'Controllers that safely regulate energy from panels into batteries.',
            'Solar thermal equipment' => 'Solar collectors and hot-water equipment for low-energy homes.',
            'Small wind turbines' => 'Compact turbines to generate clean power where the wind blows.',
            'Micro-hydropower' => 'Small water turbines for streams, farms and off-grid installations.',
            'Biomass and bioenergy' => 'Compact biomass equipment for useful heat and local energy recovery.',
            'Geothermal heat pumps' => 'Efficient ground-source systems for heating and cooling spaces.',
            'Marine energy' => 'Small tidal and wave-energy prototypes for coastal experiments.',
            'Hydrogen and fuel cells' => 'Educational electrolyzers and fuel-cell backup power systems.',
            'Energy monitoring' => 'Meters and sensors that make renewable-energy usage visible.',
        ];

        $categoryModels = [];
        foreach ($categories as $name => $description) {
            $categoryModels[$name] = Category::updateOrCreate(
                ['name' => $name],
                ['description' => $description, 'status' => 'active'],
            );
        }

        $catalog = [
            ['category' => 'Portable power stations', 'name' => 'Portable battery 1000 Wh', 'brand' => 'Voltix', 'model' => 'PB-1000', 'price' => 18, 'location' => 'Tunis', 'status' => 'available', 'condition' => 'good', 'power' => 500, 'capacity' => 1000, 'technology' => 'Lithium-ion', 'image' => 'https://iallpowers.com/cdn/shop/files/ALLPOWERSr600_6.jpg?v=1764056227&width=1600'],
            ['category' => 'Portable solar panels', 'name' => 'Foldable solar panel 200 W', 'brand' => 'SunFold', 'model' => 'SF-200', 'price' => 9, 'location' => 'La Marsa', 'status' => 'available', 'condition' => 'new', 'power' => 200, 'capacity' => null, 'technology' => 'Monocrystalline', 'image' => 'https://images.unsplash.com/photo-1509391366360-2e959784a276?auto=format&fit=crop&w=1200&q=85'],
            ['category' => 'Small wind turbines', 'name' => 'Portable wind turbine 400 W', 'brand' => 'Aeolus', 'model' => 'AW-400', 'price' => 14, 'location' => 'Bizerte', 'status' => 'available', 'condition' => 'good', 'power' => 400, 'capacity' => null, 'technology' => 'Horizontal axis', 'image' => 'https://images.unsplash.com/photo-1466611653911-95081537e5b7?auto=format&fit=crop&w=1200&q=85'],
            ['category' => 'Portable power stations', 'name' => 'Power station 2000 Wh', 'brand' => 'Voltix', 'model' => 'PB-2000', 'price' => 30, 'location' => 'Sousse', 'status' => 'available', 'condition' => 'new', 'power' => 1200, 'capacity' => 2000, 'technology' => 'Lithium-ion', 'image' => 'https://www.chipcom.com.gt/_next/image?q=75&url=https%3A%2F%2Fftp3.syscom.mx%2Fusuarios%2Ffotos%2FBancoFotografiasSyscom%2FUGREEN%2F15053%2F15053-p.PNG&w=1200'],
            ['category' => 'Portable solar panels', 'name' => 'Solar kit 2 × 100 W', 'brand' => 'SunFold', 'model' => 'SF-100x2', 'price' => 11, 'location' => 'Nabeul', 'status' => 'maintenance', 'condition' => 'fair', 'power' => 200, 'capacity' => null, 'technology' => 'Monocrystalline', 'image' => 'https://images.unsplash.com/photo-1548613053-220c6f8b88b5?auto=format&fit=crop&w=1200&q=85'],
            ['category' => 'Portable power stations', 'name' => 'Compact battery 500 Wh', 'brand' => 'Voltix', 'model' => 'PB-500', 'price' => 10, 'location' => 'Ariana', 'status' => 'available', 'condition' => 'good', 'power' => 300, 'capacity' => 500, 'technology' => 'Lithium-ion', 'image' => 'https://www.everything4wd.com.au/assets/full/PS800.webp'],
            ['category' => 'Solar charge controllers', 'name' => 'MPPT charge controller 40 A', 'brand' => 'SunTrack', 'model' => 'MPPT-40', 'price' => 7, 'location' => 'Ben Arous', 'status' => 'available', 'condition' => 'new', 'power' => 960, 'capacity' => null, 'technology' => 'MPPT', 'image' => 'https://images-cdn.ubuy.co.in/63529847357c4b083207ad84-epever-40a-mppt-solar-charge.jpg'],
            ['category' => 'Solar charge controllers', 'name' => 'Off-grid solar regulator 20 A', 'brand' => 'EcoCharge', 'model' => 'EC-20', 'price' => 5, 'location' => 'Monastir', 'status' => 'available', 'condition' => 'good', 'power' => 480, 'capacity' => null, 'technology' => 'PWM', 'image' => 'https://solarpanel.vip/cdn/shop/products/PowMr_mppt_charge_controller_10_amp.webp?v=1658405622'],
            ['category' => 'Solar thermal equipment', 'name' => 'Flat-plate solar collector', 'brand' => 'ThermaSun', 'model' => 'TC-200', 'price' => 16, 'location' => 'Hammamet', 'status' => 'available', 'condition' => 'good', 'power' => 2000, 'capacity' => null, 'technology' => 'Solar thermal', 'image' => 'https://termax.store/cdn/shop/collections/panouri-solare-cu-tuburi.png?v=1677680145&width=1420'],
            ['category' => 'Solar thermal equipment', 'name' => 'Solar water heater kit', 'brand' => 'ThermaSun', 'model' => 'SWH-150', 'price' => 22, 'location' => 'Djerba', 'status' => 'available', 'condition' => 'new', 'power' => 1800, 'capacity' => null, 'technology' => 'Evacuated tube', 'image' => 'https://images.unsplash.com/photo-1508514177221-188b1cf16e9d?auto=format&fit=crop&w=1200&q=85'],
            ['category' => 'Small wind turbines', 'name' => 'Vertical-axis wind turbine', 'brand' => 'Aeolus', 'model' => 'VA-800', 'price' => 24, 'location' => 'Kélibia', 'status' => 'available', 'condition' => 'good', 'power' => 800, 'capacity' => null, 'technology' => 'Vertical axis', 'image' => 'https://images.unsplash.com/photo-1523867574998-1a336b6ded04?auto=format&fit=crop&w=1200&q=85'],
            ['category' => 'Micro-hydropower', 'name' => 'Micro-hydro stream turbine', 'brand' => 'FlowPower', 'model' => 'FH-300', 'price' => 26, 'location' => 'Aïn Draham', 'status' => 'available', 'condition' => 'good', 'power' => 300, 'capacity' => null, 'technology' => 'Pelton', 'image' => 'https://images.unsplash.com/photo-1531206715517-5c0ba140b2b8?auto=format&fit=crop&w=1200&q=85'],
            ['category' => 'Micro-hydropower', 'name' => 'Run-of-river generator', 'brand' => 'FlowPower', 'model' => 'ROR-750', 'price' => 32, 'location' => 'Jendouba', 'status' => 'maintenance', 'condition' => 'fair', 'power' => 750, 'capacity' => null, 'technology' => 'Crossflow', 'image' => 'https://images.unsplash.com/photo-1520962922320-2038eebab146?auto=format&fit=crop&w=1200&q=85'],
            ['category' => 'Biomass and bioenergy', 'name' => 'Pellet heater 8 kW', 'brand' => 'BioHeat', 'model' => 'PH-8', 'price' => 20, 'location' => 'Le Kef', 'status' => 'available', 'condition' => 'good', 'power' => 8000, 'capacity' => null, 'technology' => 'Biomass pellets', 'image' => 'https://images.orgill.com/weblarge/10020/7485832.jpg'],
            ['category' => 'Biomass and bioenergy', 'name' => 'Small biogas digester', 'brand' => 'BioLoop', 'model' => 'BD-120', 'price' => 28, 'location' => 'Kairouan', 'status' => 'available', 'condition' => 'new', 'power' => 1200, 'capacity' => null, 'technology' => 'Anaerobic digestion', 'image' => 'https://image.made-in-china.com/226f3j00eomtHrVPZbkc/Cow-Fram-Manure-Waster-Biogas-Anaerobic-Digester-Fermenter-Reactor-Plant-Project.jpg'],
            ['category' => 'Geothermal heat pumps', 'name' => 'Ground-source heat pump', 'brand' => 'TerraWarm', 'model' => 'GSHP-5', 'price' => 35, 'location' => 'Tunis', 'status' => 'available', 'condition' => 'new', 'power' => 5000, 'capacity' => null, 'technology' => 'Ground-source', 'image' => 'https://images.unsplash.com/photo-1473341304170-971dccb5ac1e?auto=format&fit=crop&w=1200&q=85'],
            ['category' => 'Marine energy', 'name' => 'Wave-energy buoy demonstrator', 'brand' => 'BlueCurrent', 'model' => 'WB-150', 'price' => 29, 'location' => 'Tabarka', 'status' => 'available', 'condition' => 'good', 'power' => 150, 'capacity' => null, 'technology' => 'Point absorber', 'image' => 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=85'],
            ['category' => 'Hydrogen and fuel cells', 'name' => 'PEM fuel-cell backup kit', 'brand' => 'H2Go', 'model' => 'PEM-500', 'price' => 38, 'location' => 'Sfax', 'status' => 'available', 'condition' => 'new', 'power' => 500, 'capacity' => null, 'technology' => 'PEM fuel cell', 'image' => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&w=1200&q=85'],
            ['category' => 'Energy monitoring', 'name' => 'Smart energy meter', 'brand' => 'GridSense', 'model' => 'SEM-3P', 'price' => 6, 'location' => 'Tunis', 'status' => 'available', 'condition' => 'new', 'power' => 0, 'capacity' => null, 'technology' => 'Wi-Fi monitoring', 'image' => 'https://images.unsplash.com/photo-1558008258-3256797b43f3?auto=format&fit=crop&w=1200&q=85'],
            ['category' => 'Energy monitoring', 'name' => 'Portable solar irradiance meter', 'brand' => 'GridSense', 'model' => 'SIM-100', 'price' => 8, 'location' => 'Mahdia', 'status' => 'available', 'condition' => 'good', 'power' => 0, 'capacity' => null, 'technology' => 'Irradiance sensor', 'image' => 'https://cpimg.tistatic.com/10877393/b/5/extra-10877393.jpg'],
        ];

        foreach ($catalog as $item) {
            $equipment = Equipment::updateOrCreate(
                ['name' => $item['name'], 'owner_id' => $owner->id],
                [
                    'category_id' => $categoryModels[$item['category']]->id,
                    'description' => "SolarShare community listing: {$item['name']}, available for responsible short-term use.",
                    'image_url' => $item['image'],
                    'brand' => $item['brand'], 'model' => $item['model'],
                    'price_per_day' => $item['price'], 'condition' => $item['condition'],
                    'location' => $item['location'], 'status' => $item['status'],
                ],
            );

            $equipment->energyProfile()->updateOrCreate([], [
                'power_watts' => $item['power'], 'voltage' => 24, 'capacity_wh' => $item['capacity'],
                'efficiency' => 92, 'technology' => $item['technology'], 'max_output' => $item['power'],
                'operating_duration' => $item['capacity'] ? 8 : null,
            ]);
        }

        // Keep a stable representative image for each category.
        foreach ($catalog as $item) {
            Category::where('name', $item['category'])
                ->whereNull('image_url')
                ->update(['image_url' => $item['image']]);
        }

        // Keep the old validation-only category out of the public catalogue when unused.
        Category::where('name', 'Batteries')->whereDoesntHave('equipment')->update(['status' => 'inactive']);

        // Rental module (Student 3): rentals, contracts and extension requests.
        $this->call(RentalSeeder::class);
    }
}
