<li class="nav-item">
	<a href="javascript:void(0)" class="nav-link">
	  <i class="nav-icon fa fa-users"></i>
	  <p>
		{{ __('usermgmt')['user']['sidemenu'] }}
		<i class="fas fa-angle-left right"></i>                
	  </p>
	</a>
	<ul class="nav nav-treeview">              
	  <li class="nav-item">
		<a href="{{ route('users')}}" class="pl-4 nav-link">
		  <i class="fa fa-user nav-icon"></i>
		  <p>{{ __('usermgmt')['user']['submenu_a'] }}</p>
		</a>
	  </li>                            
	</ul>
</li>