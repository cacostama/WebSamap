# PROJECT_CONTEXT.md — SAMAP (estado REAL del código)

> **Léeme primero.** Este archivo describe lo que **realmente existe hoy** en este repositorio.
>
> ⚠️ **Importante:** `AGENTS.md` y `CLAUDE.md` describen un **destino futuro** (migración a
> Next.js 15 + Drizzle + Auth.js + AI SDK). **Ese stack NO está implementado en este código.**
> El código actual es un **sitio PHP clásico (procedural) con MySQL**. No existe `app/`, ni
> `pnpm`, ni Drizzle, ni Next.js en este repo. Si una IA recibe una tarea, debe trabajar
> sobre la realidad PHP descrita aquí, no sobre el stack aspiracional de AGENTS.md.

---

## 1. Qué es

**SAMAP — Medicina Prepaga del Sanatorio Adventista de Asunción** (Paraguay).
Sitio web institucional + comercial de una aseguradora/prepaga de salud, con:

- **Sitio público** (raíz del repo): landing, planes, servicios, guía médica, blog, convenios, contacto.
- **Panel de administración** (`admin/`): CMS para cargar/editar el contenido del sitio.

Idioma del contenido y el código: **español (es-PY)**. Zona horaria: `America/Asuncion`.

---

## 2. Stack REAL (lo que hay en el código)

| Capa | Tecnología real |
|---|---|
| Lenguaje | **PHP 8.1** (procedural, no OOP, no framework) |
| Servidor | **Apache** (`php:8.1-apache`) con `mod_rewrite` |
| Base de datos | **MySQL 8.0**, base `web_samap` |
| Driver DB | `mysqli` (procedural en el front, orientado a objetos en `admin/conexion.php`) |
| Frontend | HTML + **Bootstrap 5** + jQuery + plantilla "Medical HTML5" (vendor en `assets/vendor/`) |
| Email | **PHPMailer clásico** (`class/class.phpmailer.php`, SMTP/POP3) |
| Contenedores | **Docker + docker-compose** (servicios `web` y `db`) |
| Routing | `.htaccess` con reglas `mod_rewrite` (URLs "bonitas" con pares clave/valor) |

No hay: Node, pnpm, Next.js, React, Drizzle, Auth.js, TypeScript, tests automatizados.

---

## 3. Cómo correr (Docker)

```bash
docker compose up -d --build
# Web:   http://localhost:8081
# MySQL: localhost:3307  (dentro de la red docker el host es "db")
```

- La DB se inicializa automáticamente desde `docker/mysql/init/backup_samap.sql`
  (copia de `backup_samap.sql.gz`), montado en `/docker-entrypoint-initdb.d`.
- El código se monta como volumen en `/var/www/html`.

**Credenciales de DB (definidas en docker-compose.yml y en el código):**
base `web_samap`, user `webadmin`, host `db`. ⚠️ La contraseña está **hardcodeada** en
`funciones/db.php`, `admin/funciones/db.php` y `admin/conexion.php`. Es deuda técnica de
seguridad conocida (ver §8). No la repliques en código nuevo: idealmente mover a variables
de entorno.

---

## 4. Estructura de carpetas

```
/                         → SITIO PÚBLICO (raíz)
├── index.php             → home (hay variantes: index2, indexOld, indexOld2 = legacy/backups)
├── header.php            → nav compartido (lee tbl_planes para el menú "Planes")
├── footer.php            → footer compartido
├── nosotros.php          → institucional
├── planes.php            → listado de planes
├── plan-detalle.php      → detalle de un plan (por id/título vía URL rewrite)
├── servicios.php         → listado de servicios
├── beneficios.php        → beneficios (alias visual de servicios/aliados)
├── beneficio-detalle.php
├── guiamedica.php        → buscador de médicos (especialidad / sanatorio / ciudad)
├── blogs.php             → listado blog
├── blog-detalle.php
├── convenios.php         → convenios/aliados comerciales
├── aliados.php
├── contactos.php         → formulario de contacto
├── enviar.php            → procesa envío de contacto (PHPMailer)
├── newsletter.php        → alta a newsletter
├── trabajeconnosotros.php
├── funciones/db.php      → conexión mysqli + rutas de upload + tablas de normalización de strings
├── class/                → PHPMailer (envío de correos)
├── assets/               → css, js, images, vendor (Bootstrap, slick, magnific-popup, etc.)
└── documentos/           → imágenes subidas desde el admin (blog, medicos, slider, servicios, aliados…)

/admin                    → PANEL CMS
├── index.php             → login (POST usuario/clave → tbl_user, password con MD5)
├── home.php              → dashboard (protegido por $_SESSION['ADM_Username'])
├── header.php / aside.php→ layout del admin
├── conexion.php          → conexión mysqli (OO) a web_samap
├── funciones/db.php      → conexión + rutas de upload del admin
├── agregar-*.php         → formularios de ALTA (plan, blog, medico, slider, servicio, aliado, ciudad, convenio, guia, sanatorio, galeria, etc.)
├── editar*.php           → formularios de EDICIÓN equivalentes
├── proceso.php           → endpoint de procesamiento de algunos formularios
├── <recurso>.php         → listados (planes.php, blogs.php, medicos.php, slider.php, …)
├── class/                → PHPMailer + token.php (CSRF/sesión, mayormente comentado)
├── pages/                → plantillas HTML del login (login.html, recover.html, etc.)
└── app/                  → css/img/js del tema del admin
```

