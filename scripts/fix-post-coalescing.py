"""
Defensivo: reemplaza accesos crudos $_POST['x'] por ($_POST['x'] ?? '') en
los formularios agregar-*.php y editar*.php del admin.

El bug clasico: el form no envia el campo "ciudad", el handler hace
  $ciudad = $_POST['ciudad'];
PHP emite "Undefined array key 'ciudad'" -> output al buffer -> header()
falla con "Cannot modify header information" -> el redirect post-Guardar
no se aplica y el usuario se queda en la misma pantalla.

Combinado con display_errors=0 en db.php, este fix es de cinturon y tirantes:
display_errors=0 silencia el output del Notice, y el ?? '' silencia el
Notice de raiz.

NO toca:
  - $_POST['MM_insert']    -> ya esta dentro de isset()
  - $_POST['csrf_token']   -> ya esta dentro de samap_csrf_validar()
  - apariciones que ya tengan ?? '' o un isset() previo (el regex chequea
    el sufijo inmediato).
"""
import glob
import os
import re

root = os.path.join(os.path.dirname(__file__), '..', 'admin')

# Match: $_POST['xxx']  pero NO  $_POST['xxx'] ??
# y NO  $_POST['xxx']) (cuando esta dentro de isset())
# Negative lookahead simple: que no le siga ?? o ).
re_post = re.compile(
    r"\$_POST\[\s*['\"]([A-Za-z_][A-Za-z0-9_]*)['\"]\s*\](?!\s*(\?\?|\)|=))",
)

# Tokens a saltar explicitamente (ya estan dentro de un wrapper seguro):
SKIP = {'MM_insert', 'csrf_token'}

def replace(m):
    key = m.group(1)
    if key in SKIP:
        return m.group(0)
    return f"($_POST['{key}'] ?? '')"

patterns = [os.path.join(root, 'agregar-*.php'), os.path.join(root, 'editar*.php')]

changed = []
for pat in patterns:
    for path in sorted(glob.glob(pat)):
        with open(path, encoding='utf-8') as fh:
            txt = fh.read()
        new = re_post.sub(replace, txt)
        if new != txt:
            with open(path, 'w', encoding='utf-8') as fh:
                fh.write(new)
            changed.append(os.path.basename(path))

print(f'Coalescing aplicado en {len(changed)} archivos:')
for f in changed:
    print(f'  - {f}')
