"""
Genera docs/presupuesto-samap.pdf — formato de presupuesto / propuesta de
servicio para presentar al cliente. Lenguaje no tecnico, sin referencias al
repositorio. Los montos quedan en blanco para que se completen a mano.
"""
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import cm
from reportlab.lib import colors
from reportlab.lib.enums import TA_LEFT, TA_RIGHT, TA_CENTER, TA_JUSTIFY
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, PageBreak, Table, TableStyle,
    KeepTogether,
)
from datetime import date
import os

OUT = os.path.join(os.path.dirname(__file__), '..', 'docs', 'presupuesto-samap.pdf')
os.makedirs(os.path.dirname(OUT), exist_ok=True)

# Paleta SAMAP
AZUL   = colors.HexColor('#274767')
VERDE  = colors.HexColor('#6CA3AB')
NEUTRO = colors.HexColor('#2F2E2D')
GRIS   = colors.HexColor('#666666')
GRIS_L = colors.HexColor('#A9B1BA')
FONDO  = colors.HexColor('#F4F6F8')
BORDE  = colors.HexColor('#D8DEE5')

styles = getSampleStyleSheet()

title_st = ParagraphStyle('title', parent=styles['Title'],
    fontName='Helvetica-Bold', fontSize=28, leading=34,
    textColor=AZUL, alignment=TA_LEFT, spaceAfter=4)

subtitle_st = ParagraphStyle('subtitle', parent=styles['Normal'],
    fontName='Helvetica', fontSize=12, leading=16,
    textColor=VERDE, alignment=TA_LEFT, spaceAfter=18)

h1 = ParagraphStyle('h1', parent=styles['Heading1'],
    fontName='Helvetica-Bold', fontSize=16, leading=20,
    textColor=AZUL, spaceBefore=14, spaceAfter=8)

h2 = ParagraphStyle('h2', parent=styles['Heading2'],
    fontName='Helvetica-Bold', fontSize=12, leading=15,
    textColor=VERDE, spaceBefore=10, spaceAfter=4)

body = ParagraphStyle('body', parent=styles['BodyText'],
    fontName='Helvetica', fontSize=10, leading=14,
    textColor=NEUTRO, alignment=TA_JUSTIFY, spaceAfter=6)

body_l = ParagraphStyle('body_l', parent=body, alignment=TA_LEFT)

bullet = ParagraphStyle('bullet', parent=body,
    leftIndent=14, bulletIndent=2, spaceAfter=3)

meta = ParagraphStyle('meta', parent=styles['Normal'],
    fontName='Helvetica-Oblique', fontSize=9, leading=12,
    textColor=GRIS, spaceAfter=4)

td_desc = ParagraphStyle('td_desc', parent=body,
    fontSize=9.5, leading=12.5, alignment=TA_LEFT, spaceAfter=0)

td_num = ParagraphStyle('td_num', parent=td_desc, alignment=TA_RIGHT)


def P(t, st=body): return Paragraph(t, st)
def B(items, st=bullet):
    return [Paragraph(f"&bull;&nbsp;&nbsp;{x}", st) for x in items]


