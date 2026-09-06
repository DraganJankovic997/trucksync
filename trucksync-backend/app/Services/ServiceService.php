<?php

namespace App\Services;

use App\Contracts\ServiceServiceContract;
use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;

class ServiceService implements ServiceServiceContract
{
    /**
     * @return Collection<int, Service>
     */
    public function all(): Collection
    {
        return Service::query()
            ->orderBy('name')
            ->get();
    }

    public function find(int $id): ?Service
    {
        return Service::query()->find($id);
    }

    public function create(string $name, ?string $measurementUnit): Service
    {
        return Service::query()->create([
            'name' => $name,
            'measurement_unit' => $measurementUnit,
        ])->refresh();
    }

    public function delete(Service $service): void
    {
        $service->delete();
    }
}
