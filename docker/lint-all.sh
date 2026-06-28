#!/bin/bash
for f in slider planes convenios aliados categorias servicios medicos guia sanatorios ciudad galeria fechas nacionalidad speakers sponsors apoyan agenda blogs; do
    RES=$(php -l "/var/www/html/admin/$f.php" 2>&1)
    if echo "$RES" | grep -q "No syntax"; then
        echo "OK $f"
    else
        echo "FAIL $f: $RES"
    fi
done
