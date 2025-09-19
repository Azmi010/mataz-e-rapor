<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportCard extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(ReportCardGrade::class);
    }

    public function getAttendanceArrayAttribute(): array
    {
        if (!$this->attendance) return [];
        try {
            return json_decode($this->attendance, true) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function numericToLetter(?int $score): ?string
    {
        if ($score === null) return null;
        return match(true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 70 => 'C',
            $score >= 60 => 'D',
            default => 'E',
        };
    }
}
