"""
Limpia variables indefinidas en los forms del admin que generan PHP Notices:

1. action="<?php echo $editFormAction; ?>"  -> action=""
2. value="<?php echo $row_noticia['id']; ?>" -> value=""

Estos Notices envenenan el output y rompen redirects con
"Cannot modify header information - headers already sent".
"""
import glob
import os
import re

root = os.path.join(os.path.dirname(__file__), '..', 'admin')
patterns = [os.path.join(root, 'editar*.php'), os.path.join(root, 'agregar*.php')]

# action="<?php echo $editFormAction; ?>" -> action=""
re_action = re.compile(
    r'action="<\?php\s+echo\s+\$editFormAction\s*;?\s*\?>"',
    re.IGNORECASE,
)

# value="<?php echo $row_noticia['...']; ?>" -> value=""   (solo en agregar*)
re_row_noticia = re.compile(
    r'value="<\?php\s+echo\s+\$row_noticia\[[^\]]+\]\s*;?\s*\?>"',
    re.IGNORECASE,
)

changed = []
for pat in patterns:
    for path in sorted(glob.glob(pat)):
        with open(path, encoding='utf-8') as fh:
            txt = fh.read()
        new = re_action.sub('action=""', txt)
        # Solo aplica row_noticia a agregar*
        if 'agregar-' in os.path.basename(path):
            new = re_row_noticia.sub('value=""', new)
        if new != txt:
            with open(path, 'w', encoding='utf-8') as fh:
                fh.write(new)
            changed.append(os.path.basename(path))

print(f'Limpiados {len(changed)} archivos:')
for f in changed:
    print(f'  - {f}')
