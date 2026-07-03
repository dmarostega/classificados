<?php

namespace App\Services;

use App\Models\City;
use App\Models\State;

class LocationOptionsService
{
    public function states(): array
    {
        return State::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['code', 'name'])
            ->map(fn (State $state): array => [
                'value' => $state->code,
                'label' => "{$state->code} - {$state->name}",
            ])
            ->all();
    }

    public function cities(): array
    {
        return City::query()
            ->orderBy('state_code')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['state_code', 'name'])
            ->map(fn (City $city): array => [
                'value' => $city->name,
                'label' => $city->name,
                'state_code' => $city->state_code,
            ])
            ->all();
    }
}
