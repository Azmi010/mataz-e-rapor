<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $fillable = [
        'user_id',
        'class_id',
        'nis',
        'nisn',
        'gender',
        'birth_place',
        'birth_date',
        'religion',
        'family_status',
        'child_order',
        'previous_school',
        'accepted_date',
        'accepted_in_class',
        'father_name',
        'mother_name',
        'father_occupation',
        'father_occupation_other',
        'mother_occupation',
        'mother_occupation_other',
        'parent_address',
        'guardian_name',
        'guardian_occupation',
        'guardian_occupation_other',
        'guardian_address',
        'wali',
        'phone',
        'address',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'accepted_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function classModel(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function dailyActivities(): HasMany
    {
        return $this->hasMany(DailyActivity::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function reportCards(): HasMany
    {
        return $this->hasMany(ReportCard::class);
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(Achievement::class);
    }
}