`*Old*`, `index2.php`, `admin.zip` y los `_notes/dwsync.xml` (metadata de Dreamweaver del
host previo `life4web.co`) son **restos legacy / backups**. No son la fuente de verdad.

---

## 5. Esquema de base de datos (`web_samap`)

Todas las tablas usan prefijo **`tbl_`**. Patrón común: `id` autoincrement, `estado`/`activo`
como flags int, imágenes guardadas como nombre de archivo (no BLOB) en `documentos/`.

| Tabla | Para qué | Columnas clave |
|---|---|---|
| `tbl_planes` | Planes de cobertura | id, **especial** (0=normal,1=destacado/oculto del menú), titulo, detalle, imagen, url |
| `tbl_servicios` | Servicios | id, titulo, intro, detalle, imagen, url |
| `tbl_aliados` | Aliados/beneficios comerciales | id, titulo, detalle, imagen |
| `tbl_convenios` | Convenios | id, ciudad, titulo, detalle, imagen, url |
| `tbl_blog` | Notas de blog | id, fecha, titulo, intro, texto, imagen (charset latin1) |
| `tbl_slider` | Slides del home | id, nombre, imagen, url |
| `tbl_medicos` | Plantel médico (cards) | id, titulo, nombre, especialidad, imagen |
| `tbl_guiamedica` | Guía médica buscable | id, **idEspecialidad**, **idSanatorios**, titulo, nombre, cv, fecha |
| `tbl_especialidad` | Catálogo especialidades | id, nombre, estado |
| `tbl_sanatorio` | Sanatorios/sedes | id, **idCiudad**, nombre, direccion, telefono, estado |
| `tbl_ciudad` | Catálogo ciudades | id, nombre, estado |
| `tbl_user` | Usuarios del admin | id, nombre, userName, **userPass (MD5)** |

Relaciones: `tbl_guiamedica.idEspecialidad → tbl_especialidad.id`,
`tbl_guiamedica.idSanatorios → tbl_sanatorio.id`, `tbl_sanatorio.idCiudad → tbl_ciudad.id`.
No hay foreign keys declaradas; las relaciones se resuelven por JOIN/consultas en el PHP.

---

## 6. Patrón de código (cómo funciona una página)

**Sitio público** — cada `.php` repite este patrón procedural:
```php
<?php require_once('funciones/db.php'); ?>      // abre $connect (mysqli)
<?php
  mysqli_select_db($connect, $database);
  $query_x = 'SELECT * FROM tbl_x WHERE ... ORDER BY id ASC';
  $x = mysqli_query($connect, $query_x);
  $row_x = mysqli_fetch_assoc($x);
?>
<!DOCTYPE html> ... <?php include 'header.php'; ?> ... 
<?php do { /* render $row_x */ $row_x = mysqli_fetch_assoc($x); } while ($row_x); ?>
... <?php include 'footer.php'; ?>
```
- Las imágenes se referencian con `$URL` (calculado desde `HTTP_HOST`) + ruta en `assets/` o `documentos/`.
- Los arrays `$especiales`/`$correctos` en `db.php` **normalizan títulos a slugs** para las URLs.

**URLs amigables** (`.htaccess`): el front genera links tipo
`plan-detalle/titulo/<slug>/cod/<id>/` y el rewrite los mapea a
`plan-detalle.php?titulo=<slug>&cod=<id>`. (Soporta 1 a 4 pares clave/valor.)

**Admin** — login en `admin/index.php`: `POST usuario/clave`, consulta
`tbl_user WHERE userName=? AND userPass=md5(?)`, setea `$_SESSION['ADM_Username']` y redirige
a `admin/home/`. Cada página del admin chequea `isset($_SESSION['ADM_Username'])` o redirige al login.
CRUD = trío `agregar-<x>.php` (form alta) / `editar<x>.php` (form edición) / `<x>.php` (listado),
con upload de imágenes a `documentos/<categoria>/`.

---

## 7. Convenciones al trabajar en este repo

