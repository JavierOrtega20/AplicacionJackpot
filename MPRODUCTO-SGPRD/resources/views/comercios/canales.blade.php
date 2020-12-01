@extends('layouts.app')
@section('titulo', 'Canales')

@section('contenido')
<div class="row wrapper border-bottom white-bg page-heading">
  <div class="col-lg-8">
	<h2><i class="fa fa-users"></i>
		Comercios
	</h2>
	<ol class="breadcrumb">
	  <li>
	  <a href="{{ url('home') }}">Panel</a>
	  </li>
	  <li>
		Comercios
	  </li>			  
	  <li class="active">
	  <strong>{{ $comercio->descripcion }}</strong>
	  </li>
	</ol>
	</div>
  <div class="col-lg-4">
    <div class="title-action">
      <a href="{{ route('comercios.edit',[$comercio->id, $comercio->retorno]) }}" class="btn btn-success preventUnsave" ><i class="fa fa-chevron-circle-left"></i> Regresar </a>
    </div>
  </div>	
</div>
<div class="wrapper wrapper-content ecommerce">
@include('success')

<div class="ibox-content m-b-sm border-bottom">
	<div class="row">
	 <form method="post" action="{{ route('canale.create') }}">
			{{ csrf_field() }}
			{!! Form::hidden('comPrincipal',$comercio->retorno,array('id'=>'comPrincipal')) !!}
			{!! Form::hidden('idComercio',$comercio->id,array('id'=>'idComercio')) !!}
				<div class="panel-body">
					<div class="form-inline" >
					<div class="form-group" >
					  <label class="control-label" for="dateranges">Canales: </label><br>
					  <div class="input-group date">
						{{ Form::select('canales', $lCanales, null, ['class' => 'form-control input-lg-8','required' => 'required', 'placeholder'=>'Seleccione una opción','id'=> 'canales']) }}
					  </div>
					</div>
					<div class="form-group" id="data_5">
						<label class="control-label" for="dateranges">Terminales:</label><br>
						<div class="input-group date">
						  <input type="text" name="terminales" required id="terminales" onkeypress="return justNumbers(event);" class="input-lg-8 form-control" maxlength="2" >
						</div>
					</div>
					@if($lCanales->count() > 0)
						<button type="submit" class="btn btn-primary" style="margin-top: 18px;">Agregar
						</button>
					@else
						<button type="submit" disabled class="btn btn-primary" style="margin-top: 18px;">Agregar
						</button>						
					@endif
					</div>
				</div>
		</form>
  </div>
</div>
@foreach($lCanalesComer as $canal)
	<div class="row">
		<div class="col-lg-12">
			<div class="ibox">
				<div class="ibox-content">
					<form method="post" id="canalForm{{$canal->id}}" action="{{ route('canale.update') }}">
					{{ csrf_field() }}
					{!! Form::hidden('comPrincipal',$comercio->retorno,array('id'=>'comPrincipal')) !!}
					{!! Form::hidden('idComercio',$comercio->id,array('id'=>'idComercio')) !!}
					{!! Form::hidden('idComercioCanal',$canal->id,array('id'=>'idComercioCanal')) !!}
						  <h2>
							  <span class="text-navy">
							  <input type="hidden" class="idCanal" value="{{ $canal->fk_id_canal }}">
							  {{ $canal->Nombre }}
							</span>
						  </h2>
							<div class="ibox-content m-b-sm border-bottom">
								<div class="row">
									<div class="panel-body">
										<div class="form-inline" >
											<div class="form-group">
												<label class="control-label" for="dateranges">Terminales:</label><br>
												<div class="input-group date">
												  <input type="text" name="num_terminales" value="{{$canal->num_terminales}}" id="terminales{{$canal->id}}" onkeypress="return justNumbers(event);" class="input-lg-8 form-control" maxlength="2" >
												</div>
											</div>
												<button type="submit" id="GuardarCambios{{$canal->id}}" class="btn btn-primary" style="margin-top: 18px;">Guardar cambios
												</button>													
										</div>
									</div>
							  </div>
							</div>					  		
						<div class="table-responsive">
							<table id="datatab{{$canal->id}}" class="table" >
								<thead>
								  <tr>
								  <th>Cod. Terminal President's</th>
								  <th>Serial</th>
								  <th width="200">Acción</th>
								  </tr>
								</thead>
								  @php
										
										$terminales = App\Models\terminal::select('terminal.*')->where('fk_id_comer_canal','=',$canal->id)->get();
										
								  @endphp
								  @foreach($terminales as $terminal)
									<tr>							
										<td>{{ $terminal->codigo_terminal_comercio }}</td>
										@if($canal->fisico)					
										<td>
											<input class="inputchange" type="hidden"  value="false">

											<input type="hidden" name="terminales[idTerminal][]" value="{{ $terminal->codigo_terminal_comercio }}">
											<input type="text" name="terminales[serial][]" value="{{ $terminal->serial }}" onkeypress="myFunction()" class="input-sm form-control seri" maxlength="30"  onblur="validarSerial(this)" required>
											<div id="msgserial" class="text-danger"></div>
										</td>
										@else
										<td>
											<input type="hidden" name="terminales[idTerminal][]" value="{{ $terminal->codigo_terminal_comercio }}">
											<input type="text" name="terminales[serial][]" value="{{ $terminal->serial }}" class="input-sm form-control" maxlength="30" onblur="validarSerial(this)">
											<div id="msgserial" class="text-danger"></div>
										</td>
										@endif
										<td>
											@if($terminal->status)
												<a class="btn-white btn btn-sm" onclick ="return confirm('¿Desea desactivar el terminal {{ $terminal->codigo_terminal_comercio }}?')" href="{{ route('comercios.desactivarTerminal',[$terminal->codigo_terminal_comercio,$comercio->id,$comercio->retorno]) }}" title="Desactivar">
													<i class="fa fa-trash-o" aria-hidden="true"></i>
												</a>
											@else
												<a class="btn-white btn btn-sm" onclick ="return confirm('¿Desea activar el terminal {{ $terminal->codigo_terminal_comercio }}?')" href="{{ route('comercios.activarTerminal',[$terminal->codigo_terminal_comercio,$comercio->id,$comercio->retorno]) }}" title="Activar">
													<i class="fa fa-plus-square-o" aria-hidden="true"></i>
												</a>												
											@endif								
										</td>
									</tr>									
								  @endforeach							
							</table>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