def itemize(rows, etapa_label):
    """
    rows = [(num, titulo, descripcion_html, alcance_html)]
    Devuelve una tabla con columnas: # | Concepto | Alcance | Monto
    """
    data = [[
        Paragraph("<b>#</b>", td_desc),
        Paragraph("<b>Concepto</b>", td_desc),
        Paragraph("<b>Alcance</b>", td_desc),
        Paragraph("<b>Monto (Gs.)</b>", td_num),
    ]]
    for n, titulo, desc, alcance in rows:
        data.append([
            Paragraph(f"<b>{n}</b>", td_desc),
            Paragraph(f"<b>{titulo}</b><br/><font size='8.5' color='#666666'>{desc}</font>", td_desc),
            Paragraph(alcance, td_desc),
            Paragraph("&nbsp;", td_num),  # se completa a mano
        ])
    # Subtotal de etapa
    data.append([
        Paragraph("", td_desc),
        Paragraph(f"<b>Subtotal {etapa_label}</b>", td_desc),
        Paragraph("", td_desc),
        Paragraph("<b>&nbsp;</b>", td_num),
    ])

    t = Table(data, colWidths=[0.9*cm, 5.6*cm, 7.5*cm, 3.0*cm], repeatRows=1)
    t.setStyle(TableStyle([
        # Header
        ('BACKGROUND',  (0,0), (-1,0), AZUL),
        ('TEXTCOLOR',   (0,0), (-1,0), colors.white),
        ('FONTNAME',    (0,0), (-1,0), 'Helvetica-Bold'),
        ('FONTSIZE',    (0,0), (-1,0), 9.5),
        # Subtotal
        ('BACKGROUND',  (0,-1), (-1,-1), VERDE),
        ('TEXTCOLOR',   (0,-1), (-1,-1), colors.white),
        ('FONTNAME',    (0,-1), (-1,-1), 'Helvetica-Bold'),
        ('LINEABOVE',   (0,-1), (-1,-1), 1.2, AZUL),
        # General
        ('VALIGN',      (0,0), (-1,-1), 'TOP'),
        ('ROWBACKGROUNDS', (0,1), (-1,-2), [colors.white, FONDO]),
        ('GRID',        (0,0), (-1,-1), 0.3, BORDE),
        ('LEFTPADDING', (0,0), (-1,-1), 6),
        ('RIGHTPADDING',(0,0), (-1,-1), 6),
        ('TOPPADDING',  (0,0), (-1,-1), 6),
        ('BOTTOMPADDING',(0,0), (-1,-1), 6),
        ('ALIGN',       (0,0), (0,-1), 'CENTER'),
        ('ALIGN',       (-1,0), (-1,-1), 'RIGHT'),
    ]))
    return t


# ============================================================================
# CONTENIDO
# ============================================================================
story = []

# ============================================================================
# PORTADA
# ============================================================================
story.append(Spacer(1, 1.5*cm))

# Encabezado
encab = Table([
    [Paragraph("PRESUPUESTO", title_st),
     Paragraph(
         f"<b>N°</b> 2026-001<br/>"
         f"<b>Fecha:</b> {date.today().strftime('%d/%m/%Y')}<br/>"
         f"<b>Validez:</b> 30 días",
         meta)],
], colWidths=[10*cm, 7*cm])
encab.setStyle(TableStyle([
    ('VALIGN', (0,0), (-1,-1), 'TOP'),
    ('ALIGN',  (1,0), (1,-1), 'RIGHT'),
    ('LEFTPADDING',  (0,0), (-1,-1), 0),
    ('RIGHTPADDING', (0,0), (-1,-1), 0),
]))
story.append(encab)

story.append(P(
    "Rediseño del sitio web institucional<br/>"
    "y modernización del panel administrativo",
    subtitle_st))

story.append(Spacer(1, 0.6*cm))

# Bloque cliente / proveedor
client = Table([
    ['CLIENTE', 'PROVEEDOR'],
    [Paragraph(
        "<b>SAMAP S.A.</b><br/>"
        "Medicina Prepaga del Sanatorio Adventista de Asunción<br/>"
        "Asunción — Paraguay",
        body_l),
     Paragraph(
        "<b>[Razón social del proveedor]</b><br/>"
        "RUC: ___________________<br/>"
        "Contacto: _______________",
        body_l)],
], colWidths=[8.5*cm, 8.5*cm])
client.setStyle(TableStyle([
    ('BACKGROUND',  (0,0), (-1,0), AZUL),
    ('TEXTCOLOR',   (0,0), (-1,0), colors.white),
    ('FONTNAME',    (0,0), (-1,0), 'Helvetica-Bold'),
    ('FONTSIZE',    (0,0), (-1,0), 9.5),
    ('ALIGN',       (0,0), (-1,0), 'LEFT'),
    ('VALIGN',      (0,0), (-1,-1), 'TOP'),
    ('GRID',        (0,0), (-1,-1), 0.3, BORDE),
    ('BACKGROUND',  (0,1), (-1,1), FONDO),
    ('LEFTPADDING', (0,0), (-1,-1), 10),
    ('RIGHTPADDING',(0,0), (-1,-1), 10),
    ('TOPPADDING',  (0,0), (-1,-1), 8),
    ('BOTTOMPADDING',(0,0), (-1,-1), 8),
]))
story.append(client)

