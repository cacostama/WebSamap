# REDESIGN_BRIEF.md — Rediseño del sitio público SAMAP

> Origen: PDF `web.pdf` (brief visual del cliente, 8 secciones). Este documento traduce ese
> brief a instrucciones accionables para cualquier IA/desarrollador.
> Stack real: **PHP procedural + MySQL + Bootstrap 5** (ver `PROJECT_CONTEXT.md`).
> Trabajar en rama **`feat/rediseno-web`**. NO tocar `main`.

## Objetivo

Modernizar el sitio público: comunicar en 5 segundos qué es SAMAP, generar confianza y
guiar al usuario a **pedir información** (WhatsApp / contacto). Diseño limpio, mucho espacio
en blanco, textos cortos, tarjetas visuales en vez de párrafos largos.

## Regla de oro para todos los agentes

⚠️ **PRESERVAR toda la lógica PHP/DB existente.** Cada página tiene loops `do { ... } while
($row = mysqli_fetch_assoc($x))` sobre tablas (`tbl_planes`, `tbl_medicos`, `tbl_convenios`,
`tbl_servicios`, `tbl_blog`, etc.). **Solo reestilizar el markup HTML y CSS** alrededor de
esos datos. No cambiar nombres de variables PHP, queries, ni `include 'header.php'/'footer.php'`.
Mantener `<?php echo $URL?>` para rutas de assets. No romper las URLs amigables existentes.

## Tokens de marca (usar SIEMPRE estos)

```css
:root{
  --samap-azul:#274767;      /* azul institucional (titulares, footer, banda guía médica) */
  --samap-azul-2:#1d3550;     /* azul oscuro hover */
  --samap-verde:#6CA3AB;      /* verde mar (bandas, acentos) */
  --samap-verde-2:#74A4B0;
  --samap-whatsapp:#25D366;   /* verde WhatsApp (CTA primario) */
  --samap-neutro:#2F2E2D;
  --samap-celeste:#eef5f8;    /* fondo celeste muy claro de secciones */
  --samap-gris:#6b7280;       /* texto secundario */
  --radius:16px;
}
```
Tipografía: la que ya usa el sitio. Botón primario = WhatsApp verde; secundario = azul.

## Clases CSS compartidas (convención común entre agentes)

Prefijo **`rd-`** para todo lo nuevo, así no choca con el tema viejo:
`rd-section`, `rd-container`, `rd-title`, `rd-subtitle`, `rd-card`, `rd-grid`,
`rd-btn`, `rd-btn--wa` (whatsapp), `rd-btn--azul`, `rd-band` (banda azul), `rd-stats`,
`rd-timeline`, `rd-logos`, `rd-hero`, `rd-nav`, `rd-footer`.

## Arquitectura de CSS (evita conflictos entre agentes)

- Cada agente crea/edita **su propio archivo CSS** en `assets/css/` (sin tocar `style.css`):
  - `rediseno-base.css` → tokens + nav/header + footer + botones + WhatsApp flotante (Agente LAYOUT)
  - `rediseno-home.css` → secciones del home (Agente HOME)
  - `rediseno-nosotros.css` → página nosotros (Agente NOSOTROS)
  - `rediseno-convenios.css` → convenios/beneficios (Agente CONVENIOS)
- Cada **página `.php`** enlaza en su `<head>`, DESPUÉS de `style.css`:
  `<link rel="stylesheet" href="<?php echo $URL?>assets/css/rediseno-base.css">`
  y el CSS de su sección. (Cada agente agrega el `<link>` en el `<head>` de SU página.)

## Secciones del brief (qué construir)

### 1. Header + Hero — "Cuidándote siempre"  (archivos: header.php + hero en index.php)
- Nav simple y limpio: **Inicio · Planes · Guía Médica · Convenios · Nosotros · Contacto** +
  botón destacado **"Solicitar información"** + ícono WhatsApp. Logo a la izquierda.
