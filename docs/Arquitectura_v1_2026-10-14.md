# Documento de Arquitectura de Software y Estándares de Programación
**Proyecto RISA — Retícula Interactiva de Seguimiento Académico**

---

### Encabezado Institucional y de Control Documental

| Campo | Detalle |
| :--- | :--- |
| **Proyecto** | RISA (Retícula Interactiva de Seguimiento Académico) |
| **Documento** | Arquitectura de Software, Modelo de Datos y Estándares de Codificación |
| **Versión** | v1.0.0 |
| **Fecha de Creación / Actualización** | 14 de octubre de 2026 |
| **Autor(es)** | Emilio / Equipo de Desarrollo RISA |
| **Organización / Carrera** | Instituto Tecnológico de Celaya — Ingeniería en Sistemas Computacionales |
| **Estado del Documento** | Aprobado para Implementación |

---

## 1. Arquitectura General del Sistema

El sistema implementa una **Arquitectura en Capas Limpia (Clean Architecture)** sobre Laravel, separando la infraestructura web del núcleo del negocio:

```
+-------------------------------------------------------------+
|                Presentación / Frontend                      |
| (Blade Components, Tailwind CSS, JavaScript Vanilla / Vite) |
+-------------------------------------------------------------+
                              | HTTP / JSON API
+-------------------------------------------------------------+
|               Capa de Aplicación (Laravel)                  |
|  - Controladores (Http/Controllers)                         |
|  - Validación de Entradas (Http/Requests - FormRequests)    |
|  - Recursos de Respuesta (Http/Resources)                   |
+-------------------------------------------------------------+
                              |
+-------------------------------------------------------------+
|               Capa de Dominio (PHP Puro)                    |
|  - Autómata Finito Determinista (PrerequisiteAutomaton)     |
|  - Motor de Recomendación Curricular (LoadRecommenderEngine)|
|  - Calculador de Proyección de Semestres (ProjectionEngine) |
|  - Modelos de Dominio y Tablas de Estados                   |
+-------------------------------------------------------------+
                              | ORM Eloquent / Repositories
+-------------------------------------------------------------+
|             Capa de Persistencia y Contenedores             |
|  - PostgreSQL 16 (Relaciones relacionales en snake_case)    |
|  - Docker (PHP-FPM 8.4, Nginx Alpine, PostgreSQL Alpine)    |
+-------------------------------------------------------------+
```

---

## 2. Modelo de Base de Datos Relacional (PostgreSQL)

Siguiendo las directrices del proyecto, todas las tablas y columnas se nombran estrictamente en `snake_case`:

1. **`users`**:
   - `id`: BIGSERIAL PRIMARY KEY
   - `name`: VARCHAR(255)
   - `email`: VARCHAR(255) UNIQUE
   - `role`: VARCHAR(50) (e.g. `estudiante`, `docente`, `coordinador`, `admin`)
   - `control_number`: VARCHAR(50) NULL (e.g. `20030582`)
   - `password`: VARCHAR(255)
   - `timestamps`

2. **`study_plans`**:
   - `id`: BIGSERIAL PRIMARY KEY
   - `code`: VARCHAR(50) UNIQUE (e.g. `ISIC-2010-224`)
   - `name`: VARCHAR(255) (e.g. `Ingeniería en Sistemas Computacionales`)
   - `institution`: VARCHAR(255) (e.g. `Instituto Tecnológico de Celaya`)
   - `total_credits`: INT
   - `timestamps`

3. **`subjects`**:
   - `id`: BIGSERIAL PRIMARY KEY
   - `study_plan_id`: BIGINT REFERENCES `study_plans(id)` ON DELETE CASCADE
   - `code`: VARCHAR(50) (e.g. `AED-1026`)
   - `name`: VARCHAR(255)
   - `credits`: SMALLINT
   - `theory_hours`: SMALLINT
   - `practice_hours`: SMALLINT
   - `semester_order`: SMALLINT (1 al 9)
   - `timestamps`

4. **`prerequisites`**:
   - `id`: BIGSERIAL PRIMARY KEY
   - `subject_id`: BIGINT REFERENCES `subjects(id)` ON DELETE CASCADE (Materia dependiente)
   - `prerequisite_subject_id`: BIGINT REFERENCES `subjects(id)` ON DELETE CASCADE (Materia llave)
   - `type`: VARCHAR(30) DEFAULT 'obligatorio' (obligatorio, correquisito, creditos_minimos)
   - `required_credits`: SMALLINT DEFAULT 0
   - `timestamps`

5. **`student_academic_histories`**:
   - `id`: BIGSERIAL PRIMARY KEY
   - `user_id`: BIGINT REFERENCES `users(id)` ON DELETE CASCADE
   - `subject_id`: BIGINT REFERENCES `subjects(id)` ON DELETE CASCADE
   - `status`: CHAR(1) (Valores: 'A', 'C', 'R', 'D', 'P')
   - `grade`: NUMERIC(5,2) NULL (0.00 a 100.00)
   - `attempts`: SMALLINT DEFAULT 1
   - `period`: VARCHAR(50) NULL (e.g. `2026-1`, `2026-2`)
   - `timestamps`

6. **`subject_comments`**:
   - `id`: BIGSERIAL PRIMARY KEY
   - `user_id`: BIGINT REFERENCES `users(id)` ON DELETE CASCADE (Alumno evaluado)
   - `subject_id`: BIGINT REFERENCES `subjects(id)` ON DELETE CASCADE
   - `author_id`: BIGINT REFERENCES `users(id)` ON DELETE SET NULL (Docente/Asesor que comenta)
   - `author_name`: VARCHAR(255)
   - `author_role`: VARCHAR(100)
   - `comment`: TEXT
   - `timestamps`

---

## 3. Estándares de Programación

1. **PHP PSR-12**:
   - Cumplimiento estricto del estándar de estilo PSR-12.
   - Declaraciones de tipos estrictos (`declare(strict_types=1);`) en archivos de lógica de negocio y dominio.
   - Tipado estricto en parámetros y valores de retorno.
2. **Nomenclatura**:
   - `camelCase`: Variables locales, parámetros y nombres de métodos/funciones (`$semestreSugerido`, `calcularCargaRecomendada()`).
   - `PascalCase`: Nombres de clases, interfaces, rasgos y enumeraciones (`PrerequisiteAutomaton`, `LoadRecommendationService`).
   - `snake_case`: Nombres de tablas de base de datos, columnas relacionales y atributos JSON de salida de APIs (`user_id`, `semester_order`).
3. **Manejo de Errores y Validaciones**:
   - Todo endpoint de la API cuenta con su respectiva clase `FormRequest` para sanitizar y validar entradas antes de llegar a la lógica de negocio.
   - Respuestas JSON uniformes con códigos de estado HTTP semánticos (200 OK, 201 Created, 422 Unprocessable Entity, 404 Not Found, 500 Internal Error).
4. **Comentarios de Código**:
   - Comentarios de bloque y de línea obligatorios en funciones complejas, especialmente en el motor de autómata y cálculo de rutas críticas.
