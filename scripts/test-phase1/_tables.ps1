$names = @('agenda','ciudad','fechas','galeria','nacionalidad','speakers','sponsors','apoyan')
foreach ($n in $names) {
    $p = "C:\Users\LENOVO THINKBOOK\.gemini\antigravity\scratch\WebSamapV01\WebSamap\admin\$n.php"
    Write-Host "=== $n ==="
    Select-String -Path $p -Pattern "FROM tbl_\w+|UPDATE tbl_\w+" 2>$null | Select-Object -First 2 | ForEach-Object { Write-Host "  $($_.Matches[0])" }
}
