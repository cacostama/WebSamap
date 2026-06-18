<?php
/**
 * Genera el PDF de presupuesto (horas humanas) para los trabajos del sitio SAMAP.
 * Uso: php scripts/presupuesto-gen.php  -> escribe presupuesto-samap.pdf en la raiz.
 */
require_once(__DIR__ . '/../vendor/fpdf/fpdf.php');

function t($s) { return iconv('UTF-8', 'windows-1252//TRANSLIT', (string)$s); }
function gs($n) { return 'Gs. ' . number_format($n, 0, ',', '.'); }

$RATE = 250000;

// Items: [concepto, detalle, hMin, hMax]
$items = [
    ['Rediseno del sitio publico', 'Maquetacion responsive de Inicio, Planes, Beneficios/Convenios, Nosotros, Guia Medica y Contacto con sistema de estilos propio y paleta de marca.', 5.0, 5.5],
    ['Optimizacion UX movil', 'Correccion de corte horizontal, menu/acordeon movil, seccion de descuentos y procesado del logo de aliado.', 2.0, 2.5],
    ['Descarga de Guia Medica en PDF', 'Generador liviano (FPDF) con filtros, agrupado por especialidad y cache-busting de estilos.', 1.5, 2.0],
    ['Seguridad', 'Credenciales fuera del codigo (variables de entorno) y formulario de contacto blindado: anti-spoofing, anti header-injection, validacion, honeypot y consentimiento.', 2.0, 2.0],
    ['SEO base', 'Metadatos unicos por pagina, Open Graph, Twitter Card y datos estructurados (JSON-LD MedicalOrganization).', 1.5, 2.0],
    ['Rendimiento', 'Carga diferida de imagenes (lazy-load), prioridad de imagen principal y limpieza de librerias JS sin uso.', 1.0, 1.0],
    ['Limpieza y cumplimiento legal', 'Eliminacion de archivos muertos, pagina de Politica de Privacidad (Ley 6534/20) y correcciones de accesibilidad.', 1.5, 1.5],
    ['Implementacion y monitoreo (1 semana)', 'Despliegue de los cambios y monitoreo/soporte durante 1 semana ante eventuales errores.', 1.5, 1.5],
];

$sumMin = 0; $sumMax = 0;
foreach ($items as $it) { $sumMin += $it[2]; $sumMax += $it[3]; }

$azul  = [39, 71, 103];
$verde = [108, 163, 171];
$gris  = [90, 90, 90];

$pdf = new FPDF('P', 'mm', 'A4');
$pdf->SetAutoPageBreak(true, 18);
$pdf->AddPage();
$pdf->SetMargins(15, 14, 15);

// ---- Encabezado ----
$pdf->SetXY(15, 14);
$pdf->SetTextColor($azul[0], $azul[1], $azul[2]);
$pdf->SetFont('Helvetica', 'B', 20);
$pdf->Cell(0, 9, t('Presupuesto'), 0, 2, 'L');
$pdf->SetFont('Helvetica', '', 10);
$pdf->SetTextColor($gris[0], $gris[1], $gris[2]);
$pdf->Cell(0, 6, t('Desarrollo y mejoras del sitio web — SAMAP Medicina Prepaga'), 0, 1, 'L');

$pdf->SetDrawColor($verde[0], $verde[1], $verde[2]);
$pdf->SetLineWidth(0.6);
$pdf->Line(15, 34, 195, 34);
$pdf->Ln(14);

// ---- Datos del presupuesto ----
$pdf->SetFont('Helvetica', '', 9.5);
$pdf->SetTextColor(70, 70, 70);
$pdf->Cell(0, 5.5, t('Fecha: ' . date('d/m/Y')), 0, 1, 'L');
$pdf->Cell(0, 5.5, t('Tarifa aplicada: ' . gs($RATE) . ' por hora de trabajo'), 0, 1, 'L');
$pdf->Cell(0, 5.5, t('Modalidad: estimacion por rango de horas (minimo / maximo)'), 0, 1, 'L');
$pdf->Ln(4);

// ---- Tabla de items ----
// Encabezado
$pdf->SetFont('Helvetica', 'B', 9.5);
$pdf->SetFillColor($azul[0], $azul[1], $azul[2]);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(110, 8, t(' Concepto'), 0, 0, 'L', true);
$pdf->Cell(22, 8, t('H. min'), 0, 0, 'C', true);
$pdf->Cell(22, 8, t('H. max'), 0, 0, 'C', true);
$pdf->Cell(26, 8, t('Subtotal'), 0, 1, 'C', true);

