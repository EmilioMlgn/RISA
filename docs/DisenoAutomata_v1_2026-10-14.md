# Especificación Formal del Autómata de Recomendación Curricular
**Proyecto RISA — Retícula Interactiva de Seguimiento Académico**

---

### Encabezado Institucional y de Control Documental

| Campo | Detalle |
| :--- | :--- |
| **Proyecto** | RISA (Retícula Interactiva de Seguimiento Académico) |
| **Documento** | Especificación Formal del Autómata Finito y Algoritmo de Carga Académica |
| **Versión** | v1.0.0 |
| **Fecha de Creación / Actualización** | 14 de octubre de 2026 |
| **Autor(es)** | Emilio / Equipo de Desarrollo RISA |
| **Organización / Carrera** | Instituto Tecnológico de Celaya — Ingeniería en Sistemas Computacionales |
| **Estado del Documento** | Aprobado para Implementación en PHP Puro |

---

## 1. Fundamentación Teórica del Autómata

Para garantizar un sistema determinista, libre de inconsistencias y sin dependencias de modelos probabilísticos o de lenguaje, la trayectoria de cada asignatura en el plan de estudios se modela formalmente como una **Máquina de Estados Finita Determinista (DFA)** definida por la tupla:

$$M = (Q, \Sigma, \delta, q_0, F)$$

Donde:
- **$Q$**: Conjunto finito de estados académicos posibles para una materia.
  - $Q = \{ P, D, C, A, R \}$
    - **$P$ (Pendiente)**: La materia no puede cursarse aún porque el estudiante no satisface la totalidad de sus prerrequisitos académicos o créditos mínimos exigidos.
    - **$D$ (Disponible)**: La materia tiene todos sus prerrequisitos acreditados (en estado $A$) y cumple con el mínimo de créditos requeridos. Se encuentra elegible para ser inscrita.
    - **$C$ (Cursando)**: La materia ha sido seleccionada e inscrita para el ciclo escolar activo.
    - **$A$ (Aprobada)**: El alumno ha acreditado la materia con calificación igual o superior a 70 puntos sobre 100 ($calif \ge 70$).
    - **$R$ (Reprobada)**: El alumno cursó la materia y obtuvo una calificación menor a 70 puntos ($calif < 70$). La materia pasa a un estado de prioridad crítica para reinscripción.
- **$\Sigma$**: Alfabeto de eventos o transiciones curriculares.
  - $\Sigma = \{ \text{EVALUAR\_REQUISITOS}, \text{INSCRIBIR}, \text{CANCELAR}, \text{ACREDITAR}, \text{NO\_ACREDITAR} \}$
- **$q_0$**: Estado inicial. Para cualquier asignatura sin antecedentes previos:
  - $q_0 = D$ si la asignatura carece de prerrequisitos (materias de primer semestre).
  - $q_0 = P$ si la asignatura posee prerrequisitos aún no cubiertos.
- **$F$**: Conjunto de estados de aceptación final.
  - $F = \{ A \}$ (Una vez aprobada, la materia queda acreditada de manera permanente en el expediente).

---

## 2. Tabla de Transiciones de Estado ($\delta: Q \times \Sigma \to Q$)

| Estado Origen | Evento de Entrada ($\Sigma$) | Condición de Guarda / Regla de Negocio | Estado Destino |
| :--- | :--- | :--- | :--- |
| **$P$ (Pendiente)** | $\text{EVALUAR\_REQUISITOS}$ | Cumple todos los prerrequisitos en $A$ y créditos requeridos | **$D$ (Disponible)** |
| **$P$ (Pendiente)** | $\text{EVALUAR\_REQUISITOS}$ | No cumple alguno de los prerrequisitos | **$P$ (Pendiente)** |
| **$D$ (Disponible)** | $\text{INSCRIBIR}$ | Asignatura seleccionada dentro del límite de carga escolar | **$C$ (Cursando)** |
| **$C$ (Cursando)** | $\text{CANCELAR}$ | Desinscripción o ajuste en periodo oficial de altas/bajas | **$D$ (Disponible)** |
| **$C$ (Cursando)** | $\text{ACREDITAR}$ | Calificación final obtenida $\ge 70$ | **$A$ (Aprobada)** |
| **$C$ (Cursando)** | $\text{NO\_ACREDITAR}$ | Calificación final obtenida $< 70$ (incrementa conteo de intentos) | **$R$ (Reprobada)** |
| **$R$ (Reprobada)** | $\text{INSCRIBIR}$ | Reinscripción de la materia (prioridad 1 en el recomendador) | **$C$ (Cursando)** |
| **$A$ (Aprobada)** | *Cualquier evento* | El estado es terminal y permanente | **$A$ (Aprobada)** |

