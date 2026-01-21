<?php

namespace Database\Seeders\Deployment;

use App\Models\Workshop;
use Illuminate\Database\Seeder;

class WorkshopSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            'Artes',
            'Circo',
            'Educação Ambiental',
            'Educação Financeira',
            'Laboratorio criativo',
            'Musica',
            'Nivelamento de Matematica',
            'Nivelamento de Portugues',
            'Teatro',
            'Xadrez',
            'Ingles',
        ];

        foreach ($items as $name) {
            Workshop::updateOrCreate(
                ['name' => $name],
                ['name' => $name, 'is_active' => true]
            );
        }
    }
}

