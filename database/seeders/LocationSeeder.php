<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = json_decode(
            File::get(database_path('seeders/data/locations.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        collect($locations['states'])->each(function (array $state, int $index): void {
            State::query()->updateOrCreate(
                ['code' => $state['code']],
                [
                    'name' => $state['name'],
                    'sort_order' => ($index + 1) * 10,
                ]
            );

            collect($state['cities'])->each(function (string $city, int $cityIndex) use ($state): void {
                City::query()->updateOrCreate(
                    ['state_code' => $state['code'], 'name' => $city],
                    ['sort_order' => ($cityIndex + 1) * 10]
                );
            });
        });
    }
}
