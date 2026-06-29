"""
Genera docs/informe-rediseno-web.pdf con el resumen completo del trabajo
realizado en el branch feat/rediseno-web, separado por bloques y fases.
"""
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import cm
from reportlab.lib import colors
from reportlab.lib.enums import TA_LEFT, TA_CENTER, TA_JUSTIFY
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, PageBreak, Table, TableStyle,
    KeepTogether,
)
from datetime import date
import os

OUT = os.path.join(os.path.dirname(__file__), '..', 'docs', 'informe-rediseno-web.pdf')
os.makedirs(os.path.dirname(OUT), exist_ok=True)

# Paleta institucional SAMAP
AZUL   = colors.HexColor('#274767')
VERDE  = colors.HexColor('#6CA3AB')
NEUTRO = colors.HexColor('#2F2E2D')
GRIS   = colors.HexColor('#666666')
FONDO  = colors.HexColor('#F4F6F8')

styles = getSampleStyleSheet()

title_st = ParagraphStyle('title', parent=styles['Title'],
    fontName='Helvetica-Bold', fontSize=26, leading=32,
    textColor=AZUL, alignment=TA_LEFT, spaceAfter=6)

subtitle_st = ParagraphStyle('subtitle', parent=styles['Normal'],
    fontName='Helvetica', fontSize=13, leading=17,
    textColor=VERDE, alignment=TA_LEFT, spaceAfter=20)

h1 = ParagraphStyle('h1', parent=styles['Heading1'],
    fontName='Helvetica-Bold', fontSize=18, leading=22,
    textColor=AZUL, spaceBefore=18, spaceAfter=10,
    borderPadding=4)

h2 = ParagraphStyle('h2', parent=styles['Heading2'],
    fontName='Helvetica-Bold', fontSize=13.5, leading=17,
    textColor=VERDE, spaceBefore=14, spaceAfter=6)

h3 = ParagraphStyle('h3', parent=styles['Heading3'],
    fontName='Helvetica-Bold', fontSize=11, leading=14,
    textColor=NEUTRO, spaceBefore=8, spaceAfter=4)

body = ParagraphStyle('body', parent=styles['BodyText'],
    fontName='Helvetica', fontSize=10, leading=14,
    textColor=NEUTRO, alignment=TA_JUSTIFY, spaceAfter=6)

bullet = ParagraphStyle('bullet', parent=body,
    leftIndent=14, bulletIndent=2, spaceAfter=3)

meta = ParagraphStyle('meta', parent=styles['Normal'],
    fontName='Helvetica-Oblique', fontSize=9, leading=12,
    textColor=GRIS, spaceAfter=4)

code = ParagraphStyle('code', parent=styles['Code'],
    fontName='Courier', fontSize=9, leading=12,
    textColor=NEUTRO, backColor=FONDO,
    borderPadding=4, leftIndent=4, rightIndent=4, spaceAfter=6)


def P(t, st=body): return Paragraph(t, st)
def B(items, st=bullet):
    return [Paragraph(f"&bull;&nbsp;&nbsp;{x}", st) for x in items]


def commit_table(rows):
    """Tabla de commits: [hash, descripcion]."""
    data = [['Hash', 'Descripción']]
    for h, d in rows:
        data.append([Paragraph(f"<font name='Courier' size='8'>{h}</font>", body),
                     Paragraph(d, body)])
    t = Table(data, colWidths=[2.2*cm, 14.3*cm])
    t.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), AZUL),
        ('TEXTCOLOR',  (0,0), (-1,0), colors.white),
        ('FONTNAME',   (0,0), (-1,0), 'Helvetica-Bold'),
        ('FONTSIZE',   (0,0), (-1,0), 9),
        ('ALIGN',      (0,0), (-1,0), 'LEFT'),
        ('VALIGN',     (0,0), (-1,-1), 'TOP'),
        ('ROWBACKGROUNDS', (0,1), (-1,-1), [colors.white, FONDO]),
        ('GRID',       (0,0), (-1,-1), 0.3, colors.HexColor('#D8DEE5')),
        ('LEFTPADDING',  (0,0), (-1,-1), 6),
        ('RIGHTPADDING', (0,0), (-1,-1), 6),
        ('TOPPADDING',   (0,0), (-1,-1), 4),
        ('BOTTOMPADDING',(0,0), (-1,-1), 4),
    ]))
    return t


