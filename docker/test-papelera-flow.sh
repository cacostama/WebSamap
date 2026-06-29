#!/bin/bash
# Test del flujo completo de papelera para sanatorios:
#   1. Login
#   2. Borrar id=1 (UPDATE deleted_at)
#   3. Ver vista de papelera (id=1 debe aparecer)
#   4. Restaurar id=1 (UPDATE deleted_at=NULL)
#   5. Verificar id=1 esta vivo de vuelta
# Tambien testea: borrar y luego borrar definitivamente otro id.

set -u
URL="${SAMAP_URL:-http://localhost:8081}"
C=/tmp/cookies-flow.txt
rm -f "$C"
PASS=0
FAIL=0

ok() { PASS=$((PASS+1)); echo "  PASS: $1"; }
ko() { FAIL=$((FAIL+1)); echo "  FAIL: $1"; }
DB() { docker exec -i samap-db mysql -uwebadmin -ps2m2p.m2st3r web_samap -N 2>/dev/null; }

# Limpieza inicial: asegura que el id=1 esta vivo
echo "UPDATE tbl_sanatorio SET deleted_at = NULL WHERE id = 1;" | DB

# Crea un sanatorio de prueba para el ciclo restaurar / borrar definitivo
echo "INSERT INTO tbl_sanatorio (id, idCiudad, nombre, direccion, telefono, estado) VALUES (999, 1, 'TEST QA Sanatorio', 'Dir test', '000', 1) ON DUPLICATE KEY UPDATE deleted_at = NULL, nombre='TEST QA Sanatorio';" | DB

# ---- 0. Login ----
curl -s -c "$C" -b "$C" "$URL/admin/index/" -o /tmp/login.html
TOKEN=$(grep -o 'csrf_token" value="[^"]*"' /tmp/login.html | head -1 | sed 's/.*value="//;s/"$//')
curl -s -c "$C" -b "$C" -X POST \
    --data-urlencode "usuario=admin" \
    --data-urlencode "clave=admin123" \
    --data-urlencode "csrf_token=$TOKEN" \
    "$URL/admin/index/" -o /dev/null

HTTP=$(curl -s -c "$C" -b "$C" -o /tmp/h.html -w '%{http_code}' "$URL/admin/home/")
[ "$HTTP" = "200" ] && ok "login admin" || ko "login admin (HTTP $HTTP)"

# ---- 1. Borrar id=1 (soft) ----
HTTP=$(curl -s -c "$C" -b "$C" -o /tmp/s.html -w '%{http_code}' "$URL/admin/sanatorios/")
DEL=$(grep -oE 'admin/sanatorios\.php\?id=1&amp;borrar=si&amp;csrf_token=[a-f0-9]+' /tmp/s.html | head -1 | sed 's/&amp;/\&/g')
[ -n "$DEL" ] && ok "GET listado y delete link encontrado" || ko "delete link NO encontrado"

curl -s -c "$C" -b "$C" -L -o /tmp/del1.html "$URL/$DEL"
DEL_OK=$(echo "SELECT deleted_at IS NOT NULL FROM tbl_sanatorio WHERE id = 1;" | DB | head -1)
[ "$DEL_OK" = "1" ] && ok "soft-delete id=1 aplicado" || ko "soft-delete id=1 NO aplicado (got '$DEL_OK')"
grep -q 'se eliminó' /tmp/del1.html && ok "toast 'se eliminó' presente" || ko "toast 'se eliminó' AUSENTE"

# ---- 2. Vista papelera ----
HTTP=$(curl -s -c "$C" -b "$C" -o /tmp/pap.html -w '%{http_code}' "$URL/admin/sanatorios/?papelera=1")
[ "$HTTP" = "200" ] && ok "GET papelera HTTP 200" || ko "GET papelera HTTP $HTTP"
grep -q 'Estás viendo la papelera' /tmp/pap.html && ok "banner papelera visible" || ko "banner papelera AUSENTE"
grep -q 'Sanatorio Adventista' /tmp/pap.html && ok "id=1 aparece en papelera" || ko "id=1 NO aparece en papelera"

# ---- 3. Restaurar id=1 ----
REST=$(grep -oE 'admin/sanatorios/\?restaurar=si&id=1&csrf_token=[a-f0-9]+' /tmp/pap.html | head -1)
[ -n "$REST" ] && ok "restaurar link encontrado" || ko "restaurar link NO encontrado"

curl -s -c "$C" -b "$C" -L -o /tmp/rest.html "$URL/$REST"
REST_OK=$(echo "SELECT deleted_at IS NULL FROM tbl_sanatorio WHERE id = 1;" | DB | head -1)
[ "$REST_OK" = "1" ] && ok "restauracion aplicada" || ko "restauracion NO aplicada (got '$REST_OK')"
grep -q 'restaurado' /tmp/rest.html && ok "toast 'restaurado' presente" || ko "toast 'restaurado' AUSENTE"

# ---- 4. Borrar definitivo del registro 999 ----
echo "UPDATE tbl_sanatorio SET deleted_at = NOW() WHERE id = 999;" | DB

HTTP=$(curl -s -c "$C" -b "$C" -o /tmp/pap2.html -w '%{http_code}' "$URL/admin/sanatorios/?papelera=1")
DEF=$(grep -oE 'admin/sanatorios/\?borrar_def=si&id=999&csrf_token=[a-f0-9]+' /tmp/pap2.html | head -1)
[ -n "$DEF" ] && ok "borrar_def link encontrado para id=999" || ko "borrar_def link NO encontrado"

curl -s -c "$C" -b "$C" -L -o /tmp/def.html "$URL/$DEF"
GONE=$(echo "SELECT COUNT(*) FROM tbl_sanatorio WHERE id = 999;" | DB | head -1)
[ "$GONE" = "0" ] && ok "borrado fisico de id=999 aplicado" || ko "id=999 sigue presente (got '$GONE')"
grep -q 'definitivamente' /tmp/def.html && ok "toast 'definitivamente' presente" || ko "toast 'definitivamente' AUSENTE"

# ---- 5. CSRF: borrado sin token debe fallar ----
echo "UPDATE tbl_sanatorio SET deleted_at = NULL WHERE id = 1;" | DB
curl -s -c "$C" -b "$C" -L -o /tmp/csrf.html "$URL/admin/sanatorios.php?id=1&borrar=si"
CSRF_OK=$(echo "SELECT deleted_at IS NULL FROM tbl_sanatorio WHERE id = 1;" | DB | head -1)
[ "$CSRF_OK" = "1" ] && ok "borrado SIN csrf_token fue rechazado" || ko "BORRADO SIN CSRF PASO (vulnerabilidad)"

# ---- 6. Modal de confirmacion en el HTML del listado ----
HTTP=$(curl -s -c "$C" -b "$C" -o /tmp/s2.html -w '%{http_code}' "$URL/admin/sanatorios/")
grep -q 'samap-confirm-overlay' /tmp/s2.html && ok "modal de confirmacion inyectado" || ko "modal NO inyectado"
grep -q 'class="samap-confirm"' /tmp/s2.html && ok "links de borrado marcados con .samap-confirm" || ko "links NO marcados"

# Resumen
echo ""
echo "======================================"
echo "  PASS=$PASS  FAIL=$FAIL"
echo "======================================"
[ "$FAIL" = "0" ] && exit 0 || exit 1
