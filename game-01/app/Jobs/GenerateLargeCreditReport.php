<?php

namespace App\Jobs;

use App\Exports\CreditReportExport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\ReportService;

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
    public function handle(ReportService $service): void
    {
        $fileName = 'reporte_crediticio_' . now()->format('Ymd_His') . '.xlsx';
        $filePath = 'reports/' . $fileName;

        try {
            $service->updateStatus($this->jobId, [
                'status' => 'processing', 
                'started_at' => now(),
                'progress' => 0
            ]);

            // Generar el reporte con optimizaciones de memoria
            Excel::store(new CreditReportExport($this->startDate, $this->endDate), $filePath, 'public');

            // Marcar como completado
            $service->updateStatus($this->jobId, [
                'status' => 'completed',
                'file_path' => $filePath,
                'file_name' => $fileName,
                'disk' => 'public',
                'generated_at' => now(),
                'download_url' => route('report.download', $this->jobId)
            ]);

        } catch (\Exception $e) {    
            $service->updateStatus($this->jobId, [
                'status' => 'failed', 
                'error' => $e->getMessage(),
                'failed_at' => now(),
                'started_at' => now(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $service = app(ReportService::class);

        $service->updateStatus($this->jobId, [
            'status' => 'failed',
            'error' => $exception->getMessage(),
            'failed_at' => now()
        ]);
    }
}
