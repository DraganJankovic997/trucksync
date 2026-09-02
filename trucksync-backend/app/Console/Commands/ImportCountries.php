<?php

namespace App\Console\Commands;

use App\Models\Country;
use Illuminate\Console\Command;
use JsonException;

class ImportCountries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'countries:import {--path= : Path to the countries JSON file. Defaults to database/data/countries.json.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import countries from a JSON file.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $path = trim($this->option('path')) ?: database_path('data/countries.json');

        if (! is_string($path) || $path === '') {
            $this->error('The countries JSON path is invalid.');

            return self::FAILURE;
        }

        if (! is_file($path)) {
            $this->error("Countries JSON file not found: {$path}");

            return self::FAILURE;
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            $this->error("Unable to read countries JSON file: {$path}");

            return self::FAILURE;
        }

        try {
            $payload = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            $this->error("Unable to parse countries JSON file: {$exception->getMessage()}");

            return self::FAILURE;
        }

        if (! is_array($payload) || ! isset($payload['countries']) || ! is_array($payload['countries'])) {
            $this->error('Countries JSON file must contain a countries array.');

            return self::FAILURE;
        }

        if ($payload['countries'] === []) {
            $this->error('Countries JSON file does not contain any countries to import.');

            return self::FAILURE;
        }

        $rows = $this->buildRows($payload['countries']);

        if ($rows === []) {
            return self::FAILURE;
        }

        Country::query()->truncate();

        foreach (array_chunk($rows, 500) as $chunk) {
            Country::query()->insert($chunk);
        }

        $this->info('Imported '.count($rows).' countries.');

        return self::SUCCESS;
    }

    /**
     * @param  array<int, mixed>  $countries
     * @return array<int, array{code: string, name: string, created_at: string, updated_at: string}>
     */
    private function buildRows(array $countries): array
    {
        $rows = [];
        $codes = [];
        $names = [];
        $timestamp = now()->toDateTimeString();

        foreach ($countries as $index => $country) {
            $rowNumber = $index + 1;

            if (! is_array($country)) {
                $this->error("Country row {$rowNumber} must be an object.");

                return [];
            }

            $code = $country['code'] ?? null;
            $name = $country['name'] ?? null;

            if (! is_string($code) || ! is_string($name)) {
                $this->error("Country row {$rowNumber} must contain string code and name values.");

                return [];
            }

            $code = strtoupper(trim($code));
            $name = trim($name);

            if (strlen($code) !== 2) {
                $this->error("Country row {$rowNumber} must contain a two-letter code.");

                return [];
            }

            if ($name === '') {
                $this->error("Country row {$rowNumber} must contain a name.");

                return [];
            }

            if (isset($codes[$code])) {
                $this->error("Duplicate country code found: {$code}");

                return [];
            }

            if (isset($names[$name])) {
                $this->error("Duplicate country name found: {$name}");

                return [];
            }

            $codes[$code] = true;
            $names[$name] = true;

            $rows[] = [
                'code' => $code,
                'name' => $name,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        return $rows;
    }
}
