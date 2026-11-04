@php
    $resumen = $grid['resumen'];
    $carrera = $student->career ?? 'Ingeniería en Sistemas Computacionales';
    $carga = $escenario['recomendacionCarga'];
    $proy = $escenario['proyeccionEgreso'];
    $cargaBase = $base['recomendacionCarga'];
    $proyBase = $base['proyeccionEgreso'];
    $difSem = $proy['semestresRestantesEstimados'] - $proyBase['semestresRestantesEstimados'];
    $esInstitucional = $limite === $limitesInstitucionales['maximo'] && $ritmo === $limitesInstitucionales['promedio'];
@endphp
@extends('layouts.risa', [
    'activo' => 'simulador',
    'perfil' => [
        'nombre' => $student->name,
        'etiqueta' => $student->control_number,
        'lineas' => ['No. de control ' . $student->control_number, $carrera],
        'avance' => $resumen['porcentajeAvance'],
    ],
])

@section('titulo', 'Simulador de escenarios')

@push('styles')
<style>
    .sim-grid { display: grid; grid-template-columns: minmax(280px, 360px) 1fr; gap: 20px; align-items: start; }
    .sim-grid > * { min-width: 0; }
    @media (max-width: 960px) { .sim-grid { grid-template-columns: 1fr; } }
    .controles { padding: 20px; position: sticky; top: 84px; }
    @media (max-width: 960px) { .controles { position: static; } }
    .controles h2 { margin: 0 0 4px; font-size: 1.05rem; color: var(--navy); }
    .control { margin-top: 20px; }
    .control-h { display: flex; justify-content: space-between; align-items: center; gap: 10px; }
    .control label { font-weight: 600; font-size: 0.875rem; }
    .control output { white-space: nowrap; font-size: 1.4rem; font-weight: 800; color: var(--navy); font-variant-numeric: tabular-nums; }
    .control output small { font-size: 0.75rem; font-weight: 500; color: var(--muted); }
    .control input[type="range"] { width: 100%; margin: 10px 0 4px; accent-color: var(--navy); height: 28px; cursor: pointer; }
    .escala { display: flex; justify-content: space-between; font-size: 0.72rem; color: var(--muted); }
    .control .hint { margin: 6px 0 0; font-size: 0.78rem; color: var(--muted); }
    .control-acciones { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 22px; }
    .control-acciones .btn-action { flex: 1; justify-content: center; text-decoration: none; }

    .compara .stat strong { display: flex; align-items: baseline; gap: 8px; flex-wrap: wrap; }
    .delta { display: inline-flex; padding: 1px 8px; border-radius: 999px; font-size: 0.72rem; font-weight: 700; }
    .delta.mejor { background: rgba(40,167,69,0.12); color: #1E7B34; }
    .delta.peor { background: rgba(220,53,69,0.1); color: #B02A37; }
    .delta.igual { background: var(--soft); color: var(--muted); }
    .base { display: block; margin-top: 4px; font-size: 0.75rem; color: var(--muted); }

    .bloque { padding: 22px; margin-bottom: 20px; }
    .tag-recom { display: inline-block; padding: 2px 9px; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
    .tag-recom.rep { background: rgba(220, 53, 69, 0.1); color: #B02A37; }
    .tag-recom.crit { background: rgba(0, 123, 255, 0.1); color: #0058B8; }
    .tag-recom.ord { background: rgba(40, 167, 69, 0.12); color: #1E7B34; }
    .motivo { font-size: 0.75rem; color: var(--muted); margin-top: 3px; }
    .medidor { height: 10px; margin: 12px 0 6px; border-radius: 5px; background: color-mix(in srgb, var(--navy) 8%, #fff); overflow: hidden; position: relative; }
    .medidor span { display: block; height: 100%; border-radius: 5px; background: linear-gradient(90deg, var(--navy), var(--navy-light)); }
    .medidor i { position: absolute; top: -2px; bottom: -2px; width: 2px; background: var(--r); }
    .medidor-leyenda { display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--muted); }
    .proy { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 12px; margin-top: 14px; }
    .proy div { padding: 12px 14px; border-radius: 12px; background: var(--soft); }
    .proy small { display: block; color: var(--muted); font-size: 0.75rem; }
    .proy strong { font-size: 1.15rem; }
    .cadena { margin-top: 14px; font-size: 0.8125rem; color: var(--muted); line-height: 1.6; }
    .cadena b { color: var(--navy); }
    .nota { margin: 18px 0 0; font-size: 0.8125rem; color: var(--muted); }
</style>
@endpush

@section('content')
<div class="wrap">
    <div class="hero">
        <div>
            <span class="eyebrow"><img src="{{ asset('images/tecnm-emblema.png') }}" alt="" aria-hidden="true">Planeación académica</span>
            <h1>Simulador de escenarios</h1>
            <p class="sub">Cambia cuántos créditos llevarás y el motor de recomendación recalcula tu carga y tu fecha estimada de egreso.</p>
        </div>
    </div>

    <div class="sim-grid">
        <form class="panel controles" method="get" action="{{ route('recomendacion.simulador') }}" id="form-sim">
            <h2>Tu escenario</h2>
            <p class="sub" style="font-size:0.82rem;">Los valores institucionales son {{ $limitesInstitucionales['maximo'] }} créditos máximos y {{ $limitesInstitucionales['promedio'] }} por semestre.</p>

            <div class="control">
                <div class="control-h">
                    <label for="creditos">Créditos máximos el próximo semestre</label>
                    <output for="creditos" id="out-creditos">{{ $limite }} <small>créditos</small></output>
                </div>
                <input type="range" id="creditos" name="creditos" min="{{ $rango[0] }}" max="{{ $rango[1] }}" step="1" value="{{ $limite }}">
                <div class="escala"><span>{{ $rango[0] }}</span><span>Mínimo institucional: {{ $limitesInstitucionales['minimo'] }}</span><span>{{ $rango[1] }}</span></div>
                <p class="hint">Define cuántas materias puede sugerirte el motor para el siguiente periodo.</p>
            </div>

            <div class="control">
                <div class="control-h">
                    <label for="ritmo">Créditos que llevarás por semestre</label>
                    <output for="ritmo" id="out-ritmo">{{ $ritmo }} <small>créditos</small></output>
                </div>
                <input type="range" id="ritmo" name="ritmo" min="{{ $rango[0] }}" max="{{ $rango[1] }}" step="1" value="{{ $ritmo }}">
                <div class="escala"><span>{{ $rango[0] }}</span><span>{{ $rango[1] }}</span></div>
                <p class="hint">Se usa para estimar cuántos semestres te faltan para egresar.</p>
            </div>

            <div class="control-acciones">
                <button type="submit" class="btn-action primary">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="M13 3L4 14h7l-1 7 9-11h-7l1-7z"/></svg>
                    Simular
                </button>
                @unless($esInstitucional)
                    <a class="btn-action" href="{{ route('recomendacion.simulador') }}">Restablecer</a>
                @endunless
            </div>
        </form>

        <div>
            <section class="stats compara" aria-label="Resultado del escenario">
                @php
                    $difCred = $carga['creditosTotales'] - $cargaBase['creditosTotales'];
                    $difMat = count($carga['materiasRecomendadas']) - count($cargaBase['materiasRecomendadas']);
                @endphp
                <div class="stat" style="--k: var(--navy)">
                    <small>Créditos sugeridos</small>
                    <strong>{{ $carga['creditosTotales'] }}
                        @if(! $esInstitucional)<span class="delta {{ $difCred === 0 ? 'igual' : ($difCred > 0 ? 'mejor' : 'peor') }}">{{ $difCred > 0 ? '+' : '' }}{{ $difCred }}</span>@endif
                    </strong>
                    <span class="base">Con límites institucionales: {{ $cargaBase['creditosTotales'] }}</span>
                </div>
                <div class="stat" style="--k: var(--c)">
                    <small>Materias sugeridas</small>
                    <strong>{{ count($carga['materiasRecomendadas']) }}
                        @if(! $esInstitucional)<span class="delta {{ $difMat === 0 ? 'igual' : ($difMat > 0 ? 'mejor' : 'peor') }}">{{ $difMat > 0 ? '+' : '' }}{{ $difMat }}</span>@endif
                    </strong>
                    <span class="base">Con límites institucionales: {{ count($cargaBase['materiasRecomendadas']) }}</span>
                </div>
                <div class="stat" style="--k: var(--gold)">
                    <small>Semestres para egresar</small>
                    <strong>{{ $proy['semestresRestantesEstimados'] }}
                        @if(! $esInstitucional)<span class="delta {{ $difSem === 0 ? 'igual' : ($difSem < 0 ? 'mejor' : 'peor') }}">{{ $difSem > 0 ? '+' : '' }}{{ $difSem }}</span>@endif
                    </strong>
                    <span class="base">Con {{ $limitesInstitucionales['promedio'] }} créditos por semestre: {{ $proyBase['semestresRestantesEstimados'] }}</span>
                </div>
            </section>

            @foreach($carga['diagnostico'] as $diag)
                <p class="banner {{ str_starts_with($diag, 'Atención') ? 'error' : 'info' }}">{{ $diag }}</p>
            @endforeach

            <section class="panel bloque" aria-labelledby="t-carga">
                <div class="seccion-h">
                    <h2 id="t-carga">Carga sugerida con este escenario</h2>
                    <span class="sub">{{ $carga['creditosTotales'] }} de {{ $limite }} créditos</span>
                </div>
                @php $pct = $limite ? min(100, round($carga['creditosTotales'] / $limite * 100)) : 0; @endphp
                <div class="medidor" role="img" aria-label="{{ $carga['creditosTotales'] }} de {{ $limite }} créditos">
                    <span style="width: {{ $pct }}%"></span>
                    @if($limitesInstitucionales['minimo'] <= $limite)
                        <i style="left: {{ round($limitesInstitucionales['minimo'] / $limite * 100) }}%" title="Mínimo institucional"></i>
                    @endif
                </div>
                <div class="medidor-leyenda"><span>0</span><span>La línea roja marca el mínimo institucional ({{ $limitesInstitucionales['minimo'] }})</span><span>{{ $limite }}</span></div>

                @if(count($carga['materiasRecomendadas']))
                    <div class="tabla-w" style="margin-top:16px;">
                        <table class="tabla">
                            <thead><tr><th>Clave</th><th>Materia</th><th class="num">Créditos</th><th class="num">Sem.</th><th>Motivo</th></tr></thead>
                            <tbody>
                                @foreach($carga['materiasRecomendadas'] as $rec)
                                    <tr>
                                        <td><code>{{ $rec['code'] }}</code></td>
                                        <td><strong>{{ $rec['name'] }}</strong></td>
                                        <td class="num">{{ $rec['credits'] }}</td>
                                        <td class="num">{{ $rec['semester_order'] }}°</td>
                                        <td>
                                            @if($rec['esRepeticion'])
                                                <span class="tag-recom rep">Repetición urgente</span>
                                            @elseif($rec['materiasQueDesbloquea'] > 0)
                                                <span class="tag-recom crit">Ruta crítica (+{{ $rec['materiasQueDesbloquea'] }})</span>
                                            @else
                                                <span class="tag-recom ord">Avance regular</span>
                                            @endif
                                            <div class="motivo">{{ $rec['motivo'] }}</div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="vacio">
                        <svg viewBox="0 0 24 24" width="40" height="40" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 11v6M12 7.5v.01"/></svg>
                        <h3>Ninguna materia cabe en este límite</h3>
                        <p>Con {{ $limite }} créditos no entra ninguna de las materias que tienes disponibles. Sube el límite para ver una propuesta.</p>
                    </div>
                @endif

                @if(count($carga['materiasDisponiblesExcluidasPorCupo']))
                    <p class="nota"><strong>No entraron por el límite de créditos:</strong>
                        @foreach($carga['materiasDisponiblesExcluidasPorCupo'] as $exc)
                            {{ $exc['name'] }} ({{ $exc['credits'] }} cr.){{ $loop->last ? '.' : ', ' }}
                        @endforeach
                    </p>
                @endif
            </section>

            <section class="panel bloque" aria-labelledby="t-proy">
                <div class="seccion-h">
                    <h2 id="t-proy">Proyección de egreso</h2>
                    <span class="sub">Llevando {{ $ritmo }} créditos por semestre</span>
                </div>
                <p style="margin:0;">{{ $proy['diagnostico'] }}</p>
                <div class="proy">
                    <div><small>Semestres restantes estimados</small><strong>{{ $proy['semestresRestantesEstimados'] }}</strong></div>
                    <div><small>Mínimo por seriación</small><strong>{{ $proy['cotaPorSeriacion'] }} sem.</strong></div>
                    <div><small>Mínimo por créditos</small><strong>{{ $proy['cotaPorCreditos'] }} sem.</strong></div>
                </div>
                @if(!empty($proy['cadenaCriticaEjemplo']))
                    <p class="cadena"><b>Cadena crítica de seriación:</b> {{ implode(' ➔ ', $proy['cadenaCriticaEjemplo']) }}</p>
                @endif
            </section>

            <p class="nota">La simulación es orientativa y no sustituye la validación ni la inscripción oficial de Control Escolar.</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Muestra el valor de cada control mientras se mueve y recalcula al soltarlo
document.querySelectorAll('#form-sim input[type="range"]').forEach(r => {
    const out = document.getElementById('out-' + r.id);
    r.addEventListener('input', () => { out.innerHTML = r.value + ' <small>créditos</small>'; });
    r.addEventListener('change', () => document.getElementById('form-sim').requestSubmit());
});
</script>
@endpush
