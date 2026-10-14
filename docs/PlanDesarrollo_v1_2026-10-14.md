# Plan de Desarrollo del Proyecto RISA
**Retícula Interactiva de Seguimiento Académico**

---

### Encabezado Institucional y de Control Documental

| Campo | Detalle |
| :--- | :--- |
| **Proyecto** | RISA (Retícula Interactiva de Seguimiento Académico) |
| **Documento** | Plan de Desarrollo y Definición de Alcance |
| **Versión** | v1.0.0 |
| **Fecha de Creación / Actualización** | 14 de octubre de 2026 |
| **Autor(es)** | Emilio / Equipo de Desarrollo RISA |
| **Organización / Carrera** | Instituto Tecnológico de Celaya — Ingeniería en Sistemas Computacionales |
| **Estado del Documento** | Aprobado para Fase de Construcción Inicial |

---

## 1. Situación Actual y Contexto
Actualmente, la retícula académica de la carrera se presenta en formato estático PDF o imagen, sin posibilidad de interacción ni de consulta dinámica. El historial de desempeño del alumno (calificaciones, número de intentos y el estatus de cada materia) se encuentra en un sistema de control escolar totalmente separado de la retícula, obligando al estudiante a cruzar manualmente ambos documentos para determinar qué materias ha cursado, cuáles puede inscribir y si cumple los prerrequisitos correspondientes.

Asimismo, los comentarios y observaciones que docentes o asesores realizan sobre el desempeño del alumno en materias críticas se comunican de forma verbal o dispersa (correos, pláticas informales), sin quedar registrados permanentemente ni vinculados al expediente académico del alumno. No existe una herramienta institucional que proyecte, con base en el avance real del estudiante, cuántos semestres le restan para egresar.

---

## 2. Problemática Identificada
1. **Falta de centralización visual**: El alumno carece de una plataforma unificada para consultar su historial completo por asignatura (calificación, intentos, comentarios de docentes y asesores).
2. **Deficiente orientación en la selección de carga académica**: Decisiones no óptimas al momento de inscribir asignaturas provocan rezago escolar y alargamiento innecesario del periodo de graduación.
3. **Ausencia de proyección predictiva**: No existe un cálculo formal del número de semestres restantes según la ruta crítica de prerrequisitos y créditos faltantes.
4. **Pérdida de retroalimentación cualitativa**: Las observaciones de profesores y asesores quedan dispersas o en el olvido.
5. **Rigidez de la retícula tradicional**: Los documentos estáticos no permiten filtrar por estados ni simular escenarios de acreditación.

---

## 3. Objetivos

### 3.1 Objetivo General
Optimizar de manera integral la planeación de la trayectoria académica de los estudiantes mediante la visualización interactiva y dinámica de su avance curricular, integrando un sistema automatizado de recomendación de cargas de materias y centralización del historial de desempeño; con el propósito de prevenir errores críticos de seriación, reducir los tiempos de egreso, mitigar el rezago escolar por una deficiente planificación y dotar al alumnado de herramientas analíticas que garanticen una toma de decisiones informada y eficiente a lo largo de su formación profesional.

### 3.2 Objetivos Específicos
- Diseñar e implementar un tablero interactivo de retícula académica organizado por semestres, con retroalimentación visual de estados: Aprobada (A), Cursando (C), Reprobada (R), Disponible (D) y Pendiente (P).
- Construir un autómata finito determinista (DFA) en PHP puro para la evaluación de transiciones de estado curricular y validación de grafos de prerrequisitos.
- Desarrollar un motor de recomendación de carga académica basado en reglas y rutas críticas que priorice asignaturas reprobadas y materias llaves de seriación dentro de los límites crediticios permitidos.
- Implementar un algoritmo topológico de proyección de semestres restantes para estimar el egreso según el avance crediticio y la longitud de la cadena de seriación.
- Habilitar un canal centralizado de observaciones y notas académicas emitidas por docentes/asesores vinculadas a la dupla alumno-materia.

---

## 4. Alcance y Limitaciones

### 4.1 Alcance
- Visualización interactiva y responsiva de la retícula con filtrado por estados y cálculo porcentual de avance.
- Ficha técnica por materia con desglose de calificación, intentos, horas teoría/práctica, créditos y prerrequisitos.
- Motor de recomendación de carga académica autónomo y determinista (sin consumo de APIs generativas de terceros).
- Algoritmo de estimación de semestres restantes hacia el egreso.
- Módulo de comentarios y asesorías para profesores y tutores.
- Gestión administrativa de planes de estudio, asignaturas y prerrequisitos.
- Contenedorización con Docker (PHP-FPM, Nginx, PostgreSQL).

### 4.2 Limitaciones
- Carácter orientativo: El sistema genera sugerencias académicas no vinculantes; no sustituye la validación oficial del departamento de servicios escolares.
- Aislamiento de datos en tiempo real: No existe webhook o enlace sincrónico con la base de datos de control escolar institucional; opera mediante carga inicial simulada o importación periódica.
- Restricciones de privacidad: Se emplean conjuntos de datos anonimizados o sintéticos para pruebas.
- Exclusiones curriculares: No se contemplan convalidaciones externas, revalidaciones interinstitucionales ni movilidad estudiantil entre planes no homologados.
- Trámites administrativos: No incluye pagos de reinscripción, altas oficiales de seguro ni cobros de cuotas.

---

## 5. Cronograma y Entregables por Fases

| Fase | Entregable Principal | Fecha Objetivo | Estado |
| :--- | :--- | :--- | :--- |
| **Fase 1: Configuración y Arquitectura** | Repositorio Git, Dockerización (PHP/Nginx/PostgreSQL), Estructura de Proyecto | 14-Oct-2026 | Completado |
| **Fase 2: Modelado y Base de Datos** | Migraciones PostgreSQL, Seeders del Plan ISC, Relaciones Eloquent | 14-Oct-2026 | En Curso |
| **Fase 3: Núcleo Algorítmico (PHP Puro)** | Autómata de Transiciones, Motor de Carga, Algoritmo de Proyección | 14-Oct-2026 | En Curso |
| **Fase 4: Capa de Servicios y API REST** | Endpoints de Retícula, Recomendación, Notas de Docentes y Pruebas Unitarias | 15-Oct-2026 | Programado |
| **Fase 5: Interfaz Web y Tablero RISA** | Componentes interactivos, Modales de Materia, Modo Impresión | 16-Oct-2026 | Programado |
| **Fase 6: Integración y Documentación Final** | Manuales de Despliegue, Bitácora de Sesión, Validación de Prerrequisitos | 18-Oct-2026 | Programado |

---

## 6. Estándares Técnicos y de Calidad
- **Convención de Ramas Git**:
  - `main`: Versión estable y lista para producción/demostración formal.
  - `develop`: Integración continua de funcionalidades.
  - `feature/<nombre>`: Desarrollo de tareas específicas.
- **Estándar de Código**: PHP PSR-12, nomenclatura camelCase para métodos/variables, PascalCase para clases y snake_case para esquema relacional.
- **Documentación**: Nomenclatura estricta `NombreDocumento_v[versión]_[fecha].md`, redacción en idioma español técnico.
