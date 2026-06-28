#requires -Version 5.1
$BASE   = 'http://localhost:8081'
$LOGIN  = "$BASE/admin/"
$ADMIN  = 'admin'
$PASS   = 'admin123'

$script:Session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$lg = Invoke-WebRequest -Uri $LOGIN -WebSession $script:Session -UseBasicParsing
$tok = ($lg.Content | Select-String -Pattern 'name="csrf_token"\s+value="([a-f0-9]+)"' -AllMatches).Matches[0].Groups[1].Value
$form = @{ usuario=$ADMIN; clave=$PASS; csrf_token=$tok }
Invoke-WebRequest -Uri $LOGIN -WebSession $script:Session -UseBasicParsing -Method POST -Body $form | Out-Null

$urls = @(
    '/admin/galeria/',
    '/admin/fechas/',
    '/admin/agenda/',
    '/admin/nacionalidad/',
    '/admin/speakers/',
    '/admin/sponsors/',
    '/admin/apoyan/',
    '/admin/slider/',
    '/admin/ciudad/'
)
foreach ($u in $urls) {
    $r = Invoke-WebRequest -Uri "$BASE$u" -WebSession $script:Session -UseBasicParsing
    Write-Host "==== $u ($($r.StatusCode), $($r.Content.Length) bytes) ===="
    $body = $r.Content
    # look for php error markers
    $errors = @()
    if ($body -match 'Fatal error|Parse error|Warning:|Notice:|Deprecated:') {
        $errors += $Matches[0]
    }
    if ($body -match 'Table .web_samap\.[a-z_]+. doesn.t exist') {
        $errors += "MISSING TABLE: $Matches[0]"
    }
    if ($body -match 'no se pudo eliminar') { $errors += "DELETE WARNING: $Matches[0]" }
    if ($errors.Count -gt 0) {
        Write-Host "  ISSUES: $($errors -join ' | ')" -ForegroundColor Yellow
        # print a slice around the issue
        $idx = $body.IndexOf("Fatal error")
        if ($idx -lt 0) { $idx = $body.IndexOf("Warning:") }
        if ($idx -lt 0) { $idx = $body.IndexOf("Notice:") }
        if ($idx -lt 0) { $idx = $body.IndexOf("Deprecated:") }
        if ($idx -ge 0) {
            $s = [Math]::Max(0, $idx - 60)
            $e = [Math]::Min($body.Length, $idx + 200)
            Write-Host "  Snippet:" -ForegroundColor Yellow
            Write-Host "  $($body.Substring($s, $e - $s))" -ForegroundColor Yellow
        }
    } else {
        # Find title
        if ($body -match '<title>([^<]+)</title>') { Write-Host "  Title: $($Matches[1])" }
        # Check for empty-state row
        if ($body -match 'no hay registros cargados') { Write-Host "  EMPTY STATE detected" }
        # First row of body
        $sliced = $body.Substring([Math]::Max(0, $body.Length - 600))
        Write-Host "  Tail: $sliced"
    }
    Write-Host ""
}
