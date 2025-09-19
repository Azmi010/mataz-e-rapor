<?php

namespace App\Console\Commands;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Console\Command;

class LoadFont extends Command
{
    protected $signature = 'dompdf:load-font';
    protected $description = 'Load Arabic fonts for DomPDF';

    public function handle()
    {
        $this->info('Loading Arabic fonts for DomPDF...');

        // Set options
        $options = new Options();
        $options->set('fontDir', [
            storage_path('fonts'),
            public_path('fonts'),
        ]);
        $options->set('fontCache', storage_path('fonts'));
        $options->set('isUnicode', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultMediaType', 'print');

        $dompdf = new Dompdf($options);

        // Register Amiri font
        $fontMetrics = $dompdf->getFontMetrics();

        try {
            // Load normal weight
            $fontMetrics->registerFont([
                'family' => 'Amiri',
                'style' => 'normal',
                'weight' => 'normal'
            ], storage_path('fonts/amiri_normal.ttf'));

            // Load bold weight (jika ada)
            if (file_exists(storage_path('fonts/amiri-bold.ttf'))) {
                $fontMetrics->registerFont([
                    'family' => 'Amiri',
                    'style' => 'normal',
                    'weight' => 'bold'
                ], storage_path('fonts/amiri_bold.ttf'));
            }

            $this->info('✅ Amiri font loaded successfully!');
        } catch (\Exception $e) {
            $this->error('❌ Error loading font: ' . $e->getMessage());

            // Fallback: coba dengan DejaVu Sans
            $this->info('Trying DejaVu Sans as fallback...');

            return 1;
        }

        return 0;
    }
}
