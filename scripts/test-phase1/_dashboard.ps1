$BASE = 'http://localhost:8081'
$script:Session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$lg = Invoke-WebRequest -Uri "$BASE/admin/" -WebSession $script:Session -UseBasicParsing
$tokv = ($lg.Content | Select-String -Pattern 'name="csrf_token"\s+value="([a-f0-9]+)"' -AllMatches).Matches[0].Groups[1].Value
$form = @{ usuario='admin'; clave='admin123'; csrf_token=$tokv }
Invoke-WebRequest -Uri "$BASE/admin/" -WebSession $script:Session -UseBasicParsing -Method POST -Body $form | Out-Null

$r = Invoke-WebRequest -Uri "$BASE/admin/home/" -WebSession $script:Session -UseBasicParsing
$b = $r.Content

# Count panel-primary blocks (metric cards)
$panels = ([regex]::Matches($b, 'panel widget bg-')).Count
Write-Host "panel widget blocks: $panels"

# Count section links
$sections = @('planes','blog','medicos','servicios','slider','convenios','aliados','guiamedica','sanatorio','ciudad','galeria','fechas','nacionalidad','speakers','sponsors','apoyan','agenda')
foreach ($s in $sections) {
    $n = ([regex]::Matches($b, "admin/$s/")).Count
    Write-Host ("  {0}: {1} link(s)" -f $s, $n)
}

# Check metric numbers
if ($b -match 'Planes.*?<span class="badge[^>]+>(\d+)') { Write-Host "Planes count: $($Matches[1])" }
# Count "0", "1", "2" badges
$nums = ($b | Select-String -Pattern '(\d+)\s*</span>\s*</div>\s*</a>' -AllMatches).Matches
Write-Host "Count badges: $($nums.Count)"
