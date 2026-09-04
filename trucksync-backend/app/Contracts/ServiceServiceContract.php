<?php

namespace App\Contracts;

use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;

interface ServiceServiceContract
{
    /**
     * @return Collection<int, Service>
     */
    public function all(): Collection;

    public function find(int $id): ?Service;

    public function create(string $name): Service;

    public function delete(Service $service): void;
}
