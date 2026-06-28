#requires -Version 5.1
# Common helpers and config for the Phase 1 verification suite.
$BASE   = 'http://localhost:8081'
$LOGIN  = "$BASE/admin/"
$ADMIN  = 'admin'
$PASS   = 'admin123'

$script:Session    = $null
$script:CSRFToken  = $null
$script:LastBody   = $null
$script:LastStatus = $null

function Get-Session {
    if (-not $script:Session) {
        $script:Session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
    }
    return $script:Session
}

function Format-Headers {
    param($h)
    $parts = @()
    foreach ($k in $h.Keys) {
        $v = $h[$k] -join ','
        $parts += "$k`: $v"
    }
    return ($parts -join "`n")
}

function Invoke-Get {
    param(
        [Parameter(Mandatory = $true)][string]$Url,
        [switch]$KeepBody
    )
    $s = Get-Session
    try {
        $r = Invoke-WebRequest -Uri $Url -WebSession $s -UseBasicParsing -Method GET -TimeoutSec 30 -ErrorAction Stop
        $script:LastStatus = [int]$r.StatusCode
        if ($KeepBody) { $script:LastBody = $r.Content }
        return $r
    } catch {
        $code = 0
        if ($_.Exception.Response) { $code = [int]$_.Exception.Response.StatusCode }
        $script:LastStatus = $code
        if ($KeepBody) { $script:LastBody = $_.Exception.Message }
        return $null
    }
}

function Invoke-GetWithHeaders {
    param(
        [Parameter(Mandatory = $true)][string]$Url
    )
    $s = Get-Session
    try {
        $r = Invoke-WebRequest -Uri $Url -WebSession $s -UseBasicParsing -Method GET -TimeoutSec 30 -ErrorAction Stop
        return [pscustomobject]@{
            Status  = [int]$r.StatusCode
            Headers = $r.Headers
            Body    = $r.Content
        }
    } catch {
        $code = 0
        $hdrs = $null
        if ($_.Exception.Response) {
            $code = [int]$_.Exception.Response.StatusCode
            $hdrs = $_.Exception.Response.Headers
        }
        $body = ''
        try { $body = (New-Object IO.StreamReader($_.Exception.Response.GetResponseStream())).ReadToEnd() } catch {}
        return [pscustomobject]@{
            Status  = $code
            Headers = $hdrs
            Body    = $body
        }
    }
}

function Wait-NoRateLimit {
    # If locked, wait the remaining window (max 15 min). For tests we just warn
    # if the file exists and the caller should clear it via docker.
    $tmp = '/tmp/samap_login'
    docker exec samap-web ls $tmp 2>$null | Out-Null
    if ($LASTEXITCODE -eq 0) {
        $count = docker exec samap-web sh -c "ls /tmp/samap_login 2>/dev/null | wc -l" 2>$null
        if ($count -and $count.Trim() -gt 0) {
            Write-Warning "Rate-limit files present ($count) - consider clearing with: docker exec samap-web rm -f /tmp/samap_login/*.json"
        }
    }
}

function Test-Login {
    $s = Get-Session
    # 1) GET login page to acquire session cookie + CSRF token
    $loginGet = Invoke-GetWithHeaders -Url $LOGIN
    if ($loginGet.Status -ne 200) {
        throw "Login page returned HTTP $($loginGet.Status)"
    }
    if ($loginGet.Body -match 'name="csrf_token"\s+value="([a-f0-9]{16,})"') {
        $script:CSRFToken = $Matches[1]
    } else {
        # Fallback: look for any 64-char hex
        if ($loginGet.Body -match '([a-f0-9]{32,64})') {
            $script:CSRFToken = $Matches[1]
        } else {
            throw "Could not extract CSRF token from login page"
        }
    }

    # 2) POST credentials
    $form = @{
        usuario     = $ADMIN
        clave       = $PASS
        csrf_token  = $script:CSRFToken
    }
    try {
        $post = Invoke-WebRequest -Uri $LOGIN -WebSession $s -UseBasicParsing `
            -Method POST -TimeoutSec 30 -Body $form -ErrorAction Stop
        $code = [int]$post.StatusCode
    } catch {
        $code = 0
        if ($_.Exception.Response) { $code = [int]$_.Exception.Response.StatusCode }
    }
    return $code
}

function Test-Auth {
    $s = Get-Session
    $r = Invoke-GetWithHeaders -Url "$BASE/admin/home/"
    return ($r.Status -eq 200 -and $r.Body -match 'Escritorio')
}

function Get-CsrfFromPage {
    param([string]$Url)
    $s = Get-Session
    $r = Invoke-GetWithHeaders -Url $Url
    if ($r.Status -ne 200) { return @{ ok = $false; status = $r.Status; token = $null; body = $r.Body } }
    if ($r.Body -match 'name="csrf_token"\s+value="([a-f0-9]{16,})"') {
        return @{ ok = $true; status = 200; token = $Matches[1]; body = $r.Body }
    }
    if ($r.Body -match 'csrf_token=([a-f0-9]{16,})') {
        return @{ ok = $true; status = 200; token = $Matches[1]; body = $r.Body }
    }
    return @{ ok = $false; status = 200; token = $null; body = $r.Body }
}
