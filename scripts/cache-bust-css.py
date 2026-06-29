"""
Agrega cache-busting query string a las referencias de admin/app/css/app.css
en todos los archivos del admin. Esto fuerza al browser a re-descargar el CSS
ignorando cualquier copia cacheada (en disco, en service workers o en proxies
como ngrok).

Version manejada via timestamp: la primera ejecucion agrega ?v=<TS>. Las
ejecuciones siguientes actualizan el TS.
"""
import glob
import os
import re
from datetime import datetime

root = os.path.join(os.path.dirname(__file__), '..', 'admin')
patterns = [
    os.path.join(root, '*.php'),
]

VERSION = datetime.now().strftime('%Y%m%d%H%M')

# Coincide con:
#   <link rel="stylesheet" href="<?php echo $URL;?>admin/app/css/app.css">
#   <link rel="stylesheet" href="<?php echo $URL;?>admin/app/css/app.css?v=2026...">
re_link = re.compile(
    r'(<link\s+rel="stylesheet"\s+href="<\?php\s+echo\s+\$URL\s*;\s*\?>admin/app/css/app\.css)(\?v=\d+)?(")',
    re.IGNORECASE,
)

changed = []
for pat in patterns:
    for path in sorted(glob.glob(pat)):
        with open(path, encoding='utf-8') as fh:
            txt = fh.read()
        new = re_link.sub(rf'\g<1>?v={VERSION}\g<3>', txt)
        if new != txt:
            with open(path, 'w', encoding='utf-8') as fh:
                fh.write(new)
            changed.append(os.path.basename(path))

print(f'Cache-busted app.css en {len(changed)} archivos con ?v={VERSION}')
for f in changed:
    print(f'  - {f}')