story.append(Spacer(1, 0.8*cm))

# Resumen ejecutivo
story.append(P("Resumen ejecutivo", h1))
story.append(P(
    "El presente presupuesto detalla los trabajos realizados sobre el sitio "
    "institucional de SAMAP y su panel administrativo. El proyecto contempló "
    "dos grandes ejes:", body))
story.extend(B([
    "<b>Sitio público:</b> rediseño visual completo según la identidad de marca "
    "(paleta institucional azul y verde mar), optimización para dispositivos "
    "móviles, mejoras de SEO, cumplimiento legal y nuevas funcionalidades de "
    "descarga y consulta para visitantes.",
    "<b>Panel administrativo:</b> modernización integral pensada para que el "
    "equipo de Marketing pueda operar el sitio sin asistencia técnica. Se "
    "agregaron 4 grandes mejoras incrementales, desde funcionalidades básicas "
    "que faltaban hasta gestión completa de leads comerciales, auditoría y "
    "backups."
]))
story.append(P(
    "El trabajo se organizó en <b>nueve etapas</b> que se detallan a "
    "continuación. Cada etapa indica el alcance entregado y deja una columna "
    "de monto para completar.",
    body))

story.append(PageBreak())

# ============================================================================
# ETAPA 1 — Infraestructura
# ============================================================================
story.append(P("Etapa 1 — Preparación del entorno de trabajo", h1))
story.append(P(
    "Montaje de un entorno de desarrollo local que reproduce el servidor de "
    "producción, permitiendo probar cada cambio antes de aplicarlo al sitio "
    "real sin riesgo de interrumpir el servicio.",
    body))
story.append(Spacer(1, 0.2*cm))
story.append(itemize([
    (1, "Entorno de desarrollo local",
     "Instalación, configuración y prueba del entorno completo (servidor web, base de datos, dependencias) para trabajar de forma segura.",
     "Servidor local funcional, base de datos cargada con los datos actuales del sitio, posibilidad de revertir cualquier cambio sin afectar producción."),
], "Etapa 1"))
story.append(PageBreak())

# ============================================================================
# ETAPA 2 — Sitio público
# ============================================================================
story.append(P("Etapa 2 — Rediseño del sitio público", h1))
story.append(P(
    "Aplicación de la identidad visual de SAMAP a todo el sitio público "
    "(paleta institucional, tipografías, logotipo) según el brief entregado "
    "por el cliente. Incluye mejoras de experiencia móvil, nuevas secciones "
    "y descargas en PDF.",
    body))
story.append(Spacer(1, 0.2*cm))
story.append(itemize([
    (1, "Nuevo diseño visual",
     "Home, planes, beneficios, prestadores, blog, contacto y cotizador con la paleta institucional azul (#274767) y verde mar (#6CA3AB).",
     "Sitio público rediseñado de punta a punta respetando la identidad de marca entregada por el cliente."),
    (2, "Optimización móvil",
     "Adaptación de menú, hero, secciones internas y formularios para celulares y tablets.",
     "Navegación fluida en cualquier dispositivo; eliminación de botones duplicados y ajustes específicos por sección."),
    (3, "Sección Descuentos Exclusivos",
     "Listado de comercios y aliados con beneficios para socios, con filtros por categoría.",
     "Sección operativa con la red de comercios actual y posibilidad de incorporar nuevos aliados desde el panel."),
    (4, "Descarga de Guía Médica en PDF",
     "Generación del PDF de la guía médica con cache automático para que la descarga no esté nunca obsoleta.",
     "Botón de descarga visible y disponible en la página de prestadores."),
    (5, "Descarga de anexo por plan",
     "PDF descargable con el detalle de coberturas y presupuesto por cada plan ofrecido.",
     "Un PDF por plan, descargable desde el detalle del plan."),
    (6, "SEO, rendimiento y legales",
     "Endurecimiento de cabeceras, mejora de SEO (etiquetas, metadatos), velocidad de carga y textos de cumplimiento legal.",
     "Sitio preparado para indexación, con políticas de privacidad acordes a la Ley 6534/20 de Paraguay."),
], "Etapa 2"))
story.append(PageBreak())

