$BASE = 'http://localhost:8081'
$script:Session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$lg = Invoke-WebRequest -Uri "$BASE/admin/" -WebSession $script:Session -UseBasicParsing
$tok = ($lg.Content | Select-String -Pattern 'name="csrf_token"\s+value="([a-f0-9]+)"' -AllMatches).Matches[0].Groups[1].Value
$form = @{ usuario='admin'; clave='admin123'; csrf_token=$tok }
Invoke-WebRequest -Uri "$BASE/admin/" -WebSession $script:Session -UseBasicParsing -Method POST -Body $form | Out-Null

$files = @('agregar-agenda','agregar-fotos','agregar-speaker','agregar-categoria')
foreach ($f in $files) {
    $r = Invoke-WebRequest -Uri "$BASE/admin/$f.php" -WebSession $script:Session -UseBasicParsing
    $b = $r.Content
    Write-Host "=== $f.php ==="
    # raw context around Cancelar
    $idx = $b.IndexOf('Cancelar')
    if ($idx -gt 0) {
        $s = [Math]::Max(0, $idx - 250)
        $e = [Math]::Min($b.Length, $idx + 100)
        Write-Host "  Context: $($b.Substring($s, $e - $s))"
    } else {
        Write-Host "  No 'Cancelar' found in response"
    }
    # Regex attempt
    if ($b -match '<button[^>]*type="(button|submit)"[^>]*>\s*Cancelar') {
        Write-Host "  RegEx match: type=$($Matches[1])" -ForegroundColor Green
    } elseif ($b -match '<button[^>]*type="(button|submit)"[^>]*>Cancelar') {
        Write-Host "  RegEx match (no \s*): type=$($Matches[1])" -ForegroundColor Green
    } elseif ($b -match 'type="(button|submit)"[^>]*>[\s\n]*Cancelar') {
        Write-Host "  RegEx match (with newline): type=$($Matches[1])" -ForegroundColor Green
    } else {
        Write-Host "  No regex match" -ForegroundColor Yellow
    }
    Write-Host ""
}
