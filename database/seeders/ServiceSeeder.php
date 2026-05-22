<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'title' => 'Gimnasio Médico',
                'description' => 'Un concepto de entrenamiento inteligente bajo supervisión profesional. Contamos con áreas de fuerza y cardio ideales para mantener un estilo de vida activo, fortalecer tu sistema muscular y prevenir futuras lesiones, siempre adaptado a tus capacidades y objetivos de salud.',
                'photo_path' => null,
            ],
            [
                'title' => 'Spa Terapéutico',
                'description' => 'Un oasis diseñado para desconectar el cuerpo y la mente. Más que un lujo, nuestro Spa ofrece terapias de relajación profunda, masajes terapéuticos y tratamientos que reducen el estrés y la inflamación, optimizando la respuesta de tu cuerpo hacia la sanación.',
                'photo_path' => null,
            ],
            [
                'title' => 'Sala de Rehabilitación',
                'description' => 'Nuestro espacio de rehabilitación combina tecnología de vanguardia con fisioterapia especializada. Diseñamos programas personalizados para tratar lesiones, aliviar el dolor crónico y devolverte la libertad de movimiento en un entorno seguro y supervisado por expertos.',
                'photo_path' => null,
            ],
        ];

        foreach ($services as $data) {
            $service = Service::updateOrCreate(
                ['title' => $data['title']],
                [
                    'description' => $data['description'],
                    'photo_path' => $data['photo_path'],
                    'is_active' => true,
                ]
            );

            $this->createOfficeSchedules($service);
        }
    }

    private function createOfficeSchedules(Service $service): void
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

        foreach ($days as $day) {
            $service->schedules()->createMany([
                ['day' => $day, 'start_time' => '08:00', 'end_time' => '12:00'],
                ['day' => $day, 'start_time' => '14:00', 'end_time' => '17:00'],
            ]);
        }
    }
}