# ============================================================================
# ETAPA 3 — Seguridad inicial
# ============================================================================
story.append(P("Etapa 3 — Endurecimiento de seguridad", h1))
story.append(P(
    "Cierre de las vulnerabilidades críticas detectadas en una auditoría "
    "inicial del sitio y del panel. Esta etapa es obligatoria para cumplir "
    "con la normativa de protección de datos personales y para evitar "
    "exposición de información sensible de afiliados.",
    body))
story.append(Spacer(1, 0.2*cm))
story.append(itemize([
    (1, "Credenciales fuera del repositorio",
     "Migración de las contraseñas de base de datos y servicios a un archivo de configuración separado, no accesible desde la web.",
     "Imposible acceder a las credenciales desde el navegador o desde el código fuente."),
    (2, "Protección contra inyección de código",
     "Revisión y corrección de todos los puntos donde el sitio toma datos del visitante (formularios, URLs) para prevenir ataques de inyección.",
     "Cierre verificado de las vulnerabilidades de tipo SQLi y XSS detectadas."),
    (3, "Login con protección anti-fraude",
     "Implementación de protección contra el envío de formularios falsificados (CSRF) y bloqueo automático tras 5 intentos fallidos en 15 minutos.",
     "Imposible reutilizar un enlace o iframe externo para forzar acciones en el panel; bloqueo automático ante ataques de fuerza bruta."),
    (4, "Validación real de archivos subidos",
     "Verificación del tipo de archivo según su contenido (no solo por la extensión), previniendo la subida de archivos maliciosos.",
     "Solo se aceptan imágenes válidas en los formularios de carga."),
    (5, "Formulario Trabajá con Nosotros",
     "Conexión del formulario a la base de datos, validación de campos, envío por correo al equipo de RR.HH.",
     "Postulaciones llegan por correo al área responsable y quedan registradas en el panel."),
    (6, "Roles y borrado seguro",
     "Definición de tres roles (administrador, editor, comercial) y mecanismo de papelera de reciclaje para que nada se borre por accidente.",
     "Cada usuario ve solo lo que su rol permite; los borrados son recuperables."),
], "Etapa 3"))
story.append(PageBreak())

# ============================================================================
# ETAPA 4 — Mejoras tempranas UX
# ============================================================================
story.append(P("Etapa 4 — Mejoras de experiencia del panel", h1))
story.append(P(
    "Quick wins de usabilidad para que el panel sea presentable al equipo de "
    "Marketing antes de iniciar las fases formales de modernización.",
    body))
story.append(Spacer(1, 0.2*cm))
story.append(itemize([
    (1, "Confirmaciones de borrado",
     "Diálogo de confirmación con el nombre del registro antes de eliminar cualquier elemento.",
     "Imposible borrar por accidente; el usuario siempre confirma."),
    (2, "Rol visible en el menú lateral",
     "Indicación del rol actual del usuario logueado en el sidebar.",
     "El operador sabe en todo momento con qué permisos está trabajando."),
    (3, "Estado vacío en listados",
     "Mensaje amable cuando una sección todavía no tiene registros, con botón directo para agregar el primero.",
     "Mejor experiencia para Marketing en secciones recién creadas."),
    (4, "Mensajes claros de éxito y error",
     "Etiquetado correcto de cada operación (qué se borró, qué se editó, qué falló y por qué).",
     "Marketing puede operar sin asistencia técnica para interpretar errores."),
    (5, "Rótulos de medida en imágenes",
     "Indicación clara del tamaño recomendado y máximo en cada formulario de carga.",
     "Eliminación de la duda sobre qué tamaño de imagen subir."),
], "Etapa 4"))
story.append(PageBreak())

