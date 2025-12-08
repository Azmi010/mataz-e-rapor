<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tahfidz extends Model
{
    use HasFactory;

    protected $table = 'tahfidz';

    protected $guarded = [];

    protected $casts = [
        'juz' => 'integer',
    ];

    public function tahfidzDetails(): HasMany
    {
        return $this->hasMany(TahfidzDetail::class);
    }
}
