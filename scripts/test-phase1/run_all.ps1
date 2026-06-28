#requires -Version 5.1
# ============================================================================
#  Phase 1 verification — final, single-session run
# ============================================================================

$BASE   = 'http://localhost:8081'
$LOGIN  = "$BASE/admin/"
$ADMIN  = 'admin'
$PASS   = 'admin123'

$script:Fails = @()
$script:Session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$script:LastBody = $null
$script:LastStatus = 0

function Note([string]$s, [string]$m) { Write-Host "[$s] $m" }
function OkMsg([string]$s, [string]$m)  { Write-Host "[$s] OK:   $m" -ForegroundColor Green }
function FailMsg([string]$s, [string]$m) {
    Write-Host "[$s] FAIL: $m" -ForegroundColor Red
    $script:Fails += "$s - $m"
}

function Invoke-GetBody {
    param([string]$Url)
    try {
        $r = Invoke-WebRequest -Uri $Url -WebSession $script:Session `
            -UseBasicParsing -Method GET -TimeoutSec 30 -ErrorAction Stop
        $script:LastStatus = [int]$r.StatusCode
        $script:LastBody   = $r.Content
        return $r
    } catch {
        $code = 0; $body = ''
        if ($_.Exception.Response) {
            $code = [int]$_.Exception.Response.StatusCode
            try { $body = (New-Object IO.StreamReader($_.Exception.Response.GetResponseStream())).ReadToEnd() } catch {}
        }
        $script:LastStatus = $code
        $script:LastBody   = if ($body) { $body } else { $_.Exception.Message }
        return $null
    }
}

# --------------------------------------------------------------------------
# A. Health check
# --------------------------------------------------------------------------
Write-Host "===== A. HEALTH CHECK =====" -ForegroundColor Cyan
$dc = docker compose ps
$dc | Out-Host
$db = docker exec samap-db mysql -uwebadmin -p's2m2p.m2st3r' web_samap -N -B -e "SELECT COUNT(*) FROM tbl_user;" 2>$null
$db = if ($LASTEXITCODE -ne 0) { (docker exec samap-db mysql -uwebadmin -p's2m2p.m2st3r' web_samap -N -B -e "SELECT COUNT(*) FROM tbl_user;" 2>&1 | Where-Object { $_ -match '^\d+$' }) } else { $db }
Note 'A' "DB tbl_user count = $db"
$head = (Invoke-WebRequest -Uri "$BASE/admin/" -UseBasicParsing -Method HEAD -TimeoutSec 10).StatusCode
Note 'A' "HEAD /admin/ -> $head"
docker exec samap-web bash -c "rm -f /tmp/samap_login/*.json" 2>$null | Out-Null

# --------------------------------------------------------------------------
# B. Login
# --------------------------------------------------------------------------
Write-Host ""
Write-Host "===== B. LOGIN =====" -ForegroundColor Cyan
$lg = Invoke-GetBody -Url $LOGIN
$tok = $null
if ($script:LastBody -match 'name="csrf_token"\s+value="([a-f0-9]{16,})"') { $tok = $Matches[1] }
if (-not $tok) { FailMsg 'B' "Could not extract CSRF token" }
else { Note 'B' ("CSRF token len = {0}" -f $tok.Length) }

$form = @{ usuario=$ADMIN; clave=$PASS; csrf_token=$tok }
$post = Invoke-WebRequest -Uri $LOGIN -WebSession $script:Session -UseBasicParsing -Method POST -Body $form -TimeoutSec 30
$loginCode = [int]$post.StatusCode
Note 'B' ("Login POST -> {0}" -f $loginCode)

$homeChk = Invoke-GetBody -Url "$BASE/admin/home/"
$authOk = ($script:LastStatus -eq 200) -and ($script:LastBody -match 'Escritorio')
if ($authOk) { OkMsg 'B' 'Authenticated session works (home shows Escritorio)' }
else         { FailMsg 'B' ("Auth check failed (status={0})" -f $script:LastStatus) }

# --------------------------------------------------------------------------
# C. 19 listados
# --------------------------------------------------------------------------
Write-Host ""
Write-Host "===== C. 19 LISTADOS =====" -ForegroundColor Cyan
$listados = @(
    [pscustomobject]@{ N=1;  Name='slider';       Url='/admin/slider/' },
    [pscustomobject]@{ N=2;  Name='planes';       Url='/admin/planes/' },
    [pscustomobject]@{ N=3;  Name='convenios';    Url='/admin/convenios/' },
    [pscustomobject]@{ N=4;  Name='aliados';      Url='/admin/aliados/' },
    [pscustomobject]@{ N=5;  Name='categorias';   Url='/admin/categorias/' },
    [pscustomobject]@{ N=6;  Name='servicios';    Url='/admin/servicios/' },
    [pscustomobject]@{ N=7;  Name='medicos';      Url='/admin/medicos/' },
    [pscustomobject]@{ N=8;  Name='guia';         Url='/admin/guia/' },
    [pscustomobject]@{ N=9;  Name='sanatorios';   Url='/admin/sanatorios/' },
    [pscustomobject]@{ N=10; Name='ciudad';       Url='/admin/ciudad/' },
    [pscustomobject]@{ N=11; Name='galeria';      Url='/admin/galeria/' },
    [pscustomobject]@{ N=12; Name='fechas';       Url='/admin/fechas/' },
    [pscustomobject]@{ N=13; Name='nacionalidad'; Url='/admin/nacionalidad/' },
    [pscustomobject]@{ N=14; Name='speakers';     Url='/admin/speakers/' },
    [pscustomobject]@{ N=15; Name='sponsors';     Url='/admin/sponsors/' },
    [pscustomobject]@{ N=16; Name='apoyan';       Url='/admin/apoyan/' },
    [pscustomobject]@{ N=17; Name='agenda';       Url='/admin/agenda/' },
    [pscustomobject]@{ N=18; Name='blogs';        Url='/admin/blogs/' },
    [pscustomobject]@{ N=19; Name='home';         Url='/admin/home/' }
)
$listadoTable = @()
foreach ($l in $listados) {
    Invoke-GetBody -Url "$BASE$($l.Url)" | Out-Null
    $code = $script:LastStatus
    $body = $script:LastBody
    $err  = ''
    $okRender = $true
    if ($code -ne 200) {
        $err = "NOT 200"
        $okRender = $false
    } elseif ($body -match 'Sistema de Administraci') {
        $err = 'redirected to login (session lost)'
        $okRender = $false
    } elseif ($body -match 'Fatal error.*Table .web_samap\.([a-z_]+). doesn.t exist') {
        $err = "FATAL: missing table $($Matches[1])"
        $okRender = $false
    } elseif ($body -match 'Fatal error') {
        $err = 'FATAL PHP error (unhandled)'
        $okRender = $false
    } elseif ($body -match '<b>Warning</b>:') {
        $err = "PHP Warning: $($Matches[0])"
        $okRender = $false
    } elseif ($body -match '<b>Deprecated</b>:') {
        $err = "PHP Deprecated: $($Matches[0])"
    } elseif ($body.Length -lt 1500) {
        $err = "tiny body ($($body.Length) bytes)"
        $okRender = $false
    }
    $listadoTable += [pscustomobject]@{
        N    = $l.N
        Name = $l.Name
        Url  = $l.Url
        Code = $code
        Size = $body.Length
        Note = $err
        OK   = $okRender
    }
}
$listadoTable | Format-Table -AutoSize | Out-Host

# --------------------------------------------------------------------------
# D. Delete flow on the 9 fixed listados
# --------------------------------------------------------------------------
Write-Host ""
Write-Host "===== D. DELETE FLOW ON 9 FIXED LISTADOS =====" -ForegroundColor Cyan
$fixes = @(
    @{ Name='slider';       Table='tbl_slider';        File='slider.php' },
    @{ Name='agenda';       Table='tbl_agenda_detalle';File='agenda.php' },
    @{ Name='ciudad';       Table='tbl_ciudad';        File='ciudad.php' },
    @{ Name='galeria';      Table='tbl_galeria';       File='galeria.php' },
    @{ Name='fechas';       Table='tbl_agenda';        File='fechas.php' },
    @{ Name='nacionalidad'; Table='tbl_nacionalidad';  File='nacionalidad.php' },
    @{ Name='speakers';     Table='tbl_speaker';       File='speakers.php' },
    @{ Name='sponsors';     Table='tbl_sponsor';       File='sponsors.php' },
    @{ Name='apoyan';       Table='tbl_apoyan';        File='apoyan.php' }
)
$deleteResults = @()
foreach ($f in $fixes) {
    $row = [ordered]@{
        Name    = $f.Name
        Table   = $f.Table
        TableExists = '-'
        LastId  = '-'
        CsrfInLink = '-'
        DeleteOK  = '-'
        DbAfter  = '-'
        Restored = '-'
        Note     = ''
    }
    # 1) Check table exists
    $tblChk = docker exec samap-db mysql -uwebadmin -p's2m2p.m2st3r' web_samap -N -B -e "SHOW TABLES LIKE '$($f.Table)';" 2>$null
    $tblExists = ($tblChk -and $tblChk.Trim() -eq $f.Table)
    $row.TableExists = $tblExists
    if (-not $tblExists) {
        $row.Note = "table $($f.Table) missing from DB"
        $deleteResults += [pscustomobject]$row
        continue
    }
    # 2) Check CSRF in listing delete link
    Invoke-GetBody -Url "$BASE/admin/$($f.File)" | Out-Null
    $lb = $script:LastBody
    $csrfInLink = $false
    if ($lb -match '\?id=\d+&borrar=si&csrf_token=') { $csrfInLink = $true }
    $row.CsrfInLink = $csrfInLink
    # 3) Get last id
    $qOut = docker exec samap-db mysql -uwebadmin -p's2m2p.m2st3r' web_samap -N -B -e "SELECT id FROM $($f.Table) WHERE deleted_at IS NULL ORDER BY id DESC LIMIT 1;" 2>$null
    $lastId = $null
    if ($qOut -and $qOut.Trim() -match '^\d+$') { $lastId = $qOut.Trim() }
    if (-not $lastId) {
        $qOut2 = docker exec samap-db mysql -uwebadmin -p's2m2p.m2st3r' web_samap -N -B -e "SELECT id FROM $($f.Table) ORDER BY id DESC LIMIT 1;" 2>$null
        if ($qOut2 -and $qOut2.Trim() -match '^\d+$') { $lastId = $qOut2.Trim() }
    }
    if (-not $lastId) {
        $row.Note = "no records in $($f.Table)"
        $deleteResults += [pscustomobject]$row
        continue
    }
    $row.LastId = $lastId
    # 4) Extract CSRF token from listing
    $token = $null
    if ($lb -match 'csrf_token=([a-f0-9]{16,})') { $token = $Matches[1] }
    if (-not $token) { $token = $tok }  # fall back to login token
    # 5) Issue delete (with csrf)
    $delUrl = "$BASE/admin/$($f.File)?id=$lastId&borrar=si&csrf_token=$token"
    $dr = Invoke-GetBody -Url $delUrl
    $dcode = $script:LastStatus
    $dbody = $script:LastBody
    $success = ($dcode -eq 200 -and $dbody -match "alert\('?[^)]*elimin")
    $row.DeleteOK = $success
    # 6) Check DB
    $st = docker exec samap-db mysql -uwebadmin -p's2m2p.m2st3r' web_samap -N -B -e "SELECT IFNULL(deleted_at,'NULL') FROM $($f.Table) WHERE id = $lastId;" 2>$null
    $soft = ($st -and $st.Trim() -ne 'NULL' -and $st.Trim() -ne '')
    $row.DbAfter = if ($soft) { $st.Trim() } else { 'unchanged' }
    # 7) Restore
    if ($soft) {
        $rest = "UPDATE $($f.Table) SET deleted_at = NULL WHERE id = $lastId;"
        docker exec samap-db mysql -uwebadmin -p's2m2p.m2st3r' web_samap -e $rest 2>$null | Out-Null
        $st2 = docker exec samap-db mysql -uwebadmin -p's2m2p.m2st3r' web_samap -N -B -e "SELECT IFNULL(deleted_at,'NULL') FROM $($f.Table) WHERE id = $lastId;" 2>$null
        $row.Restored = ($st2.Trim() -eq 'NULL')
    }
    # 8) Also test delete WITHOUT csrf (regression: handler should reject)
    $dr2 = Invoke-GetBody -Url "$BASE/admin/$($f.File)?id=$lastId&borrar=si"
    $reject = ($script:LastBody -match 'No se pudo eliminar')
    $row.Note = if ($reject) { "CSRF enforced" } else { "CSRF NOT enforced (vuln)" }
    $deleteResults += [pscustomobject]$row
}
$deleteResults | Format-Table -AutoSize | Out-Host

# --------------------------------------------------------------------------
# E. Cancelar button in agregar-*.php
# --------------------------------------------------------------------------
Write-Host ""
Write-Host "===== E. CANCELAR BUTTON IN agregar-*.php =====" -ForegroundColor Cyan
$agregar = @(
    'agregar-agenda','agregar-aliado','agregar-apoyan','agregar-blog',
    'agregar-categoria','agregar-ciudad','agregar-convenio','agregar-fecha',
    'agregar-fotos','agregar-galeria','agregar-guia','agregar-medico',
    'agregar-nacionalidad','agregar-plan','agregar-sanatorio','agregar-servicio',
    'agregar-slider','agregar-speaker','agregar-sponsor'
)
$cancelarResults = @()
foreach ($a in $agregar) {
    $url = "$BASE/admin/$a.php"
    Invoke-GetBody -Url $url | Out-Null
    $code = $script:LastStatus
    $body = $script:LastBody
    $cancelarType = '-'
    $hasCancelar = $false
    $isLink      = $false
    if ($body -match '<button[^>]*type="(button|submit)"[^>]*>(?:\s|<[^>]+>)*Cancelar') {
        $cancelarType = $Matches[1]
        if ($cancelarType -eq 'button') { $hasCancelar = $true }
    } elseif ($body -match '<a[^>]+class="[^"]*btn[^"]*"[^>]*>Cancelar</a>') {
        # It's an <a> link styled as button -- safe (no submit)
        $isLink = $true
        $hasCancelar = $true
    }
    # Detect Fatal error case
    $fatal = $false
    if ($body -match 'Fatal error.*Table .web_samap\.[a-z_]+. doesn.t exist') { $fatal = $true }
    $cancelarResults += [pscustomobject]@{
        Page    = "$a.php"
        Code    = $code
        Type    = $cancelarType
        Link    = $isLink
        Fatal   = $fatal
        OK      = $hasCancelar
    }
}
$cancelarResults | Format-Table -AutoSize | Out-Host

# --------------------------------------------------------------------------
# F. Dashboard
# --------------------------------------------------------------------------
Write-Host ""
Write-Host "===== F. DASHBOARD =====" -ForegroundColor Cyan
Invoke-GetBody -Url "$BASE/admin/home/" | Out-Null
$hb = $script:LastBody
$cardCount = ([regex]::Matches($hb, 'panel widget bg-')).Count
$titleOk = $hb -match 'Escritorio'
$cards12 = ($cardCount -eq 12)
Note 'F' ("Dashboard code={0}, title={1}, cards={2} (expect 12)" -f $script:LastStatus, $titleOk, $cardCount)
if ($cards12) { OkMsg 'F' 'Dashboard renders 12 metric cards' }
else          { FailMsg 'F' ("Dashboard has $cardCount cards, expected 12") }
# Click-through
Invoke-GetBody -Url "$BASE/admin/planes/" | Out-Null
$pb = $script:LastBody
$planesOk = ($script:LastStatus -eq 200) -and ($pb -notmatch 'Sistema de Administraci') -and ($pb -match 'Planes')
Note 'F' ("Click /admin/planes/ -> {0}, is login? {1}, has Planes? {2}" -f $script:LastStatus, ($pb -match 'Sistema de Administraci'), ($pb -match 'Planes'))

# --------------------------------------------------------------------------
# G. Search
# --------------------------------------------------------------------------
Write-Host ""
Write-Host "===== G. SEARCH =====" -ForegroundColor Cyan
$queries = @('plan','alfa','xyzqq')
$searchResults = @()
foreach ($q in $queries) {
    Invoke-GetBody -Url "$BASE/admin/buscar/?q=$q" | Out-Null
    $code = $script:LastStatus
    $b = $script:LastBody
    $isLogin = $b -match 'Sistema de Administraci'
    # "X resultados en Y secciones"
    $nResults = $null
    if ($b -match 'Resultados para:[^<]*<strong>"[^"]+"</strong>\s*&mdash;\s*(\d+)\s*resultados') { $nResults = [int]$Matches[1] }
    $hits = ([regex]::Matches($b, 'admin/editar[a-z]+/')).Count
    $nores = $b -match 'No se encontraron|sin resultados'
    $searchResults += [pscustomobject]@{
        Query    = $q
        Code     = $code
        IsLogin  = $isLogin
        StatedResults = if ($nResults) { $nResults } else { '?' }
        EditLinks = $hits
        NoResMsg  = $nores
    }
}
$searchResults | Format-Table -AutoSize | Out-Host

# --------------------------------------------------------------------------
# H. Profile
# --------------------------------------------------------------------------
Write-Host ""
Write-Host "===== H. PROFILE =====" -ForegroundColor Cyan
Invoke-GetBody -Url "$BASE/admin/perfil/" | Out-Null
$pb2 = $script:LastBody
$h1 = $pb2 -match 'Mi perfil'
$h2 = $pb2 -match 'Mis datos'
$h3 = $pb2 -match 'Cambiar contraseña'
Note 'H' ("Perfil code={0}, Mi perfil={1}, Mis datos={2}, Cambiar contraseña={3}" -f $script:LastStatus, $h1, $h2, $h3)
if ($h1 -and $h2 -and $h3) { OkMsg 'H' 'Profile page has all 3 expected sections' }
else                        { FailMsg 'H' 'Profile page missing expected sections' }

# --------------------------------------------------------------------------
# Summary
# --------------------------------------------------------------------------
Write-Host ""
Write-Host "===== SUMMARY =====" -ForegroundColor Cyan
Write-Host "Failures captured: $($script:Fails.Count)" -ForegroundColor $(if ($script:Fails.Count -eq 0) { 'Green' } else { 'Red' })
if ($script:Fails.Count -gt 0) {
    $script:Fails | ForEach-Object { Write-Host "  - $_" -ForegroundColor Red }
}
