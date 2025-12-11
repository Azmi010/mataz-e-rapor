<?php

namespace App\Filament\Resources\SubjectCategories\Pages;

use App\Filament\Resources\SubjectCategories\SubjectCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSubjectCategory extends CreateRecord
{
    protected static string $resource = SubjectCategoryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
