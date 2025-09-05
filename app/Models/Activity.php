<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function dailyActivities(): HasMany
    {
        return $this->hasMany(DailyActivity::class);
    }
}