- **PHP procedural, español.** Seguí el estilo existente; no introduzcas un framework salvo
  que la tarea sea explícitamente la migración descrita en AGENTS.md.
- **Tablas con prefijo `tbl_`** y columnas `snake_case`/`camelCase` mezcladas (respetá lo que ya hay por tabla).
- **No edites** archivos `*Old*.php`, `index2.php`, ni descomprimas/edites `admin.zip` salvo pedido explícito.
- **Imágenes**: nombre de archivo en DB, archivo físico en `documentos/`. Nunca BLOB.
- Cambios de schema → reflejarlos también en `backup_samap.sql` (es el seed de Docker).

---

## 8. Seguridad / deuda técnica (estado)

**Resuelto:**
- ✅ **Credenciales DB fuera del código**: ahora se leen con `getenv('DB_HOST'|'DB_NAME'|'DB_USER'|'DB_PASS')`
  (fallback a defaults) en `funciones/db.php`, `admin/funciones/db.php`, `admin/conexion.php`.
  Valores reales en `.env` (gitignored) e inyectados por `docker-compose.yml`. Plantilla en `.env.example`.
- ✅ **SQL injection en login**: `admin/index.php` usa **prepared statement** (`prepare`+`bind_param`).
- ✅ Warnings de login corregidos (`$login`/`$captcha` con `?? ''`, y `?>` final removido de `admin/funciones/db.php`).

**Pendiente (NO empeorar):**
- ⚠️ **Passwords de admin en MD5** (`tbl_user.userPass`) — algoritmo roto; migrar a `password_hash()`/`password_verify()`.
- ⚠️ **SQL injection en el resto**: front concatena `$_GET`/`$_POST` (`cod`/`id`) en muchas páginas. Toda query
  nueva debe ser **parametrizada** (mysqli prepared statements).
- ⚠️ CSRF/token y reCAPTCHA existen pero **comentados** (`admin/class/token.php`, `recaptchalib.php`).
- ⚠️ Headers de seguridad (CSP) y validación de uploads por magic bytes.
- Datos de salud = datos sensibles (Ley 6534/20 Paraguay). Cuidá PII en logs y formularios.

Al tocar estas zonas: corregí hacia lo seguro, no repliques el patrón inseguro.

---

## 8b. Mapa de rutas públicas

URLs amigables (sin `.php`, vía `.htaccess`). El home lee `tbl_slider` + `tbl_planes` +
`tbl_servicios`; el menú de `header.php` lee `tbl_planes WHERE especial=0`.

```
SITIO PÚBLICO (http://localhost:8081/)
│
├── /                                  index.php        Home: slider, planes, servicios, blog
├── /nosotros/                         nosotros.php     Institucional
│
├── /planes/                          planes.php       Listado de planes (tbl_planes)
│   └── /plan-detalle/titulo/<slug>/cod/<id>/           plan-detalle.php  → tbl_planes.id=<id>
│
├── /servicios/                       servicios.php    Listado (tbl_servicios)
│   └── /beneficio-detalle/...                          beneficio-detalle.php
├── /beneficios/                      beneficios.php   (aliados / tbl_aliados)
│
├── /guiamedica/                      guiamedica.php   Buscador médicos
│        filtros → tbl_especialidad · tbl_sanatorio · tbl_ciudad → tbl_guiamedica
│
├── /convenios/                       convenios.php    tbl_convenios
├── /aliados/                         aliados.php      tbl_aliados
│
├── /blogs/                           blogs.php        Listado (tbl_blog)
│   └── /blog-detalle/.../cod/<id>/                     blog-detalle.php  → tbl_blog.id=<id>
│
├── /contactos/                       contactos.php    Formulario  ──POST──► enviar.php (PHPMailer)
├── /newsletter/                      newsletter.php   Alta newsletter
└── /trabajeconnosotros/              trabajeconnosotros.php

PANEL ADMIN (http://localhost:8081/admin/)
│
├── /admin/index/                     index.php   Login (POST usuario+clave → tbl_user, md5)
│        └─ ok → $_SESSION['ADM_Username'] → /admin/home/
│        └─ error → /admin/index/log/error/
├── /admin/home/                      home.php    Dashboard (requiere sesión)
├── /admin/logout/                    logout.php
└── CRUD por recurso (requieren sesión):
     listado <x>.php  ·  alta agregar-<x>.php  ·  edición editar<x>.php
     recursos: planes, servicios, aliados, convenios, blogs, slider, medicos,
               guia, especialidad, sanatorios, ciudad, galeria, fechas, agenda,
               speakers, sponsors, apoyan, nacionalidad
```

---

## 8c. Plan de remediación de seguridad (pendiente — NO ejecutado aún)

Orden sugerido, de mayor a menor impacto. Cada punto debe hacerse y probarse aislado
(la app está en producción/Docker y los cambios pueden romperla):

