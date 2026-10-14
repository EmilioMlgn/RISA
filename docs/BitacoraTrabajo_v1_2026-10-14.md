# Bitácora de Sesión de Trabajo — Proyecto RISA
**Retícula Interactiva de Seguimiento Académico**

---

### Encabezado Institucional y de Control Documental

| Campo | Detalle |
| :--- | :--- |
| **Proyecto** | RISA (Retícula Interactiva de Seguimiento Académico) |
| **Documento** | Bitácora de Trabajo y Registro de Decisiones de Diseño |
| **Versión** | v1.0.0 |
| **Fecha de Registro** | 14 de octubre de 2026 |
| **Responsable de Sesión** | Emilio / Equipo de Desarrollo RISA |
| **Rama Git Activa** | `develop` |

---

## 1. Registro de Actividades Realizadas en la Sesión

| Hora | Actividad | Resultado |
| :--- | :--- | :--- |
| 09:55 | Recepción de requerimientos y definición de alcance de RISA | Se analizaron los requerimientos funcionales, alcances y limitaciones institucionales. |
| 10:00 | Inspección del repositorio GitHub `https://github.com/EmilioMlgn/RISA.git` | Se descargó la base con el archivo `RISA-mockup.html` con la paleta de colores y catálogo de Sistemas Computacionales. |
| 10:05 | Inicialización del entorno Laravel 13 con PHP 8.4/8.5 y Composer | Estructura base de Laravel instalada satisfactoriamente con Vite y Tailwind CSS. |
| 10:10 | Configuración de estrategia de ramas Git | Se creó y seleccionó la rama `develop` para la integración continua de características. |
| 10:15 | Creación de infraestructura Docker y entorno PostgreSQL | Se diseñaron `Dockerfile`, `docker-compose.yml`, `docker/nginx/default.conf` y `docker/php/local.ini`. |
| 10:25 | Redacción de estándares documentales (`docs/`) | Se generaron los documentos formales con versión y fecha: Plan de Desarrollo, Arquitectura y Especificación Formal del Autómata. |
| 10:35 | Diseño del Autómata en PHP Puro | Modelado de la máquina finita determinista para transiciones de estados (A, C, R, D, P) y cálculo de ruta crítica. |

---

## 2. Matriz de Decisiones Técnicas y de Diseño

1. **Separación de Lógica Pura del Framework**:
   - *Decisión*: El autómata de estados y el recomendador se programan en PHP puro bajo el namespace `App\Core\Automaton` y `App\Core\AcademicLoad`.
   - *Razón*: Permite máxima testabilidad sin depender del ORM o framework, garantizando velocidad y modularidad.
2. **PostgreSQL como Motor de Base de Datos**:
   - *Decisión*: Implementar el esquema relacional en PostgreSQL 16 con tipos numéricos precisos y llaves foráneas estrictas (`ON DELETE CASCADE`).
   - *Razón*: Integridad referencial sólida requerida para el árbol de prerrequisitos y el historial de calificaciones.
3. **Persistencia de Observaciones Docentes**:
   - *Decisión*: Tabla dedicada `subject_comments` con autor, rol y fecha, ligada directamente a la relación alumno-materia.
   - *Razón*: Resuelve la problemática de notas verbales y correos dispersos.