@endforeach

</div>
@endsection

@section('modal')

@endsection

@section('scripts')
 <script type='text/javascript' src="{!!asset('js/knockout-3.5.0.js')!!}"></script>
<script type="text/javascript">

@foreach($lCanalesComer as $canal)

	$(document).ready(function() {

		$('#datatab{{$canal->id}}').DataTable({
		  responsive: true,
		  "language": idioma,

		});

		$('.preventUnsave').on('click', function(){
			var canal= JSON.parse('{!!json_encode($canal)!!}');

		var arr = [];
			var sav = [];
		if(canal.fisico==true){

			$($('#canalForm'+canal.id).find("input[name='terminales[serial][]']")).each(function() {      		

      			arr.push($(this).val());
    		});//each find	

    			$($('#canalForm'+canal.id).find("input[class='inputchange']")).each(function() {

      				sav.push($(this).val());
    			});//each find	
		}//if validar canal

			if(sav != "" || sav != null){

				for (var i =0; i<sav.length; i++) {
    				if(sav[i] == "true"){
    					swal("Aviso", "Debe guardar los cambios antes de salir de esta pantalla", "warning");
    					event.preventDefault();
    				}//if sav dentro del for
				}//for sav
			}//if sav

		
		if(arr != "" || arr != null){
			for (var i =0; i<arr.length; i++) {
    			if(arr[i] == null || arr[i] == ""){
    				swal("Error", "El campo Serial es requerido, por favor verifique los datos ingresados", "error");
    					event.preventDefault();
					};
    		}//for arr
		}//if arr
		});//preventUnsave



	});//document.ready
	
	$('#canalForm{{$canal->id}}').submit(function() {
		var arr = [];
		$($('#canalForm{{$canal->id}}').find("input[name='terminales[serial][]']")).each(function() {      		
      		arr.push($(this).val());
    	});    	
    	for (var i =0; i<arr.length; i++) {
    		
    		const valorExistente= arr.find((elemento, index)=>{return elemento===arr[i] && index != i})
		
    		if(valorExistente){
    			swal("Error", "Uno o varios seriales ingresados estan repetidos, verifique los datos ingresados", "error");
    			event.preventDefault();	
    		}
    	}
		if($("#terminales{{$canal->id}}").val() >= {{$canal->num_terminales}})
		{
			return true;
		}
		else
		{
			alert("El número de terminales de {{ $canal->Nombre }} no puede ser menor a {{$canal->num_terminales}}." );
			return false;
		}
	});	

@endforeach
function justNumbers(e){
	var keynum = window.event ? window.event.keyCode : e.which;
	if ((keynum == 8))
	return true;

	return /\d/.test(String.fromCharCode(keynum));
}
function myFunction() {
	$('.inputchange').prop('value', 'true');
}


function validarSerial(e){
	var url = window.location;


	var pat = /canales/;

	if(pat.test(url) == true){
		url=String(url);

		url=url.replace("/comercios/canales/{{$comercio->id}}/{{$comercio->retorno}}",'');
	}

    var idcomer = $('#idComercio').val();
    var idcanal = $($(e).parents('form')[0]).find('.idCanal').val();

    var serial = $(e).val();
    var id = idcanal+"|"+serial+"|"+idcomer;
		
    if(idcanal.length > 0){              
    console.log(url);             
      $.get(url+"/serial/checkSerial/"+id+'/', function(res){
        if(res == true){

        	$($(e).siblings()[1]).html('El Serial "'+ serial + '" que ha ingresado ya está en uso.');
        	$(e).val('');
      
        }else{
           $($(e).siblings()[1]).html("");
        }
      });
    }
  }//END FUNCTION VALIDAR CARNET
</script>

<script src="{!!asset('js/plugins/jsvalidator/jsvalidation.js')!!}"></script>
@endsection
