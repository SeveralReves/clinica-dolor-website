<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Specialist;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

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
                'name' => 'Sharon Alondra Guerra Sierralta',
                'role' => 'Médico Cirujano - Médico Estético',
                'description' => 'Especialista en medicina estética y cirugía, brindando atención integral a sus pacientes.',
                'photo' => 'doctors/doctor-1.webp',
                'phone' => '0414-3137942',
                'email' => 's.alonestetica@gmail.com',
            ],
            [
                'name' => 'Víctor Fonseca',
                'role' => 'Reumatólogo',
                'description' => 'Especialista en enfermedades reumáticas y del sistema musculoesquelético.',
                'photo' => 'doctors/doctor-2.webp',
                'phone' => '0414-4440079',
                'email' => 'victor.fonseca@clinicadeldolor.com',
            ],
            [
                'name' => 'Darwin Antonio Abreu Martínez',
                'role' => 'Médico General Integral',
                'description' => 'Médico general con enfoque integral en la atención y bienestar del paciente.',
                'photo' => 'doctors/doctor-3.webp',
                'phone' => '0424-3233577',
                'email' => 'darwin.abreu@clinicadeldolor.com',
            ],
            [
                'name' => 'Leolgavis Rattia',
                'role' => 'Psicólogo Clínico',
                'description' => 'Especialista en psicología clínica, acompañando a los pacientes en su salud mental y emocional.',
                'photo' => 'doctors/doctor-4.webp',
                'phone' => '0414-0393174',
                'email' => 'leolgavis.rattia@clinicadeldolor.com',
            ],
            [
                'name' => 'Yisman Parra',
                'role' => 'Cirujano General',
                'description' => 'Cirujano general con amplia experiencia en procedimientos quirúrgicos de alta complejidad.',
                'photo' => 'doctors/doctor-5.webp',
                'phone' => '0414-1471357',
                'email' => 'yisman.parra@clinicadeldolor.com',
            ],
            [
                'name' => 'Carmen Carrillo',
                'role' => 'Médico Anestesiólogo - Medicina del Dolor',
                'description' => 'Con más de una década de experiencia, la Dra. Carrillo es especialista en Medicina del Dolor y Anestesiología.',
                'photo' => 'doctors/doctor-6.webp',
                'phone' => '0414-9221029',
                'email' => 'carmen.carrillo@clinicadeldolor.com',
            ],
        ];

        foreach ($specialists as $data) {
           $specialist = Specialist::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'specialty' => $data['role'],
                    'description' => $data['description'],
                    'photo_path' => $data['photo'],
                    'phone' => $data['phone'],
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