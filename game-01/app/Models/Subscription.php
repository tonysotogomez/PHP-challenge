<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $table = 'subscriptions';

    protected $fillable = ['full_name', 'document', 'email', 'phone', 'created_at'];

    public function reports()
    {
        return $this->hasMany(SubscriptionReport::class);
    }
}
