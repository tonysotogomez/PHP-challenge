<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class SubscriptionReport extends Model
{
    protected $table = 'subscriptions';

    protected $fillable = ['period', 'created_at'];

    public function subscription() {
        return $this->belongsTo(Subscription::class);
    }

    public function loans() {
        return $this->hasMany(ReportLoan::class);
    }

    public function otherDebts() {
        return $this->hasMany(ReportOtherDebt::class);
    }

    public function creditCards() {
        return $this->hasMany(ReportCreditCard::class);
    }
}
