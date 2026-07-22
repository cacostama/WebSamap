# Contexto de trabajo — Blog, consola pública y hardening

Fecha: 2026-07-21
Rama: `main`

Documento de contexto de los cambios de esta tanda. Sirve para retomar el
trabajo sin tener que releer los diffs. El stack real en producción es
**PHP 8.1 + Apache + MySQL 8 (mysqli)** — el stack Next.js descrito en
`AGENTS.md` / `CLAUDE.md` es el objetivo de migración, todavía **no** está
desplegado.

Flujo de trabajo vigente: los cambios se editan en el repo y **el usuario los
sube a producción** (`/mnt/disk2/www/html/WebSamap/`) por Webmin.

---

## 1. Blog — imagen del artículo (decisión de diseño)

**Regla definitiva: la foto de un artículo es SIEMPRE la del campo "Imagen"**
(columna `tbl_blog.imagen`). Las imágenes pegadas dentro del cuerpo del texto
(editor Summernote) **no se publican**.

Motivo: el editor guarda imágenes como data-URI base64 dentro de `texto`. Al
mostrar la página de detalle se veía la imagen destacada arriba **más** la
incrustada en el cuerpo → dos imágenes ("sube doble imagen"). Depender de que
Marketing limpie el cuerpo a mano no era viable: al volver a guardar desde el
editor, la imagen se re-insertaba.

Implementación:

- `funciones/db.php` → helper **`samap_sin_imagenes($html)`**: quita `<img>` y
  `<figure>…</figure>` al **renderizar**. No toca lo guardado en la base.
- `blog-detalle.php` → el cuerpo se imprime con
  `samap_linkificar(samap_sin_imagenes($row_blog['texto']))`.
  La imagen destacada se muestra siempre arriba.
- `admin/editarblog.php` → la **vista previa en vivo** aplica el mismo filtro,
  server-side (render inicial) y en JS (`onChange` de Summernote), y además
  muestra la imagen destacada arriba. Así la previa refleja exactamente lo que
  se publica.

> **Pendiente conocido / trade-off:** con esta regla, una imagen puesta *dentro*
> del cuerpo (ej. una infografía en medio del texto) no se ve. Si en el futuro
> hace falta, hay que volverlo selectivo (ej. permitir imágenes del cuerpo solo
> si NO hay imagen destacada, o un flag por artículo).

### Helper de imagen con fallback

`funciones/db.php` → **`samap_img_blog($imagen)`**: si el artículo no tiene
imagen devuelve `assets/images/blog_articles.png` en vez de apuntar a la carpeta
`documentos/blog/` (que devolvía **403** y mostraba la imagen rota).
Usado en `blog-detalle.php`, `blogs.php` e `index.php`.

## 2. Blog — URLs automáticas ("opción 1")

`blog-detalle.php` → **`samap_linkificar($html)`**: convierte URLs sueltas
(`https://…`) pegadas en el texto en enlaces clickeables
(`target="_blank" rel="noopener noreferrer"`).

- Tokeniza por etiquetas y **no** toca lo que ya está dentro de un `<a>`.
- Escapa con `htmlspecialchars`.
- Se eligió esta vía en lugar de incrustar el video de Instagram: embeber
  requeriría **aflojar el CSP** y cargar scripts de terceros, algo desaconsejado
  en un sitio de salud regulado (Ley 6534/20, datos sensibles).

## 3. Admin — bugs de blog corregidos

- **Editor no arrancaba (grave):** en `admin/editarblog.php` el script de
  inicialización de Summernote corría **antes** de cargar la librería. Se
  reordenó: `partials/scripts-comunes.php` → `summernote.min.js` → init. Se
  eliminó el `summernote.min.js` duplicado que cargaba tarde.
- **Guardado silencioso:** en `editarblog.php` y `agregar-blog.php` el
  `UPDATE`/`INSERT` estaba dentro de `if ($stmt) { … }` y si `execute()` fallaba
  **no avisaba nada** (parecía guardado exitoso). Ahora, ante un fallo, se
  muestra el error real de MySQL vía flash y se vuelve al formulario.
- **Charset:** `$conexion` (de `conexion.php`) no seteaba charset. Se agregó
  `set_charset('utf8mb4')` antes de guardar, para no corromper tildes y ñ.
- **Placeholder 404:** la previa del formulario apuntaba a `img/sin-imagen.jpg`,
  que no existe (no hay carpeta `img/`). Ahora usa
  `assets/images/blog_articles.png`.
- **`texto` truncado:** `crear_tablas_samap.sql` → `tbl_blog.texto` pasó de
  `text` a **`longtext`**. Las imágenes base64 del editor superan los 64 KB de
  `text` y el contenido se cortaba.

