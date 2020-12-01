@extends('layouts.appLogin')

@section('contenido')
@php
$contra=File::get(storage_path('contrato/contrato.txt'));
@endphp
    
    <style type="text/css">
        #confirm-password{
            background-color: red;
            width: 
        }
    </style>
    <div class="passwordBox animated fadeInDown">
        @include('error')
        @include('success')

            @php
              foreach (Auth::user()->roles as $v){
                $rolUser = $v->id;
              }
			  
				$comercio = App\Models\comercios::select(
					'comercios.rif',
					'comercios.razon_social',
					'comercios.estado_afiliacion_comercio')
				->join('miem_come','miem_come.fk_id_comercio','comercios.id')
				->where('miem_come.fk_id_miembro','=',$user->id)
				->withTrashed()
				->first();	
				
            @endphp

        <div class="row">

            <div class="col-md-12">
                <div class="ibox-content">

                    <h2 class="font-bold">Cambio de Contraseña</h2>

                    <p>
                        Bienvenido {{ $user->first_name.' '.$user->last_name}}, por ser su primer inicio de sesion es necesario que sea modificada su contraseña, por favor indique su nueva contraseña.
                    </p>

                    <div class="row">

                        <div class="col-lg-12">
                            @if (session('status'))
                          <div class="alert alert-success">
                              {{ session('status') }}
                          </div>
                            @endif
                            {!! Form::model($user, ['class'=>'form-horizontal','method' => 'PATCH','route' => ['password.update', $user->id,]]) !!}
                                        {{ csrf_field() }}
                                <div class="form-group">
                                <center>{!! Form::password('password', array('style' => 'width: 92%;','placeholder' => 'Nueva Contraseña','class' => 'form-control input-lg m-b')) !!}</center>
                                    
                                    <center>{!! Form::password('confirm-password', array('style' => 'width: 92%;','placeholder' => 'Confirmar Contraseña','class' => 'form-control input-lg m-b' )) !!}</center>
                                    
                                </div>
                                @if ($rolUser == 3 && $comercio->estado_afiliacion_comercio != null)
                                <div class="form-check">
                                    <input type="checkbox" name="checked" class="form-check-input" id="check">
                                    <label class="form-check-label" for="exampleCheck1">Contrato de Afiliación</label>
                                </div>
                                @endif
                                <button id="aceptar" type="submit" class="btn btn-primary block full-width m-b">Cambiar Contraseña</button>
                                <a href="{{ url('logout') }}" class="btn btn-default block full-width m-b">Cerrar sesion</a>

                            {!!Form::close()!!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr/>
        <div class="row">
            <div class="col-md-6">
                <strong>Meritop C.A.</strong>
            </div>
            <div class="col-md-6 text-right">
               <small> &copy; 2018</small>
            </div>
        </div>
    </div>

@if($rolUser == 3 && $comercio->estado_afiliacion_comercio != null)
<div class="container">
  <!-- Modal -->
  <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">

      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Contrato de Afiliación</h4>
        </div>

        <div class="modal-body">
         <form id="form">
          {{ csrf_field() }}
              <div class="row">
                <div class="col-md-12">
                <div class="form-group scroll scroll4" id="contra">
                  
                </div>
              </div>
              </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" onclick="myFunction()" class="btn btn-danger" data-dismiss="modal">
            Cerrar
          </button>
          <button type="submit" id="contrato" class="btn btn-primary" data-dismiss="modal">   Aceptar
          </button>
        </div>
      </div>

    </div>
  </div>

</div>
@endif
@endsection

@section('script')

<script src="{!!asset('js/plugins/jsvalidator/jsvalidation.js')!!}"></script>
{!! JsValidator::formRequest('App\Http\Requests\PasswordRequest') !!}

<script>
    function myFunction() {

            $("#myModal").hide();
            $("#check").prop("checked", false);

            $('#aceptar').attr('disabled', true);
        }
    $(document).ready(function() {
      var rol = {!! json_encode($rolUser) !!}; 
	  var estado = null;
	  if(rol == 3)
	  {
		  @if($rolUser == 3 && $comercio->estado_afiliacion_comercio != null)
			  estado = {!! json_encode($comercio->estado_afiliacion_comercio) !!};
		      var cadena = {!! json_encode($contra) !!}; 
		      var repl = cadena.replace(/['"]+/g, '') ;
		      $('#contra').append(repl);			  
		  @endif		  
	  }	  
      
      if(rol == 3 && estado != null){
        $('#aceptar').attr('disabled', true);
      }else{
        $('#aceptar').attr('disabled', false);
      }
        


            $('#check').on('click', function(){
                 
                if( $('#check').prop('checked') ) {
                    $("#myModal").modal({ backdrop: 'static', keyboard: false });
                    $('#aceptar').attr('disabled', false);
                }else{

                    $('#aceptar').attr('disabled', true);
            }
        });
    });
</script>

@endsection
