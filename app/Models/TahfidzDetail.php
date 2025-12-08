<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TahfidzDetail extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'grade' => 'integer',
    ];

    public function reportCard(): BelongsTo
    {
        return $this->belongsTo(ReportCard::class);
    }

    public function tahfidz(): BelongsTo
    {
        return $this->belongsTo(Tahfidz::class);
    }
}
