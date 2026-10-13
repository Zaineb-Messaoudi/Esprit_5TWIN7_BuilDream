<?php

namespace App\Http\Requests\Concerns;

use App\Models\Category;

/** Adds category-aware minimum technical data to catalogue forms. */
trait ValidatesEnergyProfile
{
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $name = strtolower((string) Category::find($this->input('category_id'))?->name);
            $required = match (true) {
                str_contains($name, 'batter') || str_contains($name, 'power station') => ['power_watts', 'capacity_wh'],
                str_contains($name, 'solar panel') => ['power_watts'],
                str_contains($name, 'wind') || str_contains($name, 'hydro') || str_contains($name, 'marine') => ['power_watts'],
                str_contains($name, 'charge controller') => ['voltage', 'max_output'],
                default => [],
            };

            foreach ($required as $field) {
                if (blank($this->input("energy.$field"))) {
                    $validator->errors()->add("energy.$field", __('This technical value is required for the selected category.'));
                }
            }
        });
    }
}