# ============================================================================
# ETAPA 5 — Beneficios y aliados
# ============================================================================
story.append(P("Etapa 5 — Beneficios y aliados configurables", h1))
story.append(P(
    "Habilitación al equipo de Marketing para administrar la totalidad del "
    "ecosistema de beneficios, descuentos y red de comercios aliados sin "
    "necesidad de intervención técnica.",
    body))
story.append(Spacer(1, 0.2*cm))
story.append(itemize([
    (1, "Descuentos exclusivos por categoría",
     "Sistema para crear, editar y publicar descuentos agrupados por categoría (farmacias, ópticas, gastronomía, etc.).",
     "Marketing administra el listado completo de descuentos desde el panel."),
    (2, "ABM de categorías de aliados",
     "Alta, baja y modificación de categorías visibles en el sitio público.",
     "Las categorías se ordenan, renombran y activan/desactivan sin tocar código."),
    (3, "Detalle de beneficio con comercios",
     "Cada beneficio puede tener múltiples comercios asociados con logo, dirección y condiciones.",
     "Las páginas de beneficios se enriquecen y mantienen actualizadas desde el panel."),
    (4, "Migración de farmacias a aliados",
     "Reubicación del desglose de descuentos de farmacias dentro del nuevo sistema unificado.",
     "Información consistente y un único lugar para administrarla."),
], "Etapa 5"))
story.append(PageBreak())

# ============================================================================
# FASE 1 — Quick wins
# ============================================================================
story.append(P("Etapa 6 — Modernización del panel (Fase 1)", h1))
story.append(P("Quick wins de usabilidad y seguridad", h2))
story.append(P(
    "Funcionalidades básicas que el panel original no tenía y que son "
    "indispensables para una operación profesional.",
    body))
story.append(Spacer(1, 0.2*cm))
story.append(itemize([
    (1, "Tablero de inicio con métricas reales",
     "Pantalla de bienvenida que muestra cantidades reales por sección (planes, médicos, leads, etc.) y accesos directos.",
     "Marketing ve el estado del sitio al entrar, sin tener que recorrer el menú."),
    (2, "Búsqueda global",
     "Buscador único en la cabecera que encuentra contenido en todas las secciones, agrupado por tipo y con resaltado del término buscado.",
     "Encontrar un plan, un médico, un convenio o un lead toma segundos."),
    (3, "Perfil de usuario y cambio de contraseña",
     "Cada operador puede actualizar sus datos y rotar su contraseña sin pedirle al administrador.",
     "Pantalla de perfil con cambio de contraseña validado."),
    (4, "Compatibilidad con versión moderna de PHP",
     "Actualización de código incompatible con servidores actualizados.",
     "El sitio funciona en servidores con PHP 8, eliminando errores ocultos."),
    (5, "Correcciones de seguridad adicionales",
     "Cierre de cabeceras de seguridad, prevención de fuga de rutas internas, protección anti-fraude en formularios faltantes.",
     "Cobertura de seguridad completa en todos los formularios del panel."),
], "Etapa 6"))
story.append(PageBreak())

# ============================================================================
# FASE 2 — Listados
# ============================================================================
story.append(P("Etapa 7 — Modernización del panel (Fase 2)", h1))
story.append(P("Productividad en los listados", h2))
story.append(P(
    "Refactorización de las 18 pantallas de listado del panel y de los 11 "
    "formularios de carga de imágenes, agregando funcionalidades que "
    "ahorran tiempo a Marketing en su operación diaria.",
    body))
story.append(Spacer(1, 0.2*cm))
story.append(itemize([
    (1, "Búsqueda en cada listado",
     "Caja de búsqueda que filtra los resultados en tiempo real sobre cualquier columna.",
     "Aplicado a las 18 secciones del panel."),
    (2, "Paginación y ordenamiento",
     "División en páginas de tamaño configurable y orden por cualquier columna haciendo clic.",
     "Las pantallas con cientos de registros (médicos, leads, blog) dejan de ser lentas."),
    (3, "Exportación a Excel/CSV",
     "Botón de exportar el listado filtrado a un archivo abrible en Excel o Google Sheets.",
     "Marketing genera reportes ad-hoc sin pedir información a sistemas."),
    (4, "Carga de imágenes con vista previa",
     "Selector de archivo que muestra la imagen elegida antes de guardar el formulario.",
     "Aplicado a los 11 formularios donde se sube una imagen."),
    (5, "Completar la base de datos",
     "Creación de las 8 tablas que faltaban del panel original y que provocaban errores en algunas secciones.",
     "Todas las secciones del menú abren correctamente; se eliminaron las pantallas rotas."),
], "Etapa 7"))
story.append(PageBreak())