def inventario_table(rows):
    data = [['Métrica', 'Valor']]
    for k, v in rows:
        data.append([Paragraph(k, body),
                     Paragraph(f"<b>{v}</b>", body)])
    t = Table(data, colWidths=[12*cm, 4.5*cm])
    t.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), VERDE),
        ('TEXTCOLOR',  (0,0), (-1,0), colors.white),
        ('FONTNAME',   (0,0), (-1,0), 'Helvetica-Bold'),
        ('ALIGN',      (1,0), (1,-1), 'CENTER'),
        ('VALIGN',     (0,0), (-1,-1), 'MIDDLE'),
        ('ROWBACKGROUNDS', (0,1), (-1,-1), [colors.white, FONDO]),
        ('GRID',       (0,0), (-1,-1), 0.3, colors.HexColor('#D8DEE5')),
        ('LEFTPADDING',  (0,0), (-1,-1), 8),
        ('RIGHTPADDING', (0,0), (-1,-1), 8),
        ('TOPPADDING',   (0,0), (-1,-1), 5),
        ('BOTTOMPADDING',(0,0), (-1,-1), 5),
    ]))
    return t


# ============================================================================
# CONTENIDO
# ============================================================================
story = []

# ----------- PORTADA -----------
story.append(Spacer(1, 4*cm))
story.append(P("Informe de implementación", title_st))
story.append(P(
    "Rediseño del sitio público y panel administrativo<br/>"
    "<b>SAMAP — Medicina Prepaga del Sanatorio Adventista de Asunción</b>",
    subtitle_st))

portada_data = [
    ['Repositorio',       'WebSamap'],
    ['Branch',            'feat/rediseno-web'],
    ['Commits totales',   '49'],
    ['Archivos tocados',  '280'],
    ['Líneas añadidas',   '+26.538'],
    ['Líneas eliminadas', '−4.295'],
    ['Fecha del informe', date.today().strftime('%d/%m/%Y')],
]
pt = Table(portada_data, colWidths=[5*cm, 7*cm])
pt.setStyle(TableStyle([
    ('FONTNAME',    (0,0), (0,-1), 'Helvetica-Bold'),
    ('TEXTCOLOR',   (0,0), (0,-1), AZUL),
    ('TEXTCOLOR',   (1,0), (1,-1), NEUTRO),
    ('FONTSIZE',    (0,0), (-1,-1), 10),
    ('VALIGN',      (0,0), (-1,-1), 'MIDDLE'),
    ('LINEBELOW',   (0,0), (-1,-1), 0.3, colors.HexColor('#D8DEE5')),
    ('LEFTPADDING', (0,0), (-1,-1), 0),
    ('TOPPADDING',  (0,0), (-1,-1), 5),
    ('BOTTOMPADDING', (0,0), (-1,-1), 5),
]))
story.append(pt)
story.append(Spacer(1, 1*cm))
story.append(P(
    "El presente documento detalla, de punta a punta, el trabajo realizado "
    "sobre el branch <b>feat/rediseno-web</b>: desde la solicitud inicial "
    "(montar el entorno local y rediseñar el sitio público) hasta las cuatro "
    "fases de mejora del panel administrativo. Cada bloque incluye su "
    "objetivo, los commits asociados y el detalle funcional de lo entregado.",
    body))
story.append(PageBreak())

# ----------- ÍNDICE -----------
story.append(P("Índice", h1))
indice = [
    "1. Solicitud inicial y contexto del proyecto",
    "2. Bloque A — Infraestructura y arranque",
    "3. Bloque B — Rediseño del sitio público (marketing)",
    "4. Bloque C — Endurecimiento de seguridad inicial",
    "5. Bloque D — UX del panel admin (mejoras tempranas)",
    "6. Bloque E — Beneficios y aliados configurables",
    "7. FASE 1 — Quick wins UX del admin",
    "8. FASE 2 — Productividad en listados",
    "9. FASE 3 — Nuevas funcionalidades",
    "10. FASE 4 — Calidad, seguridad y autonomía",
    "11. QA — Scripts de testing",
    "12. Inventario final y métricas",
    "13. Estado actual del repositorio",
]
for i in indice:
    story.append(P(i, bullet))
