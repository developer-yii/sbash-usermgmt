<li class="nav-item">
	<a href="#" class="nav-link">
	  <i class="nav-icon fa fa-users"></i>
	  <p>
		{{ __('usermgmt::user.sidemenu') }}
		<i class="fas fa-angle-left right"></i>                
	  </p>
	</a>
	<ul class="nav nav-treeview">              
	  <li class="nav-item">
		<a href="{{ route('users')}}" class="nav-link">
		  <i class="fa fa-user nav-icon"></i>
		  <p>{{ __('usermgmt::user.submenu_a') }}</p>
		</a>
	  </li>                            
	</ul>
</li>