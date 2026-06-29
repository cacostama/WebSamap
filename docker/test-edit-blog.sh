#!/bin/bash
# Reproducir el flujo de "Guardar" en editarblog.php
# Caso reportado: usuario apreta Guardar y "no le deja" (sin feedback).

set -u
URL="${SAMAP_URL:-http://localhost:8081}"
C=/tmp/cookies-blog.txt
rm -f "$C"

DB() { docker exec -i samap-db mysql -uwebadmin -ps2m2p.m2st3r web_samap -N 2>/dev/null; }

echo "=== Estado INICIAL del blog id=1 ==="
echo "SELECT id, LEFT(titulo,40) AS titulo, LEFT(intro,40) AS intro, LEFT(imagen,40) AS imagen FROM tbl_blog WHERE id = 1;" | DB

# ---- Login ----
echo
echo "=== Login admin ==="
curl -s -c "$C" -b "$C" "$URL/admin/index/" -o /tmp/login.html
TOKEN=$(grep -o 'csrf_token" value="[^"]*"' /tmp/login.html | head -1 | sed 's/.*value="//;s/"$//')
curl -s -c "$C" -b "$C" -X POST \
    --data-urlencode "usuario=admin" \
    --data-urlencode "clave=admin123" \
    --data-urlencode "csrf_token=$TOKEN" \
    "$URL/admin/index/" -o /dev/null
HTTP=$(curl -s -c "$C" -b "$C" -o /tmp/h.html -w '%{http_code}' "$URL/admin/home/")
echo "Login -> home HTTP $HTTP"

# ---- GET editar blog ----
echo
echo "=== GET /admin/editarblog/cod/1/ ==="
HTTP=$(curl -s -c "$C" -b "$C" -o /tmp/e.html -w '%{http_code}' "$URL/admin/editarblog/cod/1/")
SIZE=$(wc -c < /tmp/e.html)
echo "HTTP $HTTP size=$SIZE bytes"
grep -m3 'Warning:\|Fatal\|Notice:\|Parse error' /tmp/e.html | head -3 || true

# Extraer CSRF del form
CSRF=$(grep -oE 'name="csrf_token" value="[a-f0-9]+"' /tmp/e.html | head -1 | sed -E 's/.*value="([a-f0-9]+)".*/\1/')
echo "CSRF token del form de edicion: ${CSRF:0:16}..."

ACT=$(grep -oE 'form[^>]*action="[^"]*"' /tmp/e.html | head -1)
echo "Action del form: $ACT"

# Existe el boton Guardar?
grep -q '>Guardar<' /tmp/e.html && echo "PASS: boton Guardar presente" || echo "FAIL: boton Guardar AUSENTE"

# ---- POST con cambio menor: tocar el titulo ----
echo
echo "=== POST update titulo ==="
NEW="QA-test-$(date +%s)"
HTTP=$(curl -s -c "$C" -b "$C" -o /tmp/post.html -w '%{http_code}' -X POST \
    -F "MM_insert=form2" \
    -F "csrf_token=$CSRF" \
    -F "id=1" \
    -F "titulo=$NEW" \
    -F "intro=intro-de-prueba" \
    -F "texto=texto-de-prueba" \
    -F "imagen=" \
    -L \
    "$URL/admin/editarblog/cod/1/")
echo "POST + redirect -> HTTP $HTTP"
grep -m3 'Warning:\|Fatal\|Notice:\|Parse error\|Cannot modify' /tmp/post.html | head -3 || true
grep -q 'Blog guardado' /tmp/post.html && echo "PASS: toast 'Blog guardado' presente" || echo "WARN: toast no presente"

# ---- Verificar DB ----
echo
echo "=== Estado FINAL del blog id=1 ==="
echo "SELECT id, LEFT(titulo,40) AS titulo, LEFT(intro,40) AS intro FROM tbl_blog WHERE id = 1;" | DB
RES=$(echo "SELECT titulo = '$NEW' FROM tbl_blog WHERE id = 1;" | DB | head -1)
[ "$RES" = "1" ] && echo "PASS: titulo actualizado en DB" || echo "FAIL: titulo NO actualizado (got '$RES', esperaba '1')"

# ---- Restaurar ----
echo "UPDATE tbl_blog SET titulo='¡Descansa!', intro='intro-de-prueba' WHERE id = 1;" | DB > /dev/null
echo "(restaurado para no contaminar)"
