<?php

namespace Database\Seeders;

use App\Models\Workshop;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

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

        $hasIsActive = Schema::hasColumn('workshops', 'is_active');

        foreach ($items as $name) {
            $values = ['name' => $name];
            if ($hasIsActive) {
                $values['is_active'] = true;
            }

            Workshop::updateOrCreate(
                ['name' => $name], // chave para idempotência
                $values
            );
        }
    }
}

