@extends('layouts.app')
 @section('titulo')
    {{ $user->first_name .' '. $user->last_name}}
@endsection
@section('contenido')
	<div class="row">
	    <div class="col-lg-12 margin-tb">
	        <div class="pull-left">
	            <h2> Información del Usuario</h2>
	        </div>
	        <div class="pull-right">
	            <a class="btn btn-primary" href="{{ asset('home') }}" title="Volver"><i class="fa fa-chevron-left" aria-hidden="true"></i></a>
	        </div>
	    </div>
	</div>

	</div>



	<div class="col-md-6 col-sm-6 col-xs-12 profile_details">
                        <div class="well profile_view">
                          <div class="col-sm-12">
                            <h4 class="brief"><center>{{$user->habbo}}</center></h4>
                            <div class="left col-xs-8">
                              
                              <ul class="list-unstyled">
                              <li><i class="fa fa-user"></i> Nombre: <b>{{ $user->name.' '. $user->apellido}} </b></li>
                              	<li><i class="fa fa-envelope"></i> Correo: {{ $user->email }}</li>
                                <li><i class="fa fa-user-circle-o"></i> Perfiles: 
                                	@if(!empty($user->roles))
										@foreach($user->roles as $v)
										<label class="label label-success">{{ $v->display_name }}</label>
										@endforeach
									@endif</li>
                              </ul>
                            </div>
                            <div class="right col-xs-4 text-center">
                              <img src="{{ asset('https://www.habbo.es/habbo-imaging/avatarimage?user='.Auth::user()->habbo.'&direction=2&head_direction=3&gesture=sml&action=&size=n') }}" alt="" class="img-circle img-responsive">
                            </div>
                          </div>
                        </div>
    </div>

@endsection