---

## 3. Algoritmo del Motor de Recomendación de Carga Académica

El motor de recomendación opera en PHP puro desacoplado de Laravel, evaluando la retícula mediante un enfoque de optimización curricular:

### Paso 1: Clasificación de Materias Elegibles
Se construye el subconjunto de materias candidatas a inscripción:
$$Candidatas = \{ m \in Asignaturas \mid Estado(m) \in \{ R, D \} \}$$

### Paso 2: Ordenamiento por Prioridad y Ruta Crítica
Cada materia candidata recibe un puntaje ponderado de prelación:
1. **Regla de Emergencia Académica (Prioridad 1 - Materias $R$)**:
   Las materias reprobadas se colocan al inicio de la lista de selección con prioridad absoluta, ponderadas adicionalmente por su número de intentos cursados para prevenir deserción por baja reglamentaria.
2. **Grado de Salida Curricular (Ruta Crítica)**:
   Se evalúa la función de dependencia:
   $$RutaCritica(m) = \sum_{h \in Descendientes(m)} 1$$
   Aquellas materias que son prerrequisito directo o indirecto de un mayor número de asignaturas futuras tienen mayor prioridad para no estrangular la trayectoria curricular.
3. **Semestre Sugerido**:
   A igualdad de condiciones, se priorizan las materias correspondientes a semestres inferiores para consolidar el avance troncal.

### Paso 3: Optimización con Restricción de Carga Crediticia
Dado un límite máximo de créditos institucional $C_{max}$ (ejemplo: 36 créditos) y un mínimo $C_{min}$ (ejemplo: 20 créditos):
- Se itera sobre la lista ordenada de asignaturas candidatas.
- Se selecciona una materia si:
  $$CreditosAcumulados + Creditos(m) \le C_{max}$$
- Si la suma de materias reprobadas satura o limita la carga permitida, el sistema restringe materias optativas o de semestres adelantados conforme al reglamento escolar.

---

## 4. Algoritmo de Proyección de Semestres Restantes

Para calcular la estimación certera de cuántos semestres restan para el egreso:
1. Se construye el **Grafo Acíclico Dirigido (DAG)** de asignaturas no aprobadas:
   $$G = (V, E)$$
   donde $V = \{ m \in Asignaturas \mid Estado(m) \neq A \}$ y $(u, v) \in E$ si la materia $u$ es prerrequisito de $v$.
2. **Cálculo de la Cadena Crítica de Seriación ($L_{serie}$)**:
   Se obtiene el camino más largo en el DAG mediante ordenamiento topológico. Si la cadena más larga de prerrequisitos tiene una longitud de $k$ materias en serie, el estudiante requerirá **como mínimo $k$ semestres**, pues dos materias en serie no pueden cursarse de manera simultánea.
3. **Cálculo por Volumen de Créditos ($L_{cred}$)**:
   $$L_{cred} = \left\lceil \frac{\sum_{m \in V} Creditos(m)}{CreditosPromedioPorSemestre} \right\rceil$$
4. **Proyección Final**:
   $$SemestresRestantes = \max(L_{serie}, L_{cred})$$
   Este resultado ofrece al alumno una previsión matemática realista de su tiempo de egreso.
