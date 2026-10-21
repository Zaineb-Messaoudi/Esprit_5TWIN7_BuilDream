<?php

it('shows the energy calculator page to guests', function () {
    $this->get(route('front.energy-calculator'))
        ->assertOk()
        ->assertSee('Energy needs calculator')
        ->assertSee('Ask AI')
        ->assertSee('Sign in to use AI')
        ->assertSee('Refrigerator');
});

it('calculates the needs for a selected appliance', function () {
    $this->post(route('front.energy-calculator.calculate'), [
        'items' => ['fridge' => ['selected' => 1, 'quantity' => 1]],
        'autonomy_days' => 1,
        'sun_hours' => 5,
    ])
        ->assertOk()
        ->assertSee('Your estimated needs')
        ->assertSee('Ask AI to explain this estimate')
        ->assertSee(route('ai.chat'), false)
        ->assertSee('1 200 Wh')
        ->assertSee('1 700 Wh');
});

it('asks to select an appliance when none is chosen', function () {
    $this->post(route('front.energy-calculator.calculate'), [
        'autonomy_days' => 1,
    ])
        ->assertOk()
        ->assertSee('Select at least one appliance.');
});

it('rejects out of range values', function () {
    $this->post(route('front.energy-calculator.calculate'), [
        'items' => ['fridge' => ['selected' => 1, 'quantity' => 500]],
    ])->assertSessionHasErrors('items.fridge.quantity');
});
