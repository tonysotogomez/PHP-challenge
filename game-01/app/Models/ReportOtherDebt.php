<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportOtherDebt extends Model
{
    protected $table = 'report_other_debts';

    protected $fillable = ['subscription_report_id', 'entity', 'currency', 'amount', 'expiration_days', 'created_at', 'updated_at'];

    public function report()
    {
        return $this->belongsTo(SubscriptionReport::class, 'subscription_report_id');
    }
}