story.append(PageBreak())

# ----------- 1. SOLICITUD INICIAL -----------
story.append(P("1. Solicitud inicial y contexto del proyecto", h1))
story.append(P("Punto de partida", h2))
story.append(P(
    "El sitio original de SAMAP era una mezcla de template HTML5 médico y "
    "un panel PHP legacy con las siguientes carencias:",
    body))
story.extend(B([
    "Un único usuario administrador con contraseña MD5 hardcoded en la base.",
    "Sin sistema de búsqueda, papelera, auditoría ni recuperación de contraseña.",
    "Validación de inputs débil, vulnerabilidades de XSS stored y SQLi en varios endpoints.",
    "<code>alert()</code> y <code>window.location.href</code> como mecanismo de feedback (113 ocurrencias).",
    "Sin separación de roles: cualquier sesión podía escribir en cualquier módulo.",
    "Listados sin paginación, sin búsqueda, sin exportar y sin soft-delete.",
    "Formularios de contacto y &laquo;Trabajá con Nosotros&raquo; sin persistir en base.",
    "Imposible para Marketing operar el CMS sin asistencia técnica.",
]))
story.append(P("Pedido del cliente", h2))
story.append(P(
    "El cliente entregó un brief (<code>web.pdf</code>) y los manuales de "
    "marca (paleta institucional <font color='#274767'><b>#274767</b></font> "
    "azul Red Hospitales IASD y <font color='#6CA3AB'><b>#6CA3AB</b></font> "
    "verde mar SAMAP) solicitando:",
    body))
story.extend(B([
    "Rediseñar el sitio público respetando la identidad visual de la marca.",
    "Modernizar el panel para que sea operable por personas de Marketing sin conocimientos técnicos.",
    "Endurecer la seguridad para cumplir con la Ley 6534/20 de protección de datos personales del Paraguay.",
    "Conservar la arquitectura PHP existente y la base de datos legacy <code>web_samap</code>.",
]))
story.append(P("Estrategia de trabajo", h2))
story.append(P(
    "El trabajo se organizó en bloques iniciales (infra, rediseño público, "
    "seguridad temprana, UX del admin, beneficios) y luego se ejecutaron "
    "<b>cuatro fases</b> incrementales sobre el panel administrativo. Cada "
    "fase siguió el ciclo <i>Analizar → Desarrollar → Testear → Corregir</i>, "
    "delegando trabajo en agentes especializados cuando la tarea lo justificaba.",
    body))
story.append(PageBreak())

# ----------- 2. BLOQUE A -----------
story.append(P("2. Bloque A — Infraestructura y arranque", h1))
story.append(P("Objetivo", h2))
story.append(P(
    "Levantar un entorno reproducible para trabajar contra el código legacy "
    "sin depender del servidor de producción. Se construyó un Docker Compose "
    "con MySQL 8 + Apache/PHP, seed de la base con el dump original y volúmenes "
    "mapeados al working tree para edición en vivo.",
    body))
story.append(P("Commit", h2))
story.append(commit_table([
    ('a65e1be', 'chore(infra): entorno local Docker + fixes para levantar el sitio'),
]))
story.append(PageBreak())

# ----------- 3. BLOQUE B -----------
story.append(P("3. Bloque B — Rediseño del sitio público (marketing)", h1))
story.append(P("Objetivo", h2))
story.append(P(
    "Aplicar la identidad visual de SAMAP al sitio público, mejorar UX móvil, "
    "incorporar descargas de PDF (Guía Médica, anexo por plan), reforzar SEO "
    "y cumplimiento legal, y agregar la sección de Descuentos Exclusivos.",
    body))
