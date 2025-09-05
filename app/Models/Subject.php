<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Subject extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(ClassModel::class, 'class_subjects', 'subject_id', 'class_model_id');
    }

    public function classModels(): BelongsToMany
    {
        return $this->belongsToMany(ClassModel::class, 'class_subjects', 'subject_id', 'class_model_id');
    }
}
