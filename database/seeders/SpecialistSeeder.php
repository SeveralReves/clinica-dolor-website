<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Specialist;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SpecialistSeeder extends Seeder
{
    public function run(): void
    {
        $sourcePath = database_path('seeders/images/doctors');
        $destinationPath = storage_path('app/public/doctors');

        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }

        if (File::exists($sourcePath)) {
            File::copyDirectory($sourcePath, $destinationPath);
            $this->command->info('Imágenes de especialistas copiadas al storage.');
        } else {
            $this->command->warn('No se encontró la carpeta de origen de imágenes en: ' . $sourcePath);
        }

        $specialists = [
            [
                'name' => 'Dra. CARMEN CARRILLO',
                'role' => 'Medicina del Dolor - Anestesiólogo',
                'description' => 'Con más de una década de experiencia, la Dra. Carrillo es la especialista experta en Medicina del Dolor.',
                'photo' => 'doctors/doctor-1.webp'
            ],
            [
                'name' => 'Dr. MARIA GUILLEN',
                'role' => 'Anestesiólogo',
                'description' => 'Como médico en jefe de Clínica del Dolor Apure, la Dra. Guillen se especializa en Anestesiología.',
                'photo' => 'doctors/doctor-2.webp'
            ],
            [
                'name' => 'Dra. Natalia Ramos',
                'role' => 'Medicina fisica y Rehabilitacion',
                'description' => 'La Dra. Ramos cuenta con más de 15 años de experiencia en las áreas de fertilidad y obstetricia.',
                'photo' => 'doctors/doctor-3.webp'
            ],
            [
                'name' => 'Dr. Juan Pérez',
                'role' => 'Especialista en Manejo del Dolor',
                'description' => 'Con más de 15 años de experiencia en el tratamiento del dolor crónico, el Dr. Pérez lidera nuestro equipo con un enfoque centrado en el paciente.',
                'photo' => 'doctors/doctor1.jpg'
            ],
            [
                'name' => 'Dra. María Gómez',
                'role' => 'Fisioterapeuta',
                'description' => 'Especialista en rehabilitación y fisioterapia para el manejo del dolor.',
                'photo' => 'doctors/doctor2.png'
            ],
            [
                'name' => 'Dr. Carlos Rodríguez',
                'role' => 'Anestesiólogo',
                'description' => 'Experto en técnicas de anestesia y procedimientos intervencionistas para el alivio del dolor.',
                'photo' => 'doctors/doctor3.png'
            ]
            // ... resto de los doctores
        ];

        foreach ($specialists as $data) {
           $specialist = Specialist::updateOrCreate(
                ['email' => Str::slug($data['name']) . '@clinicadeldolor.com'],
                [
                    'name' => $data['name'],
                    'specialty' => $data['role'],
                    'description' => $data['description'],
                    'photo_path' => $data['photo'],
                    'phone' => '+58 412-' . mt_rand(1000000, 9999999),
                    'is_active' => true,
                ]
            );
            $this->createDefaultSchedules($specialist);
        }
    }
    private function createDefaultSchedules($specialist)
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        
        foreach ($days as $day) {
            $specialist->schedules()->createMany([
                ['day' => $day, 'start_time' => '08:00', 'end_time' => '12:00'],
                ['day' => $day, 'start_time' => '14:00', 'end_time' => '17:00'],
            ]);
        }
    }
}