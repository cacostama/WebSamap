$BASE = 'http://localhost:8081'
$script:Session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$lg = Invoke-WebRequest -Uri "$BASE/admin/" -WebSession $script:Session -UseBasicParsing
$tokv = ($lg.Content | Select-String -Pattern 'name="csrf_token"\s+value="([a-f0-9]+)"' -AllMatches).Matches[0].Groups[1].Value
$form = @{ usuario='admin'; clave='admin123'; csrf_token=$tokv }
Invoke-WebRequest -Uri "$BASE/admin/" -WebSession $script:Session -UseBasicParsing -Method POST -Body $form | Out-Null

$queries = @('plan','alfa','xyzqq')
foreach ($q in $queries) {
    $r = Invoke-WebRequest -Uri "$BASE/admin/buscar/?q=$q" -WebSession $script:Session -UseBasicParsing
    $b = $r.Content
    Write-Host "=== q=$q (code=$($r.StatusCode), len=$($b.Length)) ==="
    if ($b -match '<title>([^<]+)</title>') { Write-Host "  title: $($Matches[1])" }
    # Count results
    $hits = ([regex]::Matches($b, 'admin/editar[a-z]+/')).Count
    Write-Host "  result links: $hits"
    # show context around first result
    $idx = $b.IndexOf('Resultados')
    if ($idx -gt 0) {
        $s = [Math]::Max(0, $idx - 60)
        $e = [Math]::Min($b.Length, $idx + 300)
        Write-Host "  context: $($b.Substring($s, $e - $s) -replace "`n", ' ')"
    } else {
        # try to find "no se encontraron"
        $idx2 = $b.IndexOf('encontraron')
        if ($idx2 -gt 0) {
            $s = [Math]::Max(0, $idx2 - 60)
            $e = [Math]::Min($b.Length, $idx2 + 200)
            Write-Host "  no-results: $($b.Substring($s, $e - $s) -replace "`n", ' ')"
        }
    }
}
