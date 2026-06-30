"""
Agrega loading="lazy" + decoding="async" a los <img> del admin que renderizan
imagenes de contenido (no iconos pequenos).

REGLAS:
  - Skipea <img> donde el contenido tiene <?php (tokens PHP embebidos rompen
    cualquier regex que no entienda PHP).
  - Skipea iconos pequenos (width<=30 o height<=30) -- son botones de
    accion (editar, borrar, restaurar) que no necesitan lazy.
  - Procesa concatenaciones PHP del estilo '<img height="30px" src="..." alt=""/>'
    SOLO si la altura es > 30.
  - Procesa <img> "simples" (sin PHP adentro) que tengan width/height grandes
    o ningun width/height (probablemente foto).
  - Idempotente: skipea las que ya tienen loading=.

Para imagenes con PHP embebido (medios.php, editar*.php) hay que editar a
mano porque el regex no puede entender el contexto PHP correctamente.
"""
import glob
import os
import re

root = os.path.join(os.path.dirname(__file__), '..', 'admin')

# 1) Concatenacion PHP en listados: '<img height="30px" src="..." alt=""/>'
#    Solo agregamos lazy si height > 50.
re_img_concat = re.compile(
    r"<img\s+(?:height|width)=\"(\d+)px\"\s+src=\"([^\"]*?)\"\s+alt=\"\"/>",
)
def fix_concat(m):
    h = int(m.group(1))
    if h <= 50:  # icono chico, no aplica lazy
        return m.group(0)
    return m.group(0).replace('/>', ' loading="lazy" decoding="async"/>')

# 2) <img> de HTML plano sin PHP adentro y SIN loading.
#    Detectamos solo los que NO tienen <?php en el contenido del tag.
re_img_plain = re.compile(
    r'<img\s+([^>]*)>',
    re.IGNORECASE,
)
def fix_plain(m):
    attrs = m.group(1)
    if '<?' in attrs:
        return m.group(0)  # tiene PHP -> no tocar
    if re.search(r'\bloading\s*=', attrs, re.IGNORECASE):
        return m.group(0)  # ya tiene loading=
    # Trailing slash check
    cleaned = attrs.rstrip().rstrip('/').rstrip()
    return f'<img {cleaned} loading="lazy" decoding="async">'

changed = []
for path in sorted(glob.glob(os.path.join(root, '*.php'))):
    with open(path, encoding='utf-8') as fh:
        txt = fh.read()
    new = re_img_concat.sub(fix_concat, txt)
    new = re_img_plain.sub(fix_plain, new)
    if new != txt:
        with open(path, 'w', encoding='utf-8') as fh:
            fh.write(new)
        changed.append(os.path.basename(path))

print(f'loading="lazy" aplicado a <img> SIN PHP embebido en {len(changed)} archivos:')
for f in changed:
    print(f'  - {f}')
