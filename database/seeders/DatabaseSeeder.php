<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create('id_ID');

        // =====================================================
        // 1. TRAINS
        // =====================================================
        $classes = ['EXECUTIVE', 'BUSINESS', 'ECONOMY'];
        $trainIds = [];

        for ($i = 1; $i <= 10; $i++) {
            $trainIds[] = DB::table('trains')->insertGetId([
                'train_code'  => 'TRN-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'train_name'  => $faker->company . ' Express',
                'train_class' => $faker->randomElement($classes),
                'total_seats' => $faker->numberBetween(50, 200),
            ]);
        }

        // Simpan total_seats per train_id supaya tidak query ulang saat generate booking
        $trainSeats = DB::table('trains')->pluck('total_seats', 'train_id')->all();

        // =====================================================
        // 2. SCHEDULES
        // =====================================================
        $stations = ['Jakarta', 'Bandung', 'Surabaya', 'Yogyakarta', 'Semarang', 'Malang'];
        $scheduleIds = [];
        $scheduleTrain = []; // schedule_id => train_id, dipakai nanti untuk cek kapasitas
        $schedulePrice = []; // schedule_id => base_ticket_price

        foreach ($trainIds as $trainId) {
            for ($j = 0; $j < 3; $j++) {
                $origin = $faker->randomElement($stations);
                do {
                    $destination = $faker->randomElement($stations);
                } while ($destination === $origin);

                $dep = $faker->dateTimeBetween('-15 days', '+30 days');
                $arr = (clone $dep)->modify('+' . $faker->numberBetween(2, 8) . ' hours');
                $price = $faker->numberBetween(150000, 950000);

                $scheduleId = DB::table('schedules')->insertGetId([
                    'train_id'            => $trainId,
                    'origin_station'      => $origin,
                    'destination_station' => $destination,
                    'departure_time'      => $dep,
                    'arrival_time'        => $arr,
                    'base_ticket_price'   => $price,
                ]);

                $scheduleIds[] = $scheduleId;
                $scheduleTrain[$scheduleId] = $trainId;
                $schedulePrice[$scheduleId] = $price;
            }
        }

        // =====================================================
        // 3. PASSENGERS + PASSENGER_DETAILS + USERS (role PASSENGER)
        // =====================================================
        $passengerIds = [];

        for ($i = 0; $i < 500; $i++) {
            $fullName = $faker->name;
            $identityNumber = $faker->unique()->numerify('################'); // 16 digit
            $email = $faker->unique()->safeEmail;

            $passengerId = DB::table('passengers')->insertGetId([
                'identity_number' => $identityNumber,
                'full_name'       => $fullName,
                'email'           => $email,
                'phone_number'    => $faker->phoneNumber,
                'passenger_type'  => $faker->randomElement(['ADULT', 'INFANT']),
                'created_at'      => now(),
            ]);
            $passengerIds[] = $passengerId;

            DB::table('passenger_details')->insert([
                'passenger_id'            => $passengerId,
                'address'                 => $faker->address,
                'emergency_contact_name'  => $faker->name,
                'emergency_contact_phone' => $faker->phoneNumber,
            ]);

            // Akun login untuk penumpang ini.
            // Password default = No. Identitas (sama seperti alur create manual di Admin/Passengers).
            DB::table('users')->insert([
                'name'          => $fullName,
                'email'         => $email,
                'password'      => Hash::make($identityNumber),
                'role'          => 'PASSENGER',
                'passenger_id'  => $passengerId,
                'email_verified_at' => now(),
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        // =====================================================
        // 4. USER ADMIN & OFFICER (tidak terhubung ke passenger_id)
        // =====================================================
        DB::table('users')->insert([
            [
                'name'              => 'Railway Admin',
                'email'             => 'admin@railway.test',
                'password'          => Hash::make('password123'),
                'role'              => 'ADMIN',
                'passenger_id'      => null,
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'name'              => 'Station Officer',
                'email'             => 'officer@railway.test',
                'password'          => Hash::make('password123'),
                'role'              => 'OFFICER',
                'passenger_id'      => null,
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
        ]);

        // =====================================================
        // 5. BOOKINGS
        // =====================================================
        $statuses = ['CONFIRMED', 'CANCELLED', 'COMPLETED'];
        $seatCounter = []; // schedule_id => jumlah kursi yang sudah terisi (dipakai untuk generate seat_number & batasi kapasitas)
        $bookingBatch = [];
        $targetBookings = 1200;
        $created = 0;
        $attempts = 0;
        $maxAttempts = $targetBookings * 5; // guard supaya tidak infinite loop kalau semua schedule penuh

        while ($created < $targetBookings && $attempts < $maxAttempts) {
            $attempts++;

            $scheduleId = $faker->randomElement($scheduleIds);
            $trainId = $scheduleTrain[$scheduleId];
            $capacity = $trainSeats[$trainId];

            $seatCounter[$scheduleId] ??= 0;

            // Lewati jadwal yang sudah penuh, sesuai batas kapasitas kereta
            if ($seatCounter[$scheduleId] >= $capacity) {
                continue;
            }

            $seatCounter[$scheduleId]++;
            $status = $faker->randomElement($statuses);
            $price = $schedulePrice[$scheduleId];

            $bookingBatch[] = [
                'passenger_id'   => $faker->randomElement($passengerIds),
                'schedule_id'    => $scheduleId,
                'booking_code'   => 'BK' . strtoupper($faker->unique()->bothify('??####??')),
                'seat_number'    => 'S' . str_pad((string) $seatCounter[$scheduleId], 3, '0', STR_PAD_LEFT),
                'booking_status' => $status,
                // booking dibatalkan dianggap sudah direfund penuh (selaras dengan sp_archive_cancelled_bookings)
                'total_paid'     => $status === 'CANCELLED' ? 0 : $price,
            ];
            $created++;

            if (count($bookingBatch) === 300) {
                DB::table('bookings')->insert($bookingBatch);
                $bookingBatch = [];
            }
        }

        if (!empty($bookingBatch)) {
            DB::table('bookings')->insert($bookingBatch);
        }

        // =====================================================
        // 6. PASSENGER_TRAVEL_LOGS — 5.000+ baris untuk uji indexing
        // =====================================================
        $actions = ['GATE_IN', 'GATE_OUT', 'BOARDING'];
        $logBatch = [];

        for ($i = 0; $i < 5000; $i++) {
            $logBatch[] = [
                'passenger_id' => $faker->randomElement($passengerIds),
                'action_type'  => $faker->randomElement($actions),
                'gate_number'  => 'G' . $faker->numberBetween(1, 20),
                'scanned_at'   => $faker->dateTimeBetween('-30 days', 'now'),
            ];

            if (count($logBatch) === 500) {
                DB::table('passenger_travel_logs')->insert($logBatch);
                $logBatch = [];
            }
        }

        if (!empty($logBatch)) {
            DB::table('passenger_travel_logs')->insert($logBatch);
        }

        $this->command->info('Seeding selesai: ' . count($passengerIds) . ' passenger, '
            . $created . ' booking, 5000 travel log, 1 admin, 1 officer.');
    }
}