1. **Credenciales fuera del código** → leer DB host/user/pass de variables de entorno
   (`getenv()`), inyectadas por `docker-compose.yml` / `/etc/samap/env`. Hoy están en
   `funciones/db.php`, `admin/funciones/db.php`, `admin/conexion.php`.
2. **Passwords de admin** → migrar `tbl_user.userPass` de `md5()` a `password_hash()` /
   `password_verify()`. Requiere re-hashear o forzar reset de la única cuenta existente.
3. **SQL injection** → reemplazar concatenación de `$_GET`/`$_POST` por **prepared statements**
   (`mysqli_prepare` + `bind_param`), empezando por login y por las páginas que reciben `cod`/`id` en la URL.
4. **Reactivar CSRF + reCAPTCHA** en login y formularios (la lógica existe comentada en
   `admin/class/token.php` y `recaptchalib.php`).
5. **Endurecer headers** (CSP, X-Frame-Options consistente) y validar uploads por magic bytes en el admin.

> Recomendación: abordar de a uno, con backup de DB previo, y verificar HTTP 200 + login tras cada cambio.

---

## 9. Relación con AGENTS.md / CLAUDE.md

Esos documentos son la **guía del proyecto destino** (reescritura en Next.js). Sirven para:
entender el negocio, la paleta de marca, las reglas de compliance y hacia dónde va el proyecto.
**Pero los comandos (`pnpm dev`, Drizzle, evals, worktrees) y la estructura `app/` NO aplican
al código actual.** Si te piden "arreglar X" o "agregar Y", hacelo en PHP sobre la estructura de §4
a menos que la tarea sea explícitamente migrar a Next.js.

---

## 10. Registro de sesión (changelog)

### Estado actual: ✅ APP FUNCIONANDO en `http://localhost:8081/` (admin en `/admin/`, usuario `admin`)

Cómo levantar: `docker compose up -d --build` → web en `:8081`, MySQL en `:3307`.
Datos reales cargados (7 planes, 85 médicos, 325 guía médica, 210 sanatorios, 13 blogs, etc.).

### Cambios aplicados en esta sesión

**Documentación**
- Creado este `PROJECT_CONTEXT.md` (aclara que el código real es PHP, no el Next.js de AGENTS.md).
- Creados `.env` (gitignored, credenciales reales) y `.env.example` (plantilla).

**Seguridad**
- Credenciales DB movidas a `getenv('DB_HOST'|'DB_NAME'|'DB_USER'|'DB_PASS')` con fallback,
  en `funciones/db.php`, `admin/funciones/db.php`, `admin/conexion.php`.
  `docker-compose.yml` las inyecta desde `.env`.
- Login admin (`admin/index.php`) pasado a **prepared statement** (`prepare`+`bind_param`).
- Definidos `$login`/`$captcha` con `?? ''` (cartel de error de login volvió a funcionar).
- Removido `?>` final de `admin/funciones/db.php` (evita "headers already sent").

**Bugs de render corregidos**
- `$URL` ya no fuerza `https://`: detecta protocolo real (http/https) en los dos `db.php`.
  → Causaba `ERR_SSL_PROTOCOL_ERROR` en todos los assets (sitio sin estilos).
- `index.php` query de médicos ahora filtra `WHERE imagen IS NOT NULL AND imagen <> ''`.
  → Evitaba `GET /documentos/medicos/ 403` por registro basura (tbl_medicos id=92, sin nombre/imagen).
- **Dockerfile**: agregada extensión PHP **`intl`** (+ `libicu-dev`) y rebuild de la imagen.
  → El home usaba `IntlDateFormatter` (fechas del blog en español); sin `intl` lanzaba
  `Fatal error: Class "IntlDateFormatter" not found` en `index.php:460`, cortaba el HTML
  antes de los `<script>` y dejaba el **preloader colgado** (logo SAMAP girando).

### Pendientes de seguridad (sin hacer)
- Passwords admin en MD5 (`tbl_user.userPass`) → migrar a `password_hash()`/`password_verify()`.
- Prepared statements en el resto de páginas que reciben `cod`/`id` por URL (front concatena `$_GET`/`$_POST`).
- Reactivar CSRF/token + reCAPTCHA (existen comentados en `admin/class/token.php`, `recaptchalib.php`).
- Headers CSP + validación de uploads por magic bytes.
- (Opcional) Borrar/limpiar el registro basura `tbl_medicos` id=92.

### Notas para retomar
- El registro id=92 sigue en la DB; está neutralizado por el filtro del query, no borrado.
- `intl` quedó en el Dockerfile: si se reconstruye desde cero, ya viene incluida.
- Si los assets vuelven a fallar por SSL, revisar que `$URL` (en ambos `db.php`) detecte protocolo.
