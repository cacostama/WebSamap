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
				

						<span class="user-block-name item-text"><?php echo $_SESSION['ADM_Nombre'];?></span>
						<span class="user-block-role">
							Administrador

						</span>

						<div class="btn-group user-block-status">
							<button type="button" data-toggle="dropdown" data-play="fadeIn" data-duration="0.2" class="btn btn-xs dropdown-toggle">
								<div class="point point-success"></div>Online</button>

								</div>

						</div>
					</div>

					
					

</li>


<li class="active">
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


<li class="">
	<a href="<?php echo $URL?>admin/blogs/" title="Blog" class="has-submenu">
		<em class="fa fa-book"></em>
		<span class="item-text">Blog</span>
	</a>
</li>





<li class="nav-footer">
	<div class="nav-footer-divider"></div>

	<div class="btn-group text-center">
		<a href="<?php echo $URL?>admin/logout/">
			<button type="button" data-toggle="tooltip" data-title="Logout" class="btn btn-link">
				<em class="fa fa-sign-out text-muted"></em>
			</button>
        </a>
	</div>

</li>
</ul>
</nav>

</aside>