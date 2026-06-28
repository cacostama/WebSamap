$BASE = 'http://localhost:8081'
$script:Session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$lg = Invoke-WebRequest -Uri "$BASE/admin/" -WebSession $script:Session -UseBasicParsing
$tokv = ($lg.Content | Select-String -Pattern 'name="csrf_token"\s+value="([a-f0-9]+)"' -AllMatches).Matches[0].Groups[1].Value
$form = @{ usuario='admin'; clave='admin123'; csrf_token=$tokv }
Invoke-WebRequest -Uri "$BASE/admin/" -WebSession $script:Session -UseBasicParsing -Method POST -Body $form | Out-Null

# Get a slider ID and a ciudad ID
$slider = docker exec samap-db mysql -uwebadmin -p's2m2p.m2st3r' web_samap -N -B -e "SELECT id FROM tbl_slider WHERE deleted_at IS NULL ORDER BY id DESC LIMIT 1;" 2>$null
$ciudad = docker exec samap-db mysql -uwebadmin -p's2m2p.m2st3r' web_samap -N -B -e "SELECT id FROM tbl_ciudad WHERE deleted_at IS NULL ORDER BY id DESC LIMIT 1;" 2>$null
Write-Host "slider id=$slider, ciudad id=$ciudad"

# Fetch listing pages and pull CSRF tokens
$sb = (Invoke-WebRequest -Uri "$BASE/admin/slider/" -WebSession $script:Session -UseBasicParsing).Content
$cb = (Invoke-WebRequest -Uri "$BASE/admin/ciudad/" -WebSession $script:Session -UseBasicParsing).Content
$sliderTok = $null
$ciudadTok = $null
if ($sb -match 'csrf_token=([a-f0-9]+)') { $sliderTok = $Matches[1] }
if ($cb -match 'csrf_token=([a-f0-9]+)') { $ciudadTok = $Matches[1] }
Write-Host "slider csrf: $sliderTok"
Write-Host "ciudad csrf: $ciudadTok"

# Test delete for slider WITH csrf
if ($sliderTok) {
    $url = "$BASE/admin/slider.php?id=$slider&borrar=si&csrf_token=$sliderTok"
    $r = Invoke-WebRequest -Uri $url -WebSession $script:Session -UseBasicParsing
    Write-Host "slider delete WITH csrf: code=$($r.StatusCode), alert=$($r.Content -match 'se elimin')"
}

# Test delete for slider WITHOUT csrf (should fail)
$url = "$BASE/admin/slider.php?id=$slider&borrar=si"
$r = Invoke-WebRequest -Uri $url -WebSession $script:Session -UseBasicParsing
Write-Host "slider delete WITHOUT csrf: code=$($r.StatusCode), alert=$($r.Content -match 'No se pudo eliminar')"

# Test delete for ciudad WITH csrf
$url = "$BASE/admin/ciudad.php?id=$ciudad&borrar=si&csrf_token=$sliderTok"
$r = Invoke-WebRequest -Uri $url -WebSession $script:Session -UseBasicParsing
Write-Host "ciudad delete WITH csrf: code=$($r.StatusCode)"
$st = docker exec samap-db mysql -uwebadmin -p's2m2p.m2st3r' web_samap -N -B -e "SELECT IFNULL(deleted_at,'NULL') FROM tbl_ciudad WHERE id = $ciudad;" 2>$null
Write-Host "ciudad state after delete: $st"

# Restore
docker exec samap-db mysql -uwebadmin -p's2m2p.m2st3r' web_samap -e "UPDATE tbl_ciudad SET deleted_at = NULL WHERE id = $ciudad;" 2>$null | Out-Null
$st2 = docker exec samap-db mysql -uwebadmin -p's2m2p.m2st3r' web_samap -N -B -e "SELECT IFNULL(deleted_at,'NULL') FROM tbl_ciudad WHERE id = $ciudad;" 2>$null
Write-Host "ciudad restored: $st2"

# Test delete for ciudad WITHOUT csrf
$url = "$BASE/admin/ciudad.php?id=$ciudad&borrar=si"
$r = Invoke-WebRequest -Uri $url -WebSession $script:Session -UseBasicParsing
Write-Host "ciudad delete WITHOUT csrf: code=$($r.StatusCode)"
$st = docker exec samap-db mysql -uwebadmin -p's2m2p.m2st3r' web_samap -N -B -e "SELECT IFNULL(deleted_at,'NULL') FROM tbl_ciudad WHERE id = $ciudad;" 2>$null
Write-Host "ciudad state after delete (no csrf): $st"
# Restore
docker exec samap-db mysql -uwebadmin -p's2m2p.m2st3r' web_samap -e "UPDATE tbl_ciudad SET deleted_at = NULL WHERE id = $ciudad;" 2>$null | Out-Null
