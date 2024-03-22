<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $datos = [
            array(
                'name' => 'Administrador',
                'description' => 'Este rol tiene acceso a todas las funcionalidades del sistema.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            array(
                'name' => 'Encuestador',
                'description' => 'Este rol puede crear, editar y aplicar encuestas.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            array(
                'name' => 'Participante',
                'description' => 'Este rol puede responder encuestas del sistema.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
        ];
        
        
        DB::table('roles')->insert($datos);

    }
}
