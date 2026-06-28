#!/bin/bash
# Test all 18 listados
FILES=(
    "slider:slider"
    "planes:planes"
    "convenios:convenios"
    "aliados:aliados"
    "categorias:categorias"
    "servicios:servicios"
    "medicos:medicos"
    "guia:guia"
    "sanatorios:sanatorios"
    "ciudad:ciudad"
    "galeria:galeria"
    "fechas:fechas"
    "nacionalidad:nacionalidad"
    "speakers:speakers"
    "sponsors:sponsors"
    "apoyan:apoyan"
    "agenda:agenda"
    "blogs:blogs"
)

for entry in "${FILES[@]}"; do
    FILE="${entry%%:*}"
    URL_PATH="${entry##*:}"
    bash /tmp/test-listado.sh "$FILE" "$URL_PATH"
done
