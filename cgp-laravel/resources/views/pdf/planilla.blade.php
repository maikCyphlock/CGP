{{-- Planilla de Recepción (Res. CMP-013-2024) llenada con los datos del expediente.
     Réplica de design/pages/comprobante.html para dompdf: mismas medidas (hoja carta, 15mm de margen,
     anchos de columna y altos de fila medidos sobre el diseño), solo tablas, sin flexbox. --}}
@php
    $x = fn (bool $marcado) => '<span class="box">'.($marcado ? 'X' : '').'</span>';
    $chk = fn (bool $marcado, string $texto) => '<span class="chk">'.$x($marcado).' '.e($texto).'</span>';
    $tipo = $case->claimType->code;
    $cd = $c->contact_data ?? [];
    $fecha = $case->created_at->timezone('America/Caracas');
    $otroTipo = collect($senalados)->pluck('otro')->filter()->first();
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Planilla {{ $case->case_number }}</title>
<style>
    @page { margin: 15mm; }
    body { margin: 0; font-family: Helvetica, Arial, sans-serif; font-size: 10px; line-height: 1.15; color: #000; }
    table { width: 100%; border-collapse: collapse; margin-bottom: -1px; table-layout: fixed; }
    td { border: 1px solid #444; padding: 3px 4px; vertical-align: top; }
    table.nb td, td.nb { border: none; }
    .bg-blue { background: #a9d0f5; text-align: center; font-weight: bold; text-transform: uppercase; font-size: 9px; padding: 4px; }
    .label { font-weight: bold; font-size: 9px; display: block; margin-bottom: 2px; color: #222; }
    .val { min-height: 15px; }
    .val30 { min-height: 30px; }
    .checks { margin-top: 2px; font-size: 9px; }
    .chk { margin-right: 8px; white-space: nowrap; font-size: 9px; }
    .box { display: inline-block; width: 10px; height: 10px; border: 1px solid #444; background: #fff;
           margin-right: 3px; vertical-align: middle; text-align: center; font-size: 9px; line-height: 10px; font-weight: bold; }
    .center { text-align: center; }
    .middle { vertical-align: middle; }
    .doc .box { float: left; }
    .doc div { margin-left: 15px; font-size: 9px; }
    .sig td { border: none; padding: 0; text-align: center; vertical-align: bottom; font-size: 9px; }
    .sig-line { border-top: 1px solid #000; margin-top: 40px; padding-top: 3px; }
</style>
</head>
<body>

{{-- LOGOS SUPERIORES --}}
<table class="nb" style="margin-bottom:5px">
    <tr>
        <td style="padding:0 5px"><img src="{{ public_path('assets/img/logoletrasnegras.png') }}" style="width:75px"></td>
        <td style="padding:0 5px; text-align:right"><img src="{{ public_path('assets/img/IMG-20241209-WA0052-removebg-preview.png') }}" style="width:105px"></td>
    </tr>
</table>

{{-- ENCABEZADO --}}
<table>
    <tr>
        <td style="width:50%; padding:4px; position:relative" class="middle">
            <img src="{{ public_path('assets/img/logosinfondo.png') }}" style="position:absolute; left:6px; top:6px; width:40px">
            <div class="center">
                <div style="font-size:9px">Contraloría del Municipio Páez<br>Del Estado Portuguesa</div>
                <div style="font-size:8.5px">Dirección de Atención al Ciudadano</div>
                <div style="font-weight:bold; font-size:10px; margin-top:2px">PLANILLA DE RECEPCIÓN</div>
            </div>
        </td>
        <td style="width:35%; padding:0">
            <table class="nb" style="margin:0">
                <tr><td colspan="3" style="border-bottom:1px solid #444"><span class="label">2. Fecha de solicitud:</span></td></tr>
                <tr>
                    <td class="center" style="border-right:1px solid #444">Día<br>{{ $fecha->format('d') }}</td>
                    <td class="center" style="border-right:1px solid #444">Mes<br>{{ $fecha->format('m') }}</td>
                    <td class="center">Año<br>{{ $fecha->format('Y') }}</td>
                </tr>
            </table>
        </td>
        <td style="width:15%"><span class="label">3. Hora:</span>{{ $fecha->format('h:i a') }}</td>
    </tr>
</table>

<table>
    <tr>
        <td style="width:50%">
            <span class="label">1. Tipo de Recepción:</span>
            <table class="nb" style="width:auto; margin:0; table-layout:auto"><tr>
                <td style="padding:0 40px 0 0; font-size:9px">{!! $x($tipo === 'COMPLAINT') !!} Denuncia<div style="height:4px"></div>{!! $x($tipo === 'CLAIM') !!} Reclamo</td>
                <td style="padding:0; font-size:9px">{!! $x($tipo === 'GRIEVANCE') !!} Queja<div style="height:4px"></div>{!! $x($tipo === 'PETITION') !!} Petición</td>
                @if ($tipo === 'SUGGESTION')<td style="padding:0 0 0 40px; font-size:9px">{!! $x(true) !!} Sugerencia</td>@endif
            </tr></table>
        </td>
        <td style="width:50%"><span class="label">4. Nro. Expediente / Registro:</span><div class="val"><b>{{ $case->case_number }}</b></div></td>
    </tr>
</table>

{{-- DATOS DEL CIUDADANO --}}
<table>
    <tr><td colspan="3" class="bg-blue">Datos del ciudadano (solicitante)</td></tr>
</table>
<table>
    <tr>
        <td style="width:40%">
            <span class="label">5. Cédula de Identidad/Pasaporte:</span>
            <div class="checks">{!! $chk($docTipo === 'V', 'V') !!}{!! $chk($docTipo === 'E', 'E') !!}@if ($docTipo === 'PASSPORT'){!! $chk(true, 'Pasaporte') !!}@endif {{ $c->id_doc_number }}</div>
        </td>
        <td style="width:60%"><span class="label">6. Apellidos y Nombres:</span><div class="val">{{ $c->last_name }}, {{ $c->first_name }}</div></td>
    </tr>
</table>
<table>
    <tr>
        <td>
            <span class="label">7. Sexo:</span>
            <div class="checks">{!! $chk($c->sex === 'M', 'M') !!}{!! $chk($c->sex === 'F', 'F') !!}</div>
        </td>
        <td style="width:33%"><span class="label">8. Edad:</span><div class="val">{{ $c->birth_date?->age }}</div></td>
        <td style="width:27%">
            <span class="label">9. Estado Civil:</span>
            <div class="checks" style="line-height:20px">
                {!! $chk(($cd['marital_status'] ?? '') === 'Soltero(a)', 'Soltero(a)') !!}{!! $chk(($cd['marital_status'] ?? '') === 'Casado(a)', 'Casado(a)') !!}<br>
                {!! $chk(($cd['marital_status'] ?? '') === 'Divorciado(a)', 'Divorciado(a)') !!}{!! $chk(($cd['marital_status'] ?? '') === 'Viudo(a)', 'Viudo(a)') !!}
                @if (($cd['marital_status'] ?? '') === 'Unión estable')<br>{!! $chk(true, 'Unión estable') !!}@endif
            </div>
        </td>
    </tr>
</table>
<table>
    <tr>
        <td style="width:73%"><span class="label">10. Lugar y fecha de nacimiento:</span><div class="val">{{ collect([$cd['birth_place'] ?? null, $c->birth_date?->format('d/m/Y')])->filter()->join(', ') }}</div></td>
        <td style="width:27%; padding:0">
            <table class="nb" style="margin:0"><tr>
                <td style="width:50%; border-right:1px solid #444"><span class="label">11. Nivel Educativo:</span>{{ $cd['education_level'] ?? '' }}</td>
                <td><span class="label">12. Profesión y/u ocupación:</span>{{ collect([$cd['profession'] ?? null, $cd['occupation'] ?? null])->filter()->join(' / ') }}</td>
            </tr></table>
        </td>
    </tr>
</table>
<table>
    <tr><td colspan="3"><span class="label">13. Dirección de habitación:</span><div class="val">{{ $c->address }}</div></td></tr>
</table>
<table>
    <tr>
        <td style="width:40%"><span class="label">14. Parroquia:</span><div class="val">{{ $c->parish }}</div></td>
        <td style="width:33%"><span class="label">15. Municipio:</span><div class="val">{{ $c->municipality }}</div></td>
        <td style="width:27%"><span class="label">16. Ciudad:</span><div class="val">{{ $c->city }}</div></td>
    </tr>
</table>
<table>
    <tr>
        <td style="width:40%"><span class="label">17. Correo Electrónico:</span><div class="val">{{ $c->email }}</div></td>
        <td style="width:33%"><span class="label">18. Telf. Hab.:</span><div class="val">{{ $cd['home_phone'] ?? '' }}</div></td>
        <td style="width:27%; padding:0">
            <table class="nb" style="margin:0"><tr>
                <td style="width:50%; border-right:1px solid #444"><span class="label">19. Telf. Celular:</span>{{ $c->mobile_phone }}</td>
                <td><span class="label">20. Telf. Trabajo:</span>{{ $cd['work_phone'] ?? '' }}</td>
            </tr></table>
        </td>
    </tr>
</table>

{{-- SEÑALADO(S) --}}
<table>
    <tr><td colspan="5" class="bg-blue">Datos de identificación y/o ubicación del señalado(s) según sea el caso</td></tr>
</table>
<table>
    <tr>
        <td colspan="5">
            <span class="label">21. Tipo del señalado(a):</span>
            <div class="checks">
                @foreach (['NATURAL_PERSON' => 'Persona Natural', 'LEGAL_ENTITY' => 'Persona Jurídica', 'GOV_AGENCY' => 'Órgano o Ente',
                           'COMMUNE' => 'Comuna', 'COMMUNAL_COUNCIL' => 'Consejo Comunal', 'JUSTICE_OF_PEACE' => 'Juez de Paz'] as $code => $nombre)<span class="chk" style="margin-right:15px">{!! $x(in_array($code, $tiposSenalado)) !!} {{ $nombre }}</span>@endforeach<span class="chk">{!! $x(in_array('OTHER', $tiposSenalado)) !!} Otro: {{ $otroTipo ?? '____________' }}</span>
            </div>
        </td>
    </tr>
</table>
<table>
    <tr><td colspan="5"><span class="label">22. Ubicación Geográfica del señalado(a) (Comuna y/o Consejo Comunal, Órgano, Ente, Dirección de habitación)</span><div class="val">{{ $case->respondents->first()?->location }}</div></td></tr>
</table>
<table>
    <tr>
        <td style="width:15%"><span class="label">23. Cédula de Identidad<br>(V/E):</span></td>
        <td style="width:25%"><span class="label">24. Apellidos y Nombres:</span></td>
        <td style="width:25%"><span class="label">25. Nombre de la Comuna o Consejo Comunal:</span></td>
        <td style="width:15%"><span class="label">26. Código SITUR:</span></td>
        <td style="width:20%"><span class="label">27. R.I.F.:</span></td>
    </tr>
    @forelse ($senalados as $s)
</table>
<table>
        <tr><td style="width:15%">{{ $s['cedula'] }}</td><td style="width:25%">{{ $s['nombre'] }}</td><td style="width:25%">{{ $s['comuna'] }}</td><td style="width:15%">{{ $s['situr'] }}</td><td style="width:20%">{{ $s['rif'] }}</td></tr>
    @empty
</table>
<table>
        <tr><td style="width:15%"></td><td style="width:25%"></td><td style="width:25%"></td><td style="width:15%"></td><td style="width:20%"></td></tr>
    @endforelse
</table>

{{-- CONSULTA POPULAR --}}
<table>
    <tr><td colspan="2" class="bg-blue">Proyecto de la Consulta Popular Nacional (de ser el caso)</td></tr>
</table>
<table>
    <tr>
        <td style="width:75%"><span class="label">28. Nombre del Proyecto:</span><div class="val">{{ $consulta?->project_name }}</div></td>
        <td style="width:25%"><span class="label">29. Fecha de aprobación</span><div class="val">{{ $consulta ? \Carbon\Carbon::parse($consulta->approval_date)->format('d/m/Y') : '' }}</div></td>
    </tr>
</table>
<table>
    <tr>
        <td style="width:75%"><span class="label">30. Monto estimado del Proyecto:</span><div class="val">{{ $consulta ? 'Bs. '.number_format($consulta->project_amount, 2, ',', '.') : '' }}</div></td>
        <td style="width:25%"><span class="label">31. Ente financiador:</span><div class="val">{{ $consulta?->funding_entity }}</div></td>
    </tr>
</table>

{{-- DESCRIPCIÓN Y SEÑALAMIENTO --}}
<table>
    <tr><td colspan="3" class="bg-blue">Descripción y señalamiento:</td></tr>
</table>
<table>
    <tr><td colspan="3"><span class="label">32. Narración circunstanciada de los hechos:</span><div class="val30" style="word-wrap:break-word">{!! nl2br(e($case->narrative)) !!}</div></td></tr>
</table>
<table>
    <tr>
        <td style="width:80%"><span class="label" style="margin:0">33. ¿Esta situación y/o problemática ha sido presentada ante otra instancia en fecha anterior o en la presente fecha?</span></td>
        <td class="center middle" style="width:20%; font-size:9px">{!! $chk($case->other_instance, 'SÍ') !!}{!! $chk(! $case->other_instance, 'NO') !!}</td>
    </tr>
</table>
<table>
    <tr><td colspan="3"><span class="label">34. Indique a cuál:</span><div class="val">{{ $case->other_instance_name }}</div></td></tr>
</table>
<table>
    <tr>
        <td style="width:60%"><span class="label">35. Documentos que anexa como prueba de los hechos:</span></td>
        <td style="width:40%; padding:0">
            <table class="nb" style="margin:0">
                @foreach ([['WITNESS_ID' => 'Copia de C.I. del testigo', 'COVER_LETTER' => 'Carta de exposición de motivo', 'PHOTOS' => 'Fotografías'],
                           ['CLAIMANT_ID' => 'Copia de C.I. del denunciante', 'VIDEO' => 'Video', 'AUDIO' => 'Grabación de voz']] as $fila)
                    <tr>
                        @foreach ($fila as $code => $nombre)
                            <td class="doc" style="width:33%; border-bottom:1px solid #444; {{ $loop->last ? '' : 'border-right:1px solid #444' }}">{!! $x(in_array($code, $evidencias)) !!}<div>{{ $nombre }}</div></td>
                        @endforeach
                    </tr>
                @endforeach
                <tr>
                    <td colspan="3" style="font-size:9px">{!! $x(in_array('WRITTEN_TESTIMONY', $evidencias) || in_array('OTHER', $evidencias)) !!} Otros:
                        @php($otros = collect(['WRITTEN_TESTIMONY' => 'Testimonio escrito', 'OTHER' => 'Otros documentos'])->only($evidencias)->join(', '))
                        {{ $otros !== '' ? $otros : '_________________________________________________' }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<table>
    <tr>
        <td style="width:60%; padding-right:15px"><span class="label" style="text-align:justify">36. Declaro que los datos suministrados son fidedignos, y estoy en conocimiento que cualquier falta o falsedad en los mismos, involucra sanciones o a la no aceptación de la solicitud</span></td>
        <td style="width:20%" class="center"><span class="label">37. Firma del interesado</span><div class="val30"></div></td>
        <td style="width:20%" class="center"><span class="label">38. Huella dactilar:</span><div class="val30"></div></td>
    </tr>
</table>
<table>
    <tr><td colspan="3" class="bg-blue">Descripción y señalamiento:</td></tr>
</table>
<table>
    <tr>
        <td style="width:80%"><span class="label">39. Descripción del trámite</span><div class="val30"></div></td>
        <td style="width:20%"><span class="label">40. Sello y firma del funcionario receptor:</span><div class="val30"></div></td>
    </tr>
</table>

{{-- FIRMAS INFERIORES: bloques 18/18/22/18 % repartidos con space-between --}}
<table class="sig" style="margin-top:20px">
    <tr>
        <td style="width:18%">Elaborado por:<div class="sig-line">&nbsp;</div></td>
        <td style="width:8%"></td>
        <td style="width:18%">Revisado por:<div class="sig-line">Abg. Esther Jiménez</div></td>
        <td style="width:8%"></td>
        <td style="width:22%">Aprobado y autorizado por:<div class="sig-line">Abg. Leonel O. Lucena H.</div></td>
        <td style="width:8%"></td>
        <td style="width:18%"><b>Resolución<br>Administrativa N°</b><div style="margin-top:10px; font-weight:bold">CMP-013-2024<br>05/03/2024</div></td>
    </tr>
</table>

</body>
</html>
