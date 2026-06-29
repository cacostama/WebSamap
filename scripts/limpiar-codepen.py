"""
Elimina los <script src='//static.codepen.io/...stopExecutionOnTimeout...'>
de los formularios editar/agregar del panel admin.

Este script es un artefacto del template original (pen.js de CodePen). En
produccion devuelve 500 ERR_ABORTED y ensucia la consola sin aportar nada.

Tambien deja un sourceURL=pen.js en el <script> de addContent() que es del
mismo origen -- lo dejamos porque es solo un comentario JS.
"""
import glob
import os
import re

root = os.path.join(os.path.dirname(__file__), '..', 'admin')
patterns = [os.path.join(root, 'editar*.php'), os.path.join(root, 'agregar*.php')]

# Patron multilinea: <script src='//static.codepen.io/...stopExecution...js'> ... </script>
# Acepta variantes con/sin espacios y con quoting simple/doble.
re_codepen = re.compile(
    r"\s*<script\s+src=['\"][^'\"]*static\.codepen\.io[^'\"]*stopExecutionOnTimeout[^'\"]*['\"]\s*>\s*</script>\s*",
    re.IGNORECASE | re.DOTALL,
)

changed = []
for pat in patterns:
    for path in sorted(glob.glob(pat)):
        with open(path, encoding='utf-8') as fh:
            txt = fh.read()
        new_txt = re_codepen.sub('\n\t\t', txt)
        if new_txt != txt:
            with open(path, 'w', encoding='utf-8') as fh:
                fh.write(new_txt)
            changed.append(os.path.basename(path))

print(f'Limpiados {len(changed)} archivos:')
for f in changed:
    print(f'  - {f}')
