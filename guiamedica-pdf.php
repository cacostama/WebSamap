<?php
/**
 * guiamedica-pdf.php — Descarga de la Guía Médica en PDF (FPDF, liviano).
 * Acepta filtros opcionales por GET (esp, ciu, med) para que el PDF
 * refleje la búsqueda actual. Sin parámetros, exporta la guía completa.
 */
ob_start(); // captura cualquier salida accidental de los includes
require_once('funciones/db.php');
require_once('vendor/fpdf/fpdf.php');

mysqli_select_db($connect, $database);

// ---- Filtros opcionales (sanitizados a entero / string seguro) ----
$esp = isset($_GET['esp']) && $_GET['esp'] !== '' ? (int) $_GET['esp'] : null;
$ciu = isset($_GET['ciu']) && $_GET['ciu'] !== '' ? (int) $_GET['ciu'] : null;
$med = isset($_GET['med']) ? trim($_GET['med']) : '';

$where = [];
if ($esp !== null) { $where[] = "a.idEspecialidad = $esp"; }
if ($ciu !== null) { $where[] = "c.idCiudad = $ciu"; }
if ($med !== '') {
    $medEsc = mysqli_real_escape_string($connect, $med);
    $where[] = "a.nombre LIKE '%$medEsc%'";
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$query = "SELECT a.titulo, a.nombre AS medico,
                 b.nombre AS especialidad,
                 c.nombre AS sanatorio, c.direccion, c.telefono,
                 d.nombre AS ciudad
          FROM tbl_guiamedica a
          LEFT JOIN tbl_especialidad b ON a.idEspecialidad = b.id
          LEFT JOIN tbl_sanatorio  c ON a.idSanatorios   = c.id
          LEFT JOIN tbl_ciudad     d ON c.idCiudad        = d.id
          $whereSql
          ORDER BY b.nombre ASC,
                   CASE WHEN c.nombre LIKE '%Sanatorio Adventista%' THEN 0 ELSE 1 END,
                   c.nombre ASC";
$res = mysqli_query($connect, $query) or die('Error al generar la guía');

// ---- Helper: UTF-8 -> Latin1 para las fuentes core de FPDF ----
function t($s) {
    $s = (string) $s;
    return iconv('UTF-8', 'windows-1252//TRANSLIT', $s);
}

class GuiaPDF extends FPDF
{
    public $azul = [39, 71, 103];   // #274767
    public $verde = [108, 163, 171]; // #6CA3AB

    function Header()
    {
        $logo = __DIR__ . '/assets/images/logo.png';
        if (is_file($logo)) {
            $this->Image($logo, 12, 9, 38);
        }
        $this->SetY(11);
        $this->SetX(54);
        $this->SetTextColor($this->azul[0], $this->azul[1], $this->azul[2]);
        $this->SetFont('Helvetica', 'B', 17);
        $this->Cell(0, 9, t('Guía Médica'), 0, 2, 'L');
        $this->SetFont('Helvetica', '', 9);
        $this->SetTextColor(110, 110, 110);
        $this->Cell(0, 6, t('SAMAP — Medicina Prepaga · Sanatorio Adventista de Asunción'), 0, 1, 'L');
        // línea separadora
        $this->SetDrawColor($this->verde[0], $this->verde[1], $this->verde[2]);
        $this->SetLineWidth(0.6);
        $this->Line(12, 28, 198, 28);
        $this->Ln(8);
    }

    function Footer()
    {
        $this->SetY(-14);
        $this->SetFont('Helvetica', 'I', 8);
        $this->SetTextColor(140, 140, 140);
        $fecha = date('d/m/Y');
        $this->Cell(0, 8, t("Generado el $fecha"), 0, 0, 'L');
        $this->Cell(0, 8, t('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'R');
    }

    // Encabezado de grupo por especialidad
    function Especialidad($nombre)
    {
        if ($this->GetY() > 250) { $this->AddPage(); }
        $this->SetFillColor($this->azul[0], $this->azul[1], $this->azul[2]);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Helvetica', 'B', 11);
        $this->Cell(0, 8, ' ' . t($nombre), 0, 1, 'L', true);
        $this->Ln(1.5);
    }

    // Una ficha de médico
    function Medico($titulo, $sanatorio, $direccion, $telefono)
    {
        // salto de página si no entra
        if ($this->GetY() > 268) { $this->AddPage(); }
        $this->SetTextColor(39, 66, 102);
        $this->SetFont('Helvetica', 'B', 10.5);
        $this->MultiCell(0, 5.5, t($titulo), 0, 'L');
        $this->SetTextColor(60, 60, 60);
        $this->SetFont('Helvetica', '', 9);
        $linea = $sanatorio;
        if ($direccion) { $linea .= '  ·  ' . $direccion; }
        if ($telefono)  { $linea .= '  ·  Tel: ' . $telefono; }
        $this->MultiCell(0, 5, t($linea), 0, 'L');
        $this->Ln(2.5);
    }
}

$pdf = new GuiaPDF('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 16);
$pdf->SetMargins(12, 10, 12);
$pdf->AddPage();

$total = mysqli_num_rows($res);
if ($total == 0) {
    $pdf->SetFont('Helvetica', '', 11);
    $pdf->SetTextColor(90, 90, 90);
    $pdf->MultiCell(0, 7, t('No se encontraron resultados para los filtros seleccionados.'), 0, 'L');
} else {
    $espActual = null;
    while ($row = mysqli_fetch_assoc($res)) {
        $espNombre = $row['especialidad'] ?: 'Sin especialidad';
        if ($espNombre !== $espActual) {
            $espActual = $espNombre;
            $pdf->Especialidad($espActual);
        }
        $titulo = trim(($row['titulo'] ?? '') . ' ' . ($row['medico'] ?? ''));
        $pdf->Medico(
            $titulo,
            $row['sanatorio'] ?? '',
            $row['direccion'] ?? '',
            $row['telefono'] ?? ''
        );
    }
}

ob_end_clean(); // descarta salida accidental antes de enviar el PDF
$pdf->Output('I', 'guia-medica-samap.pdf'); // 'I' = inline (el navegador permite descargar)
exit;
