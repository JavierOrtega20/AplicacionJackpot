@extends('layouts.app')

@section('titulo')
Panel
@endsection

@section('contenido')



<div class="row wrapper border-bottom white-bg page-heading">
  <div class="col-lg-8">
    <h2><i class="fa fa-credit-card"></i>   Panel principal </h2>

  </div>
  <div class="col-lg-4">
    <div class="title-action">
{{--                   <a href="transacciones_create.php" class="btn btn-primary"><i class="fa fa-plus"></i> Autorizar </a>
--}}
@php

	use App\Models\miem_come;
	use App\Models\miem_ban;
	use App\Models\comercios;
	use App\Models\User;

        $user= User::find(Auth::user()->id);
        $roles= $user->roles;
        $rol = null;
        foreach ($roles as $value) {
            $rol = $value->id;
        }
		
		$transar = true;
		$tarjeta_internacional = false;
		
		if($rol == 3){			
			$Comercio = miem_come::select('comercios.es_sucursal','comercios.rif','comercios.id')->join('comercios','comercios.id','miem_come.fk_id_comercio')->where('miem_come.fk_id_miembro',Auth::user()->id)->first();
					
			$producto_int = miem_come::select('banc_comer.tasa_cobro_comer_stripe')
							->join('comercios','comercios.id','miem_come.fk_id_comercio')
							->join('banc_comer','banc_comer.fk_id_comer','miem_come.fk_id_comercio')
							->where('miem_come.fk_id_miembro',Auth::user()->id)
							->where('banc_comer.status_stripe', 'true')
							->get();					
			
			if(count($producto_int) > 0)
			{
				$tarjeta_internacional = true;
			}			
			
			if(!$Comercio->es_sucursal)
			{
				$Sucursales = comercios::select('id')
				->where('rif','=',$Comercio->rif)
				->where('id','!=',$Comercio->id)
				->get();
				
				if(count($Sucursales) > 0)
				{
					$transar = false;
				}
			}
		}

@endphp
@permission('transacciones-create')
		@if($transar)		
			<a href="{{ route('transacciones.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Autorizar 
      </a>
		  @if($tarjeta_internacional)
			  <a href={{route('Stripe.create')}} class="btn btn-info"><i class="fa fa-plus"></i> Tarjeta Internacionall 
			  </a>
		  @endif
		@endif

@endpermission

</div>
</div>

</div>

<div class="wrapper wrapper-content">
  @include('success')
  <div class="row">
    <div class="col-lg-12">
      <div class="jumbotron">
        <h1>Bienvenido</h1>
        <p>Plataforma de transacciones para President’s Club.</p>

      </div>
    </div>
  </div>
    <div class="row">
      <div class="col-lg-12">
        <div class="ibox float-e-margins">
          <div class="ibox-title">
            <h5>Totalización<small class="m-l-sm">de transacciones</small></h5>

          </div>

          <div class="ibox-content ibox-heading">
            <div class="row">
              <div class="col-sm-3">
                <h3><i class="fa fa-calendar"></i> Seleccione</h3>
              </div>


              <div class="col-sm-3">
                <div class="form-group">
                  <label class="col-sm-4  control-label" for="date_added">Monedas</label>
                  <div class="col-sm-8">
                    <select class="form-control m-b" name="mon_nombre" id="monedas">
                      	<option value="" disabled selected>Moneda</option>
                    </select>
                  </div>
                </div>
              </div>


              <div class="col-sm-3">
                <div class="form-group">
                  <label class="col-sm-4  control-label" for="date_added">Mes</label>
                  <div class="col-sm-8">
                    <select class="form-control m-b" name="mes" id="mes">
                          <option value="">Seleccione</option>
                          <option value="01">Enero</option>
                          <option value="02">Febrero</option>
                          <option value="03">Marzo</option>
                          <option value="04">Abril</option>
                          <option value="05">Mayo</option>
                          <option value="06">Junio</option>
                          <option value="07">Julio</option>
                          <option value="08">Agosto</option>
                          <option value="09">Septiembre</option>
                          <option value="10">Octubre</option>
                          <option value="11">Noviembre</option>
                          <option value="12">Diciembre</option>

                    </select>
                  </div>
                </div>
              </div>

              <div class="col-sm-3">
                <div class="form-group">
                  <label class="col-sm-3" for="date_modified">Año</label>
                  <div class="input-group date" id="anioCalendario">
                      <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                      {!! Form::text('anio', null, ['class'=>'form-control input-lg m-b','id'=>'anio','placeholder'=>'Año', 'readonly'//'data-mask'=>'9999-99-99'
                      ]) !!}
                  </div>
              </div>
              <div class="col-sm-3">
                <button type="button" class="btn btn-primary" id="filtrar" onclick="filtro_consolidado();">Filtrar</button>
              </div>
            </div>

          </div>
          <div class="ibox-content">
            <h5>Autorizaciones totalizadas</h5>
            <div class="row">
              <div class="col-md-4">
                <h1 class="no-margins"><div id="totalAutorizaciones">{{$totalAutorizaciones}}</div></h1>
                <div class="font-bold text-navy"><i class="fa fa-check"></i> <small>Aprobadas</small></div>
              </div>
              <div class="col-md-4">
                <h1 class="no-margins"><div id="totalPorAutorizar">{{$totalPorAutorizar}}</div></h1>
                <div class="font-bold text-warning"><i class="fa fa-exclamation-triangle"></i> <small>Por autorizar</small></div>
              </div>
              <div class="col-md-4">
                <h1 class="no-margins"><div id="totalCanceladas">{{$totalCanceladas}}</div></h1>
                <div class="font-bold text-danger"><i class="fa fa-ban"></i> <small>Canceladas</small></div>
              </div>
            </div>
          </div>

          <div class="ibox-content">
            <h5>Cantidad de Autorizaciones</h5>
            <div class="row">
              <div class="col-md-4">
                <h1 class="no-margins"><div id="cantidadAutorizaciones">{{$cantidadAutorizaciones}}</div></h1>
                <div class="font-bold text-navy"><i class="fa fa-check"></i> <small>Aprobadas</small></div>
              </div>
              <div class="col-md-4">
                <h1 class="no-margins"><div id="cantidadPorAutorizar">{{$cantidadPorAutorizar}}</div></h1>
                <div class="font-bold text-warning"><i class="fa fa-exclamation-triangle"></i> <small>Por autorizar</small></div>
              </div>
              <div class="col-md-4">
                <h1 class="no-margins"><div id="cantidadCanceladas">{{$cantidadCanceladas}}</div></h1>
                <div class="font-bold text-danger"><i class="fa fa-ban"></i> <small>Canceladas</small></div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>


