<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MassiveDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Generando datos controlados para pruebas...');
        
        DB::beginTransaction();
        try {
            $this->generateSubscriptions(100);
            $this->generateReports(500);
            $this->generateDebts(1500);

            DB::commit();
            $this->command->info('✅ Datos generados exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function generateSubscriptions($qty): void
    {
        $subscriptions = [];
        for ($i = 1; $i <= $qty; $i++) {
            $subscriptions[] = [
                'full_name' => "Usuario Test {$i}",
                'document' => str_pad($i, 8, '0', STR_PAD_LEFT),
                'email' => "usuario{$i}@test.com",
                'phone' => '+519' . rand(10000000, 99999999),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('subscriptions')->insert($subscriptions);
    }

    private function generateReports($qty): void
    {
        $reports = [];
        for ($i = 1; $i <= $qty; $i++) {
            $reports[] = [
                'subscription_id' => rand(1, 100),
                'period' => '2025-12',
                'created_at' => Carbon::create(2025, 12, rand(1, 30)),
                'updated_at' => now(),
            ];
        }
        DB::table('subscription_reports')->insert($reports);
    }

    private function generateDebts($totalQty): void
    {
        $banks = ['BCP', 'Interbank', 'BBVA'];
        $entities = ['Telefónica', 'Claro', 'Sedapal'];
        $perType = $totalQty / 3;

        for ($i = 0; $i < $perType; $i++) {
            DB::table('report_loans')->insert([
                'subscription_report_id' => rand(1, 500),
                'bank' => $banks[array_rand($banks)],
                'status' => 'NOR',
                'currency' => 'PEN',
                'amount' => rand(1000, 5000),
                'expiration_days' => rand(0, 30),
                'created_at' => now(),
            ]);
        }

        for ($i = 0; $i < $perType; $i++) {
            DB::table('report_other_debts')->insert([
                'subscription_report_id' => rand(1, 500),
                'entity' => $entities[array_rand($entities)],
                'currency' => 'PEN',
                'amount' => rand(100, 500),
                'expiration_days' => rand(0, 10),
                'created_at' => now(),
            ]);
        }

        for ($i = 0; $i < $perType; $i++) {
            DB::table('report_credit_cards')->insert([
                'subscription_report_id' => rand(1, 500),
                'bank' => $banks[array_rand($banks)],
                'currency' => 'PEN',
                'line' => 5000,
                'used' => rand(500, 2000),
                'created_at' => now(),
            ]);
        }
    }
}