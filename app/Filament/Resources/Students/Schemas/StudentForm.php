<?php

namespace App\Filament\Resources\Students\Schemas;

use App\Models\Student;
use App\Models\ClassModel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;

class StudentForm
{
    private static array $occupationOptions = [
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

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Pilih User')
                    ->relationship('user', 'name', function($query, $livewire) {
                        $query->where('role', 'student');

                        $excludeUserIds = Student::pluck('user_id');

                        if ($livewire->record) {
                            $excludeUserIds = $excludeUserIds->reject(fn($id) => $id == $livewire->record->user_id);
                        }

                        $query->whereNotIn('id', $excludeUserIds);
                    })
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->name} ({$record->email})")
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('class_id')
                    ->label('Kelas')
                    ->relationship('classModel', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),

                TextInput::make('nis')
                    ->label('NIS (Nomor Induk Siswa)')
                    ->required()
                    ->maxLength(20)
                    ->unique(ignoreRecord: true),

                TextInput::make('nisn')
                    ->label('NISN')
                    ->maxLength(20),

                Select::make('gender')
                    ->label('Jenis Kelamin')
                    ->options([
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan'
                    ])
                    ->required(),

                TextInput::make('birth_place')
                    ->label('Tempat Lahir'),

                DatePicker::make('birth_date')
                    ->label('Tanggal Lahir')
                    ->native(false)
                    ->displayFormat('d/m/Y'),

                Select::make('religion')
                    ->label('Agama')
                    ->options([
                        'Islam' => 'Islam', 'Kristen' => 'Kristen',
                        'Katolik' => 'Katolik', 'Hindu' => 'Hindu',
                        'Buddha' => 'Buddha', 'Konghucu' => 'Konghucu'
                    ]),

                Select::make('family_status')
                    ->label('Status Keluarga')
                    ->options([
                        'Anak Kandung' => 'Anak Kandung',
                        'Anak Tiri' => 'Anak Tiri',
                        'Anak Angkat' => 'Anak Angkat'
                    ]),

                TextInput::make('child_order')
                    ->label('Anak Ke')
                    ->numeric(),

                TextInput::make('phone')
                    ->label('No. Telepon')
                    ->tel()
                    ->maxLength(20)
                    ->nullable()
                    ->helperText('Nomor telepon siswa atau wali'),

                Textarea::make('address')
                    ->label('Alamat')
                    ->rows(3)
                    ->maxLength(500)
                    ->nullable()
                    ->helperText('Alamat lengkap siswa'),

                TextInput::make('previous_school')
                    ->label('Sekolah Asal'),

                DatePicker::make('accepted_date')
                    ->label('Tanggal Diterima')
                    ->native(false)
                    ->displayFormat('d/m/Y'),

                Select::make('accepted_in_class')
                    ->label('Diterima di Kelas')
                    ->options(ClassModel::pluck('name', 'id'))
                    ->searchable(),

                TextInput::make('father_name')
                    ->label('Nama Ayah'),

                TextInput::make('mother_name')
                    ->label('Nama Ibu'),

                Select::make('father_occupation')
                    ->label('Pekerjaan Ayah')
                    ->options(self::$occupationOptions)
                    ->reactive(),

                TextInput::make('father_occupation_other')
                    ->label('Pekerjaan Ayah Lainnya')
                    ->visible(fn ($get) => $get('father_occupation') === 'Lainnya'),

                Select::make('mother_occupation')
                    ->label('Pekerjaan Ibu')
                    ->options(self::$occupationOptions)
                    ->reactive(),

                TextInput::make('mother_occupation_other')
                    ->label('Pekerjaan Ibu Lainnya')
                    ->visible(fn ($get) => $get('mother_occupation') === 'Lainnya'),

                Textarea::make('parent_address')
                    ->label('Alamat Orang Tua')
                    ->rows(3),

                TextInput::make('wali')
                    ->label('Nama Wali')
                    ->maxLength(255)
                    ->nullable(),

                Select::make('guardian_occupation')
                    ->label('Pekerjaan Wali')
                    ->options(self::$occupationOptions)
                    ->reactive(),

                TextInput::make('guardian_occupation_other')
                    ->label('Pekerjaan Wali Lainnya')
                    ->visible(fn ($get) => $get('guardian_occupation') === 'Lainnya'),

                Textarea::make('guardian_address')
                    ->label('Alamat Wali')
                    ->rows(3),
            ]);
    }
}
