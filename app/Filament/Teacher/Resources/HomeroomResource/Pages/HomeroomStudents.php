<?php

namespace App\Filament\Teacher\Resources\HomeroomResource\Pages;

use App\Filament\Teacher\Resources\HomeroomResource;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\ClassModel;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;

class HomeroomStudents extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = HomeroomResource::class;

    protected string $view = 'filament.teacher.resources.homeroom.students';

    protected static bool $shouldRegisterNavigation = false;

    private array $occupationOptions = [
        'Dokter' => 'Dokter', 'Pilot' => 'Pilot', 'Pedagang' => 'Pedagang',
        'Petani/Peternak' => 'Petani/Peternak', 'Nelayan' => 'Nelayan',
        'Buruh' => 'Buruh', 'Sopir/Masinis' => 'Sopir/Masinis',
        'Politikus' => 'Politikus', 'Tidak Bekerja' => 'Tidak Bekerja',
        'Pensiunan' => 'Pensiunan', 'PNS' => 'PNS', 'TNI/Polri' => 'TNI/Polri',
        'Guru/Dosen' => 'Guru/Dosen', 'Pegawai Swasta' => 'Pegawai Swasta',
        'Wiraswasta/Wirausaha' => 'Wiraswasta/Wirausaha',
        'Pengacara/Hakim/Jaksa/Notaris' => 'Pengacara/Hakim/Jaksa/Notaris',
        'Seniman' => 'Seniman', 'Lainnya' => 'Lainnya'
    ];

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        $teacher = Teacher::where('user_id', Auth::id())->first();
        $class = ClassModel::where('homeroom_teacher_id', $teacher?->id)->first();

        return 'Data Siswa - ' . ($class?->name ?? '');
    }

    public function getHeading(): string
    {
        return $this->getTitle();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('index')->label('No')->rowIndex(),
                TextColumn::make('nis')->label('NIS/NISN')
                    ->formatStateUsing(fn (Student $record) => $record->nis . ($record->nisn ? ' / ' . $record->nisn : ''))
                    ->searchable(['nis', 'nisn']),
                TextColumn::make('user.name')->label('Nama')->searchable()->sortable(),
                TextColumn::make('gender')->label('Jenis Kelamin')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'L' => 'info', 'P' => 'danger', default => 'gray',
                    }),
                TextColumn::make('ttl')->label('TTL')->state(function (Student $record) {
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
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Edit Data Siswa')
                    ->modalSubmitActionLabel('Simpan')
                    ->modalWidth('7xl')
                    ->fillForm(fn (Student $record): array => [
                        'nis' => $record->nis,
                        'nisn' => $record->nisn,
                        'name' => $record->user->name,
                        'gender' => $record->gender,
                        'birth_place' => $record->birth_place,
                        'birth_date' => $record->birth_date,
                        'religion' => $record->religion,
                        'family_status' => $record->family_status,
                        'child_order' => $record->child_order,
                        'phone' => $record->phone,
                        'address' => $record->address,
                        'previous_school' => $record->previous_school,
                        'accepted_date' => $record->accepted_date,
                        'accepted_in_class' => $record->accepted_in_class,
                        'father_name' => $record->father_name,
                        'mother_name' => $record->mother_name,
                        'father_occupation' => $record->father_occupation,
                        'father_occupation_other' => $record->father_occupation_other,
                        'mother_occupation' => $record->mother_occupation,
                        'mother_occupation_other' => $record->mother_occupation_other,
                        'parent_address' => $record->parent_address,
                        'wali' => $record->wali,
                        'guardian_occupation' => $record->guardian_occupation,
                        'guardian_occupation_other' => $record->guardian_occupation_other,
                        'guardian_address' => $record->guardian_address,
                    ])
                    ->form([
                        Grid::make(2)
                            ->schema([
                                Group::make()
                                    ->schema([
                                        TextInput::make('nis')->label('NIS')->required(),
                                        TextInput::make('nisn')->label('NISN'),
                                        TextInput::make('name')->label('Nama')->required(),
                                        Select::make('gender')->label('Jenis Kelamin')
                                            ->options(['L' => 'Laki-laki', 'P' => 'Perempuan'])
                                            ->required(),

                                        Grid::make(2)->schema([
                                            TextInput::make('birth_place')->label('Tempat Lahir'),
                                            DatePicker::make('birth_date')->label('Tanggal Lahir')->native(false)->displayFormat('d/m/Y'),
                                        ]),

                                        Select::make('religion')->label('Agama')
                                            ->options(['Islam' => 'Islam', 'Kristen' => 'Kristen', 'Katolik' => 'Katolik', 'Hindu' => 'Hindu', 'Buddha' => 'Buddha', 'Konghucu' => 'Konghucu']),

                                        Grid::make(2)->schema([
                                            Select::make('family_status')->label('Status Keluarga')
                                                ->options(['Anak Kandung' => 'Anak Kandung', 'Anak Tiri' => 'Anak Tiri', 'Anak Angkat' => 'Anak Angkat']),
                                            TextInput::make('child_order')->label('Anak Ke')->numeric(),
                                        ]),

                                        TextInput::make('phone')->label('Telpon Siswa')->tel(),
                                        Textarea::make('address')->label('Alamat Siswa')->rows(3),
                                    ])
                                    ->columnSpan(1),

                                Group::make()
                                    ->schema([
                                        TextInput::make('previous_school')->label('Sekolah Asal'),

                                        Grid::make(2)->schema([
                                            DatePicker::make('accepted_date')->label('Tanggal Diterima')->native(false)->displayFormat('d/m/Y'),
                                            Select::make('accepted_in_class')->label('Terima di kelas')->options(ClassModel::pluck('name', 'id')),
                                        ]),

                                        Grid::make(2)->schema([
                                            TextInput::make('father_name')->label('Nama Ayah'),
                                            TextInput::make('mother_name')->label('Nama Ibu'),
                                        ]),

                                        Select::make('father_occupation')->label('Pekerjaan Ayah')
                                            ->options($this->occupationOptions)->reactive(),
                                        TextInput::make('father_occupation_other')->label('Pekerjaan Ayah Lainnya')
                                            ->visible(fn ($get) => $get('father_occupation') === 'Lainnya'),

                                        Select::make('mother_occupation')->label('Pekerjaan Ibu')
                                            ->options($this->occupationOptions)->reactive(),
                                        TextInput::make('mother_occupation_other')->label('Pekerjaan Ibu Lainnya')
                                            ->visible(fn ($get) => $get('mother_occupation') === 'Lainnya'),

                                        Textarea::make('parent_address')->label('Alamat Orang Tua')->rows(3),

                                        TextInput::make('wali')->label('Nama Wali'),
                                        Select::make('guardian_occupation')->label('Pekerjaan Wali')
                                            ->options($this->occupationOptions)->reactive(),
                                        TextInput::make('guardian_occupation_other')->label('Pekerjaan Wali Lainnya')
                                            ->visible(fn ($get) => $get('guardian_occupation') === 'Lainnya'),
                                        Textarea::make('guardian_address')->label('Alamat Wali')->rows(3),
                                    ])
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->action(function (array $data, Student $record): void {
                        if (isset($data['name'])) {
                            $record->user->update(['name' => $data['name']]);
                            unset($data['name']);
                        }

                        $record->update($data);
                        Notification::make()->success()->title('Data siswa berhasil diperbarui')->send();
                    }),
            ])
            ->defaultSort('nis', 'asc');
    }

    protected function getTableQuery(): Builder
    {
        $teacher = Teacher::where('user_id', Auth::id())->first();
        $class = ClassModel::where('homeroom_teacher_id', $teacher?->id)->first();

        return Student::query()
            ->where('class_id', $class?->id)
            ->with(['user']);
    }
}
