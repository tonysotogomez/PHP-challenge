<?php

namespace App\Http\Controllers;

use App\Exports\CreditReportExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

ini_set('memory_limit', '512M');
set_time_limit(300);

class ReportController extends Controller
{
    /**
     * Generar reporte crediticio con optimizaciones
     */
    public function export(Request $request) 
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        \Log::info("export");
        // Verificar el tamaño estimado del reporte
        $estimatedSize = $this->estimateReportSize($startDate, $endDate);
        \Log::info($estimatedSize);
        if ($estimatedSize > 1000) {
            \Log::info("se ejecutara queueLargeReport");
            return $this->queueLargeReport($startDate, $endDate);
        }

        // Para reportes pequeños, generar directamente
        return $this->generateReport($startDate, $endDate);
    }

    /**
     * Generar reporte directamente (reportes pequeños)
     */
    private function generateReport($startDate, $endDate)
    {
        $fileName = 'reporte_crediticio_' . now()->format('Ymd_His') . '.xlsx';
        
        return Excel::download(
            new CreditReportExport($startDate, $endDate), 
            $fileName
        );
    }

    /**
     * Encolar reporte grande para procesamiento en background
     */
    private function queueLargeReport($startDate, $endDate)
    {
        $jobId = uniqid('report_');
        \Log::info("Intentando enviar Job a la cola con ID: " . $jobId);
        // Encolar el trabajo usando el Job
        try {
                \App\Jobs\GenerateLargeCreditReport::dispatch($startDate, $endDate, $jobId);
                \Log::info("Job enviado exitosamente.");
            } catch (\Exception $e) {
                \Log::error("ERROR AL ENVIAR EL JOB: " . $e->getMessage());
            }
        // Marcar como en proceso
        Cache::put("report_status_{$jobId}", [
            'status' => 'queued',
            'queued_at' => now()
        ], 7200);

        return response()->json([
            'message' => 'Reporte grande detectado. Se está procesando en segundo plano.',
            'job_id' => $jobId,
            'estimated_records' => $this->estimateReportSize($startDate, $endDate),
            'check_status_url' => route('report.status', $jobId)
        ]);
    }

    /**
     * Verificar estado del reporte
     */
    public function checkStatus($jobId)
    {
        $status = Cache::get("report_status_{$jobId}");
        
        if (!$status) {
            return response()->json(['error' => 'Trabajo no encontrado'], 404);
        }
        
        if ($status['status'] === 'completed') {
            return response()->json([
                'status' => 'completed',
                'download_url' => route('report.download', $jobId),
                'generated_at' => $status['generated_at']
            ]);
        }
        
        return response()->json([
            'status' => 'processing',
            'started_at' => $status['started_at'] ?? now()
        ]);
    }

    /**
     * Descargar reporte generado
     */
    public function download($jobId)
    {
        $status = Cache::get("report_status_{$jobId}");
        
        if (!$status || $status['status'] !== 'completed') {
            abort(404, 'Reporte no encontrado o aún en proceso');
        }
        
        $disk = $status['disk'] ?? 'local'; 
        $path = $status['file_path'];

        // Verificación de depuración:
        if (!Storage::disk($disk)->exists($status['file_path'])) {
            dd([
                'mensaje' => 'El archivo físico no existe',
                'ruta_guardada_en_cache' => $path,
                'ruta_real_en_disco' => Storage::disk('public')->path($path)
            ]);
        }

        return Storage::disk($disk)->download($path, $status['file_name']);
    }

    /**
     * Estimar tamaño del reporte
     */
    private function estimateReportSize($startDate, $endDate): int
    {
        $reportCount = \DB::table('subscription_reports')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Estimar registros totales (promedio de 3-4 registros por reporte)
        return $reportCount * 3.5;
    }

    /**
     * Mostrar formulario de generación de reportes
     */
    public function index()
    {
        return view('reports.index');
    }
}