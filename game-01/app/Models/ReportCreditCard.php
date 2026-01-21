<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportCreditCard extends Model
{
    protected $table = 'report_credit_cards';

    protected $fillable = ['subscription_report_id', 'bank', 'currency', 'line', 'used', 'created_at'];

    public function report()
    {
        return $this->belongsTo(SubscriptionReport::class, 'subscription_report_id');
    }
}