$pdf->SetFont('Helvetica', '', 8.5);
$fill = false;
foreach ($items as $it) {
    list($concepto, $detalle, $hMin, $hMax) = $it;
    $yStart = $pdf->GetY();

    // Columna concepto + detalle (multilinea) en celda izquierda
    $pdf->SetFillColor(245, 248, 250);
    $xStart = $pdf->GetX();
    $pdf->SetTextColor(40, 60, 90);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->MultiCell(110, 5, t(' ' . $concepto), 0, 'L', $fill);
    $pdf->SetTextColor(95, 95, 95);
    $pdf->SetFont('Helvetica', '', 7.8);
    $pdf->SetX($xStart);
    $pdf->MultiCell(110, 4.2, t('  ' . $detalle), 0, 'L', $fill);
    $yEnd = $pdf->GetY();
    $rowH = $yEnd - $yStart;

    // Celdas numericas alineadas a la altura de la fila
    $pdf->SetXY($xStart + 110, $yStart);
    $pdf->SetTextColor(60, 60, 60);
    $pdf->SetFont('Helvetica', '', 9);
    $pdf->Cell(22, $rowH, t(rtrim(rtrim(number_format($hMin,1,',','.'),'0'),',')), 0, 0, 'C', $fill);
    $pdf->Cell(22, $rowH, t(rtrim(rtrim(number_format($hMax,1,',','.'),'0'),',')), 0, 0, 'C', $fill);
    $pdf->SetFont('Helvetica', '', 8);
    $pdf->Cell(26, $rowH, t(gs($hMax * 250000)), 0, 1, 'C', $fill);

    // linea separadora
    $pdf->SetDrawColor(225, 230, 234);
    $pdf->SetLineWidth(0.2);
    $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
    $fill = !$fill;
}

// ---- Totales ----
$pdf->Ln(3);
$totalMin = $sumMin * $RATE;
$totalMax = $sumMax * $RATE;

$pdf->SetFont('Helvetica', 'B', 10);
$pdf->SetTextColor($azul[0], $azul[1], $azul[2]);
$pdf->Cell(110, 7, t('TOTAL DE HORAS ESTIMADAS'), 0, 0, 'R');
$pdf->Cell(22, 7, t((int)$sumMin), 0, 0, 'C');
$pdf->Cell(22, 7, t((int)$sumMax), 0, 0, 'C');
$pdf->Cell(26, 7, '', 0, 1, 'C');

// Caja de montos
$pdf->Ln(2);
$pdf->SetFillColor($verde[0], $verde[1], $verde[2]);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Helvetica', 'B', 11);
$pdf->Cell(90, 11, t('  Presupuesto MINIMO (' . (int)$sumMin . ' h)'), 0, 0, 'L', true);
$pdf->Cell(90, 11, t(gs($totalMin) . '  '), 0, 1, 'R', true);
$pdf->SetFillColor($azul[0], $azul[1], $azul[2]);
$pdf->Cell(90, 11, t('  Presupuesto MAXIMO (' . (int)$sumMax . ' h)'), 0, 0, 'L', true);
$pdf->Cell(90, 11, t(gs($totalMax) . '  '), 0, 1, 'R', true);

// ---- Alcance / notas ----
$pdf->Ln(7);
$pdf->SetTextColor($azul[0], $azul[1], $azul[2]);
$pdf->SetFont('Helvetica', 'B', 10.5);
$pdf->Cell(0, 6, t('Alcance incluido'), 0, 1, 'L');
$pdf->SetTextColor(70, 70, 70);
$pdf->SetFont('Helvetica', '', 8.8);
$notas = [
    'Todos los trabajos detallados arriba, ya desarrollados y verificados.',
    'Implementacion / despliegue de los cambios en el entorno productivo.',
    'Monitoreo y soporte durante 1 (una) semana posterior a la implementacion ante eventuales errores derivados de estos cambios.',
    'Tarifa: ' . gs($RATE) . ' por hora. El monto final se factura segun las horas efectivamente requeridas, dentro del rango minimo-maximo indicado.',
];
foreach ($notas as $n) {
    $pdf->Cell(4, 5, t('-'), 0, 0, 'L');
    $pdf->MultiCell(0, 5, t($n), 0, 'L');
}

$pdf->Ln(4);
$pdf->SetFont('Helvetica', 'I', 8);
$pdf->SetTextColor(130, 130, 130);
$pdf->MultiCell(0, 4.5, t('Presupuesto valido por 15 dias desde la fecha de emision. No incluye costos de hosting, dominio ni servicios de terceros (correo SMTP, certificados).'), 0, 'L');

$pdf->Output('F', __DIR__ . '/../presupuesto-samap.pdf');
echo "OK presupuesto-samap.pdf (min={$sumMin}h max={$sumMax}h)\n";
