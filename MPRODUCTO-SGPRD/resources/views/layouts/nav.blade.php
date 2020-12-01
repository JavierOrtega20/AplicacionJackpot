<nav class="navbar-default navbar-static-side" role="navigation">
    <div class="sidebar-collapse">
        <ul class="nav metismenu" id="side-menu">
            <li class="nav-header">
                <div class="dropdown profile-element"> <span>
                        <img alt="Banplus"  style="visibility: hidden;" src="{{asset('img/Logo_Meritop_white.svg')}}" width="100px" />

                         </span>
                    @php

                        use App\Models\miem_come;
                        use App\Models\miem_ban;
                        use App\Models\comercios;

						
                        $query = miem_come::select(DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.razon_social ELSE comercios.razon_social || ' (' || comercios.nombre_sucursal || ')' END as razon_social"))->join('comercios','comercios.id','miem_come.fk_id_comercio')->where('miem_come.fk_id_miembro',Auth::user()->id)->first();

                        $query1 = miem_ban::select('bancos.descripcion')->join('bancos','bancos.id','miem_ban.fk_id_banco')->where('miem_ban.fk_dni_miembro',Auth::user()->id)->first();
						
						

                    @endphp

                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <span class="clear"> <span class="block m-t-xs"> <strong class="font-bold">{{Auth::user()->first_name}} {{Auth::user()->last_name}}</strong>
                        </span>
                            <span class="text-muted text-xs block">
                            @foreach (Auth::user()->roles as $v)
                                {{$v->display_name}}
                            @endforeach
                            <b class="caret"></b>
                            </span>
                        </span>
                        <span class="clear">
                            <strong class="font-bold">
                                @if (!empty($query->razon_social))
                                    {{$query->razon_social}}
                                @elseif (!empty($query1->descripcion))
                                    {{$query1->descripcion}}
                                @endif
                            </strong>
                        </span>
                    </a>
                    <ul class="dropdown-menu animated fadeInRight m-t-xs">
                        <li><a href="#" class="preventUnsave">Perfil</a></li>
                    </ul>
                </div>
                <div class="logo-element preventUnsave">
                    <img  src="{{asset('img/banplusWIcon.png')}}" width="30px" />
                </div>
            </li>
            <li>
                <a href="{{ url('home') }}" class="preventUnsave"><i class="fa fa-th-large"></i> <span class="nav-label">Panel </span></a>
            </li>
            @permission('transacciones-list')
            <li>
                <a href="{{ url('transacciones') }}" class="preventUnsave"><i class="fa fa-credit-card"></i> <span class="nav-label">Transacciones </span></a>
            </li>
            @endpermission
            @permission('comercio-list')
            <li>
                <a href="{{ url('comercios') }}" class="preventUnsave"><i class="fa fa-diamond"></i> <span class="nav-label">Comercios </span></a>
            </li>
            @endpermission
            @permission('banco-list')
            <li>
                <a href="{{ url('bancos') }}" class="preventUnsave"><i class="fa fa-university"></i> <span class="nav-label">Bancos </span></a>
            </li>
            @endpermission

            @php
            foreach (Auth::user()->roles as $v){
                $rolUser = $v->id;
            }
			
			$posee_gift = false;
			
			if($rolUser == 3)
			{
				$gift_card = miem_come::select('emisores.id','emisores.cod_emisor')
				->join('comercios','comercios.id','miem_come.fk_id_comercio')
				->join('emisores','emisores.rif','comercios.rif')
				->where('miem_come.fk_id_miembro',Auth::user()->id)
				->first();
				
				if($gift_card )
				{
					$posee_gift = true;
				}
			}
            @endphp

            @permission('user-list')
            <li>
                <a href="{{ url('users') }}" class="preventUnsave"><i class="fa fa-users"></i> <span class="nav-label">
                              @if($rolUser =='1' || $rolUser =='2')
                                Usuarios
                              @else
                                Clientes
                              @endif </span></a>
            </li>
            @endpermission
            @permission('role-list')
            <li>
                <a href="{{ url('roles') }}" class="preventUnsave"><i class="fa fa-users"></i> <span class="nav-label">Perfiles </span></a>
            </li>
            @endpermission
            @permission('reportes-list')
            <li>
                <a href="#" class="preventUnsave"><i class="fa fa-bar-chart-o"></i> <span class="nav-label">Reportes </span><span class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    @permission('reporte-consolidado')
                        <li>
                            <a href="{{ url('transacciones/reports_preview') }}" class="preventUnsave">
                            Reporte Consolidado
                            </a>
                        </li>
                    @endpermission
                    @permission('totalizado-clientes')
                        <li>
                            <a href="{{ url('users/reports') }}" class="preventUnsave">
                            Reporte Totalizado por Clientes
                            </a>
                        </li>
                    @endpermission
                    @permission('totalizado-comercio')
                        <li>
                            <a href="{{ url('comercios/report_tl_comercios') }}" class="preventUnsave">
                            Reporte Totalizado por Comercios
                        </a>
                        </li>
                    @endpermission
                    @permission('domiciliacion-liquidacion')
                        <li>
                            <a href="{{ url('transacciones/reports_liq_comercios') }}" class="preventUnsave">
                            Liquidación de Comercios y Domiciliación de Clientes
                            </a>
                        </li>
                    @endpermission
                    @permission('limites-disponibles')
                        <li>
                            <a href="{{ url('reporte/LimitesDisponibles') }}" class="preventUnsave">
                            Reporte Limites y Disponibles
                            </a>
                        </li>
                    @endpermission
                     @if($rolUser =='6')
                    <li>
                        <a href="{{ url('Contratos') }}" class="preventUnsave">
                            Reporte Contratos
                        </a>
                    </li>
                    @endif
                </ul>
            </li>
            @endpermission

             @if($rolUser =='1' || $rolUser =='2' || $rolUser =='6')
            @permission('totalizado-clientes')
            <li>
                <a href="{{ url('list/monedas') }}" class="preventUnsave"><i class="fa fa-money"></i> <span class="nav-label">Parametrización </span></a>
            </li>
            @endpermission
            @endif

            @permission('carga-list')
            <li>
                <a href="#" class="preventUnsave">
                    <i class="fa fa-upload"></i>
                    <span class="nav-label">Cargas</span>
                    <span class="fa arrow"></span>
                </a>
                <ul class="nav nav-second-level collapse">
                   <!--  @permission('carga-pagos')
                    <li>
                        <a href="{{ url('transacciones/cargaPagos') }}">
                            Carga Masiva de Pagos
                        </a>
                    </li>
                    @endpermission -->
                    @permission('carga-usuarios')
                    <li>
                        <a href="{{ url('users/import') }}" class="preventUnsave">Carga Masiva de Clientes </a>
						<a href="{{ route('comercios.importar', [1]) }}" class="preventUnsave">Carga Masiva de Comercios </a>
                    </li>
                    @endpermission
                    <!-- @permission('carga-limites')
                    <li>
                        <a href="{{ url('users/limites') }}">Carga Masiva de Limites </a>
                    </li>
                    @endpermission -->
                    @permission('carga-limites')
                      @if($rolUser =='1' || $rolUser =='2' || $rolUser =='6')
                        <li>
                            <a href="{{ url('transacciones/LimitesDisponibles') }}" class="preventUnsave">Carga Masiva de Limites y Disponibles </a>
                        </li>
                      @endif                
                    @endpermission                  
                </ul>
            </li>
            @endpermission
			@if($rolUser =='6' || $rolUser =='4' || $posee_gift)
				<li>
					<a href="#" class="preventUnsave">
						<i class="fa fa-gift"></i>
						<span class="nav-label">GiftCard</span>
						<span class="fa arrow"></span>
					</a>									
					<ul class="nav nav-second-level collapse">
						@if($rolUser =='3')
							<li>
								<a href="{{ route('gift.venta', [ $gift_card->cod_emisor ]) }}"><i class="fa fa-gift"></i> <span class="nav-label">Vender GiftCard</span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="label label-info float-right">Nuevo</span></a>
							</li>
						@endif
						@if($rolUser =='6')
							<li>
								<a href="{{ url('gift/create') }}" class="preventUnsave"><i class="fa fa-plus"></i>Crear GiftCard</a>
							</li>							
							<li>
								<a href="{{ url('gift') }}" class="preventUnsave">Lista</a>
							</li>
						@endif
						@if($rolUser =='3')												
							<li>
								<a href="{{ route('gift.edit',$gift_card->id) }}" class="preventUnsave"> Mi GiftCard </a>
							</li>
						@endif						
                        <li>
                            <a href="{{ url('gift/ventas') }}" class="preventUnsave">Reporte de Ventas </a>
                        </li>
                        <li>
                            <a href="{{ url('gift/consolidado') }}" class="preventUnsave">Reporte Consolidado </a>
                        </li>						
					</ul>
				</li>						
			@endif
        </ul>

    </div>
</nav>