story.append(P("Commits", h2))
story.append(commit_table([
    ('68b2793', 'docs: brief de rediseño del sitio público (desde web.pdf)'),
    ('dbba4ba', 'feat(marketing): rediseño del sitio público según brief (web.pdf)'),
    ('b4f8d65', 'fix(marketing): elimina botones duplicados en header desktop y usa foto limpia en hero'),
    ('3028ad6', 'feat(marketing): mejoras móviles, limpieza de home y nuevo aliado gym'),
    ('3fe8aa3', 'fix(marketing): mejora UX mobile y sección Descuentos Exclusivos'),
    ('7bdc11a', 'feat(marketing): descarga de Guía Médica en PDF + cache-busting de CSS'),
    ('7c825e1', 'feat(web): endurece seguridad, SEO, rendimiento y cumplimiento legal'),
    ('4ab200f', 'feat(marketing): agrega descarga de anexo PDF por plan y presupuesto'),
]))
story.append(PageBreak())

# ----------- 4. BLOQUE C -----------
story.append(P("4. Bloque C — Endurecimiento de seguridad inicial", h1))
story.append(P("Objetivo", h2))
story.append(P(
    "Cerrar las vulnerabilidades críticas detectadas en una primera pasada: "
    "credenciales fuera del repo, login con CSRF, queries parametrizadas, "
    "validación de uploads por magic bytes y soft-delete con roles.",
    body))
story.append(P("Commits", h2))
story.append(commit_table([
    ('4956500', 'fix(admin): endurece seguridad del panel (credenciales, SQLi, uploads, login)'),
    ('804c0bd', 'feat(admin): soft-delete y roles de usuario (#8, #9)'),
    ('1c5cedc', 'fix(security): castea IDs a entero en guiamedica para cerrar SQLi (#2)'),
    ('eaebf27', 'fix(leads): conecta y endurece el form de Trabajá con Nosotros'),
]))
story.append(PageBreak())

# ----------- 5. BLOQUE D -----------
story.append(P("5. Bloque D — UX del panel admin (mejoras tempranas)", h1))
story.append(P("Objetivo", h2))
story.append(P(
    "Quick wins de UX para que el panel sea presentable a Marketing antes de "
    "iniciar las fases formales: confirmaciones de borrado, rol real en el "
    "menú lateral, estados vacíos en listados y mensajes humanos.",
    body))
story.append(P("Commits", h2))
story.append(commit_table([
    ('d8e64e4', 'feat(admin): confirmación al eliminar y rol real en el menú'),
    ('b6ae956', 'feat(admin): estado vacío en listados sin registros'),
    ('31954ea', 'fix(admin): mensajes de borrado claros y con etiqueta correcta'),
    ('66091e6', 'fix(admin): rótulos de medida de imagen más claros'),
]))
story.append(PageBreak())

# ----------- 6. BLOQUE E -----------
story.append(P("6. Bloque E — Beneficios y aliados configurables", h1))
story.append(P("Objetivo", h2))
story.append(P(
    "Permitir a Marketing administrar los beneficios y descuentos sin tocar "
    "código: ABM de categorías de aliados, detalle de beneficio con comercios "
    "asociados y logos, y migración del desglose de descuentos de farmacias.",
    body))
story.append(P("Commits", h2))
story.append(commit_table([
    ('53815e7', 'feat(beneficios): descuentos exclusivos configurables por categoría'),
    ('6cfe231', 'feat(admin): ABM de categorías de aliados configurable por Marketing'),
    ('37bd3bc', 'feat(beneficios): detalle de beneficio configurable con comercios y logos'),
    ('1ddd446', 'chore(beneficios): migra desglose de descuentos de farmacias a aliados'),
    ('10e1496', 'chore: ignora artefactos locales de tooling (.agents/, skills-lock.json)'),
]))
story.append(PageBreak())

# ----------- 7. FASE 1 -----------
story.append(P("7. FASE 1 — Quick wins UX del admin", h1))
story.append(P("Objetivo", h2))
story.append(P(
    "Cerrar vulnerabilidades pendientes y darle al panel funcionalidades "
    "básicas que el legacy nunca tuvo: dashboard con métricas reales, "
    "búsqueda global y perfil de usuario.",
    body))
