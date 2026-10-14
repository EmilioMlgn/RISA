# RISA — Retícula Interactiva de Seguimiento Académico

[![PHP Version](https://img.shields.io/badge/PHP-8.4%20%7C%208.5-777BB4?style=flat&logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=flat&logo=laravel&logoColor=white)](https://laravel.com)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-4169E1?style=flat&logo=postgresql&logoColor=white)](https://postgresql.org)
[![Docker](https://img.shields.io/badge/Docker-Enabled-2496ED?style=flat&logo=docker&logoColor=white)](https://www.docker.com)
[![Tests](https://img.shields.io/badge/PHPUnit-16%20Passed-28A745?style=flat&logo=php)](./tests)
[![Code Style](https://img.shields.io/badge/Pint-PSR--12%20Compliant-blue)](./vendor/bin/pint)

---

## 📌 1. Descripción del Proyecto

**RISA** (Retícula Interactiva de Seguimiento Académico) es una plataforma institucional orientada a optimizar la planeación de la trayectoria curricular de los estudiantes universitarios. Transforma la retícula tradicional estática (PDF/imagen) en un entorno interactivo y dinámico que integra:

1. **Retícula interactiva semestral** con desglose por asignatura (créditos, estatus en tiempo real, número de intentos, calificación y observaciones docentes).
2. **Autómata de estados finitos determinista (DFA) en PHP puro** para validar transiciones curriculares y dependencias en cadena sin dependencia de IA generativa externa.
3. **Motor de recomendación de carga académica** basado en reglas institucionales, priorización de materias en repetición y cálculo de ruta crítica (materias llave).
4. **Algoritmo de proyección de semestres restantes para egreso** mediante cálculo topológico sobre Grafos Acíclicos Dirigidos (DAG).
5. **Centralización permanente de comentarios y notas de asesoría** de profesores y tutores vinculadas a cada asignatura del alumno.

---

## 🏗️ 2. Arquitectura y Tecnologías

- **Framework Web**: [Laravel 13](https://laravel.com)
- **Lógica de Autómata y Recomendación**: **PHP Puro** (`App\Core\Automaton`, `App\Core\AcademicLoad`), desacoplado de dependencias externas.
- **Base de Datos**: [PostgreSQL 16](https://www.postgresql.org/) con convenciones estrictas de nombrado en `snake_case`.
- **Contenedorización**: [Docker](https://www.docker.com/) con Docker Compose (servicios: `app` en PHP 8.4-FPM Alpine, `web` en Nginx Alpine y `db` en PostgreSQL 16 Alpine).
- **Frontend**: Blade + Tailwind CSS v4 + JavaScript Vanilla reactivo, conservando la identidad gráfica institucional y compatibilidad para impresión (`@media print`).
- **Control de Calidad**: PHPUnit para pruebas unitarias/funcionales y Laravel Pint para el estándar PSR-12.

---

## 📂 3. Estructura de Ramas Git

Siguiendo el estándar de trabajo del equipo:
- `main`: Versión estable y entregable del sistema.
- `develop`: Rama principal de integración continua de funcionalidades.
- `feature/<nombre-de-la-funcionalidad>`: Ramas de desarrollo de módulos o tareas individuales.

---

## 🚀 4. Puesta en Marcha

### Opción A: Despliegue con Docker (Recomendado)

1. Clonar el repositorio y ubicarse en la raíz:
   ```bash
   git clone https://github.com/EmilioMlgn/RISA.git
   cd RISA
   ```

2. Configurar el archivo de entorno:
   ```bash
   cp .env.example .env
   ```

3. Levantar los contenedores en segundo plano:
   ```bash
   docker compose up -d --build
   ```

4. Ejecutar las migraciones y sembrar los datos iniciales de prueba (Plan ISC e historial de Ana Sofía):
   ```bash
   docker compose exec app php artisan migrate --seed
   ```

5. Abrir en el navegador:
   - **Retícula Web**: [http://localhost:8080](http://localhost:8080)
   - **API REST**: [http://localhost:8080/api/reticula](http://localhost:8080/api/reticula)

---

### Opción B: Ejecución Local en el Entorno de Desarrollo

1. Instalar dependencias de Composer y Node.js:
   ```bash
   composer install
   npm install && npm run build
   ```

2. Generar la clave de la aplicación:
   ```bash
   php artisan key:generate
   ```

3. Ejecutar migraciones y seeders:
   ```bash
   php artisan migrate --seed
   ```

4. Iniciar el servidor local:
   ```bash
   php artisan serve
   ```
   Acceder en [http://localhost:8000](http://localhost:8000).

---

## 🧪 5. Pruebas Automatizadas y Estándares de Código

Ejecución de la suite completa de pruebas:
```bash
php artisan test
```

Verificación y formateo automático bajo **PSR-12**:
```bash
./vendor/bin/pint
```

---

## 📚 6. Documentación Técnica Entregable

Los documentos técnicos formales del proyecto se encuentran en la carpeta [`docs/`](./docs) cumpliendo con la nomenclatura requerida `NombreDocumento_v[versión]_[fecha].md`:

- [`docs/PlanDesarrollo_v1_2026-10-14.md`](./docs/PlanDesarrollo_v1_2026-10-14.md): Plan de desarrollo, objetivos, alcances y cronograma.
- [`docs/Arquitectura_v1_2026-10-14.md`](./docs/Arquitectura_v1_2026-10-14.md): Arquitectura de capas, esquema PostgreSQL en `snake_case` y convenciones.
- [`docs/DisenoAutomata_v1_2026-10-14.md`](./docs/DisenoAutomata_v1_2026-10-14.md): Definición formal del autómata $M = (Q, \Sigma, \delta, q_0, F)$ y algoritmos de carga/proyección.
- [`docs/BitacoraTrabajo_v1_2026-10-14.md`](./docs/BitacoraTrabajo_v1_2026-10-14.md): Bitácora de trabajo y registro de decisiones de la sesión.

---

## 👥 Autores y Créditos
- **Emilio** — *Desarrollador y Diseñador de Software*
- **Instituto Tecnológico de Celaya** — *Ingeniería en Sistemas Computacionales*
