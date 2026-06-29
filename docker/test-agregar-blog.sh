#!/bin/bash
# Reproducir el flujo de "Guardar" en agregar-blog.php desde 0.

set -u
URL="${SAMAP_URL:-http://localhost:8081}"
C=/tmp/cookies-add-blog.txt
rm -f "$C"
PASS=0
FAIL=0

ok() { PASS=$((PASS+1)); echo "  PASS: $1"; }
ko() { FAIL=$((FAIL+1)); echo "  FAIL: $1"; }
DB() { docker exec -i samap-db mysql -uwebadmin -ps2m2p.m2st3r web_samap -N 2>/dev/null; }

# Limpiar restos de tests previos
echo "DELETE FROM tbl_blog WHERE titulo LIKE 'QA-add-test-%';" | DB

# Login
curl -s -c "$C" -b "$C" "$URL/admin/index/" -o /tmp/li.html
TOKEN=$(grep -o 'csrf_token" value="[^"]*"' /tmp/li.html | head -1 | sed 's/.*value="//;s/"$//')
curl -s -c "$C" -b "$C" -X POST \
    --data-urlencode "usuario=admin" --data-urlencode "clave=admin123" \
    --data-urlencode "csrf_token=$TOKEN" \
    "$URL/admin/index/" -o /dev/null

HTTP=$(curl -s -c "$C" -b "$C" -o /tmp/h.html -w '%{http_code}' "$URL/admin/home/")
[ "$HTTP" = "200" ] && ok "login admin" || ko "login admin (HTTP $HTTP)"

# GET agregar-blog
HTTP=$(curl -s -c "$C" -b "$C" -o /tmp/a.html -w '%{http_code}' "$URL/admin/agregar-blog/")
SIZE=$(wc -c < /tmp/a.html)
[ "$HTTP" = "200" ] && ok "GET agregar-blog HTTP 200 ($SIZE bytes)" || ko "GET agregar-blog HTTP $HTTP"

# Verifica que NO haya warnings/notices
WARN=$(grep -c 'Warning:\|Notice:\|Fatal\|Parse error\|Cannot modify' /tmp/a.html)
[ "$WARN" = "0" ] && ok "GET sin warnings/notices PHP" || { ko "GET tiene $WARN warnings"; grep -m3 'Warning:\|Notice:\|Fatal' /tmp/a.html; }

# Verifica que no este el script de codepen.io
grep -q 'static.codepen.io' /tmp/a.html && ko "script codepen.io presente" || ok "script codepen.io eliminado"

# Verifica que no este http://fonts.googleapis (Mixed Content en HTTPS)
grep -qE 'http://fonts\.googleapis' /tmp/a.html && ko "http://fonts.googleapis (Mixed Content)" || ok "fuentes con HTTPS o protocol-relative"

# Verifica que el action no genere Notice
grep -q 'action=""' /tmp/a.html && ok "action=\"\" (sin Undefined var)" || ko "action no es vacio"

# CSRF para el form
CSRF=$(grep -oE 'name="csrf_token" value="[a-f0-9]+"' /tmp/a.html | head -1 | sed -E 's/.*value="([a-f0-9]+)".*/\1/')
[ -n "$CSRF" ] && ok "CSRF token en form ($CSRF...)" || ko "CSRF AUSENTE"

# POST: crear blog (sin -L porque el Location es protocol-relative)
NEW="QA-add-test-$(date +%s)"
HTTP=$(curl -s -c "$C" -b "$C" -o /tmp/p.html -w '%{http_code}' -X POST \
    -F 'MM_insert=form2' \
    -F "csrf_token=$CSRF" \
    -F "titulo=$NEW" \
    -F 'intro=intro de prueba' \
    -F 'texto=cuerpo del blog con <p>tags</p>' \
    "$URL/admin/agregar-blog/")
[ "$HTTP" = "302" ] && ok "POST devuelve 302 Found (redirect a /blogs/)" || ko "POST HTTP $HTTP (esperaba 302)"
# Sigue la redireccion a mano (al URL sin //host)
curl -s -c "$C" -b "$C" -o /tmp/p.html -w '' "$URL/admin/blogs/"

# Sin warnings en la respuesta
WARN=$(grep -c 'Cannot modify\|Warning:\|Fatal' /tmp/p.html)
[ "$WARN" = "0" ] && ok "POST sin warnings de header" || { ko "POST tiene $WARN warnings"; grep -m3 'Cannot modify\|Warning:\|Fatal' /tmp/p.html; }

# Toast en la respuesta
grep -q 'Blog guardado' /tmp/p.html && ok "toast 'Blog guardado' presente" || ko "toast AUSENTE"

# Existe en DB
EXISTS=$(echo "SELECT COUNT(*) FROM tbl_blog WHERE titulo = '$NEW';" | DB | head -1)
[ "$EXISTS" = "1" ] && ok "blog insertado en DB" || ko "blog NO insertado (count=$EXISTS)"

# Cleanup
echo "DELETE FROM tbl_blog WHERE titulo = '$NEW';" | DB > /dev/null

echo
echo "======================================"
echo "  PASS=$PASS  FAIL=$FAIL"
echo "======================================"
[ "$FAIL" = "0" ] && exit 0 || exit 1
