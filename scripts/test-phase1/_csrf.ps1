$names = @('slider','agenda','ciudad','fechas','galeria','nacionalidad','speakers','sponsors','apoyan')
foreach ($n in $names) {
    $p = "C:\Users\LENOVO THINKBOOK\.gemini\antigravity\scratch\WebSamapV01\WebSamap\admin\$n.php"
    Write-Host "=== $n.php ==="
    $content = Get-Content $p -Raw
    # Lines containing csrf
    $lines = $content -split "`n"
    for ($i = 0; $i -lt $lines.Count; $i++) {
        if ($lines[$i] -match 'samap_csrf_validar|csrf_token=') {
            Write-Host ("  L{0}: {1}" -f ($i+1), $lines[$i].Trim())
        }
    }
}
