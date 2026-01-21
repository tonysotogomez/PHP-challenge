<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Facades\DB;

class CreditReportExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = date('Y-m-d 00:00:00', strtotime($startDate));
        $this->endDate = date('Y-m-d 23:59:59', strtotime($endDate));
    }

    /**
     * OPTIMIZACIÓN DE MEMORIA: Procesamiento por chunks
     * En lugar de cargar todo en memoria, procesamos de 1000 en 1000
     */
    public function query()
    {
        /** * Usamos UNION para obtener todo en una sola pasada de base de datos.
         * Esto es mucho más rápido que 3 chunks separados.
         */
        $loans = DB::table('report_loans as rl')
            ->join('subscription_reports as sr', 'rl.subscription_report_id', '=', 'sr.id')
            ->join('subscriptions as s', 'sr.subscription_id', '=', 's.id')
            ->select([
                'sr.id as report_id', 's.full_name', 's.document', 's.email', 's.phone',
                'rl.bank as compañia', DB::raw("'Préstamo' as tipo"), 'rl.status', 
                'rl.expiration_days', 'rl.bank as entidad', 'rl.amount', 
                DB::raw('0 as linea_total'), DB::raw('0 as linea_usada'), 'sr.created_at'
            ])
            ->whereBetween('sr.created_at', [$this->startDate, $this->endDate]);

        $other = DB::table('report_other_debts as rd')
            ->join('subscription_reports as sr', 'rd.subscription_report_id', '=', 'sr.id')
            ->join('subscriptions as s', 'sr.subscription_id', '=', 's.id')
            ->select([
                'sr.id as report_id', 's.full_name', 's.document', 's.email', 's.phone',
                'rd.entity as compañia', DB::raw("'Otra deuda' as tipo"), DB::raw("'NOR' as status"), 
                'rd.expiration_days', 'rd.entity as entidad', 'rd.amount', 
                DB::raw('0 as linea_total'), DB::raw('0 as linea_usada'), 'sr.created_at'
            ])
            ->whereBetween('sr.created_at', [$this->startDate, $this->endDate]);

        return DB::table('report_credit_cards as rc')
            ->join('subscription_reports as sr', 'rc.subscription_report_id', '=', 'sr.id')
            ->join('subscriptions as s', 'sr.subscription_id', '=', 's.id')
            ->select([
                'sr.id as report_id', 's.full_name', 's.document', 's.email', 's.phone',
                'rc.bank as compañia', DB::raw("'Tarjeta' as tipo"), DB::raw("'NOR' as status"), 
                DB::raw('0 as expiration_days'), 'rc.bank as entidad', 'rc.used as amount', 
                'rc.line as linea_total', 'rc.used as linea_usada', 'sr.created_at'
            ])
            ->whereBetween('sr.created_at', [$this->startDate, $this->endDate])
            ->union($loans)
            ->union($other)
            ->orderBy('report_id', 'asc');
    }

    public function map($row): array
    {
        return [
            $row->report_id,
            $row->full_name,
            $row->document,
            $row->email,
            $row->phone,
            $row->compañia,
            $row->tipo,
            $row->status,
            $row->expiration_days,
            $row->entidad,
            number_format($row->amount, 2),
            number_format($row->linea_total, 2),
            number_format($row->linea_usada, 2),
            $row->created_at,
            'Activo'
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nombre Completo',
            'DNI',
            'Email',
            'Teléfono',
            'Compañía',
            'Tipo de deuda',
            'Situación',
            'Atraso',
            'Entidad',
            'Monto total',
            'Línea total',
            'Línea usada',
            'Reporte subido el',
            'Estado'
        ];
    }
}