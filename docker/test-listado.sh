#!/bin/bash
# Test a single listado: login, GET, check for Exportar CSV button, table id.
# Usage: bash test-listado.sh <file_path_no_ext> <url_path>

set -e
FILE="$1"
URL_PATH="$2"

rm -f /tmp/c.txt
curl -s -c /tmp/c.txt -b /tmp/c.txt "http://localhost/admin/index/" -o /tmp/idx.html
TOKEN=$(grep -o 'csrf_token" value="[^"]*"' /tmp/idx.html | head -1 | sed 's/.*value="//;s/"$//')
curl -s -c /tmp/c.txt -b /tmp/c.txt -X POST \
    --data-urlencode "usuario=admin" \
    --data-urlencode "clave=admin123" \
    --data-urlencode "csrf_token=$TOKEN" \
    "http://localhost/admin/index/" -o /dev/null

HTTP=$(curl -s -c /tmp/c.txt -b /tmp/c.txt -o /tmp/out.html -w '%{http_code}' \
    "http://localhost/admin/${URL_PATH}/")
SIZE=$(wc -c < /tmp/out.html)
EXPORT_CSV=$(grep -c 'Exportar CSV' /tmp/out.html 2>/dev/null | head -1)
DATATABLE1=$(grep -c 'id="datatable1"' /tmp/out.html 2>/dev/null | head -1)
LEGACY=$(grep -c 'do { // horizontal looper' /tmp/out.html 2>/dev/null | head -1)
ERROR=$(grep -ic 'fatal\|parse error' /tmp/out.html 2>/dev/null | head -1)
WARN=$(grep -c 'Warning:' /tmp/out.html 2>/dev/null | head -1)
NORM=$(grep -c 'Notice:' /tmp/out.html 2>/dev/null | head -1)

# Force integers
EXPORT_CSV=${EXPORT_CSV:-0}
DATATABLE1=${DATATABLE1:-0}
LEGACY=${LEGACY:-0}
ERROR=${ERROR:-0}
WARN=${WARN:-0}
NORM=${NORM:-0}

echo "[$FILE] HTTP=$HTTP size=$SIZE csvbtn=$EXPORT_CSV datatable1=$DATATABLE1 legacy=$LEGACY warnings=$WARN notices=$NORM errors=$ERROR"

if [ "$WARN" -gt 0 ] || [ "$ERROR" -gt 0 ] || [ "$NORM" -gt 0 ]; then
    grep -m 3 'Warning:\|Fatal\|Error\|Notice:' /tmp/out.html | head -3
fi
