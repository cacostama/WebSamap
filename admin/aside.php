<?php $currentScript = basename($_SERVER['SCRIPT_NAME'] ?? ''); ?>
<aside class="aside">

	<nav class="sidebar">
		<ul class="nav">

			<li>
				<div data-toggle="collapse-next" class="item user-block has-submenu">

					<div class="user-block-picture">
						<img src="<?php echo $URL;?>admin/app/img/user/02.jpg" alt="Avatar" width="60" height="60" class="img-thumbnail img-circle">

						<div class="user-block-status">
							<div class="point point-success point-lg"></div>
						</div>
					</div>

					<div class="user-block-info">
				

						<span class="user-block-name item-text"><?php echo htmlspecialchars((string)($_SESSION['ADM_Nombre'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
						<span class="user-block-role">
							<?php
								$rolesEtiqueta = [
									'admin'     => 'Administrador',
									'editor'    => 'Editor de contenidos',
									'comercial' => 'Comercial',
								];
								$rolActual = $_SESSION['ADM_Rol'] ?? 'admin';
								echo $rolesEtiqueta[$rolActual] ?? 'Administrador';
							?>
						</span>

						<div class="btn-group user-block-status">
							<button type="button" data-toggle="dropdown" data-play="fadeIn" data-duration="0.2" class="btn btn-xs dropdown-toggle">
								<div class="point point-success"></div>Online</button>

								</div>

						</div>
					</div>

					
					

</li>


<li class="<?php echo ($currentScript === 'home.php' || $currentScript === 'perfil.php') ? 'active' : ''; ?>">
	<a href="<?php echo $URL?>admin/home/" title="Escritorio">
		<em class="fa fa-dashboard"></em>
		<span class="item-text">Escritorio</span>
	</a>
</li>

<li class="">
	<a href="<?php echo $URL?>admin/slider/" title="Sliders" class="has-submenu">
		<em class="fa fa-caret-square-o-right"></em>
		<span class="item-text">Sliders</span>
	</a>
</li>

<li class="">
	<a href="<?php echo $URL?>admin/planes/" title="Planes" class="has-submenu">
		<em class="fa fa-umbrella"></em>
		<span class="item-text">Planes</span>
	</a>
</li>

<li class="">
	<a href="<?php echo $URL?>admin/convenios/" title="Convenios" class="has-submenu">
		<em class="fa fa-building-o"></em>
		<span class="item-text">Convenios</span>
	</a>
</li>

<li class="">
	<a href="<?php echo $URL?>admin/aliados/" title="Aliados" class="has-submenu">
		<em class="fa fa-building-o"></em>
		<span class="item-text">Aliados</span>
	</a>
</li>

<li class="">
	<a href="<?php echo $URL?>admin/categorias/" title="Categorías de aliados" class="has-submenu">
		<em class="fa fa-tags"></em>
		<span class="item-text">Categorías</span>
	</a>
</li>
<li class="">
	<a href="<?php echo $URL?>admin/servicios/" title="Servicios" class="has-submenu">
		<em class="fa fa-medkit"></em>
		<span class="item-text">Servicios</span>
	</a>
</li>
<li class="">
	<a href="<?php echo $URL?>admin/medicos/" title="Médicos" class="has-submenu">
		<em class="fa fa-user-md"></em>
		<span class="item-text">Médicos</span>
	</a>
</li>
<li class="">
	<a href="" title="Guía Médica" data-toggle="collapse-next" class="has-submenu">
		<em class="fa fa-hospital-o"></em>
		<span class="item-text">Guía Médica</span>
	</a>
	<ul class="nav collapse">
		<li class="">
			<a href="<?php echo $URL?>admin/guia/" title="Medicos" data-toggle="" class="no-submenu">
				<span class="item-text">Medicos</span>
			</a>
		</li>
		<li>
			<a href="<?php echo $URL?>admin/sanatorios/" title="Sanatorios" data-toggle="" class="no-submenu">
				<span class="item-text">Sanatorios</span>
			</a>
		</li>
        <li>
			<a href="<?php echo $URL?>admin/ciudad/" title="Ciudad" data-toggle="" class="no-submenu">
				<span class="item-text">Ciudad</span>
			</a>
		</li>
	</ul>

</li>


<li class="<?php echo ($currentScript === 'medios.php') ? 'active' : ''; ?>">
	<a href="<?php echo $URL?>admin/medios/" title="Biblioteca de medios" class="has-submenu">
		<em class="fa fa-picture-o"></em>
		<span class="item-text">Biblioteca de medios</span>
	</a>
</li>

<li class="">
	<a href="<?php echo $URL?>admin/blogs/" title="Blog" class="has-submenu">
		<em class="fa fa-book"></em>
		<span class="item-text">Blog</span>
	</a>
</li>

<?php
// Conteo de leads nuevos (solo para el badge del menu).
// Solo consultamos si hay sesion admin y la conexion $connect esta disponible
// (la pone en alcance admin/funciones/db.php via admin/header.php).
$leads_nuevos = 0;
if (isset($_SESSION['ADM_Username']) && isset($connect) && $connect instanceof mysqli) {
	@mysqli_select_db($connect, $database);
	$r = @mysqli_query($connect, "SELECT COUNT(*) AS c FROM tbl_leads WHERE estado = 'nuevo'");
	if ($r) {
		$row = mysqli_fetch_assoc($r);
		$leads_nuevos = (int)($row['c'] ?? 0);
		mysqli_free_result($r);
	}
}
?>
<li class="<?php echo ($currentScript === 'leads.php') ? 'active' : ''; ?>">
	<a href="<?php echo $URL?>admin/leads/" title="Leads" class="has-submenu">
		<em class="fa fa-inbox"></em>
		<span class="item-text">Leads <?php echo $leads_nuevos > 0 ? '<span class="badge" style="background:#f6504d;color:#fff;margin-left:6px;">' . $leads_nuevos . '</span>' : ''; ?></span>
	</a>
</li>


<?php
// Auditoria: solo admins ven el link en el sidebar.
if (samap_rol_es('admin')): ?>
<li class="<?php echo ($currentScript === 'auditoria.php') ? 'active' : ''; ?>">
	<a href="<?php echo $URL?>admin/auditoria/" title="Auditoría" class="has-submenu">
		<em class="fa fa-history"></em>
		<span class="item-text">Auditoría</span>
	</a>
</li>
<?php endif; ?>

<?php if (samap_rol_es('admin')): ?>
<li class="<?php echo ($currentScript === 'backup.php') ? 'active' : ''; ?>">
	<a href="<?php echo $URL?>admin/backup/" title="Backup de la base de datos" class="has-submenu">
		<em class="fa fa-database"></em>
		<span class="item-text">Backup</span>
	</a>
</li>
<?php endif; ?>

<?php if (samap_rol_es('admin')): ?>
<li class="<?php echo ($currentScript === 'usuarios.php' || $currentScript === 'agregar-usuario.php' || $currentScript === 'editarusuario.php') ? 'active' : ''; ?>">
	<a href="<?php echo $URL?>admin/usuarios/" title="Usuarios del panel" class="has-submenu">
		<em class="fa fa-users"></em>
		<span class="item-text">Usuarios</span>
	</a>
</li>
<?php endif; ?>





<li class="nav-footer">
	<div class="nav-footer-divider"></div>

	<div class="btn-group text-center">
		<a href="<?php echo $URL?>admin/logout/">
			<button type="button" data-toggle="tooltip" data-title="Cerrar sesión" class="btn btn-link">
				<em class="fa fa-sign-out text-muted"></em>
			</button>
        </a>
	</div>

</li>
</ul>
</nav>

</aside>