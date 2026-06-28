#!/bin/bash
# Get slider count and schema
docker exec samap-db mysql -uwebadmin -ps2m2p.m2st3r web_samap -e "SELECT id, nombre, imagen FROM tbl_slider WHERE deleted_at IS NULL LIMIT 3;" 2>&1
echo "---"
docker exec samap-db mysql -uwebadmin -ps2m2p.m2st3r web_samap -e "DESCRIBE tbl_slider;" 2>&1
