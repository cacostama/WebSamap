$BASE = 'http://localhost:8081'
$script:Session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$lg = Invoke-WebRequest -Uri "$BASE/admin/" -WebSession $script:Session -UseBasicParsing
$tokv = ($lg.Content | Select-String -Pattern 'name="csrf_token"\s+value="([a-f0-9]+)"' -AllMatches).Matches[0].Groups[1].Value
$form = @{ usuario='admin'; clave='admin123'; csrf_token=$tokv }
Invoke-WebRequest -Uri "$BASE/admin/" -WebSession $script:Session -UseBasicParsing -Method POST -Body $form | Out-Null

$r = Invoke-WebRequest -Uri "$BASE/admin/buscar/?q=plan" -WebSession $script:Session -UseBasicParsing
$b = $r.Content
# print the body starting from "Resultados de busqueda"
$idx = $b.IndexOf('Resultados de')
if ($idx -gt 0) {
    Write-Host "--- body from 'Resultados de busqueda' ---"
    Write-Host $b.Substring($idx, [Math]::Min(3500, $b.Length - $idx))
}
