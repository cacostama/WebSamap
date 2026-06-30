<?php
// ---- Flash messages (toast no-bloqueante) ----
// Si hay un flash pendiente en sesion (seteado por samap_flash_set() antes de un
// header('Location: ...')), se renderiza aca. Aparece en el top-right de la
// pagina destino y se auto-dismiss a los 5s.
if (function_exists('samap_flash_render')) {
	echo samap_flash_render();
}
?>
<script>
// Feature 11 — "Cambios sin guardar" (unsaved changes guard).
// Detecta input/change en cualquier form.form-horizontal dentro de .main-content
// y, si el form se "ensucia", muestra un confirm nativo al cerrar/refresh la
// pestaña. Al submit, se resetea. Expone window.samapFormDirty para que Summernote
// (u otros editores) puedan marcar el form como sucio desde onChange.
(function() {
    var dirty = false;
    window.samapFormDirty = function() { return dirty; };
    window.samapFormMarkDirty = function() { dirty = true; };
    window.samapFormResetDirty = function() { dirty = false; };
    // Helper de toast usado por Sortable, AJAX endpoints, etc. Replica la
    // UI de samap_flash_render() sin tener que ir/venir de sesion.
    window.samapFlash = function(tipo, msg) {
        var bg = { success: '#4ac18e', error: '#f6504d', warning: '#ffc61d', info: '#00afd1' };
        var fg = { success: '#fff',    error: '#fff',    warning: '#333',    info: '#fff' };
        var ic = { success: 'check',   error: 'times',   warning: 'exclamation-triangle', info: 'info-circle' };
        var cont = document.createElement('div');
        cont.style.cssText = 'position:fixed;top:70px;right:20px;z-index:99999;display:flex;flex-direction:column;gap:8px;';
        cont.id = 'samap-toasts';
        var t = document.createElement('div');
        t.style.cssText = 'background:' + (bg[tipo] || '#888') + ';color:' + (fg[tipo] || '#fff') + ';padding:12px 18px;border-radius:4px;box-shadow:0 4px 12px rgba(0,0,0,.2);min-width:280px;max-width:420px;display:flex;align-items:center;gap:10px;';
        t.innerHTML = '<em class="fa fa-' + (ic[tipo] || 'info') + '" style="font-size:20px;"></em><span style="flex:1;font-size:14px;">' + msg + '</span><button type="button" style="background:transparent;border:none;color:inherit;font-size:18px;cursor:pointer;padding:0 4px;line-height:1;">&times;</button>';
        cont.appendChild(t);
        document.body.appendChild(cont);
        t.querySelector('button').addEventListener('click', function(){ t.remove(); if (!cont.childNodes.length) cont.remove(); });
        setTimeout(function(){
            t.style.transition = 'opacity 0.3s,transform 0.3s';
            t.style.opacity = '0'; t.style.transform = 'translateX(20px)';
            setTimeout(function(){ t.remove(); if (!cont.childNodes.length) cont.remove(); }, 300);
        }, 5000);
    };
    function bind() {
        var forms = document.querySelectorAll('.main-content form.form-horizontal');
        forms.forEach(function(f) {
            if (f.__samapDirtyBound) return;
            f.__samapDirtyBound = true;
            f.addEventListener('input', function() { dirty = true; });
            f.addEventListener('change', function() { dirty = true; });
            f.addEventListener('submit', function() { dirty = false; });
            // Cualquier button click también limpia: cubre "Cancelar" que
            // navega a otra URL sin hacer submit.
            f.addEventListener('click', function(e) {
                var btn = e.target.closest && e.target.closest('button, input[type=button], input[type=submit]');
                if (btn) { dirty = false; }
            });
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bind);
    } else {
        bind();
    }
    window.addEventListener('beforeunload', function(e) {
        if (dirty) {
            e.preventDefault();
            e.returnValue = 'Tenés cambios sin guardar. ¿Salir sin guardar?';
            return e.returnValue;
        }
    });
})();
</script>
<style>
/* Sortable.js - estados de drag/drop */
.samap-sortable-ghost { opacity: 0.4; background: #f0f4f8 !important; }
.samap-sortable-chosen { background: #e8eef4 !important; }
.samap-sortable-drag { background: #fff !important; box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
</style>
<nav role="navigation" class="navbar navbar-default navbar-top navbar-fixed-top">

<div class="navbar-header">
<a href="#" class="navbar-brand">
<div class="brand-logo" style="font-size:15px;">SAMAP - Medicina Prepaga</div>
<div class="brand-logo-collapsed">SAMAP</div>
</a>
</div>


<div class="nav-wrapper">

<ul class="nav navbar-nav">
<li>
<a href="#" data-toggle="aside">
<em class="fa fa-align-left"></em>
</a>
</li>
<li>
<a href="#" data-toggle="navbar-search">
<em class="fa fa-search"></em>
</a>
</li>
</ul>


<ul class="nav navbar-nav navbar-right">

<li class="dropdown">
<a href="#" data-toggle="dropdown" data-play="bounceIn" class="dropdown-toggle">
<em class="fa fa-user"></em>
</a>

<ul class="dropdown-menu">
<li>
<div class="p">
<p>Overall progress</p>
<div class="progress progress-striped progress-xs m0">
<div role="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100" style="width: 80%;" class="progress-bar progress-bar-success">
<span class="sr-only">80% Complete</span>
</div>
</div>
</div>
</li>
<li class="divider"></li>
<li><a href="<?php echo $URL; ?>admin/perfil/">Profile</a>
</li>
<li><a href="<?php echo $URL; ?>admin/logout/">Logout</a>
</li>
</ul>

</li>




</ul>

</div>


<form role="search" class="navbar-form" action="<?php echo $URL;?>admin/buscar/" method="get">
<div class="form-group has-feedback">
<input type="text" name="q" placeholder="Buscar en todo el sitio..." class="form-control" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q'], ENT_QUOTES) : ''; ?>">
<div data-toggle="navbar-search-dismiss" class="fa fa-times form-control-feedback"></div>
</div>
<button type="submit" class="hidden btn btn-default">Submit</button>
</form>

</nav>