story.append(P("Entregables", h2))
story.extend(B([
    "<b>Dashboard con métricas reales</b> por sección en <code>admin/home.php</code>.",
    "<b>Búsqueda global</b> con highlight y agrupado por tipo (<code>admin/buscar.php</code>).",
    "<b>Perfil de usuario</b> con cambio de contraseña (<code>admin/perfil.php</code>).",
    "Fix de XSS stored, leak de path absoluto y headers de seguridad (CSP, X-Frame-Options).",
    "Eliminación de <code>get_magic_quotes_gpc()</code> incompatible con PHP 8.",
    "Botón Cancelar deja de enviar el formulario.",
    "Habilitación de guion en rewrite rule para URLs <code>agregar-</code> y <code>editar-</code>.",
    "Protección CSRF en el borrado de los 10 listados que faltaban.",
]))
story.append(P("Commits", h2))
story.append(commit_table([
    ('9aa7ef9', 'fix(admin,ui): refuerza guards de escritura y corrige contraste de heros'),
    ('40eecf8', 'feat(admin): escritorio con métricas reales por sección'),
    ('f9f4bcc', 'feat(admin): búsqueda global con highlight y agrupado por tipo'),
    ('3c8849e', 'feat(admin): perfil de usuario con cambio de contraseña'),
    ('5379bbd', 'fix(admin): corrige XSS stored, leak de path y headers de seguridad'),
    ('ffdac1c', 'fix(admin): elimina get_magic_quotes_gpc() incompatible con PHP 8'),
    ('9c01e56', 'fix(admin): corrige form de alta para que Cancelar no envíe el submit'),
    ('58683f2', 'fix(admin): habilita guion en rewrite rule para URLs con agregar-/editar-'),
    ('90eea17', 'fix(admin): agrega protección CSRF al borrado de los 10 listados faltantes'),
]))
story.append(PageBreak())

# ----------- 8. FASE 2 -----------
story.append(P("8. FASE 2 — Productividad en listados", h1))
story.append(P("Objetivo", h2))
story.append(P(
    "Eliminar la fricción de los listados: agregar búsqueda, paginación y "
    "exportación a CSV en todos los CRUDs sin duplicar código. Crear partials "
    "reutilizables y completar el esquema de la base.",
    body))
story.append(P("Entregables", h2))
story.extend(B([
    "<b>Migración 001</b>: crea las 8 tablas faltantes (<code>tbl_agenda</code>, "
    "<code>tbl_agenda_detalle</code>, <code>tbl_galeria</code>, <code>tbl_nacionalidad</code>, "
    "<code>tbl_speaker</code>, <code>tbl_sponsor</code>, <code>tbl_apoyan</code>, <code>tbl_fotos</code>).",
    "<b>Partial <code>tabla-searchable</code></b> con DataTables ES, búsqueda, paginación, ordenamiento y Exportar CSV.",
    "<b>18 listados</b> migrados al nuevo partial (planes, convenios, aliados, blogs, médicos, sanatorios, slider, servicios, etc.).",
    "<b>Partial <code>upload-imagen</code></b> con drop-zone, preview y validación.",
    "<b>11 formularios de upload</b> migrados al partial.",
    "Fixes: typo URl/URL, tamaño slider, comentarios de carpetas compartidas.",
]))
story.append(P("Commits", h2))
story.append(commit_table([
    ('878b481', 'feat(db): crea las 8 tablas faltantes del panel y agrega migración incremental'),
    ('972abc1', 'fix(admin): corrige typo URl/URL, tamaño de slider y comentarios de carpetas compartidas'),
    ('58f3226', 'feat(admin): partial reutilizable para listados con DataTables + búsqueda + CSV'),
    ('f4118e0', 'refactor(admin): migra 18 listados al partial tabla-searchable'),
    ('62a6082', 'refactor(admin): migra 11 formularios de upload al partial upload-imagen'),
]))
story.append(PageBreak())

# ----------- 9. FASE 3 -----------
story.append(P("9. FASE 3 — Nuevas funcionalidades", h1))
story.append(P("Objetivo", h2))
story.append(P(
    "Sumar al panel las funcionalidades que un CMS moderno necesita: CRM de "
    "leads, biblioteca de medios, papelera de reciclaje y recuperación de "
    "contraseña por email.",
    body))
