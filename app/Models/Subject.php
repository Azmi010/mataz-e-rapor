<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(ClassModel::class, 'class_subjects', 'subject_id', 'class_model_id')
            ->withPivot('teacher_id')
            ->withTimestamps();
    }

    public function classModels(): BelongsToMany
    {
        return $this->belongsToMany(ClassModel::class, 'class_subjects', 'subject_id', 'class_model_id')
            ->withPivot('teacher_id')
            ->withTimestamps();
    }

    public function details(): HasMany
    {
        return $this->hasMany(SubjectDetail::class)->orderBy('order');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(SubjectCategory::class, 'category_id');
    }
}
