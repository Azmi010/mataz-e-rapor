<?php

namespace App\Filament\Teacher\Resources\HomeroomResource\Pages;

use App\Filament\Teacher\Resources\HomeroomResource;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\ClassModel;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class HomeroomReports extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = HomeroomResource::class;

    protected string $view = 'filament.teacher.resources.homeroom.reports';

    protected static bool $shouldRegisterNavigation = false;

    public function getTitle(): string
    {
        $teacher = Teacher::where('user_id', Auth::id())->first();
        $class = ClassModel::where('homeroom_teacher_id', $teacher?->id)->first();

        return 'Data Rapor - Kelas ' . ($class?->name ?? '');
    }

    public function getHeading(): string
    {
        return $this->getTitle();
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('index')
                    ->label('No')
                    ->rowIndex(),
                TextColumn::make('nisn')
                    ->label('NISN')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('gender')
                    ->label('L/P')
                    ->formatStateUsing(fn (string $state): string => $state === 'L' ? 'L' : 'P'),
                TextColumn::make('ttl')
                    ->label('TTL')
                    ->state(function (Student $record) {
                        if (!$record->birth_place || !$record->birth_date) return '-';

                        $months = [
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                        ];

                        $day = $record->birth_date->format('d');
                        $month = $months[(int)$record->birth_date->format('m')];
                        $year = $record->birth_date->format('Y');

                        return $record->birth_place . ', ' . $day . ' ' . $month . ' ' . $year;
                    }),
            ])
            ->actions([
                Action::make('rapor')
                    ->label('Rapor')
                    ->icon('heroicon-o-document-text')
                    ->color('danger')
                    ->button()
                    ->url(fn (Student $record): string => route('rapor.pdf', ['student' => $record->id]))
                    ->openUrlInNewTab(),
                Action::make('rekap')
                    ->label('Rekap')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('info')
                    ->button()
                    ->action(function (Student $record) {
                        Notification::make()
                            ->title('Fitur Rekap')
                            ->body('Fitur ini akan segera tersedia')
                            ->info()
                            ->send();
                    }),
                ActionGroup::make([
                    Action::make('sampul')
                        ->label('Sampul')
                        ->icon('heroicon-o-book-open')
                        ->action(function (Student $record) {
                            Notification::make()
                                ->title('Fitur Sampul')
                                ->body('Fitur ini akan segera tersedia')
                                ->info()
                                ->send();
                        }),
                    Action::make('identitas')
                        ->label('Identitas')
                        ->icon('heroicon-o-identification')
                        ->action(function (Student $record) {
                            Notification::make()
                                ->title('Fitur Identitas')
                                ->body('Fitur ini akan segera tersedia')
                                ->info()
                                ->send();
                        }),
                ])
                ->button()
                ->color('warning')
                ->icon('heroicon-o-ellipsis-vertical')
                ->tooltip('Lainnya'),
            ])
            ->defaultSort('nisn', 'asc')
            ->paginated(false);
    }

    protected function getTableQuery(): Builder
    {
        $teacher = Teacher::where('user_id', Auth::id())->first();
        $class = ClassModel::where('homeroom_teacher_id', $teacher?->id)->first();

        return Student::query()
            ->where('class_id', $class?->id)
            ->with(['user'])
            ->orderBy('nisn');
    }
}
