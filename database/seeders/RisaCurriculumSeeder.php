<?php

namespace Database\Seeders;

use App\Models\Prerequisite;
use App\Models\StudentAcademicHistory;
use App\Models\StudyPlan;
use App\Models\Subject;
use App\Models\SubjectComment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RisaCurriculumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear o recuperar el Plan de Estudios
        $plan = StudyPlan::firstOrCreate(
            ['code' => 'ISIC-2010-224'],
            [
                'name' => 'Ingeniería en Sistemas Computacionales',
                'institution' => 'Instituto Tecnológico de Celaya',
                'total_credits' => 260,
                'total_semesters' => 9,
                'is_active' => true,
            ]
        );

        // 2. Definición del catálogo de materias por semestre conforme al plan oficial y RISA-mockup
        $materiasDef = [
            // Semestre 1
            ['code' => 'ACC-0906', 'name' => 'Fundamentos de Investigación', 'credits' => 4, 'sem' => 1, 'th' => 2, 'pr' => 2],
            ['code' => 'ACF-0901', 'name' => 'Cálculo Diferencial', 'credits' => 5, 'sem' => 1, 'th' => 3, 'pr' => 2],
            ['code' => 'INC-1025', 'name' => 'Química', 'credits' => 4, 'sem' => 1, 'th' => 2, 'pr' => 2],
            ['code' => 'SCD-1008', 'name' => 'Fundamentos de Programación', 'credits' => 5, 'sem' => 1, 'th' => 2, 'pr' => 3],
            ['code' => 'AEF-1041', 'name' => 'Matemáticas Discretas', 'credits' => 5, 'sem' => 1, 'th' => 3, 'pr' => 2],

            // Semestre 2
            ['code' => 'ACD-0908', 'name' => 'Desarrollo Sustentable', 'credits' => 5, 'sem' => 2, 'th' => 2, 'pr' => 3],
            ['code' => 'ACF-0902', 'name' => 'Cálculo Integral', 'credits' => 5, 'sem' => 2, 'th' => 3, 'pr' => 2],
            ['code' => 'ACA-0907', 'name' => 'Taller de Ética', 'credits' => 4, 'sem' => 2, 'th' => 0, 'pr' => 4],
            ['code' => 'SCC-1005', 'name' => 'Contabilidad Financiera', 'credits' => 4, 'sem' => 2, 'th' => 2, 'pr' => 2],
            ['code' => 'SCD-1020', 'name' => 'Programación Orientada a Objetos', 'credits' => 5, 'sem' => 2, 'th' => 2, 'pr' => 3],
            ['code' => 'AEF-1052', 'name' => 'Probabilidad y Estadística', 'credits' => 5, 'sem' => 2, 'th' => 3, 'pr' => 2],

            // Semestre 3
            ['code' => 'ACF-0903', 'name' => 'Álgebra Lineal', 'credits' => 5, 'sem' => 3, 'th' => 3, 'pr' => 2],
            ['code' => 'ACF-0904', 'name' => 'Cálculo Vectorial', 'credits' => 5, 'sem' => 3, 'th' => 3, 'pr' => 2],
            ['code' => 'EDD-1001', 'name' => 'Estructura de Datos', 'credits' => 5, 'sem' => 3, 'th' => 2, 'pr' => 3],
            ['code' => 'SCC-1006', 'name' => 'Cultura Empresarial', 'credits' => 4, 'sem' => 3, 'th' => 2, 'pr' => 2],
            ['code' => 'SCC-1013', 'name' => 'Investigación de Operaciones', 'credits' => 4, 'sem' => 3, 'th' => 2, 'pr' => 2],
            ['code' => 'SCF-1007', 'name' => 'Física General', 'credits' => 5, 'sem' => 3, 'th' => 3, 'pr' => 2],
            ['code' => 'SCH-1024', 'name' => 'Taller de Administración', 'credits' => 4, 'sem' => 3, 'th' => 1, 'pr' => 3],

            // Semestre 4
            ['code' => 'ACF-0905', 'name' => 'Ecuaciones Diferenciales', 'credits' => 5, 'sem' => 4, 'th' => 3, 'pr' => 2],
            ['code' => 'SCA-1025', 'name' => 'Sistemas Operativos', 'credits' => 4, 'sem' => 4, 'th' => 2, 'pr' => 2],
            ['code' => 'AEF-1031', 'name' => 'Fundamentos de Base de Datos', 'credits' => 5, 'sem' => 4, 'th' => 3, 'pr' => 2],
            ['code' => 'SCC-1017', 'name' => 'Métodos Numéricos', 'credits' => 4, 'sem' => 4, 'th' => 2, 'pr' => 2],
            ['code' => 'SCD-1018', 'name' => 'Principios Eléctricos y Aplicaciones Digitales', 'credits' => 5, 'sem' => 4, 'th' => 2, 'pr' => 3],
            ['code' => 'SCD-1022', 'name' => 'Simulación', 'credits' => 5, 'sem' => 4, 'th' => 2, 'pr' => 3],
            ['code' => 'SCD-1027', 'name' => 'Tópicos Avanzados de Programación', 'credits' => 5, 'sem' => 4, 'th' => 2, 'pr' => 3],

            // Semestre 5
            ['code' => 'SCD-1009', 'name' => 'Fundamentos de Telecomunicaciones', 'credits' => 4, 'sem' => 5, 'th' => 2, 'pr' => 2],
            ['code' => 'SCA-1026', 'name' => 'Taller de Base de Datos', 'credits' => 4, 'sem' => 5, 'th' => 0, 'pr' => 4],
            ['code' => 'SCA-1027', 'name' => 'Taller de Sistemas Operativos', 'credits' => 4, 'sem' => 5, 'th' => 0, 'pr' => 4],
            ['code' => 'SCC-1007', 'name' => 'Fundamentos de Ingeniería de Software', 'credits' => 4, 'sem' => 5, 'th' => 2, 'pr' => 2],
            ['code' => 'SCC-1010', 'name' => 'Graficación', 'credits' => 4, 'sem' => 5, 'th' => 2, 'pr' => 2],
            ['code' => 'SCA-1012', 'name' => 'Inteligencia Artificial', 'credits' => 4, 'sem' => 5, 'th' => 2, 'pr' => 2],
            ['code' => 'SCD-1003', 'name' => 'Arquitectura de Computadoras', 'credits' => 5, 'sem' => 5, 'th' => 2, 'pr' => 3],

            // Semestre 6
            ['code' => 'AEB-1055', 'name' => 'Programación Web', 'credits' => 5, 'sem' => 6, 'th' => 1, 'pr' => 4],
            ['code' => 'SCB-1001', 'name' => 'Administración de Base de Datos', 'credits' => 5, 'sem' => 6, 'th' => 1, 'pr' => 4],
            ['code' => 'SCC-1014', 'name' => 'Lenguajes de Interfaz', 'credits' => 4, 'sem' => 6, 'th' => 2, 'pr' => 2],
            ['code' => 'SCC-1019', 'name' => 'Programación Lógica y Funcional', 'credits' => 4, 'sem' => 6, 'th' => 2, 'pr' => 2],
            ['code' => 'SCD-1011', 'name' => 'Ingeniería de Software', 'credits' => 5, 'sem' => 6, 'th' => 2, 'pr' => 3],
            ['code' => 'SCD-1015', 'name' => 'Lenguajes y Autómatas I', 'credits' => 5, 'sem' => 6, 'th' => 2, 'pr' => 3],
            ['code' => 'SCD-1021', 'name' => 'Redes de Computadoras', 'credits' => 5, 'sem' => 6, 'th' => 2, 'pr' => 3],

            // Semestre 7
            ['code' => 'ACA-0909', 'name' => 'Taller de Investigación I', 'credits' => 4, 'sem' => 7, 'th' => 0, 'pr' => 4],
            ['code' => 'SCD-1004', 'name' => 'Conmutación y Enrutamiento en Redes de Datos', 'credits' => 5, 'sem' => 7, 'th' => 2, 'pr' => 3],
            ['code' => 'SCD-1016', 'name' => 'Lenguajes y Autómatas II', 'credits' => 5, 'sem' => 7, 'th' => 2, 'pr' => 3],
            ['code' => 'SCC-1023', 'name' => 'Sistemas Programables', 'credits' => 4, 'sem' => 7, 'th' => 2, 'pr' => 2],
            ['code' => 'SCG-1009', 'name' => 'Gestión de Proyectos de Software', 'credits' => 6, 'sem' => 7, 'th' => 3, 'pr' => 3],

            // Semestre 8
            ['code' => 'ACA-0910', 'name' => 'Taller de Investigación II', 'credits' => 4, 'sem' => 8, 'th' => 0, 'pr' => 4],
            ['code' => 'SCA-1002', 'name' => 'Administración de Redes', 'credits' => 4, 'sem' => 8, 'th' => 0, 'pr' => 4],

            // Semestre 9
            ['code' => 'RES-1001', 'name' => 'Residencia Profesional', 'credits' => 10, 'sem' => 9, 'th' => 0, 'pr' => 10],
        ];

        $materiasGuardadas = [];
        foreach ($materiasDef as $m) {
            $subject = Subject::updateOrCreate(
                ['study_plan_id' => $plan->id, 'name' => $m['name']],
                [
                    'code' => $m['code'],
                    'credits' => $m['credits'],
                    'semester_order' => $m['sem'],
                    'theory_hours' => $m['th'],
                    'practice_hours' => $m['pr'],
                ]
            );
            $materiasGuardadas[$m['name']] = $subject;
        }

        // 3. Crear Prerrequisitos de Seriación Curricular
        $prereqsMap = [
            'Cálculo Integral' => ['Cálculo Diferencial'],
            'Cálculo Vectorial' => ['Cálculo Integral'],
            'Ecuaciones Diferenciales' => ['Cálculo Vectorial'],
            'Programación Orientada a Objetos' => ['Fundamentos de Programación'],
            'Estructura de Datos' => ['Programación Orientada a Objetos'],
            'Tópicos Avanzados de Programación' => ['Estructura de Datos'],
            'Taller de Base de Datos' => ['Fundamentos de Base de Datos'],
            'Administración de Base de Datos' => ['Taller de Base de Datos'],
            'Taller de Sistemas Operativos' => ['Sistemas Operativos'],
            'Lenguajes y Autómatas I' => ['Matemáticas Discretas', 'Estructura de Datos'],
            'Lenguajes y Autómatas II' => ['Lenguajes y Autómatas I'],
            'Redes de Computadoras' => ['Fundamentos de Telecomunicaciones'],
            'Conmutación y Enrutamiento en Redes de Datos' => ['Redes de Computadoras'],
            'Administración de Redes' => ['Conmutación y Enrutamiento en Redes de Datos'],
            'Ingeniería de Software' => ['Fundamentos de Ingeniería de Software'],
            'Gestión de Proyectos de Software' => ['Ingeniería de Software'],
            'Taller de Investigación II' => ['Taller de Investigación I'],
            'Residencia Profesional' => ['Taller de Investigación I', 'Gestión de Proyectos de Software'],
        ];

        foreach ($prereqsMap as $materiaNombre => $prereqNombres) {
            if (! isset($materiasGuardadas[$materiaNombre])) {
                continue;
            }
            $target = $materiasGuardadas[$materiaNombre];
            foreach ($prereqNombres as $prereqNombre) {
                if (isset($materiasGuardadas[$prereqNombre])) {
                    $prereq = $materiasGuardadas[$prereqNombre];
                    Prerequisite::firstOrCreate([
                        'subject_id' => $target->id,
                        'prerequisite_subject_id' => $prereq->id,
                    ], [
                        'type' => 'obligatorio',
                    ]);
                }
            }
        }

        // 4. Crear Usuarios Institucionales
        $estudiante = User::updateOrCreate(
            ['email' => 'ana.ramirez@itcelaya.edu.mx'],
            [
                'name' => 'Ana Sofía Ramírez Torres',
                'role' => 'estudiante',
                'control_number' => '20030582',
                'career' => 'Ingeniería en Sistemas Computacionales',
                'current_semester' => 7,
                'password' => Hash::make('secreto123'),
            ]
        );

        $docente1 = User::updateOrCreate(
            ['email' => 'roberto.mendez@itcelaya.edu.mx'],
            [
                'name' => 'Dr. Roberto Méndez Salazar',
                'role' => 'docente',
                'control_number' => 'DOC-0419',
                'career' => 'Ingeniería en Sistemas Computacionales',
                'password' => Hash::make('secreto123'),
            ]
        );

        $tutor = User::updateOrCreate(
            ['email' => 'claudia.morales@itcelaya.edu.mx'],
            [
                'name' => 'Mtra. Claudia Patricia Morales',
                'role' => 'tutor',
                'control_number' => 'DOC-0822',
                'career' => 'Ingeniería en Sistemas Computacionales',
                'password' => Hash::make('secreto123'),
            ]
        );

        // 5. Historial Académico de Ana Sofía alineado exactamente al Mockup
        // Estatus: A: Aprobada, C: Cursando, R: Reprobada, D: Disponible, P: Pendiente
        $historialMock = [
            // Semestre 1 a 6: Aprobadas
            'Fundamentos de Investigación' => ['status' => 'A', 'grade' => 92, 'attempts' => 1],
            'Cálculo Diferencial' => ['status' => 'A', 'grade' => 78, 'attempts' => 1],
            'Química' => ['status' => 'A', 'grade' => 84, 'attempts' => 1],
            'Fundamentos de Programación' => ['status' => 'A', 'grade' => 95, 'attempts' => 1],
            'Matemáticas Discretas' => ['status' => 'A', 'grade' => 88, 'attempts' => 1],

            'Desarrollo Sustentable' => ['status' => 'A', 'grade' => 90, 'attempts' => 1],
            'Cálculo Integral' => ['status' => 'A', 'grade' => 74, 'attempts' => 1],
            'Taller de Ética' => ['status' => 'A', 'grade' => 96, 'attempts' => 1],
            'Contabilidad Financiera' => ['status' => 'A', 'grade' => 82, 'attempts' => 1],
            'Programación Orientada a Objetos' => ['status' => 'A', 'grade' => 91, 'attempts' => 1],
            'Probabilidad y Estadística' => ['status' => 'A', 'grade' => 80, 'attempts' => 1],

            'Álgebra Lineal' => ['status' => 'A', 'grade' => 85, 'attempts' => 1],
            'Cálculo Vectorial' => ['status' => 'A', 'grade' => 71, 'attempts' => 1],
            'Estructura de Datos' => ['status' => 'A', 'grade' => 86, 'attempts' => 2], // Repetición aprobada
            'Cultura Empresarial' => ['status' => 'A', 'grade' => 94, 'attempts' => 1],
            'Investigación de Operaciones' => ['status' => 'A', 'grade' => 83, 'attempts' => 1],
            'Física General' => ['status' => 'A', 'grade' => 79, 'attempts' => 1],
            'Taller de Administración' => ['status' => 'A', 'grade' => 89, 'attempts' => 1],

            'Ecuaciones Diferenciales' => ['status' => 'A', 'grade' => 75, 'attempts' => 1],
            'Sistemas Operativos' => ['status' => 'A', 'grade' => 88, 'attempts' => 1],
            'Fundamentos de Base de Datos' => ['status' => 'A', 'grade' => 93, 'attempts' => 1],
            'Métodos Numéricos' => ['status' => 'A', 'grade' => 82, 'attempts' => 1],
            'Principios Eléctricos y Aplicaciones Digitales' => ['status' => 'A', 'grade' => 81, 'attempts' => 1],
            'Simulación' => ['status' => 'A', 'grade' => 87, 'attempts' => 1],
            'Tópicos Avanzados de Programación' => ['status' => 'A', 'grade' => 90, 'attempts' => 1],

            'Fundamentos de Telecomunicaciones' => ['status' => 'A', 'grade' => 84, 'attempts' => 1],
            'Taller de Base de Datos' => ['status' => 'A', 'grade' => 95, 'attempts' => 1],
            'Taller de Sistemas Operativos' => ['status' => 'A', 'grade' => 86, 'attempts' => 1],
            'Fundamentos de Ingeniería de Software' => ['status' => 'A', 'grade' => 89, 'attempts' => 1],
            'Graficación' => ['status' => 'A', 'grade' => 92, 'attempts' => 1],
            'Inteligencia Artificial' => ['status' => 'A', 'grade' => 91, 'attempts' => 1],
            'Arquitectura de Computadoras' => ['status' => 'A', 'grade' => 78, 'attempts' => 1],

            'Programación Web' => ['status' => 'A', 'grade' => 96, 'attempts' => 1],
            'Administración de Base de Datos' => ['status' => 'A', 'grade' => 94, 'attempts' => 1],
            'Lenguajes de Interfaz' => ['status' => 'A', 'grade' => 85, 'attempts' => 1],
            'Programación Lógica y Funcional' => ['status' => 'A', 'grade' => 87, 'attempts' => 1],
            'Ingeniería de Software' => ['status' => 'A', 'grade' => 90, 'attempts' => 1],
            'Lenguajes y Autómatas I' => ['status' => 'A', 'grade' => 88, 'attempts' => 1],
            'Redes de Computadoras' => ['status' => 'A', 'grade' => 93, 'attempts' => 1],

            // Semestre 7
            'Taller de Investigación I' => ['status' => 'C', 'grade' => null, 'attempts' => 1],
            'Conmutación y Enrutamiento en Redes de Datos' => ['status' => 'A', 'grade' => 92, 'attempts' => 1],
            'Lenguajes y Autómatas II' => ['status' => 'A', 'grade' => 89, 'attempts' => 1],
            'Sistemas Programables' => ['status' => 'C', 'grade' => null, 'attempts' => 1],
            'Gestión de Proyectos de Software' => ['status' => 'C', 'grade' => null, 'attempts' => 1],

            // Semestre 8
            'Taller de Investigación II' => ['status' => 'P', 'grade' => null, 'attempts' => 1],
            'Administración de Redes' => ['status' => 'D', 'grade' => null, 'attempts' => 1],

            // Semestre 9
            'Residencia Profesional' => ['status' => 'P', 'grade' => null, 'attempts' => 1],
        ];

        foreach ($historialMock as $nombre => $datos) {
            if (isset($materiasGuardadas[$nombre])) {
                $sub = $materiasGuardadas[$nombre];
                StudentAcademicHistory::updateOrCreate(
                    ['user_id' => $estudiante->id, 'subject_id' => $sub->id],
                    [
                        'status' => $datos['status'],
                        'grade' => $datos['grade'],
                        'attempts' => $datos['attempts'],
                        'period' => ($datos['status'] === 'C') ? '2026-2' : '2026-1',
                    ]
                );
            }
        }

        // 6. Registro de Comentarios de Docentes / Asesores (Resolviendo problemática del proyecto)
        if (isset($materiasGuardadas['Estructura de Datos'])) {
            SubjectComment::firstOrCreate([
                'user_id' => $estudiante->id,
                'subject_id' => $materiasGuardadas['Estructura de Datos']->id,
                'author_id' => $docente1->id,
            ], [
                'author_name' => $docente1->name,
                'author_role' => 'Docente Titular',
                'comment' => 'Acreditada en segundo intento con excelente dedicación. Gran dominio demostrado en balanceo de árboles AVL y grafos ponderados (Dijkstra/Kruskal).',
            ]);
        }

        if (isset($materiasGuardadas['Sistemas Programables'])) {
            SubjectComment::firstOrCreate([
                'user_id' => $estudiante->id,
                'subject_id' => $materiasGuardadas['Sistemas Programables']->id,
                'author_id' => $docente1->id,
            ], [
                'author_name' => $docente1->name,
                'author_role' => 'Docente Titular',
                'comment' => 'Presentó una excelente propuesta de proyecto embebido con protocolos MQTT y sensores IoT. Avance regular y participación activa en laboratorio.',
            ]);
        }

        if (isset($materiasGuardadas['Taller de Investigación I'])) {
            SubjectComment::firstOrCreate([
                'user_id' => $estudiante->id,
                'subject_id' => $materiasGuardadas['Taller de Investigación I']->id,
                'author_id' => $tutor->id,
            ], [
                'author_name' => $tutor->name,
                'author_role' => 'Asesora de Tutoría y Tesis',
                'comment' => 'El protocolo de tesis sobre modelos de autómatas para trayectorias curriculares fue aprobado por el comité revisor con comentarios favorables.',
            ]);
        }
    }
}
