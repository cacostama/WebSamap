$BASE = 'http://localhost:8081'
$script:Session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$lg = Invoke-WebRequest -Uri "$BASE/admin/" -WebSession $script:Session -UseBasicParsing
$tok = ($lg.Content | Select-String -Pattern 'name="csrf_token"\s+value="([a-f0-9]+)"' -AllMatches).Matches[0].Groups[0].Value
$tokv = ($lg.Content | Select-String -Pattern 'name="csrf_token"\s+value="([a-f0-9]+)"' -AllMatches).Matches[0].Groups[1].Value
$form = @{ usuario='admin'; clave='admin123'; csrf_token=$tokv }
Invoke-WebRequest -Uri "$BASE/admin/" -WebSession $script:Session -UseBasicParsing -Method POST -Body $form | Out-Null

$r = Invoke-WebRequest -Uri "$BASE/admin/ciudad/" -WebSession $script:Session -UseBasicParsing
$b = $r.Content
Write-Host "Length: $($b.Length)"
# Look for csrf_token in the listing
$matches_found = $b | Select-String -Pattern 'csrf_token=[a-f0-9]+' -AllMatches
Write-Host "csrf_token=xxx matches: $($matches_found.Matches.Count)"
if ($matches_found.Matches.Count -gt 0) {
    $matches_found.Matches | Select-Object -First 3 | ForEach-Object { Write-Host "  $($_.Value)" }
}
# look for delete link
$del = $b | Select-String -Pattern '\?id=\d+&borrar' -AllMatches
Write-Host "Delete link matches: $($del.Matches.Count)"
if ($del.Matches.Count -gt 0) {
    $del.Matches | Select-Object -First 2 | ForEach-Object { Write-Host "  $($_.Value)" }
}
