<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Grupo;
use App\Models\Materia;
use App\Models\Gestion;

class GrupoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $grupoModel = new Grupo();
        
        // Obtener materias y gestiones existentes
        $materiaModel = new Materia();
        $gestionModel = new Gestion();
        
        $materias = $materiaModel->findAll();
        $gestiones = $gestionModel->findAll();
        
        if (empty($materias) || empty($gestiones)) {
            $this->command->warn('No hay materias o gestiones en la base de datos. Saltando seeder de grupos.');
            return;
        }
        
        // Usar la primera gestión disponible
        $gestion = $gestiones[0];
        
        // Crear grupos para cada materia
        $grupos = ['SA', 'SB', 'SC', 'SD'];
        
        foreach ($materias as $index => $materia) {
            // Cada materia tendrá entre 2 y 4 grupos
            $numGrupos = min(4, 2 + ($index % 3));
            
            for ($i = 0; $i < $numGrupos; $i++) {
                $grupoModel->create([
                    'nombre_grupo' => $grupos[$i],
                    'idmateria' => $materia['idmateria'],
                    'idgestion' => $gestion['idgestion'],
                    'capacidad' => 30 + ($i * 5) // 30, 35, 40, 45
                ]);
                
                $this->command->info("Grupo creado: {$materia['sigla']} - {$grupos[$i]} (Capacidad: " . (30 + ($i * 5)) . ")");
            }
        }
        
        $this->command->info('Grupos creados exitosamente');
    }
}
