<?php

namespace Database\Seeders;

use App\Models\Profesion;
use Illuminate\Database\Seeder;

class ProfesionSeeder extends Seeder
{
    /**
     * Catálogo inicial de profesiones. Es idempotente: no duplica ni
     * reactiva registros que el administrador haya desactivado.
     */
    public function run(): void
    {
        $profesiones = [
            'Ninguna',
            'Abogado(a)',
            'Administrador(a)',
            'Analista de Sistemas',
            'Antropólogo(a)',
            'Arquitecto(a)',
            'Auditor(a)',
            'Bibliotecólogo(a)',
            'Bioanalista',
            'Biólogo(a)',
            'Chef',
            'Comunicador(a) Social',
            'Contador(a) Público(a)',
            'Criminólogo(a)',
            'Diseñador(a) Gráfico(a)',
            'Docente',
            'Economista',
            'Electricista',
            'Enfermero(a)',
            'Estadístico(a)',
            'Farmacéutico(a)',
            'Filósofo(a)',
            'Fisioterapeuta',
            'Físico(a)',
            'Fonoaudiólogo(a)',
            'Geógrafo(a)',
            'Geólogo(a)',
            'Historiador(a)',
            'Ingeniero(a) Agrónomo(a)',
            'Ingeniero(a) Civil',
            'Ingeniero(a) de Petróleo',
            'Ingeniero(a) de Sistemas',
            'Ingeniero(a) Electricista',
            'Ingeniero(a) Industrial',
            'Ingeniero(a) Mecánico(a)',
            'Ingeniero(a) Químico(a)',
            'Matemático(a)',
            'Mecánico(a)',
            'Médico(a) Cirujano(a)',
            'Médico(a) Veterinario(a)',
            'Militar',
            'Músico',
            'Nutricionista',
            'Odontólogo(a)',
            'Optometrista',
            'Orientador(a)',
            'Periodista',
            'Piloto',
            'Policía',
            'Politólogo(a)',
            'Psicólogo(a)',
            'Psicopedagogo(a)',
            'Químico(a)',
            'Sociólogo(a)',
            'Técnico Radiólogo(a)',
            'Técnico Superior Universitario (TSU) en Administración',
            'Técnico Superior Universitario (TSU) en Contaduría',
            'Técnico Superior Universitario (TSU) en Electricidad',
            'Técnico Superior Universitario (TSU) en Enfermería',
            'Técnico Superior Universitario (TSU) en Informática',
            'Técnico Superior Universitario (TSU) en Mecánica',
            'Terapeuta Ocupacional',
            'Topógrafo(a)',
            'Trabajador(a) Social',
            'Traductor(a)',
            'Zootecnista',
        ];

        foreach ($profesiones as $nombre) {
            Profesion::firstOrCreate(['nombre' => $nombre]);
        }
    }
}