### Nota importante sobre el escape global

`admin/funciones/db.php` escapa **todo** `$_POST`/`$_GET` de forma global
(`samap_escapar_recursivo`) y guarda copias crudas en `$POST_RAW` / `$GET_RAW`.
Los handlers que usan **sentencias preparadas** (`bind_param`) deben leer de
**`$POST_RAW`**: leer de `$_POST` ya escapado mete backslashes literales (`\"`)
que rompen el HTML del editor y las imágenes base64. El `funciones/db.php`
público **no** escapa globalmente.

## 4. Subida de imágenes (verificado, sin cambios)

`admin/funciones/seguridad.php` → `samap_guardar_imagen_upload($campo, $dir, …)`:
valida MIME por **magic bytes** (`finfo`), acepta JPG/PNG/WebP, límite 10 MB,
redimensiona con GD a máx. 1600 px y genera variantes `-640` y `-320`. Si GD no
está, cae a `move_uploaded_file`. Devuelve solo el nombre del archivo.

Destino: `$rutaBlog = '../documentos/blog/'` (definido en
`admin/funciones/db.php`). Se auditó toda la cadena y **está correcta**.

## 5. Errores de consola del sitio público (corregidos)

1. **`CircularProgressBar is not defined`** (`assets/js/plugins.js`) — el
   `ReferenceError` **cortaba la ejecución del resto del archivo** (sliders, wow,
   etc.). Se envolvió el bloque en
   `if (typeof CircularProgressBar !== "undefined")` + chequeo de que existan
   elementos `.pie`.
2. **`inner_banner.png` 404** (`assets/css/style.css`) — imagen inexistente. El
   `.banner` ahora usa un degradado con los colores de marca:
   `linear-gradient(120deg, #274767 0%, #6CA3AB 100%)`.
3. **`email-decode.min.js` 404** — script residual de Cloudflare. Eliminado de
   las 14 páginas públicas.
4. **403 en `documentos/blog/`** — resuelto con `samap_img_blog()` (ver §1).

### Cache-busting (causa raíz de "ya lo subí y sigue igual")

`.htaccess` cachea JS/CSS por **30 días**. Tras subir assets nuevos el navegador
seguía usando los viejos. Todas las páginas públicas ahora referencian:

```php
assets/css/style.css?v=<?php echo @filemtime(__DIR__."/assets/css/style.css"); ?>
assets/js/plugins.js?v=<?php echo @filemtime(__DIR__."/assets/js/plugins.js"); ?>
```

Al cambiar el archivo cambia la URL y el navegador lo vuelve a bajar solo.

## 6. Hardening `.htaccess` (raíz)

- Bloqueo por `FilesMatch` de `.env`, `*.sql`, `*.gz`, `*.zip`, `*.bak`, `*.log`,
  `*.sh`, `*.ini`, `*.sqlite`, etc. — evita descargar credenciales y dumps.
- `RedirectMatch 404 /\.git` — por si el directorio quedó publicado.
- `Options -Indexes` — nunca listar directorios.

`documentos/.htaccess` (ya existente) apaga el motor PHP y niega la ejecución de
extensiones peligrosas en la carpeta de subidas. **No** bloquea imágenes.

---

## Archivos tocados en esta tanda

| Archivo | Qué cambió |
|---|---|
| `funciones/db.php` | helpers `samap_img_blog`, `samap_sin_imagenes` |
| `blog-detalle.php` | `samap_linkificar`, filtro de imágenes, cache-busting |
| `blogs.php`, `index.php` | `samap_img_blog`, cache-busting |
| `admin/editarblog.php` | orden de scripts, error real al guardar, utf8mb4, previa fiel |
| `admin/agregar-blog.php` | error real al guardar, utf8mb4 |
| `assets/js/plugins.js` | guard de `CircularProgressBar` |
| `assets/css/style.css` | banner con degradado de marca |
| `.htaccess` | bloqueo de archivos sensibles, `-Indexes` |
| `crear_tablas_samap.sql` | `tbl_blog.texto` → `longtext` |
| +11 páginas públicas | quitado `email-decode`, cache-busting |

## Pendientes (no abordados en esta tanda)

- **Rotar la credencial MySQL de `webadmin`** (quedó pendiente de una tanda previa).
- **Validación server-side de reCAPTCHA** en los formularios de contacto.
- Definir si se permitirán imágenes dentro del cuerpo del artículo (ver §1).

## Colores de marca

- Azul institucional: `#274767` (variantes `#254564`, `#274266`)
- Verde mar: `#6CA3AB` (variante `#74A4B0`)
- Neutro oscuro: `#2F2E2D`
