<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\ClassModel;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use League\Csv\Writer;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected static ?string $title = 'Data Akun';

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\ActionGroup::make([
                Action::make('downloadTemplateAdmin')
                    ->label('Template Admin')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        return $this->downloadTemplate('admin');
                    }),
                Action::make('downloadTemplateTeacher')
                    ->label('Template Guru')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        return $this->downloadTemplate('teacher');
                    }),
                Action::make('downloadTemplateStudent')
                    ->label('Template Siswa')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        return $this->downloadTemplate('student');
                    }),
            ])
                ->label('Download Template')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('warning')
                ->button(),

            Action::make('uploadCsv')
                ->label('Upload CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    FileUpload::make('file')
                        ->label('File CSV')
                        ->acceptedFileTypes(['text/csv', 'application/csv', 'text/plain'])
                        ->required()
                        ->helperText('Upload file CSV sesuai template. Format kolom harus sesuai dengan template yang didownload.')
                ])
                ->action(function (array $data) {
                    return $this->importCsv($data['file']);
                }),

            CreateAction::make()
                ->label('Tambah Akun'),
        ];
    }

    protected function downloadTemplate($role = 'admin')
    {
        $csv = Writer::createFromString('');

        if ($role === 'admin') {
            $csv->insertOne(['name', 'email', 'password', 'role']);

            $csv->insertOne(['John Admin', 'admin@example.com', 'password123', 'admin']);
            $csv->insertOne(['Jane Admin', 'jane.admin@example.com', 'password123', 'admin']);

            $filename = 'template_admin_' . date('Y-m-d_His') . '.csv';

        } elseif ($role === 'teacher') {
            $csv->insertOne(['name', 'email', 'password', 'role', 'nip', 'phone', 'address']);

            $csv->insertOne(['Budi Guru', 'budi.guru@example.com', 'password123', 'teacher', 'NIP123456', '081234567890', 'Jl. Guru No. 1']);
            $csv->insertOne(['Siti Guru', 'siti.guru@example.com', 'password123', 'teacher', 'NIP789012', '081234567891', 'Jl. Pendidik No. 2']);

            $filename = 'template_guru_' . date('Y-m-d_His') . '.csv';

        } else {
            $csv->insertOne(['name', 'email', 'password', 'role', 'nis', 'class_name', 'wali', 'phone', 'address']);

            $csv->insertOne(['Ahmad Siswa', 'ahmad.siswa@example.com', 'password123', 'student', 'NIS123456', 'Kelas 1', 'Budi Santoso', '081234567890', 'Jl. Siswa No. 1']);
            $csv->insertOne(['Fatimah Siswa', 'fatimah.siswa@example.com', 'password123', 'student', 'NIS789012', 'Kelas 2', 'Ahmad Rahman', '081234567891', 'Jl. Pelajar No. 2']);

            $filename = 'template_siswa_' . date('Y-m-d_His') . '.csv';
        }

        return response()->streamDownload(function () use ($csv) {
            echo $csv->toString();
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }    protected function importCsv($filePath)
    {
        try {
            $file = Storage::disk('local')->path($filePath);
            $csv = Reader::createFromPath($file, 'r');
            $csv->setHeaderOffset(0);

            $records = $csv->getRecords();
            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ($records as $index => $record) {
                try {
                    if (empty($record['name']) || empty($record['email']) || empty($record['password']) || empty($record['role'])) {
                        $errors[] = "Baris " . ($index + 2) . ": Data dasar (name, email, password, role) tidak lengkap";
                        $errorCount++;
                        continue;
                    }

                    $roleMapping = [
                        'admin' => 'admin',
                        'guru' => 'teacher',
                        'teacher' => 'teacher',
                        'siswa' => 'student',
                        'student' => 'student',
                    ];

                    $role = strtolower(trim($record['role']));
                    if (!isset($roleMapping[$role])) {
                        $errors[] = "Baris " . ($index + 2) . ": Role tidak valid (harus: admin, teacher/guru, atau student/siswa)";
                        $errorCount++;
                        continue;
                    }

                    $mappedRole = $roleMapping[$role];

                    if ($mappedRole === 'teacher') {
                        if (!isset($record['nip']) || empty($record['nip'])) {
                            $errors[] = "Baris " . ($index + 2) . ": NIP wajib diisi untuk role teacher";
                            $errorCount++;
                            continue;
                        }
                        if (Teacher::where('nip', $record['nip'])->exists()) {
                            $errors[] = "Baris " . ($index + 2) . ": NIP {$record['nip']} sudah terdaftar";
                            $errorCount++;
                            continue;
                        }
                    }

                    if ($mappedRole === 'student') {
                        if (!isset($record['nis']) || empty($record['nis'])) {
                            $errors[] = "Baris " . ($index + 2) . ": NIS wajib diisi untuk role student";
                            $errorCount++;
                            continue;
                        }
                        if (Student::where('nis', $record['nis'])->exists()) {
                            $errors[] = "Baris " . ($index + 2) . ": NIS {$record['nis']} sudah terdaftar";
                            $errorCount++;
                            continue;
                        }
                        if (!isset($record['class_name']) || empty($record['class_name'])) {
                            $errors[] = "Baris " . ($index + 2) . ": class_name wajib diisi untuk role student";
                            $errorCount++;
                            continue;
                        }
                        $class = ClassModel::where('name', $record['class_name'])->first();
                        if (!$class) {
                            $errors[] = "Baris " . ($index + 2) . ": Kelas '{$record['class_name']}' tidak ditemukan";
                            $errorCount++;
                            continue;
                        }
                    }

                    if (User::where('email', $record['email'])->exists()) {
                        $errors[] = "Baris " . ($index + 2) . ": Email {$record['email']} sudah terdaftar";
                        $errorCount++;
                        continue;
                    }

                    DB::beginTransaction();

                    try {
                        $user = User::create([
                            'name' => $record['name'],
                            'email' => $record['email'],
                            'password' => Hash::make($record['password']),
                            'role' => $mappedRole,
                        ]);

                        if ($mappedRole === 'teacher') {
                            Teacher::create([
                                'user_id' => $user->id,
                                'nip' => $record['nip'],
                                'phone' => $record['phone'] ?? null,
                                'address' => $record['address'] ?? null,
                            ]);
                        }

                        if ($mappedRole === 'student') {
                            $class = ClassModel::where('name', $record['class_name'])->first();

                            Student::create([
                                'user_id' => $user->id,
                                'nis' => $record['nis'],
                                'class_id' => $class->id,
                                'wali' => $record['wali'] ?? null,
                                'phone' => $record['phone'] ?? null,
                                'address' => $record['address'] ?? null,
                            ]);
                        }

                        DB::commit();
                        $successCount++;
                    } catch (\Exception $e) {
                        DB::rollBack();
                        throw $e;
                    }

                } catch (\Exception $e) {
                    $errorMessage = $e->getMessage();
                    $userFriendlyMessage = $this->parseErrorMessage($errorMessage, $record);

                    $errors[] = "Baris " . ($index + 2) . ": " . $userFriendlyMessage;
                    $errorCount++;
                }
            }

            Storage::disk('local')->delete($filePath);

            if ($successCount > 0 && $errorCount === 0) {
                Notification::make()
                    ->success()
                    ->title('Import Berhasil')
                    ->body("Berhasil menambahkan {$successCount} akun.")
                    ->send();
            } elseif ($successCount > 0 && $errorCount > 0) {
                Notification::make()
                    ->warning()
                    ->title('Import Selesai dengan Error')
                    ->body("Berhasil: {$successCount} akun. Gagal: {$errorCount} akun. " . implode(', ', array_slice($errors, 0, 3)))
                    ->send();
            } else {
                Notification::make()
                    ->danger()
                    ->title('Import Gagal')
                    ->body('Gagal menambahkan akun. ' . implode(', ', array_slice($errors, 0, 3)))
                    ->send();
            }

        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error')
                ->body('Terjadi kesalahan saat membaca file CSV. Pastikan format file sesuai dengan template.')
                ->send();
        }
    }

    protected function parseErrorMessage($errorMessage, $record)
    {
        if (stripos($errorMessage, 'Duplicate entry') !== false && stripos($errorMessage, 'users_email_unique') !== false) {
            return "Email {$record['email']} sudah terdaftar";
        }

        if (stripos($errorMessage, 'Duplicate entry') !== false && stripos($errorMessage, 'teachers_nip_unique') !== false) {
            return "NIP {$record['nip']} sudah terdaftar";
        }

        if (stripos($errorMessage, 'Duplicate entry') !== false && stripos($errorMessage, 'students_nis_unique') !== false) {
            return "NIS {$record['nis']} sudah terdaftar";
        }

        if (stripos($errorMessage, 'Duplicate entry') !== false) {
            return "Data duplikat ditemukan";
        }

        if (stripos($errorMessage, 'Data truncated') !== false) {
            return "Format data tidak sesuai";
        }

        if (stripos($errorMessage, 'foreign key constraint') !== false) {
            return "Data terkait tidak ditemukan (periksa class_id)";
        }

        return "Terjadi kesalahan saat menyimpan data";
    }
}
