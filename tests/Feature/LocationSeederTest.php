<?php

use App\Models\City;
use App\Models\State;
use Database\Seeders\LocationSeeder;

it('seeds states and cities from the json catalog', function (): void {
    $this->seed(LocationSeeder::class);

    expect(State::query()->count())->toBe(27)
        ->and(City::query()->where('state_code', 'SC')->where('name', 'Jaragua do Sul')->exists())
        ->toBeTrue()
        ->and(City::query()->where('state_code', 'PR')->where('name', 'Curitiba')->exists())
        ->toBeTrue()
        ->and(City::query()->where('state_code', 'SP')->where('name', 'Sao Paulo')->exists())
        ->toBeTrue();
});
