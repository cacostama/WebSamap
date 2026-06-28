#!/bin/bash
# Fresh login then test delete
rm -f /tmp/c.txt
curl -s -c /tmp/c.txt -b /tmp/c.txt http://localhost/admin/index/ -o /tmp/idx.html
TOKEN=$(grep -o 'csrf_token" value="[^"]*"' /tmp/idx.html | head -1 | sed 's/.*value="//;s/"$//')
echo "LOGIN-TOKEN=$TOKEN"
curl -s -c /tmp/c.txt -b /tmp/c.txt -X POST \
    --data-urlencode "usuario=admin" \
    --data-urlencode "clave=admin123" \
    --data-urlencode "csrf_token=$TOKEN" \
    "http://localhost/admin/index/" -o /dev/null

# Get slider page
curl -s -b /tmp/c.txt http://localhost/admin/slider/ -o /tmp/slider.html

# Extract a row's CSRF token and id
TOKEN=$(grep -oE 'csrf_token=[a-f0-9]+' /tmp/slider.html | head -1 | sed 's/csrf_token=//')
ID=$(grep -oE 'id=[0-9]+&amp;borrar=si' /tmp/slider.html | head -1 | sed 's/id=//;s/&amp;borrar=si//')
echo "ROW-TOKEN=$TOKEN"
echo "ID=$ID"

echo "--- Without CSRF ---"
curl -s -b /tmp/c.txt "http://localhost/admin/slider.php?id=$ID&borrar=si" -o /tmp/del0.html
grep -oE "alert\('[^']+'" /tmp/del0.html | head -1
echo "--- With WRONG CSRF ---"
curl -s -b /tmp/c.txt "http://localhost/admin/slider.php?id=$ID&borrar=si&csrf_token=wrongtoken123" -o /tmp/del1.html
grep -oE "alert\('[^']+'" /tmp/del1.html | head -1
echo "--- With CORRECT CSRF ---"
curl -s -b /tmp/c.txt "http://localhost/admin/slider.php?id=$ID&borrar=si&csrf_token=$TOKEN" -o /tmp/del2.html
grep -oE "alert\('[^']+'" /tmp/del2.html | head -1
