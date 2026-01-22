<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExportReportRequest;
use App\Services\ReportService;
use Illuminate\Support\Facades\Storage;

ini_set('memory_limit', '512M');
set_time_limit(300);

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index()
    {
        return view('reports.index');
    }

    public function export(ExportReportRequest $request) 
    {
        $result = $this->reportService->processExport(
            $request->start_date, 
            $request->end_date
        );

        return is_array($result) ? response()->json($result) : $result;
    }

    public function checkStatus($jobId)
    {
        $status = $this->reportService->getStatus($jobId);
        
        if (!$status) {
            return response()->json(['error' => 'Not Found'], 404);
        }

        return response()->json($status);
    }

    public function download($jobId)
    {
        $status = $this->reportService->getStatus($jobId);
        
        if (!$status || $status['status'] !== 'completed') {
            abort(404);
        }

        return Storage::disk($status['disk'])->download($status['file_path'], $status['file_name']);
    }
}