<?php

namespace Database\Seeders;

use App\Models\Dispatcher;
use App\Models\Driver;
use App\Models\RestStop;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    private const PASSWORD = 'password';

    private const TEST_DRIVER_EMAIL = 'driver@trucksync.com';

    private const TEST_DISPATCHER_EMAIL = 'dispatcher@trucksync.com';

    private const TEST_REST_STOP_EMAIL = 'reststop@trucksync.com';

    /**
     * Seed profile rows for base accounts and create additional demo users.
     */
    public function run(): void
    {
        $testDispatcher = $this->upsertDispatcher(
            $this->userByEmail(self::TEST_DISPATCHER_EMAIL),
            'TruckSync Test Dispatch',
            'Belgrade',
            'Bulevar Milutina Milankovica 9',
            '11070',
            'TS-DISPATCH-TEST',
            true
        );

        $this->upsertRestStop(
            $this->userByEmail(self::TEST_REST_STOP_EMAIL),
            'Belgrade',
            'Autoput za Zagreb 18',
            '11070',
            '00:00',
            '23:59',
            true
        );

        $this->upsertDriver(
            $this->userByEmail(self::TEST_DRIVER_EMAIL),
            'TS-DRV-TEST-001',
            $testDispatcher->id,
            true
        );

        $this->seedDemoDispatchers();
        $this->seedDemoRestStops();
        $this->seedDemoDrivers($testDispatcher);
    }

    private function seedDemoDispatchers(): void
    {
        $cities = [
            'Belgrade',
            'Novi Sad',
            'Nis',
            'Subotica',
            'Kragujevac',
            'Cacak',
            'Valjevo',
            'Sombor',
            'Zrenjanin',
            'Leskovac',
        ];

        foreach (range(1, 10) as $index) {
            $user = $this->upsertUser(
                sprintf('dispatcher%d@trucksync.com', $index),
                'Demo',
                sprintf('Dispatcher %02d', $index),
                'dispatcher',
                '+38161'.sprintf('%06d', 100000 + $index)
            );

            $this->upsertDispatcher(
                $user,
                sprintf('Demo Dispatch %02d', $index),
                $cities[$index - 1],
                sprintf('Logistics Street %d', $index),
                sprintf('11%03d', $index),
                sprintf('TS-DISPATCH-DEMO-%02d', $index),
                $index <= 8
            );
        }
    }

    private function seedDemoRestStops(): void
    {
        $restStops = [
            ['Austria', 'Vienna', 'A4 Corridor 12', '1300', '06:00', '23:00'],
            ['Hungary', 'Budapest', 'M1 Service Road 8', '2040', '05:30', '22:30'],
            ['Croatia', 'Zagreb', 'A3 Rest Zone 14', '10000', '00:00', '23:59'],
            ['Slovenia', 'Ljubljana', 'Ring Road 22', '1000', '06:00', '22:00'],
            ['Germany', 'Munich', 'Freight Park 5', '80331', '05:00', '23:00'],
            ['Czechia', 'Prague', 'D1 Logistics Stop 31', '11000', '06:00', '21:30'],
            ['Slovakia', 'Bratislava', 'D2 Transit Hub 7', '82101', '06:30', '22:00'],
            ['Austria', 'Graz', 'A9 Truck Court 17', '8010', '05:30', '23:30'],
            ['Austria', 'Salzburg', 'A1 Rest Point 4', '5020', '07:00', '21:00'],
            ['Slovenia', 'Maribor', 'E59 Service Yard 3', '2000', '06:00', '20:30'],
        ];

        foreach ($restStops as $index => [$country, $city, $address, $postCode, $worksFrom, $worksTo]) {
            $number = $index + 1;
            $user = $this->upsertUser(
                sprintf('reststop%d@trucksync.com', $number),
                'Demo',
                sprintf('Rest Stop %02d', $number),
                'rest_stop',
                '+38162'.sprintf('%06d', 200000 + $number),
                $country
            );

            $this->upsertRestStop(
                $user,
                $city,
                $address,
                $postCode,
                $worksFrom,
                $worksTo,
                $number <= 8
            );
        }
    }

    private function seedDemoDrivers(Dispatcher $testDispatcher): void
    {
        foreach (range(1, 10) as $index) {
            $user = $this->upsertUser(
                sprintf('driver%d@trucksync.com', $index),
                'Demo',
                sprintf('Driver %02d', $index),
                'driver',
                '+38163'.sprintf('%06d', 300000 + $index)
            );

            $this->upsertDriver(
                $user,
                sprintf('TS-DRV-DEMO-%02d', $index),
                $index <= 6 ? $testDispatcher->id : null,
                $index <= 6
            );
        }
    }

    private function upsertUser(
        string $email,
        string $firstName,
        string $lastName,
        string $profileType,
        string $phoneNumber,
        string $country = 'Serbia'
    ): User {
        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'country' => $country,
                'phone_number' => $phoneNumber,
                'profile_type' => $profileType,
                'password' => Hash::make(self::PASSWORD),
            ]
        );

        $user->forceFill([
            'email_verified_at' => now(),
        ])->save();

        return $user;
    }

    private function userByEmail(string $email): User
    {
        return User::query()
            ->where('email', $email)
            ->firstOrFail();
    }

    private function upsertDispatcher(
        User $user,
        string $companyName,
        string $city,
        string $address,
        string $postCode,
        string $registrationNumber,
        bool $isApproved
    ): Dispatcher {
        return Dispatcher::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'company_name' => $companyName,
                'city' => $city,
                'address' => $address,
                'post_code' => $postCode,
                'registration_number' => $registrationNumber,
                'is_approved' => $isApproved,
            ]
        );
    }

    private function upsertRestStop(
        User $user,
        string $city,
        string $address,
        string $postCode,
        string $worksFrom,
        string $worksTo,
        bool $isApproved
    ): RestStop {
        return RestStop::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'city' => $city,
                'address' => $address,
                'post_code' => $postCode,
                'works_from' => $worksFrom,
                'works_to' => $worksTo,
                'is_approved' => $isApproved,
            ]
        );
    }

    private function upsertDriver(
        User $user,
        string $licenseNumber,
        ?int $dispatcherId,
        bool $isDispatcherApproved
    ): Driver {
        return Driver::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'dispatcher_id' => $dispatcherId,
                'license_number' => $licenseNumber,
                'is_dispatcher_approved' => $isDispatcherApproved,
            ]
        );
    }
}