story.append(P("Entregables", h2))
story.extend(B([
    "<b>Biblioteca de medios</b> (<code>admin/medios.php</code>) — grid filtrable con 237+ imágenes existentes del sitio.",
    "<b>Papelera de reciclaje</b> (partial <code>papelera-toggle</code>) con soft-delete, restaurar y borrar definitivamente en 9 listados.",
    "<b>Módulo de Leads</b> (<code>admin/leads.php</code>): inbox CRM con filtros (origen, estado), edición de notas y cambio de estado (nuevo, contactado, cerrado, spam).",
    "<b>Migración 002</b>: persiste leads en <code>tbl_leads</code> desde el formulario público de contacto.",
    "<b>Recuperación de contraseña</b> con tokens de un solo uso, expiración y notificación por SMTP.",
    "<b>Migración 003</b>: tabla <code>tbl_user_token</code> genérica para tokens de un solo uso.",
    "Links de Leads y Biblioteca agregados al sidebar.",
]))
story.append(P("Commits", h2))
story.append(commit_table([
    ('f54b026', 'feat(crm): persiste leads en tbl_leads al enviar formulario de contacto'),
    ('34d1963', 'feat(crm): admin/leads.php inbox con filtros, estados y notas'),
    ('823ec40', 'feat(admin): biblioteca de medios (admin/medios.php) con grid + filtros + delete'),
    ('827d0f5', 'feat(admin): papelera de reciclaje con restaurar y borrar definitivamente'),
    ('d582731', 'feat(auth): flujo de recuperación de contraseña con tokens de un solo uso'),
    ('eae806f', 'feat(admin): agrega links de Leads y Biblioteca de medios al sidebar'),
]))
story.append(PageBreak())

# ----------- 10. FASE 4 -----------
story.append(P("10. FASE 4 — Calidad, seguridad y autonomía", h1))
story.append(P("Objetivo", h2))
story.append(P(
    "Cerrar el ciclo: auditoría completa de acciones, gestión de usuarios "
    "(CRUD con roles), reemplazo de <code>alert()</code> por toasts modernos "
    "y backup/restore de la base desde el propio panel.",
    body))
story.append(P("Entregables", h2))
story.extend(B([
    "<b>Migración 004</b>: tabla <code>tbl_audit_log</code> con snapshots JSON antes/después.",
    "<b>Visor de auditoría</b> (<code>admin/auditoria.php</code>) con filtros (usuario, acción, entidad, fecha, búsqueda libre) y exportación CSV.",
    "Hook <code>samap_audit_log</code> inyectado en CRUDs, login/logout y cambios de estado de leads.",
    "<b>Sistema de toasts no-bloqueantes</b> (<code>samap_flash_set/get/render</code>) en <code>funciones/seguridad.php</code>.",
    "<b>Reemplazo de 113 <code>alert()</code></b> por <code>samap_flash_set</code> en 58 archivos.",
    "<b>Gestión de usuarios del panel</b>: CRUD con bcrypt, validación, prevención de auto-borrado y soft-delete.",
    "<b>Migración 005</b>: columnas <code>email</code>, <code>ultimo_acceso</code> y <code>activo</code> en <code>tbl_user</code>.",
    "<b>Backup/restore</b> desde <code>admin/backup.php</code>: dump comprimido <code>.sql.gz</code> con <code>mysqldump</code> + fallback PHP puro, restore en transacciones chicas con confirmación de dos pasos.",
]))
story.append(P("Commits", h2))
story.append(commit_table([
    ('876ebd8', 'feat(audit): tabla tbl_audit_log + admin/auditoria.php'),
    ('5013b3c', 'feat(audit): hook samap_audit_log en CRUDs, auth y lead status'),
    ('519229d', 'feat(ui): sistema de toasts no-bloqueantes (samap_flash_*)'),
    ('21bc3b3', 'refactor(admin): reemplaza 113 alert() por samap_flash_set en 58 archivos'),
    ('33ec540', 'feat(admin): gestión de usuarios del panel (CRUD)'),
    ('d783569', 'feat(admin): backup y restore de la base de datos desde el panel'),
]))
story.append(PageBreak())

