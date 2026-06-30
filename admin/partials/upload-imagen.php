<?php
// ============================================================================
// Partial: upload-imagen.php
// Reusable file-input + preview para los formularios de carga del admin.
// Opcionalmente integra Cropper.js (Feature 14) para recortar la imagen al
// aspect ratio recomendado antes de subirla al servidor.
//
// Variables esperadas (definidas en el padre ANTES del include):
//   $upload_campo       string  default "imagen"     -> name del input
//   $upload_label       string  default "Imagen"     -> texto del <label>
//   $upload_subcarpeta  string  default ""           -> subdir dentro de documentos/
//   $upload_actual      string  default ""           -> filename existente (modo edicion)
//   $upload_ruta        string  REQUERIDO            -> path server-side (ej: $rutaSlider)
//   $upload_medida      string  default "Mantener proporcion. JPG/PNG/WEBP, max 5 MB."
//                               Si tiene patron "W x H px" (ej. "850 × 500 px"),
//                               se parsea el aspect ratio (W/H) para Cropper.
//   $upload_requerido   bool    default false
//   $upload_multiple    bool    default false       (Cropper NO soporta multiple)
//   $upload_accept      string  default "image/jpeg,image/png,image/webp"
//   $upload_label_col   string  default "col-sm-2"  -> clase del <label>
//   $upload_input_col   string  default "col-sm-6"  -> clase del contenedor del input
//   $upload_cropper     bool    default true        -> si false, desactiva Cropper
//                                                       (util para forms que no quieran crop).
//   $URL                string  REQUERIDO            -> base URL del sitio (provista por db.php)
// ============================================================================

