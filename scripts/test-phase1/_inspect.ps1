#requires -Version 5.1
. "$PSScriptRoot\_lib.ps1"

$urls = @(
    'http://localhost:8081/admin/slider/',
    'http://localhost:8081/admin/home/',
    'http://localhost:8081/admin/blogs/'
)
foreach ($u in $urls) {
    $r = Invoke-GetWithHeaders -Url $u
    Write-Output "URL:    $u"
    Write-Output "STATUS: $($r.Status)"
    Write-Output "COOKIE: $($r.Headers['Set-Cookie'])"
    Write-Output "--- BODY (first 800 chars) ---"
    Write-Output ($r.Body.Substring(0, [Math]::Min(800, $r.Body.Length)))
    Write-Output "--- /BODY ---"
    Write-Output ""
}
