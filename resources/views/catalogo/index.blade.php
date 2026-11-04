@php $admin = auth()->user(); @endphp
@extends('layouts.risa', [
    'activo' => 'catalogo',
    'perfil' => [
        'nombre' => $admin->name,
        'etiqueta' => 'Administrador',
        'lineas' => [$admin->email, 'Administración académica'],
        'avance' => null,
    ],
])

@section('titulo', 'Catálogo de materias')

@push('styles')
<style>
  :root { --err: #B02A37; --ok: #1E7E34; --warn: #8A5300; }
  .panel-cat { padding: 20px; margin-bottom: 20px; }
  .filtros { display: grid; grid-template-columns: 1fr repeat(2, minmax(200px, 250px)); gap: 12px; align-items: end; margin-bottom: 16px; }
  @media (max-width: 780px) { .filtros { grid-template-columns: 1fr; } }

  .panel-cat label, dialog label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 0.85rem; }
  .req { color: var(--err); font-weight: 700; }
  .panel-cat input[type="text"], .panel-cat select, dialog input[type="text"], dialog select {
    width: 100%; padding: 10px 12px; color: var(--ink); background: #fff;
    border: 1px solid var(--line); border-radius: 10px; font: inherit; font-size: 0.9rem;
    transition: border-color 0.2s, box-shadow 0.2s;
  }
  .panel-cat input:focus, .panel-cat select:focus, dialog input:focus, dialog select:focus { outline: none; border-color: var(--navy-light); box-shadow: 0 0 0 3px rgba(42, 82, 152, 0.15); }
  input[readonly], input:disabled { background: var(--soft); color: var(--muted); cursor: not-allowed; }
  [aria-invalid="true"] { border-color: var(--err) !important; box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.12) !important; }
  .hint { margin: 5px 0 0; color: var(--muted); font-size: 0.78rem; }
  .err { margin: 5px 0 0; color: var(--err); font-size: 0.8125rem; font-weight: 600; }
  .err[hidden] { display: none; }

  .btn, .btn-danger, .btn-warn, .sec, .sec-danger {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    border-radius: 10px; font: inherit; font-weight: 600; cursor: pointer;
    transition: background 0.15s, transform 0.15s, box-shadow 0.2s, border-color 0.2s;
  }
  .btn, .btn-danger, .btn-warn { min-height: 42px; padding: 0 18px; border: 0; color: #fff; box-shadow: var(--shadow-sm); }
  .btn { background: linear-gradient(135deg, var(--navy), var(--navy-light)); }
  .btn-danger { background: linear-gradient(135deg, #B02A37, var(--r)); }
  .btn-warn { background: linear-gradient(135deg, #8A5300, #B87400); }
  .btn:hover, .btn-danger:hover, .btn-warn:hover { transform: translateY(-1px); box-shadow: var(--shadow-md); }
  .sec, .sec-danger { min-height: 36px; padding: 0 12px; background: #fff; font-size: 0.85rem; }
  .sec { border: 1px solid var(--line); color: var(--navy); }
  .sec:hover { border-color: color-mix(in srgb, var(--navy) 40%, var(--line)); background: var(--soft); }
  .sec-danger { border: 1px solid color-mix(in srgb, var(--r) 35%, var(--line)); color: var(--err); }
  .sec-danger:hover { background: rgba(220, 53, 69, 0.06); }
  .sec:disabled, .btn:disabled { opacity: 0.45; cursor: not-allowed; transform: none; }
  .btn:active, .sec:active, .btn-danger:active, .btn-warn:active, .sec-danger:active { transform: scale(0.98); }

  .panel-cat .tabla-w { overflow-x: auto; border: 1px solid var(--line); border-radius: 12px; }
  .panel-cat table { width: 100%; min-width: 920px; border-collapse: collapse; font-size: 0.875rem; background: #fff; }
  .panel-cat th, .panel-cat td { padding: 11px 14px; text-align: left; vertical-align: top; border-bottom: 1px solid var(--line); }
  .panel-cat th { background: var(--soft); color: var(--navy); font-size: 0.8125rem; font-weight: 600; }
  .panel-cat tbody tr { transition: background 0.15s; }
  .panel-cat tbody tr:hover { background: color-mix(in srgb, var(--soft) 70%, #fff); }
  .panel-cat tbody tr:last-child td { border-bottom: 0; }
  .num { text-align: right; }
  .panel-cat td:first-child { white-space: nowrap; }
  .acciones-celda { display: flex; gap: 6px; justify-content: flex-end; flex-wrap: nowrap; }
  .estado { display: inline-flex; align-items: center; gap: 5px; padding: 2px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 700; }
  .estado::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
  .estado-activa { color: var(--ok); background: rgba(40, 167, 69, 0.12); }
  .estado-inactiva { color: var(--muted); background: var(--soft); border: 1px solid var(--line); }
  .fila-inactiva td { opacity: 0.68; }
  .meta-uso { display: block; color: var(--muted); font-size: 0.75rem; margin-top: 2px; }

  dialog {
    width: min(640px, calc(100% - 32px)); max-height: 90vh; padding: 24px;
    border: 0; border-radius: 18px; background: #fff; color: var(--ink);
    box-shadow: var(--shadow-lg); border-top: 6px solid var(--navy);
  }
  dialog[open] { animation: emerger 0.3s var(--ease-out); }
  @keyframes emerger { from { opacity: 0; transform: translateY(14px) scale(0.97); } }
  dialog::backdrop { background: rgba(18, 40, 77, 0.5); backdrop-filter: blur(4px); }
  dialog h2 { margin: 0 0 4px; color: var(--navy); font-size: 1.25rem; font-weight: 800; }
  .grid-form { display: grid; grid-template-columns: 1fr 1fr; gap: 14px 18px; margin-top: 16px; }
  .col-full { grid-column: 1 / -1; }
  @media (max-width: 560px) { .grid-form { grid-template-columns: 1fr; } }
  fieldset { margin: 18px 0 6px; padding: 14px; border: 1px solid var(--line); border-radius: 12px; background: var(--soft); }
  legend { padding: 0 6px; font-weight: 700; font-size: 0.85rem; color: var(--navy); }
  .fila-pre { margin-bottom: 8px; }
  .fila-pre-in { display: flex; gap: 8px; }
  .fila-pre-in select { flex: 1; min-width: 0; }
  .aviso { margin: 14px 0 0; padding: 11px 13px; border-radius: 10px; border: 1px solid rgba(27, 57, 106, 0.18); background: color-mix(in srgb, var(--navy) 5%, #fff); font-size: 0.8125rem; }
  .aviso-warn { border-color: rgba(184, 116, 0, 0.3); background: rgba(224, 168, 0, 0.08); }
  .aviso-err { border-color: rgba(220, 53, 69, 0.3); background: rgba(220, 53, 69, 0.06); }
  .aviso[hidden] { display: none; }
  .acciones-dlg { display: flex; justify-content: flex-end; flex-wrap: wrap; gap: 8px; margin-top: 20px; }
  .resumen-materia { margin: 12px 0; padding: 12px 14px; border: 1px solid var(--line); border-radius: 12px; background: var(--soft); font-size: 0.875rem; }
  #dlg-bitacora table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
  #dlg-bitacora th, #dlg-bitacora td { padding: 9px 10px; text-align: left; vertical-align: top; border-bottom: 1px solid var(--line); }
  #dlg-bitacora th { position: sticky; top: 0; background: var(--soft); color: var(--navy); }
</style>
@endpush

@section('content')
<div class="wrap">
    <p class="banner" id="banner" role="status" hidden></p>

  <div class="hero">
    <div>
      <span class="eyebrow"><img src="{{ asset('images/tecnm-emblema.png') }}" alt="" aria-hidden="true">Administración</span>
      <h1>Administración del catálogo de materias</h1>
      <p class="sub">Plan de estudios oficial: {{ $plan->name }} ({{ $plan->code }}). Gestiona el alta, edición, baja lógica y eliminación de asignaturas.</p>
    </div>
    <div class="actions">
      <button type="button" class="btn-action" id="btn-bitacora">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="9"/></svg>
        <span>Bitácora (<span id="cont-bitacora">0</span>)</span>
      </button>
      <button type="button" class="btn-action primary" id="btn-nueva">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
        Dar de alta materia
      </button>
    </div>
  </div>

  <div class="stats" aria-label="Resumen del catálogo">
    <div class="stat" style="--k: var(--a)">
      <small>Materias activas en retícula</small>
      <strong id="st-activas">0</strong>
    </div>
    <div class="stat" style="--k: var(--p)">
      <small>Materias inactivas (baja lógica)</small>
      <strong id="st-inactivas">0</strong>
    </div>
    <div class="stat" style="--k: var(--navy)">
      <small>Créditos totales del plan activo</small>
      <strong id="st-creditos">0</strong>
    </div>
    <div class="stat" style="--k: var(--gold)">
      <small>Rango permitido (Créditos / Semestre)</small>
      <strong>1–10 <span>cr.</span> · 1–9 <span>sem.</span></strong>
    </div>
  </div>

  <section class="panel panel-cat" aria-labelledby="t-lista">
    <h2 id="t-lista" class="vh">Lista de materias del catálogo</h2>
    <div class="filtros">
      <div>
        <label for="f-buscar">Buscar por clave o nombre</label>
        <input id="f-buscar" type="text" placeholder="Ejemplo: SCD1018 o Estructura de Datos" autocomplete="off">
      </div>
      <div>
        <label for="f-semestre">Filtrar por semestre</label>
        <select id="f-semestre">
          <option value="">Todos los semestres (1 a 9)</option>
          <option value="1">Semestre 1</option>
          <option value="2">Semestre 2</option>
          <option value="3">Semestre 3</option>
          <option value="4">Semestre 4</option>
          <option value="5">Semestre 5</option>
          <option value="6">Semestre 6</option>
          <option value="7">Semestre 7</option>
          <option value="8">Semestre 8</option>
          <option value="9">Semestre 9</option>
        </select>
      </div>
      <div>
        <label for="f-estatus">Filtrar por estatus</label>
        <select id="f-estatus">
          <option value="">Todas (Activas e Inactivas)</option>
          <option value="Activa">Solo Activas</option>
          <option value="Inactiva">Solo Inactivas</option>
        </select>
      </div>
    </div>

    <div class="tabla-w">
      <table>
        <thead>
          <tr>
            <th>Clave</th>
            <th>Nombre de la materia</th>
            <th class="num">Semestre</th>
            <th class="num">Créditos</th>
            <th>Prerrequisitos</th>
            <th>Estatus</th>
            <th class="num">Acciones</th>
          </tr>
        </thead>
        <tbody id="tabla-cuerpo"></tbody>
      </table>
    </div>
  </section>
</div>

<!-- DIÁLOGO DE ALTA Y EDICIÓN -->
<dialog id="dlg-form" aria-labelledby="dlg-form-titulo">
  <form id="form-materia" novalidate>
    <h2 id="dlg-form-titulo">Dar de alta materia</h2>
    <p class="sub" id="dlg-form-sub">Los campos marcados con * son obligatorios. Todos los datos se validan antes de actualizar la retícula oficial.</p>

    <div class="grid-form">
      <div>
        <label for="in-clave">Clave <span class="req">*</span></label>
        <input id="in-clave" type="text" maxlength="10" autocomplete="off" aria-describedby="h-clave err-clave">
        <p class="hint" id="h-clave">De 5 a 10 caracteres (letras mayúsculas y dígitos, sin espacios ni símbolos). Ej. SCD1018.</p>
        <p class="err" id="err-clave" role="alert" hidden></p>
      </div>

      <div>
        <label for="in-estatus">Estatus del sistema</label>
        <input id="in-estatus" type="text" value="Activa" readonly aria-describedby="h-estatus">
        <p class="hint" id="h-estatus">Asignado por el sistema. Solo cambia mediante baja lógica.</p>
      </div>

      <div class="col-full">
        <label for="in-nombre">Nombre <span class="req">*</span></label>
        <input id="in-nombre" type="text" maxlength="100" autocomplete="off" aria-describedby="h-nombre err-nombre">
        <p class="hint" id="h-nombre">Entre 5 y 100 caracteres. Admite letras con acentos, dígitos, espacios y signos . , - ( ).</p>
        <p class="err" id="err-nombre" role="alert" hidden></p>
      </div>

      <div>
        <label for="in-creditos">Créditos <span class="req">*</span></label>
        <input id="in-creditos" type="text" inputmode="numeric" autocomplete="off" aria-describedby="h-creditos err-creditos">
        <p class="hint" id="h-creditos">Número entero entre 1 y 10.</p>
        <p class="err" id="err-creditos" role="alert" hidden></p>
      </div>

      <div>
        <label for="in-semestre">Semestre <span class="req">*</span></label>
        <input id="in-semestre" type="text" inputmode="numeric" autocomplete="off" aria-describedby="h-semestre err-semestre">
        <p class="hint" id="h-semestre">Número entero entre 1 y 9.</p>
        <p class="err" id="err-semestre" role="alert" hidden></p>
      </div>
    </div>

    <fieldset>
      <legend>Prerrequisitos (opcional)</legend>
      <div id="contenedor-prerrequisitos"></div>
      <p class="hint" id="hint-sin-pre">Sin prerrequisitos asignados. Cada prerrequisito debe estar activo y en un semestre menor.</p>
      <p class="err" id="err-prerrequisitos" role="alert" hidden></p>
      <button type="button" class="sec" id="btn-agregar-pre" style="margin-top:6px;">+ Agregar prerrequisito</button>
    </fieldset>

    <div class="aviso aviso-warn" id="aviso-confirmacion-edicion" hidden></div>

    <div class="acciones-dlg">
      <button type="button" class="sec" id="btn-cancelar-form">Cancelar</button>
      <button type="submit" class="btn" id="btn-guardar-form">Guardar materia</button>
    </div>
  </form>
</dialog>

<!-- DIÁLOGO DE CONFIRMACIÓN DE ELIMINACIÓN / BAJA LÓGICA -->
<dialog id="dlg-eliminar" aria-labelledby="dlg-del-titulo">
  <h2 id="dlg-del-titulo">Eliminar materia del catálogo</h2>
  <div class="resumen-materia" id="dlg-del-resumen"></div>
  <div id="dlg-del-cuerpo"></div>
  <div class="acciones-dlg" id="dlg-del-acciones"></div>
</dialog>

<!-- DIÁLOGO DE BITÁCORA DE AUDITORÍA -->
<dialog id="dlg-bitacora" aria-labelledby="dlg-bit-titulo">
  <h2 id="dlg-bit-titulo">Bitácora de auditoría del catálogo</h2>
  <p class="sub">Registro inmutable de altas, ediciones, bajas lógicas, eliminaciones e intentos rechazados por permisos.</p>
  <div class="tabla-w" style="margin-top:12px; max-height:340px; overflow-y:auto;">
    <table style="min-width:520px;">
      <thead>
        <tr>
          <th>Fecha / Hora</th>
          <th>Usuario (Rol)</th>
          <th>Acción</th>
          <th>Detalle de cambio (Anterior → Nuevo)</th>
        </tr>
      </thead>
      <tbody id="tabla-bitacora"></tbody>
    </table>
  </div>
  <div class="acciones-dlg">
    <button type="button" class="sec" id="btn-cerrar-bitacora">Cerrar</button>
  </div>
</dialog>
@endsection

@push('scripts')
<script>
const esc = s => String(s).replace(/[&<>"']/g, c => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
const $ = id => document.getElementById(id);
const BASE = "/admin/catalogo";
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

let CATALOGO = [];
let modoFormulario = "alta";
let materiaEditando = null;
let confirmacionPendiente = false;

// ---------- COMUNICACIÓN CON EL SERVIDOR ----------
async function api(metodo, ruta, cuerpo) {
  const resp = await fetch(BASE + ruta, {
    method: metodo,
    headers: { "Accept": "application/json", "Content-Type": "application/json", "X-CSRF-TOKEN": CSRF },
    body: cuerpo ? JSON.stringify(cuerpo) : undefined,
    credentials: "same-origin"
  });
  let json = {};
  try { json = await resp.json(); } catch (_) { /* respuesta sin JSON */ }
  return { ok: resp.ok, status: resp.status, json };
}

function manejarAcceso(r) {
  if (r.status === 401) { window.location.href = "/login"; return true; }
  if (r.status === 403) { mostrarBanner("HTTP 403: " + (r.json.message || "No cuentas con permisos para modificar el catálogo académico."), true); return true; }
  return false;
}

async function cargarCatalogo() {
  const r = await api("GET", "/materias");
  if (manejarAcceso(r)) return;
  CATALOGO = r.json.data;
  $("cont-bitacora").textContent = r.json.bitacoraTotal;
  renderizarTabla();
}

// ---------- UTILIDADES ----------
const buscarPorClave = c => CATALOGO.find(m => m.clave.toUpperCase() === String(c).toUpperCase());
const buscarPorId = id => CATALOGO.find(m => String(m.id) === String(id));
const dependientesDe = id => CATALOGO.filter(m => m.prerrequisitos.some(p => p.id === id));
const normalizarEspacios = txt => String(txt || "").trim().replace(/\s+/g, " ");

function mostrarBanner(mensaje, esError = false) {
  const b = $("banner");
  b.textContent = mensaje;
  b.className = esError ? "banner error" : "banner";
  b.hidden = false;
  b.scrollIntoView({ behavior: "smooth", block: "nearest" });
}

function actualizarResumen() {
  const activas = CATALOGO.filter(m => m.estatus === "Activa");
  $("st-activas").textContent = activas.length;
  $("st-inactivas").textContent = CATALOGO.length - activas.length;
  $("st-creditos").textContent = activas.reduce((a, m) => a + m.creditos, 0);
}

// ---------- TABLA ----------
function renderizarTabla() {
  actualizarResumen();
  const q = normalizarEspacios($("f-buscar").value).toLowerCase();
  const sem = $("f-semestre").value;
  const est = $("f-estatus").value;

  const filtradas = CATALOGO.filter(m => {
    if (q && !(m.clave + " " + m.nombre).toLowerCase().includes(q)) return false;
    if (sem && String(m.semestre) !== sem) return false;
    if (est && m.estatus !== est) return false;
    return true;
  });

  if (!filtradas.length) {
    $("tabla-cuerpo").innerHTML = '<tr><td colspan="7" class="sub">Ninguna materia coincide con los criterios de búsqueda.</td></tr>';
    return;
  }

  $("tabla-cuerpo").innerHTML = filtradas.map(m => {
    const deps = dependientesDe(m.id);
    const chipsPre = m.prerrequisitos.length
      ? m.prerrequisitos.map(p => '<span class="chip" title="' + esc(p.clave) + '">' + esc(p.clave) + " · " + esc(p.nombre) + '</span>').join("")
      : '<span class="sub">Sin prerrequisitos</span>';

    const infoUso = [m.alumnosConHistorial > 0 ? m.alumnosConHistorial + " alumnos con historial" : "Sin historial académico"];
    if (deps.length) infoUso.push("Prerrequisito de " + deps.map(d => d.clave).join(", "));

    const claseEstado = m.estatus === "Activa" ? "estado estado-activa" : "estado estado-inactiva";
    const botonSecundario = m.estatus === "Activa"
      ? '<button type="button" class="sec-danger" data-accion="eliminar" data-id="' + m.id + '">Eliminar</button>'
      : '<button type="button" class="sec" data-accion="reactivar" data-id="' + m.id + '">Reactivar</button>';

    return '<tr class="' + (m.estatus === "Inactiva" ? "fila-inactiva" : "") + '">' +
      '<td><strong>' + esc(m.clave) + '</strong></td>' +
      '<td>' + esc(m.nombre) + '<span class="meta-uso">' + esc(infoUso.join(" · ")) + '</span></td>' +
      '<td class="num">' + m.semestre + '</td>' +
      '<td class="num">' + m.creditos + '</td>' +
      '<td>' + chipsPre + '</td>' +
      '<td><span class="' + claseEstado + '">' + esc(m.estatus) + '</span></td>' +
      '<td class="num"><div class="acciones-celda">' +
        '<button type="button" class="sec" data-accion="editar" data-id="' + m.id + '">Editar</button>' +
        botonSecundario +
      '</div></td></tr>';
  }).join("");
}

$("f-buscar").addEventListener("input", renderizarTabla);
$("f-semestre").addEventListener("change", renderizarTabla);
$("f-estatus").addEventListener("change", renderizarTabla);

// ---------- PRERREQUISITOS EN EL FORMULARIO ----------
const obtenerPrerrequisitosSeleccionados = () => [...$("contenedor-prerrequisitos").querySelectorAll("select")].map(s => s.value);

function opcionesPrerrequisito(seleccionada, idActual) {
  const candidatas = CATALOGO.filter(m => m.estatus === "Activa" && m.id !== idActual);
  return '<option value="">Selecciona una materia activa</option>' + candidatas.map(m =>
    '<option value="' + m.id + '"' + (String(m.id) === String(seleccionada) ? " selected" : "") + '>' +
      esc(m.clave) + " — " + esc(m.nombre) + " (Sem. " + m.semestre + ")" + '</option>'
  ).join("");
}

function pintarFilasPrerrequisitos(valores) {
  const idActual = materiaEditando ? materiaEditando.id : null;
  $("contenedor-prerrequisitos").innerHTML = valores.map((val, idx) =>
    '<div class="fila-pre"><div class="fila-pre-in">' +
      '<select aria-label="Prerrequisito ' + (idx + 1) + '">' + opcionesPrerrequisito(val, idActual) + '</select>' +
      '<button type="button" class="sec" data-quitar="' + idx + '">Quitar</button>' +
    '</div></div>'
  ).join("");
  $("hint-sin-pre").hidden = valores.length > 0;
}

$("btn-agregar-pre").addEventListener("click", () => {
  pintarFilasPrerrequisitos(obtenerPrerrequisitosSeleccionados().concat(""));
  const selects = $("contenedor-prerrequisitos").querySelectorAll("select");
  if (selects.length) selects[selects.length - 1].focus();
});

$("contenedor-prerrequisitos").addEventListener("click", e => {
  const b = e.target.closest("button[data-quitar]");
  if (!b) return;
  const idx = Number(b.dataset.quitar);
  pintarFilasPrerrequisitos(obtenerPrerrequisitosSeleccionados().filter((_, i) => i !== idx));
});

// ---------- VALIDACIÓN (cliente: apoyo de UX; el servidor valida de nuevo) ----------
function marcarError(inputEl, errEl, mensaje) {
  errEl.textContent = mensaje || "";
  errEl.hidden = !mensaje;
  if (inputEl) inputEl.setAttribute("aria-invalid", mensaje ? "true" : "false");
}

function limpiarErroresFormulario() {
  marcarError($("in-clave"), $("err-clave"), "");
  marcarError($("in-nombre"), $("err-nombre"), "");
  marcarError($("in-creditos"), $("err-creditos"), "");
  marcarError($("in-semestre"), $("err-semestre"), "");
  $("err-prerrequisitos").textContent = "";
  $("err-prerrequisitos").hidden = true;
  $("aviso-confirmacion-edicion").hidden = true;
}

function validarFormulario() {
  const errores = {};
  const claveRaw = normalizarEspacios($("in-clave").value).toUpperCase();
  $("in-clave").value = claveRaw;
  if (modoFormulario === "alta") {
    const canon = c => c.replace(/[-\s]/g, "").toUpperCase();
    if (!claveRaw) errores.clave = "El campo Clave es obligatorio.";
    else if (!/^[A-Z0-9]{5,10}$/.test(claveRaw)) errores.clave = "La clave debe tener de 5 a 10 caracteres entre letras mayúsculas y números, sin espacios.";
    else if (CATALOGO.some(m => canon(m.clave) === canon(claveRaw))) errores.clave = "Ya existe una materia con la clave " + claveRaw + ".";
  }

  const nombre = normalizarEspacios($("in-nombre").value);
  $("in-nombre").value = nombre;
  if (!nombre) errores.nombre = "El campo Nombre es obligatorio.";
  else if (nombre.length < 5 || nombre.length > 100 || !/^[\p{L}0-9 .,()\-]+$/u.test(nombre) || /^[0-9 .,()\-]+$/.test(nombre))
    errores.nombre = "El nombre debe tener entre 5 y 100 caracteres válidos y no puede contener solo números.";

  const cr = normalizarEspacios($("in-creditos").value);
  if (!cr) errores.creditos = "El campo Créditos es obligatorio.";
  else if (!/^\d+$/.test(cr) || Number(cr) < 1 || Number(cr) > 10) errores.creditos = "Los créditos deben ser un número entero entre 1 y 10.";

  const sem = normalizarEspacios($("in-semestre").value);
  if (!sem) errores.semestre = "El campo Semestre es obligatorio.";
  else if (!/^\d+$/.test(sem) || Number(sem) < 1 || Number(sem) > 9) errores.semestre = "El semestre debe ser un número entero entre 1 y 9.";

  const pre = obtenerPrerrequisitosSeleccionados();
  if (pre.some(v => !v) || new Set(pre).size !== pre.length)
    errores.prerrequisitos = "El prerrequisito seleccionado genera una dependencia circular o no es válido.";

  return { errores, datos: { clave: claveRaw, nombre, creditos: cr, semestre: sem, prerrequisitos: pre } };
}

function pintarErrores(errores) {
  marcarError($("in-clave"), $("err-clave"), errores.clave);
  marcarError($("in-nombre"), $("err-nombre"), errores.nombre);
  marcarError($("in-creditos"), $("err-creditos"), errores.creditos);
  marcarError($("in-semestre"), $("err-semestre"), errores.semestre);
  $("err-prerrequisitos").textContent = errores.prerrequisitos || "";
  $("err-prerrequisitos").hidden = !errores.prerrequisitos;
  const orden = [["clave", "in-clave"], ["nombre", "in-nombre"], ["creditos", "in-creditos"], ["semestre", "in-semestre"]];
  const primero = orden.find(([k]) => errores[k]);
  if (primero) $(primero[1]).focus();
  else if (errores.prerrequisitos) { const s = $("contenedor-prerrequisitos").querySelector("select"); if (s) s.focus(); }
}

// ---------- ALTA Y EDICIÓN ----------
function abrirModalAlta() {
  modoFormulario = "alta";
  materiaEditando = null;
  confirmacionPendiente = false;
  limpiarErroresFormulario();
  $("dlg-form-titulo").textContent = "Dar de alta materia";
  $("in-clave").value = "";
  $("in-clave").readOnly = false;
  $("in-estatus").value = "Activa";
  $("in-nombre").value = "";
  $("in-creditos").value = "";
  $("in-semestre").value = "";
  pintarFilasPrerrequisitos([]);
  $("btn-guardar-form").textContent = "Guardar materia";
  $("dlg-form").showModal();
  $("in-clave").focus();
}

function abrirModalEdicion(id) {
  const mat = buscarPorId(id);
  if (!mat) return;
  modoFormulario = "edicion";
  materiaEditando = mat;
  confirmacionPendiente = false;
  limpiarErroresFormulario();
  $("dlg-form-titulo").textContent = "Editar materia " + mat.clave;
  $("in-clave").value = mat.clave;
  $("in-clave").readOnly = true;
  $("in-estatus").value = mat.estatus;
  $("in-nombre").value = mat.nombre;
  $("in-creditos").value = mat.creditos;
  $("in-semestre").value = mat.semestre;
  pintarFilasPrerrequisitos(mat.prerrequisitos.map(p => p.id));
  $("btn-guardar-form").textContent = "Guardar cambios";
  $("dlg-form").showModal();
  $("in-nombre").focus();
}

$("btn-nueva").addEventListener("click", abrirModalAlta);
$("btn-cancelar-form").addEventListener("click", () => $("dlg-form").close());

$("form-materia").addEventListener("submit", async e => {
  e.preventDefault();
  const { errores, datos } = validarFormulario();
  pintarErrores(errores);
  if (Object.keys(errores).length) return;

  const cuerpo = { ...datos, confirmado: confirmacionPendiente };
  const r = modoFormulario === "alta"
    ? await api("POST", "/materias", cuerpo)
    : await api("PUT", "/materias/" + materiaEditando.id, cuerpo);

  if (r.status === 401 || r.status === 403) { $("dlg-form").close(); manejarAcceso(r); return; }
  if (r.status === 422) { pintarErrores(r.json.errors || {}); return; }
  if (r.status === 409 && r.json.requiereConfirmacion) {
    confirmacionPendiente = true;
    const box = $("aviso-confirmacion-edicion");
    box.innerHTML = "<strong>Confirmación requerida antes de guardar:</strong><br>" +
      r.json.avisos.map(a => "• " + esc(a)).join("<br>") + "<br>Pulsa nuevamente el botón de guardar para confirmar.";
    box.hidden = false;
    $("btn-guardar-form").textContent = "Confirmar y guardar";
    return;
  }
  if (!r.ok) { mostrarBanner(r.json.message || "No se pudo guardar la materia.", true); return; }

  $("dlg-form").close();
  mostrarBanner(r.json.message);
  await cargarCatalogo();
});

// ---------- ELIMINACIÓN, BAJA LÓGICA Y REACTIVACIÓN ----------
async function abrirDialogoEliminar(id) {
  const mat = buscarPorId(id);
  if (!mat) return;
  const r = await api("GET", "/materias/" + id + "/impacto");
  if (manejarAcceso(r)) return;
  const imp = r.json.data;

  $("dlg-del-resumen").innerHTML =
    "<strong>" + esc(mat.clave) + " — " + esc(mat.nombre) + "</strong><br>" +
    "Semestre " + mat.semestre + " · " + mat.creditos + " créditos · Estatus actual: " + esc(mat.estatus);

  const cuerpo = $("dlg-del-cuerpo");
  const acciones = $("dlg-del-acciones");

  if (imp.modo === "bloqueada") {
    const lista = imp.dependientes.map(d => d.clave + " (" + d.nombre + ")").join(", ");
    $("dlg-del-titulo").textContent = "Eliminación bloqueada por prerrequisitos";
    cuerpo.innerHTML =
      '<div class="aviso aviso-err" role="alert"><strong>No se puede eliminar: es prerrequisito de ' + esc(lista) + '.</strong></div>' +
      '<p class="hint" style="margin-top:10px;">Debes retirar o reasignar primero esas dependencias antes de poder eliminar o dar de baja esta materia.</p>';
    acciones.innerHTML = '<button type="button" class="btn" id="btn-del-cerrar">Entendido</button>';
  } else if (imp.modo === "baja_logica") {
    $("dlg-del-titulo").textContent = "Baja lógica de materia con historial";
    cuerpo.innerHTML =
      '<div class="aviso aviso-warn" role="alert"><strong>La materia tiene historial académico; solo puede darse de baja.</strong></div>' +
      '<p style="margin:12px 0 0; font-size:0.875rem;">Existen <strong>' + imp.alumnosConHistorial + ' expedientes de alumnos</strong> vinculados a esta asignatura. ' +
      'Al confirmar la baja lógica, la materia pasará a estatus <strong>Inactiva</strong>: dejará de mostrarse en la retícula vigente y en nuevas recomendaciones, pero se conservará en el historial de quienes ya la cursaron.</p>';
    acciones.innerHTML =
      '<button type="button" class="sec" id="btn-del-cerrar">Cancelar</button>' +
      '<button type="button" class="btn-warn" id="btn-confirmar-eliminar">Confirmar baja lógica</button>';
  } else {
    $("dlg-del-titulo").textContent = "Confirmar eliminación física de materia";
    cuerpo.innerHTML =
      '<p style="margin:10px 0; font-size:0.9rem;">Esta materia no tiene historial académico registrado ni es prerrequisito de ninguna otra asignatura. ' +
      '¿Confirmas que deseas eliminarla físicamente del catálogo? Desaparecerá de forma permanente de la retícula oficial.</p>';
    acciones.innerHTML =
      '<button type="button" class="sec" id="btn-del-cerrar">Cancelar</button>' +
      '<button type="button" class="btn-danger" id="btn-confirmar-eliminar">Eliminar definitivamente</button>';
  }

  $("dlg-eliminar").showModal();
  $("btn-del-cerrar").addEventListener("click", () => $("dlg-eliminar").close());

  const btn = $("btn-confirmar-eliminar");
  if (btn) btn.addEventListener("click", async () => {
    btn.disabled = true;
    const res = await api("DELETE", "/materias/" + id, { confirmado: true });
    $("dlg-eliminar").close();
    if (manejarAcceso(res)) return;
    mostrarBanner(res.json.message, !res.ok);
    await cargarCatalogo();
  });
}

async function reactivarMateria(id) {
  const r = await api("POST", "/materias/" + id + "/reactivar");
  if (manejarAcceso(r)) return;
  mostrarBanner(r.json.message, !r.ok);
  await cargarCatalogo();
}

$("tabla-cuerpo").addEventListener("click", e => {
  const btn = e.target.closest("button[data-accion]");
  if (!btn) return;
  const id = Number(btn.dataset.id);
  if (btn.dataset.accion === "editar") abrirModalEdicion(id);
  else if (btn.dataset.accion === "eliminar") abrirDialogoEliminar(id);
  else if (btn.dataset.accion === "reactivar") reactivarMateria(id);
});

// ---------- BITÁCORA ----------
async function abrirBitacora() {
  const r = await api("GET", "/bitacora");
  if (manejarAcceso(r)) return;
  $("tabla-bitacora").innerHTML = r.json.data.map(b =>
    '<tr><td>' + esc(b.fecha) + '</td><td>' + esc(b.usuario) + '</td><td><strong>' + esc(b.accion) + '</strong></td><td>' + esc(b.detalle) + '</td></tr>'
  ).join("");
  $("dlg-bitacora").showModal();
}

$("btn-bitacora").addEventListener("click", abrirBitacora);
$("btn-cerrar-bitacora").addEventListener("click", () => $("dlg-bitacora").close());


cargarCatalogo();
</script>
@endpush
