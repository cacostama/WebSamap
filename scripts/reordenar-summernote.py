"""
En los archivos del admin con Summernote, el <script src="summernote.min.js">
viene DESPUES del <script>$(document).ready(...summernote...)</script>. En el
template original eso "andaba de carambola" porque jQuery se cargaba muy
tarde y el ready se disparaba cuando summernote ya estaba en memoria.

Despues de migrar a scripts-comunes.php, jQuery se carga antes -> el ready
corre mas temprano y summernote.min.js aun no esta -> "summernote is not a
function".

Fix: mover el <script src="...summernote.min.js"> arriba del bloque que lo
usa. Operacion idempotente -- si ya esta arriba, no toca nada.
"""
import glob
import os
import re

root = os.path.join(os.path.dirname(__file__), '..', 'admin')

# El script src del summernote tiene URL fija de cdnjs.
SCRIPT_TAG = "<script src='https://cdnjs.cloudflare.com/ajax/libs/summernote/0.6.6/summernote.min.js'></script>"

# Detecta cualquier variante (con comillas simples o dobles).
re_script = re.compile(
    r'\s*<script\s+src=[\'"]https://cdnjs\.cloudflare\.com/ajax/libs/summernote/[^\'"]*[\'"]\s*></script>',
    re.IGNORECASE,
)

# Detecta el bloque que invoca .summernote() en $(document).ready
re_ready_block = re.compile(
    r'<script[^>]*>\s*\$\(document\)\.ready\([^<]*\.summernote\([^<]*?\);?\s*\}\);?\s*</script>',
    re.IGNORECASE | re.DOTALL,
)

changed = []
for path in sorted(glob.glob(os.path.join(root, '*.php'))):
    with open(path, encoding='utf-8') as fh:
        txt = fh.read()
    if 'summernote.min.js' not in txt:
        continue

    # Posiciones: si el script src aparece DESPUES del ready, hay que mover.
    m_script = re_script.search(txt)
    m_ready  = re_ready_block.search(txt)
    if not m_script or not m_ready:
        continue
    if m_script.start() < m_ready.start():
        continue  # ya esta en el orden correcto

    # Sacar el script src del lugar viejo.
    nuevo = re_script.sub('', txt, count=1)
    # Insertar el script src JUSTO antes del bloque ready.
    pos = m_ready.start()
    # Re-buscar el bloque ready en el texto sin el script src (posiciones cambiaron)
    m_ready2 = re_ready_block.search(nuevo)
    if not m_ready2:
        continue
    pos = m_ready2.start()
    # Encontramos el inicio de la linea (preservar indentacion)
    line_start = nuevo.rfind('\n', 0, pos) + 1
    indent = nuevo[line_start:pos]
    nuevo = nuevo[:line_start] + indent + SCRIPT_TAG + '\n' + nuevo[line_start:]

    with open(path, 'w', encoding='utf-8') as fh:
        fh.write(nuevo)
    changed.append(os.path.basename(path))

print(f'Reordenados: {len(changed)}')
for f in changed:
    print(f'  - {f}')