# ----------- 11. QA -----------
story.append(P("11. QA — Scripts de testing", h1))
story.append(P("Objetivo", h2))
story.append(P(
    "Dejar reproducibles los tests E2E que los agentes de QA corrieron durante "
    "las cuatro fases para verificar el comportamiento del panel desde fuera, "
    "vía HTTP + DB.",
    body))
story.append(P("Contenido", h2))
story.extend(B([
    "17 scripts PowerShell en <code>scripts/test-phase1/</code> (login, listados, dashboard, búsqueda, delete con/sin CSRF, papelera, etc.).",
    "6 scripts bash en <code>docker/</code> (<code>test-all.sh</code>, <code>test-listado.sh</code>, <code>test-slider.sh</code>, <code>test-delete.sh</code>, <code>test-usuarios.sh</code>, <code>lint-all.sh</code>).",
    "Script de conexión a DB y restore de slider para casos puntuales.",
]))
story.append(P("Commit", h2))
story.append(commit_table([
    ('a630332', 'test: agrega scripts de testing de Fases 1-4 (PowerShell + bash)'),
]))
story.append(PageBreak())

# ----------- 12. INVENTARIO -----------
story.append(P("12. Inventario final y métricas", h1))
story.append(P("Métricas globales", h2))
story.append(inventario_table([
    ('Commits totales en el branch', '49'),
    ('Archivos tocados (total)', '280'),
    ('Líneas añadidas', '+26.538'),
    ('Líneas eliminadas', '−4.295'),
    ('Tablas MySQL', '24'),
    ('Migraciones incrementales', '5  (001–005)'),
    ('Partials reutilizables', '3'),
    ('Archivos nuevos en admin/', '18'),
    ('Archivos modificados en admin/', '61'),
    ('alert() restantes en código de aplicación', '0'),
    ('Roles definidos', '3 (admin / editor / comercial)'),
    ('Listados con papelera de reciclaje', '9'),
    ('Listados con búsqueda + paginación + CSV', '18'),
    ('Formularios de upload refactorizados', '11'),
    ('Scripts de testing automatizado', '23'),
]))

story.append(P("Partials reutilizables creados", h2))
story.extend(B([
    "<code>admin/partials/tabla-searchable.php</code> — listados con DataTables, búsqueda, paginación y export CSV.",
    "<code>admin/partials/upload-imagen.php</code> — input de archivo con preview y validación.",
    "<code>admin/partials/papelera-toggle.php</code> — banner y toggle de vista de papelera.",
]))

story.append(P("Migraciones incrementales", h2))
story.extend(B([
    "<code>001-create-missing-tables.sql</code> — 8 tablas faltantes del esquema legacy.",
    "<code>002-create-tbl-leads.sql</code> — persistencia de leads del formulario público.",
    "<code>003-create-tbl-user-token.sql</code> — tokens de un solo uso para recuperación.",
    "<code>004-create-tbl-audit-log.sql</code> — log de auditoría con snapshots JSON.",
    "<code>005-add-user-cols.sql</code> — columnas <code>email</code>, <code>ultimo_acceso</code>, <code>activo</code> en <code>tbl_user</code>.",
]))
story.append(PageBreak())

# ----------- 13. ESTADO ACTUAL -----------
story.append(P("13. Estado actual del repositorio", h1))
story.append(P("Rama y working tree", h2))
story.append(P(
    "El branch <code>feat/rediseno-web</code> está al día con "
    "<code>origin/feat/rediseno-web</code>. Quedan en el working tree los "
    "siguientes archivos modificados sin commitear:",
    body))
story.extend(B([
    "<code>assets/css/rediseno-base.css</code>",
    "<code>assets/css/rediseno-convenios.css</code>",
    "<code>assets/css/rediseno-home.css</code>",
    "<code>admin/partials/tabla-searchable.php</code> &mdash; fix del warning <i>Cannot reinitialise DataTable</i> (cambio del <code>$table_id</code> por defecto de <code>datatable1</code> a <code>samap-tabla</code>).",
]))

