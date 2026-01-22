<?php

namespace App\Services;

use App\Exports\CreditReportExport;
use App\Jobs\GenerateLargeCreditReport;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportService
{
    private const CACHE = 7200; // 2 horas
    private const LIMIT = 1000;

    public function processExport(string $startDate, string $endDate)
    {
        $estimatedSize = $this->estimateReportSize($startDate, $endDate);

        if ($estimatedSize > self::LIMIT) {
            return $this->queueLargeReport($startDate, $endDate, $estimatedSize);
        }

        return $this->generateImmediate($startDate, $endDate);
    }

    public function getStatus(string $jobId): ?array
    {
        return Cache::get("report_status_{$jobId}");
    }

    public function updateStatus(string $jobId, array $data): void
    {
        Cache::put("report_status_{$jobId}", $data, self::CACHE);
    }

    private function generateImmediate($startDate, $endDate)
    {
        $fileName = 'reporte_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new CreditReportExport($startDate, $endDate), $fileName);
    }

    private function queueLargeReport($startDate, $endDate, $size): array
    {
        $jobId = uniqid('report_');
        
        $this->updateStatus($jobId, [
            'status' => 'queued',
            'queued_at' => now(),
            'estimated_records' => $size
        ]);

        GenerateLargeCreditReport::dispatch($startDate, $endDate, $jobId);

        return [
            'job_id' => $jobId,
            'status' => 'queued',
            'check_status_url' => route('report.status', $jobId)
        ];
    }

    private function estimateReportSize($startDate, $endDate): int
    {
        \Log::info("estimateReportSize");
        return DB::table('subscription_reports')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count() * 3.5;
    }
}