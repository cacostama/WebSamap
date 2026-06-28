#!/bin/bash
# Login then GET slider
rm -f /tmp/c.txt
curl -s -c /tmp/c.txt -b /tmp/c.txt http://localhost/admin/index/ -o /tmp/idx.html
TOKEN=$(grep -o 'csrf_token" value="[^"]*"' /tmp/idx.html | head -1 | sed 's/.*value="//;s/"$//')
echo "TOKEN=$TOKEN"
curl -s -c /tmp/c.txt -b /tmp/c.txt -X POST -d "usuario=admin&clave=admin123&csrf_token=$TOKEN" http://localhost/admin/index/ -o /tmp/post.html -w 'LOGIN HTTP %{http_code}\n'
grep -oE 'window.location[^<]*' /tmp/post.html | head -1
curl -s -c /tmp/c.txt -b /tmp/c.txt http://localhost/admin/slider/ -o /tmp/slider.html -w 'SLIDER HTTP %{http_code} size=%{size_download}\n'
echo "--- contains 'Exportar CSV'? ---"
grep -c 'Exportar CSV' /tmp/slider.html
echo "--- contains datatable1? ---"
grep -c 'id="datatable1"' /tmp/slider.html
echo "--- contains editar.png? ---"
grep -c 'editar.png' /tmp/slider.html
echo "--- first 80 lines ---"
head -80 /tmp/slider.html
