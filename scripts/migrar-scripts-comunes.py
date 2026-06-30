"""
Reemplaza el bloque pesado de <script src> en los archivos del admin por el
include del partial scripts-comunes.php.

Detecta el patron tipico del template:
  - Empieza con <script src="...plugins/jquery/jquery.min.js"></script>  o
                <script src="...plugins/modernizr/modernizr.js"></script>
  - Termina con <script src="...app/js/app.js"></script>
  - Reemplaza todo el bloque por <?php include 'partials/scripts-comunes.php'; ?>

Preserva los <script> que vengan ANTES (modernizr/fastclick estan dentro del
<head>) y DESPUES (Summernote, codigo inline propio, etc).

Idempotente: si el archivo ya tiene el include, no hace nada.
"""
import glob
import os
import re

root = os.path.join(os.path.dirname(__file__), '..', 'admin')

# Patron: desde <script src="...plugins/jquery/jquery.min.js"> hasta
# <script src="...app/js/app.js?v=..."> inclusive, con todo lo de en medio.
re_block = re.compile(
    r'\s*<script\s+src="<\?php\s+echo\s+\$URL\s*;\s*\?>admin/plugins/jquery/jquery\.min\.js"\s*></script>'
    r'.*?'
    r'<script\s+src="<\?php\s+echo\s+\$URL\s*;\s*\?>admin/app/js/app\.js(\?v=\d+)?"\s*></script>',
    re.DOTALL | re.IGNORECASE,
)

# Tambien sacar referencias a modernizr y fastclick del <head> (porque el
# partial las re-incluye al final, evitando duplicacion). Solo si la pagina
# todavia las tiene.
re_modernizr_head = re.compile(
    r'\s*<script\s+src="<\?php\s+echo\s+\$URL\s*;\s*\?>admin/plugins/modernizr/modernizr\.js"\s+type="application/javascript"\s*></script>',
    re.IGNORECASE,
)
re_fastclick_head = re.compile(
    r'\s*<script\s+src="<\?php\s+echo\s+\$URL\s*;\s*\?>admin/plugins/fastclick/fastclick\.js"\s+type="application/javascript"\s*></script>',
    re.IGNORECASE,
)

INCLUDE_LINE = "\n\t<?php include 'partials/scripts-comunes.php'; ?>"

changed = []
already_done = []
for path in sorted(glob.glob(os.path.join(root, '*.php'))):
    name = os.path.basename(path)
    if name in ('header.php', 'aside.php'):
        # Estos no tienen bloque de scripts propio (son partials includidos)
        continue
    with open(path, encoding='utf-8') as fh:
        txt = fh.read()
    if 'partials/scripts-comunes.php' in txt:
        already_done.append(name)
        continue
    if not re_block.search(txt):
        continue
    new = re_block.sub(INCLUDE_LINE, txt, count=1)
    new = re_modernizr_head.sub('', new)
    new = re_fastclick_head.sub('', new)
    if new != txt:
        with open(path, 'w', encoding='utf-8') as fh:
            fh.write(new)
        changed.append(name)

print(f'Migrados a scripts-comunes: {len(changed)}')
for f in changed:
    print(f'  - {f}')
if already_done:
    print(f'Ya estaban migrados: {len(already_done)}')