</div>

@section('scripts')
@if(session('status')=='Upok')
<script  type="text/javascript">
  swal("Bienvenido!", "Sus Datos se Actualizaron con éxito", "success");
</script>
@endif

@if(session('status')=='ok')
<script  type="text/javascript">
  swal("Bienvenido!","a Meritop Pay", "success");
</script>
@endif
    <script>
      $(document).ready(function() {
        var moneda = {{$moneda}}
        history.pushState({}, null, "/home");
        $('#monedas').change(onMonedaChange);
                $("#anioCalendario").datepicker( {
                    format: "yyyy",
                    startView: "years",
                    minViewMode: "years",
                    autoclose: true
                });
        });
      $.get( "{{URL('/divisas')}}",function(data){
        $("#monedas").append('<option value="">Seleccione</option>');
        for(var i=0; data.length; i++){
          if (data[i].mon_id == {{$moneda}}) {
            $("#monedas").append('<option selected="selected" value="'+data[i].mon_id+'">'+data[i].mon_nombre+'</option>');
            
          } else {
            $("#monedas").append('<option value="'+data[i].mon_id+'">'+data[i].mon_nombre+'</option>');
            
          }
        }

      });//Fin del desplegable divisa
        
        function onMonedaChange(e){
          var value = e.target.value;
          window.location.href = window.location.href+"/"+value;
        }
        function fetchTotalsWithCurrency(response) {

        }
        function filtro_consolidado(){

              var mes = document.getElementById("mes").value;
              var anio = document.getElementById("anio").value;
              //alert(anio);
              var url = window.location;
              url=String(url);
              url = url.replace("/home", "");
              var selectedMoneda = $("#monedas").val();
			  
			  
             
              $.ajax({
                  data:'',
                  url:url+'/transacciones/montosConsolidados'+'/'+mes+'/'+anio+'/'+selectedMoneda,
                  method:'GET',
                  cache:false,
                  processData:false,
                  contentType: false,
                  success: function (data) {

                      $("#totalAutorizaciones").html(data['data'][0]);
                      $("#cantidadAutorizaciones").html(data['data'][1]);
                      $("#totalPorAutorizar").html(data['data'][2]);
                      $("#cantidadPorAutorizar").html(data['data'][3]);
                      $("#totalCanceladas").html(data['data'][4]);
                      $("#cantidadCanceladas").html(data['data'][5]);

                 }
            })

        }
  </script>
@endsection

@endsection