- Hero: foto cálida de familia bien iluminada (usar slider/imágenes existentes de la DB/assets),
  slogan grande **"Cuidándote siempre"**, subtítulo "Cobertura médica para vos y tu familia
  con el respaldo del Sanatorio Adventista."
- CTAs: **botón verde WhatsApp "Solicitar información"** (primario) + **botón azul "Conocer
  planes"** (secundario). Mostrar mini-stats bajo el hero (+40 años, +8.000 familias, respaldo del Sanatorio).
- WhatsApp flotante fijo abajo-derecha en todas las páginas.

### 2. "¿Por qué elegir SAMAP?"  (archivo: index.php)
- 6 tarjetas con íconos lineales en azul (Font Awesome ya está disponible):
  **Respaldo Médico · Emergencias · Especialistas · Convenios · Beneficios Exclusivos · Gestión Ágil.**
- Frases cortas, mucho espacio en blanco, sin párrafos largos.
- Cierre: "Un plan para **cada etapa** de la vida."

### 3. Planes por etapa de vida  (archivo: index.php, datos de tbl_planes)
- 3 cards principales destacadas (el brief muestra Kids/Beta/Senior, pero usar los planes reales
  de `tbl_planes`): imagen humana + nombre + texto breve emocional + botón **"Ver plan"**
  (link al `plan-detalle` existente). Fondo celeste muy claro (`--samap-celeste`).
- Buscador "**Encontrá tu médico en segundos**" (banda azul con input de búsqueda → guía médica).

### 4. Guía Médica + Convenios  (banda en index.php; página completa = guiamedica.php / convenios.php)
- Guía Médica en **franja azul fuerte** con buscador visible por especialidad/médico/centro.
- Logos de convenios en **carrusel limpio** + botón "Ver todos los convenios".

### 5. Nosotros  (archivo: nosotros.php)
- Hero "NOSOTROS" con breadcrumb. **Nuestra Historia** = timeline horizontal: **1983** (nace
  SAMAP con respaldo del Sanatorio) · **1995** (se amplían servicios y red) · **2005** (nuevas
  coberturas/beneficios) · **Hoy** (miles de familias). **Misión** y **Visión** en dos tarjetas.
- **Nuestros Valores**: Compromiso · Servicio · Excelencia · Integridad · Cercanía (íconos).
- **Stats** grandes: **+40** años · **+120.000** asegurados · **+1.200** especialistas · **+500** convenios.
- Foto de equipo médico.

### 6. Convenios y Beneficios  (archivos: convenios.php + beneficios.php)
- Hero "CONVENIOS y BENEFICIOS". **Convenios Nacionales** (logos de tbl_convenios + "Ver todos").
- **Beneficios Internacionales** (Assist Card — asistencia al viajero, botón "Conocer beneficio").
- **Descuentos Exclusivos**: tarjetas Farmacias / Ópticas / Laboratorios / Gimnasios / Y más
  (con % de descuento, íconos). Botón "Ver todos los beneficios".

### 7. Mobile / Responsive  (afecta a header.php + rediseno-base.css)
- Hero mobile prolijo. Menú hamburguesa que abre panel azul oscuro con:
  Inicio · Planes · Guía Médica · Convenios y Beneficios · Nosotros · Contacto +
  botón verde "Solicitar información". Todo el rediseño debe ser responsive (mobile-first).

### 8. Footer  (archivo: footer.php)
- CTA final sobre banda: **"¿Querés más información?"** con 3 botones: **WhatsApp · Llamar · Contactar.**
- Footer en columnas: **Accesos rápidos · Atención al cliente · Nuestras redes.** Fondo **azul
  oscuro institucional**. Logo SAMAP + datos de contacto + redes sociales.

## Verificación (hacer al final)
- `http://localhost:8081/` y cada página → **HTTP 200**, sin `Fatal error`/`Warning` PHP.
- Los datos siguen renderizando (planes, médicos, convenios vienen de la DB).
- Responsive ok (probar ancho mobile).
