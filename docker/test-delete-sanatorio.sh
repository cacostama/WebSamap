#!/bin/bash
# Reproducir paso a paso el flujo de borrado de un sanatorio.
# Loguea, captura el delete URL de la tabla, lo dispara, observa que pasa.

set -u
URL="http://localhost"
C=/tmp/cookies-sanat.txt
rm -f "$C"

DB() { docker exec -i samap-db mysql -uwebadmin -ps2m2p.m2st3r web_samap 2>/dev/null; }

echo "=== Estado INICIAL ==="
echo "SELECT id, LEFT(nombre,50) AS nombre, deleted_at FROM tbl_sanatorio ORDER BY id DESC LIMIT 3;" | DB

# ---- 0. Login ----
echo
echo "=== Login admin ==="
curl -s -c "$C" -b "$C" "$URL/admin/index/" -o /tmp/login.html
TOKEN=$(grep -o 'csrf_token" value="[^"]*"' /tmp/login.html | head -1 | sed 's/.*value="//;s/"$//')
echo "CSRF token de login: ${TOKEN:0:16}..."

curl -s -c "$C" -b "$C" -X POST \
    --data-urlencode "usuario=admin" \
    --data-urlencode "clave=admin123" \
    --data-urlencode "csrf_token=$TOKEN" \
    "$URL/admin/index/" -o /dev/null

HTTP=$(curl -s -c "$C" -b "$C" -o /tmp/home.html -w '%{http_code}' "$URL/admin/home/")
echo "Home tras login: HTTP $HTTP"

# ---- 1. GET listado de sanatorios ----
echo
echo "=== GET /admin/sanatorios/ ==="
HTTP=$(curl -s -c "$C" -b "$C" -o /tmp/s.html -w '%{http_code}' "$URL/admin/sanatorios/")
SIZE=$(wc -c < /tmp/s.html)
echo "HTTP $HTTP  size=$SIZE bytes"
grep -m1 -E 'Warning:|Fatal|Notice:|Parse error|Argument #' /tmp/s.html | head -3

# Capturar un delete URL real de la tabla
DEL_LINK=$(grep -oE 'admin/sanatorios\.php\?id=[0-9]+[^"]*borrar=si[^"]*' /tmp/s.html | head -1)
echo "Delete link encontrado: $DEL_LINK"

if [ -z "$DEL_LINK" ]; then
    echo "ERROR: no se encontro link de borrado en el HTML"
    grep -c 'samap-tabla\|datatable\|tbody' /tmp/s.html
    exit 1
fi

# Reemplazar &amp; por & (el href en HTML tiene escape correcto, el browser lo desescapa)
DEL_LINK_RAW="$(echo "$DEL_LINK" | sed 's/&amp;/\&/g')"
DEL_URL="${URL}/${DEL_LINK_RAW}"
echo "Disparando: $DEL_URL"

# ---- 2. GET delete URL ----
echo
echo "=== Disparando borrado ==="
curl -s -c "$C" -b "$C" -o /tmp/del.html -w "HTTP=%{http_code}  redirect=%{redirect_url}  size=%{size_download}\n" -L \
    "$DEL_URL"
echo "--- snippet de respuesta ---"
grep -m3 'samap-toast\|Warning:\|Fatal\|Notice:\|Parse error' /tmp/del.html | head -3
echo

# ---- 3. Estado FINAL en DB ----
echo "=== Estado FINAL ==="
echo "SELECT id, LEFT(nombre,50) AS nombre, deleted_at FROM tbl_sanatorio ORDER BY id DESC LIMIT 3;" | DB
echo
echo "SELECT COUNT(*) total, COUNT(deleted_at) borrados FROM tbl_sanatorio;" | DB
