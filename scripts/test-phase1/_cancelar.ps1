$files = @('agregar-agenda','agregar-fotos','agregar-speaker','agregar-categoria')
foreach ($f in $files) {
    $p = "C:\Users\LENOVO THINKBOOK\.gemini\antigravity\scratch\WebSamapV01\WebSamap\admin\$f.php"
    Write-Host "=== $f.php ==="
    if (Test-Path $p) {
        $content = Get-Content $p -Raw
        # Find context around Cancelar
        $idx = $content.IndexOf('ancelar')
        if ($idx -gt 0) {
            $s = [Math]::Max(0, $idx - 200)
            $e = [Math]::Min($content.Length, $idx + 200)
            Write-Host "  Snippet: $($content.Substring($s, $e - $s))"
        } else {
            Write-Host "  No 'ancelar' found"
        }
    } else {
        Write-Host "  FILE NOT FOUND"
    }
    Write-Host ""
}
