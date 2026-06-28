<?php
// ============================================================================
// Partial: upload-imagen.php
// Reusable file-input + preview para los formularios de carga del admin.
//
// Variables esperadas (definidas en el padre ANTES del include):
//   $upload_campo       string  default "imagen"     -> name del input
//   $upload_label       string  default "Imagen"     -> texto del <label>
//   $upload_subcarpeta  string  default ""           -> subdir dentro de documentos/
//   $upload_actual      string  default ""           -> filename existente (modo edicion)
//   $upload_ruta        string  REQUERIDO            -> path server-side (ej: $rutaSlider)
//   $upload_medida      string  default "Mantener proporcion. JPG/PNG/WEBP, max 5 MB."
//   $upload_requerido   bool    default false
//   $upload_multiple    bool    default false
//   $upload_accept      string  default "image/jpeg,image/png,image/webp"
//   $upload_label_col   string  default "col-sm-2"  -> clase del <label>
//   $upload_input_col   string  default "col-sm-6"  -> clase del contenedor del input
//   $URL                string  REQUERIDO            -> base URL del sitio (provista por db.php)
// ============================================================================

if (!isset($URL) || !is_string($URL)) {
    // Fallback defensivo: si el partial se incluye sin db.php, no rompemos el render.
    $URL = '';
}

$upload_campo      = isset($upload_campo)     ? (string)$upload_campo     : 'imagen';
$upload_label      = isset($upload_label)     ? (string)$upload_label     : 'Imagen';
$upload_subcarpeta = isset($upload_subcarpeta) ? (string)$upload_subcarpeta : '';
$upload_actual     = isset($upload_actual)    ? (string)$upload_actual    : '';
$upload_ruta       = isset($upload_ruta)      ? (string)$upload_ruta      : '';
$upload_medida     = isset($upload_medida)    ? (string)$upload_medida    : 'Mantener proporcion. JPG/PNG/WEBP, max 5 MB.';
$upload_requerido  = !empty($upload_requerido);
$upload_multiple   = !empty($upload_multiple);
$upload_accept     = isset($upload_accept)    ? (string)$upload_accept    : 'image/jpeg,image/png,image/webp';
$upload_label_col  = isset($upload_label_col) ? (string)$upload_label_col : 'col-sm-2';
$upload_input_col  = isset($upload_input_col) ? (string)$upload_input_col : 'col-sm-6';

// Escapamos para HTML
$e_campo     = htmlspecialchars($upload_campo, ENT_QUOTES, 'UTF-8');
$e_label     = htmlspecialchars($upload_label, ENT_QUOTES, 'UTF-8');
$e_actual    = htmlspecialchars($upload_actual, ENT_QUOTES, 'UTF-8');
$e_subcarp   = htmlspecialchars($upload_subcarpeta, ENT_QUOTES, 'UTF-8');
$e_acepta    = htmlspecialchars($upload_accept, ENT_QUOTES, 'UTF-8');
$e_medida    = htmlspecialchars($upload_medida, ENT_QUOTES, 'UTF-8');
$e_label_col = htmlspecialchars($upload_label_col, ENT_QUOTES, 'UTF-8');
$e_input_col = htmlspecialchars($upload_input_col, ENT_QUOTES, 'UTF-8');

// URL del thumbnail cuando estamos editando.
// Si $upload_subcarpeta esta vacio, el archivo vive directamente en /documentos/ (planes, convenios).
$carpeta_url = $upload_subcarpeta !== '' ? 'documentos/' . $e_subcarp . '/' : 'documentos/';

$requerido_attr = $upload_requerido ? ' required' : '';
$multiple_attr  = $upload_multiple  ? ' multiple'  : '';
?>
<div class="form-group">
    <label class="<?php echo $e_label_col; ?> control-label"><?php echo $e_label; ?></label>
    <div class="<?php echo $e_input_col; ?>">
        <?php if ($upload_actual !== ''): ?>
            <div style="margin-bottom:10px;">
                <img src="<?php echo $URL . $carpeta_url . $e_actual; ?>" style="max-width:300px; max-height:200px;" alt="Imagen actual" />
                <div style="font-size:11px;color:#888;">Imagen actual</div>
            </div>
        <?php endif; ?>

        <input type="file" name="<?php echo $e_campo; ?><?php echo $upload_multiple ? '[]' : ''; ?>" id="upload-<?php echo $e_campo; ?>" accept="<?php echo $e_acepta; ?>"<?php echo $multiple_attr . $requerido_attr; ?> data-classbutton="btn btn-default" data-classinput="form-control inline" class="filestyle form-control" />
        <span style="font-size:11px;color:#888;display:block;margin-top:5px;"><?php echo $e_medida; ?></span>

        <div id="upload-preview-<?php echo $e_campo; ?>" style="margin-top:10px;"></div>

        <script>
        (function() {
            var input = document.getElementById('upload-<?php echo $e_campo; ?>');
            var preview = document.getElementById('upload-preview-<?php echo $e_campo; ?>');
            if (!input || !preview) return;

            input.addEventListener('change', function() {
                preview.innerHTML = '';
                var files = Array.from(input.files);
                if (!files.length) return;

                var maxSize = 5 * 1024 * 1024;
                var allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

                files.forEach(function(file) {
                    var sizeKB = Math.round(file.size / 1024);
                    var sizeMB = (file.size / 1024 / 1024).toFixed(2);

                    if (file.size > maxSize) {
                        var warn = document.createElement('div');
                        warn.style.cssText = 'background:#f6504d;color:#fff;padding:8px;margin-bottom:8px;border-radius:4px;';
                        warn.innerHTML = '<strong>' + file.name + '</strong> (' + sizeMB + ' MB) — excede el limite de 5 MB';
                        preview.appendChild(warn);
                        return;
                    }
                    if (allowedTypes.indexOf(file.type) === -1) {
                        var warn = document.createElement('div');
                        warn.style.cssText = 'background:#ffc61d;color:#333;padding:8px;margin-bottom:8px;border-radius:4px;';
                        warn.innerHTML = '<strong>' + file.name + '</strong> — formato no permitido (solo JPG, PNG, WEBP)';
                        preview.appendChild(warn);
                        return;
                    }

                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var wrap = document.createElement('div');
                        wrap.style.cssText = 'display:inline-block;margin:5px;border:1px solid #eee;padding:5px;border-radius:4px;';
                        var img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.cssText = 'max-width:200px;max-height:200px;display:block;';
                        var lbl = document.createElement('div');
                        lbl.style.cssText = 'font-size:11px;color:#888;margin-top:4px;';
                        lbl.textContent = file.name + ' (' + sizeKB + ' KB)';
                        wrap.appendChild(img);
                        wrap.appendChild(lbl);
                        preview.appendChild(wrap);
                    };
                    reader.readAsDataURL(file);
                });
            });
        })();
        </script>
    </div>
</div>