story.append(P("Credenciales del seed (desarrollo)", h2))
story.append(P(
    "El seed incluye un único usuario administrador con hash MD5 legacy "
    "(<code>d0d71a1ccaa965f9c4b334d2bae558b1</code>). El login detecta el "
    "hash legacy y lo migra automáticamente a bcrypt en la primera "
    "autenticación exitosa.",
    body))
cred = Table([
    ['Usuario',     'admin'],
    ['Contraseña',  'admin123'],
    ['URL local',   'http://localhost:8081/admin/'],
], colWidths=[5*cm, 9*cm])
cred.setStyle(TableStyle([
    ('FONTNAME',    (0,0), (0,-1), 'Helvetica-Bold'),
    ('TEXTCOLOR',   (0,0), (0,-1), AZUL),
    ('FONTNAME',    (1,0), (1,-1), 'Courier'),
    ('FONTSIZE',    (0,0), (-1,-1), 10),
    ('VALIGN',      (0,0), (-1,-1), 'MIDDLE'),
    ('BACKGROUND',  (0,0), (-1,-1), FONDO),
    ('LINEBELOW',   (0,0), (-1,-1), 0.3, colors.HexColor('#D8DEE5')),
    ('LEFTPADDING', (0,0), (-1,-1), 8),
    ('RIGHTPADDING',(0,0), (-1,-1), 8),
    ('TOPPADDING',  (0,0), (-1,-1), 6),
    ('BOTTOMPADDING',(0,0), (-1,-1), 6),
]))
story.append(cred)
story.append(Spacer(1, 0.4*cm))
story.append(P(
    "<b>Importante:</b> rotar esta contraseña antes de cualquier despliegue a "
    "producción — está expuesta en el seed público y en los scripts de tests.",
    meta))

story.append(P("Próximos pasos sugeridos", h2))
story.extend(B([
    "Borrar los bloques <code>#datatable1/2/3</code> en <code>admin/app/js/app.js:148-200</code> (código muerto del template original).",
    "Encriptar a nivel de columna las PII de leads (teléfono/email) según Ley 6534/20.",
    "Sumar tests automatizados para los flujos de auditoría y backup/restore.",
    "Documentar el playbook de deploy a la VM GCP (PM2 + Nginx + certbot) en <code>docs/deploy.md</code>.",
]))

story.append(Spacer(1, 1*cm))
story.append(P(
    "<i>Fin del informe — SAMAP &middot; Rediseño del sitio público y panel "
    "administrativo &middot; branch feat/rediseno-web.</i>",
    meta))


# ============================================================================
# RENDER
# ============================================================================
def header_footer(canvas, doc):
    canvas.saveState()
    # Header
    canvas.setFillColor(AZUL)
    canvas.setFont('Helvetica-Bold', 9)
    canvas.drawString(2*cm, A4[1] - 1.2*cm, 'SAMAP — Informe de implementación')
    canvas.setFillColor(VERDE)
    canvas.drawRightString(A4[0] - 2*cm, A4[1] - 1.2*cm, 'branch feat/rediseno-web')
    canvas.setStrokeColor(VERDE)
    canvas.setLineWidth(0.5)
    canvas.line(2*cm, A4[1] - 1.4*cm, A4[0] - 2*cm, A4[1] - 1.4*cm)

    # Footer
    canvas.setFillColor(GRIS)
    canvas.setFont('Helvetica', 8)
    canvas.drawString(2*cm, 1.2*cm, date.today().strftime('%d/%m/%Y'))
    canvas.drawRightString(A4[0] - 2*cm, 1.2*cm, f'Página {doc.page}')
    canvas.restoreState()


doc = SimpleDocTemplate(
    OUT, pagesize=A4,
    leftMargin=2*cm, rightMargin=2*cm,
    topMargin=2*cm, bottomMargin=2*cm,
    title='SAMAP — Informe de implementación (branch feat/rediseno-web)',
    author='SAMAP',
)
doc.build(story, onFirstPage=header_footer, onLaterPages=header_footer)

print(f'OK -> {os.path.abspath(OUT)}')
