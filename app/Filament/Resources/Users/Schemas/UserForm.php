<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\ClassModel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->revealable()
                    ->required(fn (string $context): bool => $context === 'create')
                    ->minLength(8)
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->helperText('Minimal 8 karakter. Kosongkan jika tidak ingin mengubah password.'),
                Select::make('role')
                    ->label('Peran')
                    ->options([
                        'admin' => 'Admin',
                        'teacher' => 'Guru',
                        'student' => 'Siswa',
                    ])
                    ->required()
                    ->default('student')
                    ->live()
                    ->helperText('Pilih peran untuk menentukan akses panel yang sesuai.'),

                TextInput::make('teacher.nip')
                    ->label('NIP')
                    ->required()
                    ->maxLength(255)
                    ->visible(fn (callable $get) => $get('role') === 'teacher')
                    ->helperText('Nomor Induk Pegawai'),

                TextInput::make('teacher.phone')
                    ->label('No. HP')
                    ->tel()
                    ->maxLength(20)
                    ->visible(fn (callable $get) => $get('role') === 'teacher')
                    ->helperText('Nomor telepon yang bisa dihubungi'),

                TextInput::make('teacher.address')
                    ->label('Alamat')
                    ->maxLength(500)
                    ->visible(fn (callable $get) => $get('role') === 'teacher')
                    ->helperText('Alamat lengkap tempat tinggal'),

                TextInput::make('student.nis')
                    ->label('NIS')
                    ->required()
                    ->maxLength(255)
                    ->visible(fn (callable $get) => $get('role') === 'student')
                    ->helperText('Nomor Induk Siswa'),

                Select::make('student.class_id')
                    ->label('Kelas')
                    ->options(ClassModel::pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->visible(fn (callable $get) => $get('role') === 'student')
                    ->helperText('Kelas siswa saat ini'),

                TextInput::make('student.wali')
                    ->label('Nama Wali')
                    ->maxLength(255)
                    ->visible(fn (callable $get) => $get('role') === 'student')
                    ->helperText('Nama orang tua/wali siswa'),

                TextInput::make('student.phone')
                    ->label('No. HP Wali')
                    ->tel()
                    ->maxLength(20)
                    ->visible(fn (callable $get) => $get('role') === 'student')
                    ->helperText('Nomor telepon wali yang bisa dihubungi'),

                TextInput::make('student.address')
                    ->label('Alamat')
                    ->maxLength(500)
                    ->visible(fn (callable $get) => $get('role') === 'student')
                    ->helperText('Alamat lengkap tempat tinggal'),
            ]);
    }
}