if (!isset($URL) || !is_string($URL)) {
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
$upload_cropper    = !isset($upload_cropper) ? true : !empty($upload_cropper);

// Cropper solo tiene sentido para 1 archivo a la vez.
$use_cropper = $upload_cropper && !$upload_multiple;

$e_campo     = htmlspecialchars($upload_campo, ENT_QUOTES, 'UTF-8');
$e_label     = htmlspecialchars($upload_label, ENT_QUOTES, 'UTF-8');
$e_actual    = htmlspecialchars($upload_actual, ENT_QUOTES, 'UTF-8');
$e_subcarp   = htmlspecialchars($upload_subcarpeta, ENT_QUOTES, 'UTF-8');
$e_acepta    = htmlspecialchars($upload_accept, ENT_QUOTES, 'UTF-8');
$e_medida    = htmlspecialchars($upload_medida, ENT_QUOTES, 'UTF-8');
$e_label_col = htmlspecialchars($upload_label_col, ENT_QUOTES, 'UTF-8');
$e_input_col = htmlspecialchars($upload_input_col, ENT_QUOTES, 'UTF-8');

$carpeta_url = $upload_subcarpeta !== '' ? 'documentos/' . $e_subcarp . '/' : 'documentos/';

$requerido_attr = $upload_requerido ? ' required' : '';
$multiple_attr  = $upload_multiple  ? ' multiple'  : '';

// Parsea el aspect ratio del campo $upload_medida cuando tiene formato "W x H".
// Ej. "850 × 500 px" / "850x500" / "838 × 960 px" -> 850/500 (1.7) o 838/960 (0.872).
$aspect_ratio = 0.0; // 0 = libre (sin forzar proporcion)
if (preg_match('/(\d{2,4})\s*[x×]\s*(\d{2,4})/iu', $upload_medida, $m)) {
    $w = (int)$m[1];
    $h = (int)$m[2];
    if ($w > 0 && $h > 0) {
        $aspect_ratio = round($w / $h, 4);
    }
}
$e_aspect = json_encode((float)$aspect_ratio);
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

        <link rel="stylesheet" href="<?php echo $URL; ?>admin/plugins/cropperjs/cropper.min.css" />

        <script>
        (function() {
            var input   = document.getElementById('upload-<?php echo $e_campo; ?>');
            var preview = document.getElementById('upload-preview-<?php echo $e_campo; ?>');
            if (!input || !preview) return;
            var useCropper = <?= $use_cropper ? 'true' : 'false' ?>;
            var aspect = <?= $e_aspect ?>;
            var baseUrl = <?= json_encode($URL, JSON_UNESCAPED_UNICODE) ?>;

            // Carga Cropper.js on-demand (mismo patron que Sortable en el partial de tablas)
            function loadCropper(cb) {
                if (typeof window.Cropper !== 'undefined') { cb(); return; }
                var s = document.createElement('script');
                s.src = baseUrl + 'admin/plugins/cropperjs/cropper.min.js';
                s.async = false;
                s.onload = cb;
                s.onerror = function() {
                    console.warn('Cropper.min.js no se pudo cargar; se omite la etapa de recorte.');
                    cb();
                };
                document.head.appendChild(s);
            }

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
                        if (!useCropper) {
                            // Sin Cropper: comportamiento legacy (preview plano).
                            var wrap = document.createElement('div');
                            wrap.style.cssText = 'display:inline-block;margin:5px;border:1px solid #eee;padding:5px;border-radius:4px;';
                            var img = document.createElement('img');
                            img.src = e.target.result;
                            img.style.cssText = 'max-width:200px;max-height:200px;display:block;';
                            var lbl = document.createElement('div');
                            lbl.style.cssText = 'font-size:11px;color:#888;margin-top:4px;';
                            lbl.textContent = file.name + ' (' + sizeKB + ' KB)';
                            wrap.appendChild(img); wrap.appendChild(lbl);
                            preview.appendChild(wrap);
                            return;
                        }
                        loadCropper(function() {
                            if (typeof window.Cropper === 'undefined') {
                                // Fallback si no se cargo Cropper.js
                                var wrap = document.createElement('div');
                                wrap.style.cssText = 'display:inline-block;margin:5px;border:1px solid #eee;padding:5px;border-radius:4px;';
                                var img2 = document.createElement('img');
                                img2.src = e.target.result;
                                img2.style.cssText = 'max-width:200px;max-height:200px;display:block;';
                                wrap.appendChild(img2);
                                preview.appendChild(wrap);
                                return;
                            }
                            renderCropperUI(file, e.target.result, sizeKB);
                        });
                    };
                    reader.readAsDataURL(file);
                });
            });

            function renderCropperUI(file, dataUrl, sizeKB) {
                var box = document.createElement('div');
                box.className = 'samap-cropper-box';
                box.style.cssText = 'border:1px solid #ddd;padding:10px;border-radius:4px;background:#fafafa;margin:5px 0;max-width:100%;';

                var header = document.createElement('div');
                header.style.cssText = 'display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;';
                header.innerHTML = '<strong style="font-size:13px;">' + file.name + '</strong> <span style="font-size:11px;color:#888;">' + sizeKB + ' KB — recortá al tamaño recomendado antes de subir</span>';
                box.appendChild(header);

                var imgContainer = document.createElement('div');
                imgContainer.style.cssText = 'max-height:400px;overflow:hidden;background:#fff;';
                var img = document.createElement('img');
                img.src = dataUrl;
                img.style.cssText = 'max-width:100%;display:block;';
                imgContainer.appendChild(img);
                box.appendChild(imgContainer);

                var btns = document.createElement('div');
                btns.style.cssText = 'margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;';
                btns.innerHTML = '' +
                    '<button type="button" class="btn btn-primary btn-sm samap-crop-apply"><em class="fa fa-crop"></em> Recortar y subir</button>' +
                    '<button type="button" class="btn btn-default btn-sm samap-crop-skip"><em class="fa fa-upload"></em> Subir sin recortar</button>' +
                    '<button type="button" class="btn btn-default btn-sm samap-crop-rotate"><em class="fa fa-rotate-right"></em> Rotar 90°</button>' +
                    '<button type="button" class="btn btn-default btn-sm samap-crop-reset" style="margin-left:auto;"><em class="fa fa-undo"></em> Resetear</button>';
                box.appendChild(btns);

                preview.appendChild(box);

                var cropper = new window.Cropper(img, {
                    aspectRatio: aspect > 0 ? aspect : NaN,
                    viewMode: 1,
                    autoCropArea: 0.9,
                    responsive: true,
                    background: false
                });

                // Actualizar el flag de cambios sin guardar (Feature 11)
                function markDirty() { if (window.samapFormMarkDirty) window.samapFormMarkDirty(); }

                box.querySelector('.samap-crop-rotate').addEventListener('click', function() {
                    cropper.rotate(90);
                    markDirty();
                });
                box.querySelector('.samap-crop-reset').addEventListener('click', function() {
                    cropper.reset();
                    markDirty();
                });

                // Aceptar recorte -> reemplaza el File en el input por la imagen recortada.
                box.querySelector('.samap-crop-apply').addEventListener('click', function() {
                    var canvas = cropper.getCroppedCanvas({ fillColor: '#fff' });
                    if (!canvas) return;
                    var ext = (file.type === 'image/png') ? 'png' : (file.type === 'image/webp' ? 'webp' : 'jpg');
                    var mime = (ext === 'png') ? 'image/png' : (ext === 'webp' ? 'image/webp' : 'image/jpeg');
                    canvas.toBlob(function(blob) {
                        if (!blob) return;
                        var newName = file.name.replace(/\.(jpe?g|png|webp)$/i, '') + '-recortada.' + ext;
                        var newFile = new File([blob], newName, { type: mime, lastModified: Date.now() });
                        // Reasignamos files del input via DataTransfer (Chrome/FF soportan).
                        try {
                            var dt = new DataTransfer();
                            dt.items.add(newFile);
                            input.files = dt.files;
                        } catch (e) {
                            // Fallback para navegadores viejos: mostramos un mensaje y dejamos
                            // el input con el archivo original.
                            if (window.samapFlash) {
                                window.samapFlash('warning', 'Tu navegador no permite reasignar archivos. Se subira el original.');
                            }
                        }
                        // Pintamos un thumbnail del recorte para feedback visual.
                        box.innerHTML = '';
                        var done = document.createElement('div');
                        done.style.cssText = 'display:inline-block;margin:5px;border:1px solid #4ac18e;padding:5px;border-radius:4px;background:#f4fbf6;';
                        var prev = document.createElement('img');
                        prev.src = URL.createObjectURL(blob);
                        prev.style.cssText = 'max-width:200px;max-height:200px;display:block;';
                        var lbl = document.createElement('div');
                        lbl.style.cssText = 'font-size:11px;color:#4ac18e;margin-top:4px;font-weight:bold;';
                        lbl.textContent = 'Recortado listo: ' + newName + ' (' + Math.round(blob.size / 1024) + ' KB)';
                        done.appendChild(prev); done.appendChild(lbl);
                        box.appendChild(done);
                        cropper.destroy();
                        if (window.samapFlash) {
                            window.samapFlash('success', 'Imagen recortada lista para subir.');
                        }
                    }, mime, 0.92);
                });

                // Saltar recorte -> confirma y deja el archivo original.
                box.querySelector('.samap-crop-skip').addEventListener('click', function() {
                    if (!confirm('¿Subir la imagen original sin recortar?')) return;
                    cropper.destroy();
                    box.innerHTML = '';
                    var wrap = document.createElement('div');
                    wrap.style.cssText = 'display:inline-block;margin:5px;border:1px solid #eee;padding:5px;border-radius:4px;';
                    var img3 = document.createElement('img');
                    img3.src = dataUrl;
                    img3.style.cssText = 'max-width:200px;max-height:200px;display:block;';
                    var lbl2 = document.createElement('div');
                    lbl2.style.cssText = 'font-size:11px;color:#888;margin-top:4px;';
                    lbl2.textContent = file.name + ' (' + sizeKB + ' KB) — sin recortar';
                    wrap.appendChild(img3); wrap.appendChild(lbl2);
                    box.appendChild(wrap);
                });
            }
        })();
        </script>
    </div>
</div>
