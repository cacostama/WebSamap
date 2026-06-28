$BASE = 'http://localhost:8081'
$script:Session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$lg = Invoke-WebRequest -Uri "$BASE/admin/" -WebSession $script:Session -UseBasicParsing
$tokv = ($lg.Content | Select-String -Pattern 'name="csrf_token"\s+value="([a-f0-9]+)"' -AllMatches).Matches[0].Groups[1].Value
$form = @{ usuario='admin'; clave='admin123'; csrf_token=$tokv }
Invoke-WebRequest -Uri "$BASE/admin/" -WebSession $script:Session -UseBasicParsing -Method POST -Body $form | Out-Null

$r = Invoke-WebRequest -Uri "$BASE/admin/buscar/?q=alfa" -WebSession $script:Session -UseBasicParsing
$b = $r.Content
$idx = $b.IndexOf('Resultados para')
if ($idx -gt 0) {
    Write-Host $b.Substring($idx, [Math]::Min(2500, $b.Length - $idx))
}
