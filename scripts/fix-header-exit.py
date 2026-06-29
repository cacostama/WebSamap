"""
Recorre admin/editar*.php y admin/agregar*.php y asegura que despues de cada
header('Location: ...'); haya un exit; en la siguiente linea no vacia.

Bug que motiva el fix: si un Notice/Warning sale al output ANTES del header,
header() falla silenciosamente y el codigo sigue rederizando el HTML del form.
El usuario apreta Guardar, el UPDATE pasa en DB, pero no ve toast ni redirect.
exit; corta el flujo apenas se envia el header, evitando el problema cuando
el header efectivamente se envio.
"""
import glob
import os
import re
import sys

root = os.path.join(os.path.dirname(__file__), '..', 'admin')
patterns = [os.path.join(root, 'editar*.php'), os.path.join(root, 'agregar*.php')]

re_header = re.compile(r"header\s*\(\s*['\"]Location:", re.IGNORECASE)
re_exit   = re.compile(r"^\s*exit\s*[;\(]")
re_indent = re.compile(r"^(\s*)")

changed = []
for pat in patterns:
    for path in sorted(glob.glob(pat)):
        with open(path, encoding='utf-8') as fh:
            lines = fh.readlines()

        out = []
        modified = False
        i = 0
        while i < len(lines):
            out.append(lines[i])
            if re_header.search(lines[i]):
                # Mira la proxima linea no vacia
                j = i + 1
                while j < len(lines) and lines[j].strip() == '':
                    j += 1
                if j >= len(lines) or not re_exit.match(lines[j]):
                    indent = re_indent.match(lines[i]).group(1)
                    out.append(indent + 'exit;\n')
                    modified = True
            i += 1

        if modified:
            with open(path, 'w', encoding='utf-8') as fh:
                fh.writelines(out)
            changed.append(os.path.basename(path))

print(f'Fixed {len(changed)} archivos:')
for f in changed:
    print(f'  - {f}')
