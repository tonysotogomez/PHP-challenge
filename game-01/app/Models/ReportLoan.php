<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportLoan extends Model
{
    protected $table = 'report_loans';

    protected $fillable = ['subscription_report_id', 'bank', 'status', 'currency', 'amount', 'expiration_days', 'created_at'];

    public function report()
    {
        return $this->belongsTo(SubscriptionReport::class, 'subscription_report_id');
    }
}
