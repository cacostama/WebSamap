$BASE = 'http://localhost:8081'
$script:Session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$lg = Invoke-WebRequest -Uri "$BASE/admin/" -WebSession $script:Session -UseBasicParsing
$tokv = ($lg.Content | Select-String -Pattern 'name="csrf_token"\s+value="([a-f0-9]+)"' -AllMatches).Matches[0].Groups[1].Value
$form = @{ usuario='admin'; clave='admin123'; csrf_token=$tokv }
Invoke-WebRequest -Uri "$BASE/admin/" -WebSession $script:Session -UseBasicParsing -Method POST -Body $form | Out-Null

$slider = docker exec samap-db mysql -uwebadmin -p's2m2p.m2st3r' web_samap -N -B -e "SELECT id FROM tbl_slider WHERE deleted_at IS NULL ORDER BY id DESC LIMIT 1;" 2>$null

# Test delete for slider WITHOUT csrf -- detailed
$url = "$BASE/admin/slider.php?id=$slider&borrar=si"
$r = Invoke-WebRequest -Uri $url -WebSession $script:Session -UseBasicParsing
Write-Host "Status: $($r.StatusCode)"
Write-Host "Body:"
Write-Host $r.Content
Write-Host "---"
$st = docker exec samap-db mysql -uwebadmin -p's2m2p.m2st3r' web_samap -N -B -e "SELECT IFNULL(deleted_at,'NULL') FROM tbl_slider WHERE id = $slider;" 2>$null
Write-Host "Slider state: $st"
# Restore
docker exec samap-db mysql -uwebadmin -p's2m2p.m2st3r' web_samap -e "UPDATE tbl_slider SET deleted_at = NULL WHERE id = $slider;" 2>$null | Out-Null