# ============================================================================
# FASE 3 — Nuevas funcionalidades
# ============================================================================
story.append(P("Etapa 8 — Modernización del panel (Fase 3)", h1))
story.append(P("Nuevas funcionalidades de gestión", h2))
story.append(P(
    "Incorporación de cuatro módulos que el panel original no tenía y que "
    "convierten al sistema en un CMS profesional comparable a herramientas "
    "comerciales del mercado.",
    body))
story.append(Spacer(1, 0.2*cm))
story.append(itemize([
    (1, "Biblioteca de medios",
     "Galería visual de todas las imágenes del sitio (más de 237) con filtros por carpeta y posibilidad de eliminar las que ya no se usan.",
     "Marketing reutiliza imágenes en lugar de subir duplicados; ahorro de espacio en disco."),
    (2, "Papelera de reciclaje",
     "Los elementos borrados van a una papelera por sección, donde se pueden restaurar o eliminar definitivamente.",
     "Implementado en 9 secciones críticas (planes, blog, médicos, sanatorios, slider, servicios, convenios, aliados, categorías)."),
    (3, "Módulo de Leads (CRM básico)",
     "Inbox de leads recibidos por el formulario de contacto y el de Trabajá con Nosotros, con filtros, cambio de estado (nuevo, contactado, cerrado, spam) y notas internas.",
     "El equipo comercial ve y trabaja todos los contactos entrantes desde un único lugar."),
    (4, "Persistencia automática de leads",
     "Cada formulario completado por un visitante queda guardado en la base, además de enviarse por correo.",
     "Nunca más se pierde un contacto por una caída del servidor de correo."),
    (5, "Recuperación de contraseña por correo",
     "Flujo completo: el usuario solicita un enlace, recibe un correo con token de un solo uso, y restablece su contraseña.",
     "Marketing no depende del administrador para recuperar su acceso."),
], "Etapa 8"))
story.append(PageBreak())

# ============================================================================
# FASE 4 — Calidad y autonomía
# ============================================================================
story.append(P("Etapa 9 — Modernización del panel (Fase 4)", h1))
story.append(P("Calidad, trazabilidad y autonomía operativa", h2))
story.append(P(
    "Cierre del ciclo de modernización: trazabilidad completa de todas las "
    "acciones, gestión de usuarios, eliminación definitiva de las ventanas "
    "emergentes intrusivas y backup/restauración de la base desde el "
    "propio panel.",
    body))
story.append(Spacer(1, 0.2*cm))
story.append(itemize([
    (1, "Auditoría completa",
     "Registro automático de todas las acciones del panel: quién hizo qué, cuándo, sobre qué registro, qué valores cambiaron.",
     "Visor con filtros por usuario, acción, fecha y exportación a Excel. Trazabilidad completa para cumplimiento normativo."),
    (2, "Notificaciones modernas (toasts)",
     "Reemplazo de las 113 ventanas emergentes molestas del panel por avisos no bloqueantes en la esquina superior, con auto-cierre.",
     "Operación más ágil y profesional; sin clics extra para descartar avisos."),
    (3, "Gestión de usuarios del panel",
     "ABM completo de operadores: alta, edición, desactivación, asignación de rol, último acceso visible.",
     "El administrador crea y gestiona usuarios sin intervención técnica."),
    (4, "Backup y restauración desde el panel",
     "Generación y descarga de un respaldo comprimido de toda la base con un clic, y restauración desde un archivo con confirmación de dos pasos.",
     "Marketing puede sacar respaldos antes de operaciones masivas y restaurar en caso de necesidad."),
    (5, "Endurecimiento adicional",
     "Migración del esquema de contraseñas a un cifrado moderno (bcrypt) realizada de forma automática y transparente en cada login.",
     "Las contraseñas se actualizan solas al estándar actual sin que el usuario haga nada."),
], "Etapa 9"))
story.append(PageBreak())

