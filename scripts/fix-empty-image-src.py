"""
Evita 403 (Forbidden) cuando un registro no tiene imagen.

El admin renderiza <img src="...documentos/<subdir>/<nombre>.jpg"> tanto en
los listados como en los forms de edicion. Si <nombre> esta vacio, el src
queda apuntando al directorio (documentos/, documentos/blog/, etc.) y Apache
devuelve 403 -- el browser lo muestra como error en consola y ademas no
muestra placeholder.

Este script aplica 2 patrones de reemplazo idempotentes:

A) Listados (planes.php, blogs.php, convenios.php, ...):
   return '<td><img height="..." src="' . htmlspecialchars(...) . 'documentos/X/' . $img . '" alt=""/></td>';
   --> envuelve con $img === '' ? '<td>—</td>' : <td>...</td>'

B) Forms editar* (editarblog.php, editarplan.php, ...):
   <img width="100px" src="<?php echo $URL?>documentos/X/<?php echo $row_x['imagen']; ?>" alt=""/>
   --> envuelve con condicion: muestra placeholder cuando imagen es vacia.
"""
import glob
import os
import re

root = os.path.join(os.path.dirname(__file__), '..', 'admin')

# ----------------------------------------------------------------------------
# Patron A: listados que arman <td><img ... documentos/.../ . $img . ...> con
# concatenacion PHP. Lo reemplazamos por una expresion ternaria sobre $img.
# ----------------------------------------------------------------------------
re_listado = re.compile(
    r"return\s+'<td><img\s+height=\"(\d+)px\"\s+src=\"'\s*\.\s*"
    r"htmlspecialchars\(\$URL_BASE,\s*ENT_QUOTES,\s*'UTF-8'\)\s*\.\s*"
    r"'(documentos/[a-z]*/?)'\s*\.\s*\$img\s*\.\s*'\"\s*alt=\"\"/></td>';",
    re.IGNORECASE,
)

def replace_listado(m):
    h = m.group(1)
    path = m.group(2)
    return (
        "return $img === '' "
        "? '<td><span style=\"color:#bbb;\">—</span></td>' "
        ": '<td><img height=\"" + h + "px\" src=\"' . htmlspecialchars($URL_BASE, ENT_QUOTES, 'UTF-8') . '"
        + path + "' . $img . '\" alt=\"\"/></td>';"
    )

# ----------------------------------------------------------------------------
# Patron B: forms editar* con <img width=... src="...documentos/.../...">.
# Solo el bloque ya esta dentro de un "if ($row_X['imagen'] != \"\")"... veamos.
# En el codigo actual ya hay:
#   <?php if ($row_blog['imagen'] != "") {?>
#       <img width="100px" src="<?php echo $URL?>documentos/blog/<?php echo $row_blog['imagen']; ?>" alt=""/>
#   <?php } else {?>
#       <img width="60px" src="<?php echo $URL?>img/sin-imagen.jpg" alt=""/>
#   <?php }?>
# Asi que YA esta protegido. El 403 que ve el usuario es por OTROS lugares.
# ----------------------------------------------------------------------------

changed = []
for path in sorted(glob.glob(os.path.join(root, '*.php'))):
    with open(path, encoding='utf-8') as fh:
        txt = fh.read()
    new = re_listado.sub(replace_listado, txt)
    if new != txt:
        with open(path, 'w', encoding='utf-8') as fh:
            fh.write(new)
        changed.append(os.path.basename(path))

print(f'Listados protegidos contra <img src=\"documentos/\"> vacio: {len(changed)}')
for f in changed:
    print(f'  - {f}')
