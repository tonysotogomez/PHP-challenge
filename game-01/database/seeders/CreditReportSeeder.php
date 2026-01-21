<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class CreditReportSeeder extends Seeder
{
    public function run(): void
    {
        $sqlPath = database_path('seeders/data/database.sql');
        
        if (!File::exists($sqlPath)) {
            $this->command->error("Archivo SQL no encontrado en: {$sqlPath}");
            return;
        }

        $this->command->info('📂 Leyendo archivo SQL...');
        $sql = File::get($sqlPath);
        

        $sql = preg_replace('/^--.*$/m', '', $sql);
        $statements = explode(';', $sql);
        
        DB::beginTransaction();
        
        try {
            $executed = 0;
            
            foreach ($statements as $statement) {
                $statement = trim($statement);
                
                if (empty($statement)) {
                    continue;
                }
                
                if (stripos($statement, 'INSERT') === 0) {
                    DB::statement($statement);
                    $executed++;
                }
            }
            
            $this->command->info("Datos base importados ({$executed} statements ejecutados)");
            $this->addTimestamps();
            
            DB::commit();
            $this->command->info("Fechas agregadas exitosamente");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error al importar datos: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Agregar timestamps coherentes a los datos
     */
    private function addTimestamps(): void
    {
        $this->command->info('Agregando fechas coherentes...');
        
        $baseDate = Carbon::create(2025, 12, 1);
        $endDate = Carbon::create(2025, 12, 31);
        
        $subscriptions = DB::table('subscriptions')->get(['id']);
        foreach ($subscriptions as $subscription) {
            $createdAt = $baseDate->copy()->subDays(rand(1, 30));
            $updatedAt = $createdAt->copy()->addDays(rand(0, 5));
            
            DB::table('subscriptions')
                ->where('id', $subscription->id)
                ->update([
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt
                ]);
        }

        $reports = DB::table('subscription_reports')->get(['id']);
        foreach ($reports as $report) {
            $createdAt = $baseDate->copy()->addDays(rand(0, 30));
            $updatedAt = $createdAt->copy()->addHours(rand(1, 24));
            
            DB::table('subscription_reports')
                ->where('id', $report->id)
                ->update([
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt
                ]);
        }

        $loans = DB::table('report_loans')
            ->join('subscription_reports', 'report_loans.subscription_report_id', '=', 'subscription_reports.id')
            ->select('report_loans.id', 'subscription_reports.created_at as report_date')
            ->get();
            
        foreach ($loans as $loan) {
            $reportDate = Carbon::parse($loan->report_date);
            $createdAt = $reportDate->copy()->addMinutes(rand(1, 60));
            $updatedAt = $createdAt->copy()->addMinutes(rand(1, 30));
            
            DB::table('report_loans')
                ->where('id', $loan->id)
                ->update([
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt
                ]);
        }

        $otherDebts = DB::table('report_other_debts')
            ->join('subscription_reports', 'report_other_debts.subscription_report_id', '=', 'subscription_reports.id')
            ->select('report_other_debts.id', 'subscription_reports.created_at as report_date')
            ->get();
            
        foreach ($otherDebts as $debt) {
            $reportDate = Carbon::parse($debt->report_date);
            $createdAt = $reportDate->copy()->addMinutes(rand(1, 60));
            $updatedAt = $createdAt->copy()->addMinutes(rand(1, 30));
            
            DB::table('report_other_debts')
                ->where('id', $debt->id)
                ->update([
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt
                ]);
        }

        $creditCards = DB::table('report_credit_cards')
            ->join('subscription_reports', 'report_credit_cards.subscription_report_id', '=', 'subscription_reports.id')
            ->select('report_credit_cards.id', 'subscription_reports.created_at as report_date')
            ->get();
            
        foreach ($creditCards as $card) {
            $reportDate = Carbon::parse($card->report_date);
            $createdAt = $reportDate->copy()->addMinutes(rand(1, 60));
            $updatedAt = $createdAt->copy()->addMinutes(rand(1, 30));
            
            DB::table('report_credit_cards')
                ->where('id', $card->id)
                ->update([
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt
                ]);
        }
    }
}
