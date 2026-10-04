{{-- Planilla de Recepción (Res. CMP-013-2024) llenada con los datos del expediente.
     Copia de cgp/pages/comprobante.html adaptada a dompdf: solo tablas, sin flexbox. --}}
@php
    $x = fn (bool $marcado) => '<span class="box">'.($marcado ? 'X' : '&nbsp;').'</span>';
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
    @page { margin: 10mm 12mm; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 8.5px; color: #000; }
    table { width: 100%; border-collapse: collapse; margin-bottom: -1px; }
    td { border: 1px solid #444; padding: 2px 4px; vertical-align: top; }
    td.nb, table.nb td { border: none; }
    .bg-blue { background: #a9d0f5; text-align: center; font-weight: bold; text-transform: uppercase; font-size: 8px; }
    .label { font-weight: bold; font-size: 7.5px; color: #222; display: block; }
    .val { font-size: 9px; }
    .box { display: inline-block; width: 9px; height: 9px; line-height: 9px; border: 1px solid #444;
           text-align: center; font-size: 8px; font-weight: bold; margin-right: 2px; }
    .chk { margin-right: 10px; white-space: nowrap; }
    .center { text-align: center; }
    .sig td { border: none; text-align: center; vertical-align: bottom; height: 38px; font-size: 8px; }
    .sig-line { border-top: 1px solid #000; padding-top: 2px; }
</style>
</head>
<body>

<table class="nb">
    <tr>
        <td><img src="{{ public_path('assets/img/logoletrasnegras.png') }}" style="width:55px"></td>
        <td style="text-align:right"><img src="{{ public_path('assets/img/IMG-20241209-WA0052-removebg-preview.png') }}" style="width:80px"></td>
    </tr>
</table>

<table>
    <tr>
        <td style="width:50%" class="center">
            <img src="{{ public_path('assets/img/logosinfondo.png') }}" style="width:34px; float:left">
            Contraloría del Municipio Páez<br>Del Estado Portuguesa<br>
            <span style="font-size:8px">Dirección de Atención al Ciudadano</span><br>
            <b style="font-size:10px">PLANILLA DE RECEPCIÓN</b>
        </td>
        <td style="width:35%; padding:0">
            <table class="nb">
                <tr><td colspan="3" style="border-bottom:1px solid #444"><span class="label">2. Fecha de solicitud:</span></td></tr>
                <tr>
                    <td class="center" style="border-right:1px solid #444">Día<br><b>{{ $fecha->format('d') }}</b></td>
                    <td class="center" style="border-right:1px solid #444">Mes<br><b>{{ $fecha->format('m') }}</b></td>
                    <td class="center">Año<br><b>{{ $fecha->format('Y') }}</b></td>
                </tr>
            </table>
        </td>
        <td style="width:15%"><span class="label">3. Hora:</span><div class="val">{{ $fecha->format('h:i a') }}</div></td>
    </tr>
</table>
<table>
    <tr>
        <td style="width:50%">
            <span class="label">1. Tipo de Recepción:</span>
            <span class="chk">{!! $x($tipo === 'COMPLAINT') !!} Denuncia</span>
            <span class="chk">{!! $x($tipo === 'GRIEVANCE') !!} Queja</span>
            <span class="chk">{!! $x($tipo === 'CLAIM') !!} Reclamo</span>
            <span class="chk">{!! $x($tipo === 'PETITION') !!} Petición</span>
            @if ($tipo === 'SUGGESTION')<span class="chk">{!! $x(true) !!} Sugerencia</span>@endif
        </td>
        <td style="width:50%"><span class="label">4. Nro. Expediente / Registro:</span><div class="val"><b>{{ $case->case_number }}</b></div></td>
    </tr>
</table>

{{-- DATOS DEL CIUDADANO --}}
<table>
    <tr><td colspan="3" class="bg-blue">Datos del ciudadano (solicitante)</td></tr>
    <tr>
        <td style="width:40%">
            <span class="label">5. Cédula de Identidad/Pasaporte:</span>
            <span class="chk">{!! $x($docTipo === 'V') !!} V</span>
            <span class="chk">{!! $x($docTipo === 'E') !!} E</span>
            @if ($docTipo === 'PASSPORT')<span class="chk">{!! $x(true) !!} Pasaporte</span>@endif
            <b>{{ $c->id_doc_number }}</b>
        </td>
        <td colspan="2"><span class="label">6. Apellidos y Nombres:</span><div class="val">{{ $c->last_name }}, {{ $c->first_name }}</div></td>
    </tr>
    <tr>
        <td>
            <span class="label">7. Sexo:</span>
            <span class="chk">{!! $x($c->sex === 'M') !!} M</span>
            <span class="chk">{!! $x($c->sex === 'F') !!} F</span>
        </td>
        <td style="width:15%"><span class="label">8. Edad:</span><div class="val">{{ $c->birth_date?->age }}</div></td>
        <td>
            <span class="label">9. Estado Civil:</span>
            @foreach (['Soltero(a)', 'Casado(a)', 'Divorciado(a)', 'Viudo(a)'] as $ec)
                <span class="chk">{!! $x(($cd['marital_status'] ?? '') === $ec) !!} {{ $ec }}</span>
            @endforeach
            @if (($cd['marital_status'] ?? '') === 'Unión estable')<span class="chk">{!! $x(true) !!} Unión estable</span>@endif
        </td>
    </tr>
    <tr>
        <td colspan="2"><span class="label">10. Lugar y fecha de nacimiento:</span><div class="val">{{ collect([$cd['birth_place'] ?? null, $c->birth_date?->format('d/m/Y')])->filter()->join(', ') }}</div></td>
        <td style="padding:0">
            <table class="nb"><tr>
                <td style="width:50%; border-right:1px solid #444"><span class="label">11. Nivel Educativo:</span><div class="val">{{ $cd['education_level'] ?? '' }}</div></td>
                <td><span class="label">12. Profesión y/u ocupación:</span><div class="val">{{ collect([$cd['profession'] ?? null, $cd['occupation'] ?? null])->filter()->join(' / ') }}</div></td>
            </tr></table>
        </td>
    </tr>
    <tr><td colspan="3"><span class="label">13. Dirección de habitación:</span><div class="val">{{ $c->address }}</div></td></tr>
    <tr>
        <td><span class="label">14. Parroquia:</span><div class="val">{{ $c->parish }}</div></td>
        <td><span class="label">15. Municipio:</span><div class="val">{{ $c->municipality }}</div></td>
        <td><span class="label">16. Ciudad:</span><div class="val">{{ $c->city }}</div></td>
    </tr>
    <tr>
        <td><span class="label">17. Correo Electrónico:</span><div class="val">{{ $c->email }}</div></td>
        <td><span class="label">18. Telf. Hab.:</span><div class="val">{{ $cd['home_phone'] ?? '' }}</div></td>
        <td style="padding:0">
            <table class="nb"><tr>
                <td style="width:50%; border-right:1px solid #444"><span class="label">19. Telf. Celular:</span><div class="val">{{ $c->mobile_phone }}</div></td>
                <td><span class="label">20. Telf. Trabajo:</span><div class="val">{{ $cd['work_phone'] ?? '' }}</div></td>
            </tr></table>
        </td>
    </tr>
</table>

{{-- SEÑALADO(S) --}}
<table>
    <tr><td colspan="5" class="bg-blue">Datos de identificación y/o ubicación del señalado(s) según sea el caso</td></tr>
    <tr>
        <td colspan="5">
            <span class="label">21. Tipo del señalado(a):</span>
            @foreach (['NATURAL_PERSON' => 'Persona Natural', 'LEGAL_ENTITY' => 'Persona Jurídica', 'GOV_AGENCY' => 'Órgano o Ente',
                       'COMMUNE' => 'Comuna', 'COMMUNAL_COUNCIL' => 'Consejo Comunal', 'JUSTICE_OF_PEACE' => 'Juez de Paz'] as $code => $nombre)
                <span class="chk">{!! $x(in_array($code, $tiposSenalado)) !!} {{ $nombre }}</span>
            @endforeach
            <span class="chk">{!! $x(in_array('OTHER', $tiposSenalado)) !!} Otro: {{ $otroTipo ?? '____________' }}</span>
        </td>
    </tr>
    <tr><td colspan="5"><span class="label">22. Ubicación Geográfica del señalado(a) (Comuna y/o Consejo Comunal, Órgano, Ente, Dirección de habitación)</span><div class="val">{{ $case->respondents->first()?->location }}</div></td></tr>
    <tr>
        <td style="width:15%"><span class="label">23. Cédula de Identidad (V/E):</span></td>
        <td style="width:25%"><span class="label">24. Apellidos y Nombres:</span></td>
        <td style="width:25%"><span class="label">25. Nombre de la Comuna o Consejo Comunal:</span></td>
        <td style="width:15%"><span class="label">26. Código SITUR:</span></td>
        <td style="width:20%"><span class="label">27. R.I.F.:</span></td>
    </tr>
    @forelse ($senalados as $s)
        <tr class="val">
            <td>{{ $s['cedula'] }}</td><td>{{ $s['nombre'] }}</td><td>{{ $s['comuna'] }}</td><td>{{ $s['situr'] }}</td><td>{{ $s['rif'] }}</td>
        </tr>
    @empty
        <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td></tr>
    @endforelse
</table>

{{-- CONSULTA POPULAR --}}
<table>
    <tr><td colspan="2" class="bg-blue">Proyecto de la Consulta Popular Nacional (de ser el caso)</td></tr>
    <tr>
        <td style="width:75%"><span class="label">28. Nombre del Proyecto:</span><div class="val">{{ $consulta?->project_name }}</div></td>
        <td><span class="label">29. Fecha de aprobación</span><div class="val">{{ $consulta ? \Carbon\Carbon::parse($consulta->approval_date)->format('d/m/Y') : '' }}</div></td>
    </tr>
    <tr>
        <td><span class="label">30. Monto estimado del Proyecto:</span><div class="val">{{ $consulta ? 'Bs. '.number_format($consulta->project_amount, 2, ',', '.') : '' }}</div></td>
        <td><span class="label">31. Ente financiador:</span><div class="val">{{ $consulta?->funding_entity }}</div></td>
    </tr>
</table>

{{-- DESCRIPCIÓN Y SEÑALAMIENTO --}}
<table>
    <tr><td colspan="3" class="bg-blue">Descripción y señalamiento:</td></tr>
    <tr><td colspan="3"><span class="label">32. Narración circunstanciada de los hechos:</span><div class="val" style="word-wrap:break-word">{!! nl2br(e($case->narrative)) !!}</div></td></tr>
    <tr>
        <td colspan="2" style="width:85%"><span class="label">33. ¿Esta situación y/o problemática ha sido presentada ante otra instancia en fecha anterior o en la presente fecha?</span></td>
        <td class="center">
            <span class="chk">{!! $x($case->other_instance) !!} SÍ</span>
            <span class="chk">{!! $x(! $case->other_instance) !!} NO</span>
        </td>
    </tr>
    <tr><td colspan="3"><span class="label">34. Indique a cuál:</span><div class="val">{{ $case->other_instance_name }}</div></td></tr>
    <tr>
        <td style="width:30%"><span class="label">35. Documentos que anexa como prueba de los hechos:</span></td>
        <td colspan="2" style="padding:0">
            <table class="nb">
                <tr>
                    <td>{!! $x(in_array('WITNESS_ID', $evidencias)) !!} Copia de C.I. del testigo</td>
                    <td>{!! $x(in_array('COVER_LETTER', $evidencias)) !!} Carta de exposición de motivo</td>
                    <td>{!! $x(in_array('PHOTOS', $evidencias)) !!} Fotografías</td>
                </tr>
                <tr>
                    <td>{!! $x(in_array('CLAIMANT_ID', $evidencias)) !!} Copia de C.I. del denunciante</td>
                    <td>{!! $x(in_array('VIDEO', $evidencias)) !!} Video</td>
                    <td>{!! $x(in_array('AUDIO', $evidencias)) !!} Grabación de voz</td>
                </tr>
                <tr>
                    <td colspan="3">{!! $x(in_array('WRITTEN_TESTIMONY', $evidencias) || in_array('OTHER', $evidencias)) !!} Otros:
                        {{ collect(['WRITTEN_TESTIMONY' => 'Testimonio escrito', 'OTHER' => 'Otros documentos'])->only($evidencias)->join(', ') }}</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="width:60%; text-align:justify"><span class="label">36. Declaro que los datos suministrados son fidedignos, y estoy en conocimiento que cualquier falta o falsedad en los mismos, involucra sanciones o a la no aceptación de la solicitud</span></td>
        <td class="center" style="width:20%; height:40px"><span class="label">37. Firma del interesado</span></td>
        <td class="center" style="width:20%"><span class="label">38. Huella dactilar:</span></td>
    </tr>
    <tr><td colspan="3" class="bg-blue">Descripción y señalamiento:</td></tr>
    <tr>
        <td colspan="2" style="height:40px"><span class="label">39. Descripción del trámite</span></td>
        <td><span class="label">40. Sello y firma del funcionario receptor:</span></td>
    </tr>
</table>

<table class="sig" style="margin-top:4px">
    <tr>
        <td style="width:20%">Elaborado por:<div class="sig-line">&nbsp;</div></td>
        <td style="width:20%">Revisado por:<div class="sig-line">Abg. Esther Jiménez</div></td>
        <td style="width:24%">Aprobado y autorizado por:<div class="sig-line">Abg. Leonel O. Lucena H.</div></td>
        <td style="width:20%"><b>Resolución<br>Administrativa N°<br>CMP-013-2024<br>05/03/2024</b></td>
    </tr>
</table>

</body>
</html>