# ============================================================================
# QA
# ============================================================================
story.append(P("Etapa 10 — Aseguramiento de calidad y pruebas", h1))
story.append(P(
    "Diseño y ejecución de pruebas automatizadas que validan los flujos "
    "críticos del panel (login, listados, búsqueda, papelera, gestión de "
    "usuarios) y que quedan disponibles para correrse en cualquier momento "
    "ante futuros cambios.",
    body))
story.append(Spacer(1, 0.2*cm))
story.append(itemize([
    (1, "Pruebas automatizadas",
     "Suite de 23 scripts de prueba que reproducen las acciones más comunes del panel y verifican que el resultado sea correcto.",
     "Posibilidad de validar el sistema completo en minutos antes de cada despliegue futuro."),
], "Etapa 10"))

story.append(PageBreak())

# ============================================================================
# TOTALES
# ============================================================================
story.append(P("Resumen de inversión", h1))

resumen = Table([
    ['Etapa', 'Descripción', 'Monto (Gs.)'],
    ['1',  'Preparación del entorno de trabajo', ''],
    ['2',  'Rediseño del sitio público', ''],
    ['3',  'Endurecimiento de seguridad', ''],
    ['4',  'Mejoras de experiencia del panel', ''],
    ['5',  'Beneficios y aliados configurables', ''],
    ['6',  'Modernización del panel — Fase 1 (Quick wins)', ''],
    ['7',  'Modernización del panel — Fase 2 (Productividad)', ''],
    ['8',  'Modernización del panel — Fase 3 (Nuevas funcionalidades)', ''],
    ['9',  'Modernización del panel — Fase 4 (Calidad y autonomía)', ''],
    ['10', 'Aseguramiento de calidad y pruebas', ''],
    ['',   'SUBTOTAL', ''],
    ['',   'IVA (10 %)', ''],
    ['',   'TOTAL GENERAL', ''],
], colWidths=[1.5*cm, 11.5*cm, 4.0*cm])
resumen.setStyle(TableStyle([
    # Header
    ('BACKGROUND',  (0,0), (-1,0), AZUL),
    ('TEXTCOLOR',   (0,0), (-1,0), colors.white),
    ('FONTNAME',    (0,0), (-1,0), 'Helvetica-Bold'),
    ('ALIGN',       (-1,0), (-1,0), 'RIGHT'),
    # Body
    ('VALIGN',      (0,0), (-1,-1), 'MIDDLE'),
    ('ROWBACKGROUNDS', (0,1), (-1,-4), [colors.white, FONDO]),
    ('GRID',        (0,0), (-1,-1), 0.3, BORDE),
    ('ALIGN',       (0,0), (0,-1), 'CENTER'),
    ('ALIGN',       (-1,0), (-1,-1), 'RIGHT'),
    ('LEFTPADDING', (0,0), (-1,-1), 8),
    ('RIGHTPADDING',(0,0), (-1,-1), 8),
    ('TOPPADDING',  (0,0), (-1,-1), 7),
    ('BOTTOMPADDING',(0,0), (-1,-1), 7),
    # Subtotal
    ('BACKGROUND',  (0,-3), (-1,-3), FONDO),
    ('FONTNAME',    (0,-3), (-1,-3), 'Helvetica-Bold'),
    ('LINEABOVE',   (0,-3), (-1,-3), 1.0, AZUL),
    # IVA
    ('FONTNAME',    (0,-2), (-1,-2), 'Helvetica'),
    # Total
    ('BACKGROUND',  (0,-1), (-1,-1), AZUL),
    ('TEXTCOLOR',   (0,-1), (-1,-1), colors.white),
    ('FONTNAME',    (0,-1), (-1,-1), 'Helvetica-Bold'),
    ('FONTSIZE',    (0,-1), (-1,-1), 11),
    ('TOPPADDING',  (0,-1), (-1,-1), 10),
    ('BOTTOMPADDING',(0,-1), (-1,-1), 10),
]))
story.append(resumen)

