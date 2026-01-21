<?php

namespace App\Jobs;

use App\Exports\CreditReportExport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class GenerateLargeCreditReport implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $timeout = 3600; // 1 hora de timeout
    public $tries = 3; // 3 intentos

    protected $startDate;
    protected $endDate;
    protected $jobId;

    /**
     * Create a new job instance.
     */
    public function __construct($startDate, $endDate, $jobId)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->jobId = $jobId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Log::info("Iniciando Job para ID: " . $this->jobId);

        $fileName = 'reporte_crediticio_' . now()->format('Ymd_His') . '.xlsx';
        $filePath = 'reports/' . $fileName;

        try {
            \Log::info("Intentando generar Excel...");
            Cache::put("report_status_{$this->jobId}", [
                'status' => 'processing',
                'started_at' => now(),
                'progress' => 0
            ], 7200); // 2 horas de cache

            // Generar el reporte con optimizaciones de memoria
            Excel::store(new CreditReportExport($this->startDate, $this->endDate), $filePath, 'public');

            // Marcar como completado
            Cache::put("report_status_{$this->jobId}", [
                'status' => 'completed',
                'file_path' => $filePath,
                'file_name' => $fileName,
                'disk' => 'public',
                'generated_at' => now(),
                'download_url' => route('report.download', $this->jobId)
            ], 7200);

        } catch (\Exception $e) {
            \Log::error("ERROR EN EL JOB: " . $e->getMessage());
            Cache::put("report_status_{$this->jobId}", [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'failed_at' => now(),
                'started_at' => now(),
            ], 7200);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Cache::put("report_status_{$this->jobId}", [
            'status' => 'failed',
            'error' => $exception->getMessage(),
            'failed_at' => now()
        ], 7200);
    }
}
