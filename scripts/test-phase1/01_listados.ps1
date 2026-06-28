#requires -Version 5.1
. "$PSScriptRoot\_lib.ps1"

$listados = @(
    @{ n=1;  name='slider';       url='/admin/slider/' },
    @{ n=2;  name='planes';       url='/admin/planes/' },
    @{ n=3;  name='convenios';    url='/admin/convenios/' },
    @{ n=4;  name='aliados';      url='/admin/aliados/' },
    @{ n=5;  name='categorias';   url='/admin/categorias/' },
    @{ n=6;  name='servicios';    url='/admin/servicios/' },
    @{ n=7;  name='medicos';      url='/admin/medicos/' },
    @{ n=8;  name='guia';         url='/admin/guia/' },
    @{ n=9;  name='sanatorios';   url='/admin/sanatorios/' },
    @{ n=10; name='ciudad';       url='/admin/ciudad/' },
    @{ n=11; name='galeria';      url='/admin/galeria/' },
    @{ n=12; name='fechas';       url='/admin/fechas/' },
    @{ n=13; name='nacionalidad'; url='/admin/nacionalidad/' },
    @{ n=14; name='speakers';     url='/admin/speakers/' },
    @{ n=15; name='sponsors';     url='/admin/sponsors/' },
    @{ n=16; name='apoyan';       url='/admin/apoyan/' },
    @{ n=17; name='agenda';       url='/admin/agenda/' },
    @{ n=18; name='blogs';        url='/admin/blogs/' },
    @{ n=19; name='home';         url='/admin/home/' }
)

$rows = @()
foreach ($l in $listados) {
    $url = "$BASE$($l.url)"
    $r = Invoke-GetWithHeaders -Url $url
    $body = $r.Body
    $err  = ''
    if ($r.Status -ne 200) {
        $err = 'NOT 200'
    } elseif ($body -match '(Fatal error|Parse error|Warning:|Notice:|Deprecated:|<b>Warning</b>:|<b>Fatal error</b>:|<b>Notice</b>:|<b>Parse error</b>:|<b>Deprecated</b>:)') {
        $m = $Matches[0]
        $err = "PHP error: $m"
    } elseif ($body.Length -lt 200) {
        $err = "Tiny body ($($body.Length) bytes)"
    }
    $rows += [pscustomobject]@{
        N    = $l.n
        Name = $l.name
        Url  = $l.url
        Code = $r.Status
        Size = $body.Length
        Note = $err
    }
}

$rows | Format-Table N, Name, Url, Code, Size, Note -AutoSize | Out-String