story.append(Spacer(1, 0.6*cm))

# ============================================================================
# Condiciones
# ============================================================================
story.append(P("Condiciones comerciales", h1))
story.extend(B([
    "<b>Forma de pago:</b> a convenir (sugerencia: 40 % al inicio, 30 % en mitad de proyecto, 30 % contra entrega).",
    "<b>Plazo de entrega:</b> el trabajo descripto en este presupuesto ya se encuentra ejecutado y disponible para puesta en producción.",
    "<b>Garantía:</b> 90 días posteriores a la puesta en producción para corrección de errores no detectados durante las pruebas.",
    "<b>Capacitación:</b> incluye una sesión de capacitación al equipo de Marketing sobre el uso del panel administrativo modernizado.",
    "<b>Documentación:</b> se entrega manual de uso del panel y credenciales iniciales.",
    "<b>No incluye:</b> hosting, dominio, costos de SSL, servicios de correo SMTP de terceros, ni mantenimiento posterior a la garantía (cotizable aparte).",
]))

story.append(Spacer(1, 0.4*cm))
story.append(P("Alcance ya entregado", h2))
story.append(P(
    "Todas las funcionalidades descriptas en este presupuesto están "
    "implementadas, probadas y disponibles para validación del cliente "
    "en el entorno de pruebas convenido.",
    body))

story.append(Spacer(1, 0.8*cm))

# Firmas
firmas = Table([
    ['', ''],
    ['_________________________', '_________________________'],
    ['Por el cliente', 'Por el proveedor'],
    ['SAMAP S.A.', ''],
], colWidths=[8.5*cm, 8.5*cm])
firmas.setStyle(TableStyle([
    ('ALIGN',  (0,0), (-1,-1), 'CENTER'),
    ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
    ('FONTSIZE', (0,0), (-1,-1), 9.5),
    ('TEXTCOLOR', (0,0), (-1,-1), NEUTRO),
    ('TOPPADDING', (0,0), (-1,-1), 4),
    ('BOTTOMPADDING', (0,0), (-1,-1), 4),
    ('FONTNAME', (0,2), (-1,2), 'Helvetica-Bold'),
]))
story.append(firmas)


# ============================================================================
# RENDER
# ============================================================================
def header_footer(canvas, doc):
    canvas.saveState()
    # Header
    canvas.setFillColor(AZUL)
    canvas.setFont('Helvetica-Bold', 9)
    canvas.drawString(2*cm, A4[1] - 1.2*cm, 'SAMAP S.A. — Presupuesto')
    canvas.setFillColor(VERDE)
    canvas.drawRightString(A4[0] - 2*cm, A4[1] - 1.2*cm,
                           'Rediseño del sitio web y panel administrativo')
    canvas.setStrokeColor(VERDE)
    canvas.setLineWidth(0.5)
    canvas.line(2*cm, A4[1] - 1.4*cm, A4[0] - 2*cm, A4[1] - 1.4*cm)

    # Footer
    canvas.setStrokeColor(BORDE)
    canvas.line(2*cm, 1.5*cm, A4[0] - 2*cm, 1.5*cm)
    canvas.setFillColor(GRIS)
    canvas.setFont('Helvetica', 8)
    canvas.drawString(2*cm, 1.0*cm, f'Presupuesto N° 2026-001  ·  {date.today().strftime("%d/%m/%Y")}')
    canvas.drawRightString(A4[0] - 2*cm, 1.0*cm, f'Página {doc.page}')
    canvas.restoreState()


doc = SimpleDocTemplate(
    OUT, pagesize=A4,
    leftMargin=2*cm, rightMargin=2*cm,
    topMargin=2.2*cm, bottomMargin=2*cm,
    title='SAMAP — Presupuesto',
    author='SAMAP',
)
doc.build(story, onFirstPage=header_footer, onLaterPages=header_footer)

print(f'OK -> {os.path.abspath(OUT)}')
