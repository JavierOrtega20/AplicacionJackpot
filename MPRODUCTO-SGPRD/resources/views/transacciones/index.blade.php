@extends('layouts.app')
@section('titulo', 'Transacciones')

@section('contenido')
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
<div id="refresh">
<div class="row wrapper border-bottom white-bg page-heading">
          <div class="col-lg-8">
            <h2><i class="fa fa-credit-card"></i>   Transacciones</h2>
            <ol class="breadcrumb">
              <li>
              <a href="{{ url('home') }}">Panel</a>
              </li>
              <li>Transacciones
              </li>
              <li class="active">
              <strong>Listado</strong>
              </li>
            </ol>
            </div>
            <div class="col-lg-4">
              <div class="title-action">
                @permission('transacciones-create')
					@if($transar)		
						<a href="{{ route('transacciones.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Autorizar </a>
						  @if($tarjeta_internacional)
							<a href={{route('Stripe.create')}} class="btn btn-info"><i class="fa fa-plus"></i> Tarjeta Internacional </a>
						  @endif 					
					@endif				                
                @endpermission
              </div>
          </div>

  </div>




        <div class="wrapper wrapper-content ecommerce">
          @include('flash::message')
          <div class="alert alert-info" role="alert">Estimado cliente, si presenta problemas para autorizar, agradecemos comunicarse con el <strong>Centro de Atención President's</strong> al número telefónico que posee como comercio President Pay.</div>   
          <div class="ibox-content m-b-sm border-bottom">
                <div class="row">


                 <form method="post" action=" {{ url('transacciones/filter') }} ">
                        {{ csrf_field() }}
                        <input type="hidden" name="_method" value="get">
                            <div class="panel-body">
                                <div class="form-inline" >
                                    <div class="form-group" >
                                        <label class="control-label" for="fecha_desde">Fecha desde: </label><br>
                                        <div class="input-group date" id="datepicker">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        {!! Form::text('fecha_desde', date('d/m/Y'), ['class'=>'input-sm form-control','id'=>'fecha_desde','readonly']) !!}
                                    </div>

                                </div>

                                <div class="form-group" id="data_5">
                                        <label class="control-label" for="dateranges">Fecha hasta: </label><br>
                                        <div class="input-group date" id="datepicker">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        {!! Form::text('fecha_hasta', date('d/m/Y'), ['class'=>'input-sm form-control','id'=>'fecha_hasta','readonly']) !!}

                                        </div>

                                </div>

                                <div class="form-group" id="data_5">
                                    <label class="control-label" for="dateranges">C&eacute;dula:</label><br>
                                    <div class="input-group date">
                                      <input type="text" name="cedula" id="cedula" onkeypress="return justNumbers(event);" class="input-sm form-control" maxlength="8">
                                    </div>

                                </div>
                                @if ($rol != 3)
                                  <div class="form-group" id="data_5">
                                      <label class="control-label" for="dateranges">Nro. Tarjeta:</label><br>
                                      <input type="text" name="tarjeta" id="tarjeta" onkeypress="return justNumbers(event);" class="input-sm form-control" maxlength="16">
                                  </div>
                                @endif

                                <div class="form-group" >
                                  <label class="control-label" for="dateranges">Monto: </label><br>
                                  <div class="input-group date" >
                                    <input type="text" placeholder="Monto" name="monto" id="monto" class="input-sm form-control" maxlength ="10" onkeypress="return justNumbers2(event);" onblur="format(this)">
                                  </div>
                                </div>


                                  <div class="form-group">
                                    <label class="control-label" for="dateranges">Monedas:</label><br>
                                    <div class="input-group date">
                                      <select class="input-sm form-control" name="mon_nombre" id="monedas">
                                            <option value="" disabled selected>Moneda</option>
                                      </select>
                                    </div>
                                  </div>


                                <button type="submit" class="btn btn-primary" style="margin-top: 18px;">Buscar</button>

                                </div>
                            </div>
                    </form>
                      </div>
                            </div>
{{-- <div class="col-sm-4">
                        <div class="form-group">
                            <label class="control-label" for="date_added">Fecha desde</label>
                            <div class="input-group date">
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="date_added" type="text" class="form-control" value="03/04/2014">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label class="control-label" for="date_modified">Fecha hasta</label>
                            <div class="input-group date">
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="date_modified" type="text" class="form-control" value="03/06/2014">
                            </div>
                        </div>
                    </div> --}}



            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox">
                        <div class="ibox-content">
                         {{--  <h2>
                              2,160 transacciones: <span class="text-navy"> Desde el 15/4/2018 hasta el 24/4/2018</span>
                          </h2> --}}
                          <h2>
                             {{ count($transacciones) }} <span class="text-navy"> Transacciones</span>
                          </h2>
                          <div class="hr-line-dashed"></div>
                            <div class="table-responsive">
                            <table id="datatab" class="table">
                                <thead>
                                <tr>
                                    <th>Transacción</th>
									<th NOWRAP>Tipo</th>								
                                    <th NOWRAP>Fecha</th>
                                    <th NOWRAP>Cédula</th>
                                    <th>Cliente</th>
                                    @if ($rol != 3)
                                      <th NOWRAP>Tarjeta de Membresía</th>
                                    @endif
                                    <th>Comercio</th>
                                   
                                      <th NOWRAP>Terminal</th>
                                    								
                                    <th>Monto</th>
                                    <th>Propina</th>
                                    {{--<th>Banco</th>--}}
                                     <th>Moneda</th>

                                    <th>Status</th>


                                    <th style="size: 200px">Acción</th>
                                </tr>
                                </thead>
                                <tbody>
                                   @foreach($transacciones as $i => $element)
                                   <?php $i++;
                                   //dd($transacciones);
                                   //dd($element);
                                   //dd($element->fechaTrans);
                                      setLocale(LC_TIME, 'es_VE.utf8');
                                          date_default_timezone_set('America/Caracas');
                                          $actual = \Carbon\Carbon::now();
                                          //dd($fechaActual->addHours(24));
                                          $fechaini = \Carbon\Carbon::parse($element->fechaTrans);
                                          //dd($fechaini->addDay());
                                          $fin = \Carbon\Carbon::parse($element->fechaTrans);
                                          $fechafin = $fin->addHours(30);
                                          //dd($actual->diffInDays($fechafin));

                                          //dd($fechafin);
                                          $actual = \Carbon\Carbon::now()->between($fechaini, $fechafin);
                                          //dd($actual, $fechaini, $fechafin);
                                          /*
                                          $actual = $fechaini->diffInMinutes($fechafin);


                                          $fi = \Carbon\Carbon::now();
                                          $ff = \Carbon\Carbon::parse($element->fechaTrans);
                                          $actual = $ff->diffInMinutes($fi);

                                          if($actual >= 3){
                                            $actual = false;
                                          }else{
                                            $actual = true;
                                          }
                                          */
                                    ?>


                                  <tr>
                                    <td>{{ $element -> idTrans }}</td>
									<td>{{ $element->origen }}</td>
                                    <td NOWRAP><?php \Carbon\Carbon::setLocale('es'); echo \Carbon\Carbon::createFromTimeStamp(strtotime($element->fechaTrans))->format('d/m/Y'); ?></td>
                                    <td NOWRAP>{{ $element -> nacionalidad .'-'. $element -> dni }}</td>
                                    <td NOWRAP>{{ $element -> first_name }} {{ $element -> last_name }}</td>
                                    @if ($rol != 3)
                                      <td NOWRAP>{{ substr($element->carnet_cliente,-16, 4) }} XXXX XXXX  {{ substr($element->carnet_cliente,-4) }} </td>
                                    @endif
                                    <td>{{ $element -> descripcioncomercios }}</td>
                                   
                                      <td>{{$element->codigo_terminal_comercio}}</td>
                                    							
                                    <td class="text-capitalize">{{ number_format($element -> monto , 2, ',', '.') }}</td>
                                    <td class="text-capitalize">{{ number_format($element -> propina , 2, ',', '.') }}</td>
                                    {{-- <td class="text-capitalize">{{ $element -> descripcionBancos }}</td> --}}

                                    <td NOWRAP>{{ $element -> moneda }}</td>

                                    <td class="text-capitalize">@if ( $element -> status == 3) <span class="label label-danger">Rechazada</span> @elseif ($element -> status == 1) <span class="label label-warning">Por autorizar</span>
                                      @elseif ($element -> status == 0)
                                        @if($element->reverso != null)
                                          <span class="label label-primary">Cancelada por Reverso</span>
                                        @else
                                          <span class="label label-primary">Aprobada</span>
                                        @endif

                                      @elseif ($element -> status == 2) <span class="label label-danger">Cancelada</span> @elseif ($element -> status == 4) <span class="label label-danger">Reverso</span>@elseif($element -> status == 6)
                                        <span class="label label-primary">Aprobada</span>
                                      @endif</td>

                                      


                                    <td class="text-capitalize" class="col-lg-3">
                                      <div class="btn-group">

                                      
                                          <button class="btn-white btn btn-sm" data-toggle="modal" data-target="#detalle" onclick="show_transacciones('{{ $element -> idTrans}}','0')" title="Ver" >
                                             <i class="fa fa-eye"></i> 
                                          </button>
                                          <button class="btn-white btn btn-sm" data-toggle="modal" data-target="#log" onclick="show_log_transacciones('{{ $element -> idTrans}}')" title="Historial" >
                                             <i class="fa fa-book"></i>
                                          </button>                     
                                          @permission('transacciones-create')
                                          @if ($rol == 4)
                                            @if ( $element -> status == 0)
                                              @if ($actual == TRUE)
                                                @if ($element->reverso ==NULL)
                                                  @if($element->procesado == NULL)
                                                    <button class="btn-warning btn btn-sm" data-toggle="modal" data-target="#detalleReverso" onclick="show_transacciones('{{ $element -> idTrans}}','0')" >
                                                      Reversar
                                                    </button>
                                                  @endif
                                                @else
                                                @endif
                                              @endif
                                            @endif
                                          @endif

                                           @if ($element -> status == 1)
											   @if($transar)	
                                            <a href="#" class="btn-warning btn btn-sm" data-toggle="modal" data-target="#token" onclick="show_transacciones('{{ $element -> idTrans}}','{{ $element -> requiere_pin}}')">  Validar  </a>
												@endif
                                          @endif
                                          @endpermission


                                      {{--     <a class="btn-white btn btn-sm" data-target="#detalle_edit" href="{{ route('transacciones.edit', $element -> idTrans) }}">
                                          Editar
                                          </a> --}}
                                      </div>
                                    </td>
                                  </tr>
                                    @include('transacciones.validar')
                                    @include('transacciones.reversar')
                                    @include('transacciones.log')
                                  @endforeach
                                </tbody>
                            </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </div>

 @stop

   @section('modal')

    <div class="modal inmodal" id="detalle" tabindex="-1" role="dialog"  aria-hidden="true">
              <div class="modal-dialog">
                  <div class="modal-content animated fadeIn">
                      <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                          <i class="fa fa-credit-card modal-icon"></i>
                          <h2 class="modal-title">Detalle de transacción</h2>
                          </div>
                          <div class="ibox-content">
                            <ul class="unstyled">
                                  <div id="id"></div>
                                  <div id="fecha"></div>
                                  <div id="nacionalidad"></div>
                                  <div id="dni"></div>
                                  <div id="miembro"></div>
                                  @if ($rol != 3)
                                    <div id="carnet"></div>
                                    <div id="currency"></div>
                                  @endif
                                  <div id="comercio"></div>
                                  <div id="mont"></div>
                                  <div id="propina"></div>
                                  <div id="banco"></div>
                            </ul>
                          </div>

                      <div class="modal-footer">
                          <button type="button" class="btn btn-white" data-dismiss="modal">Cerrar</button>
                      </div>
                  </div>
              </div>
        </div>
         <!-- Modal token -->



    <!-- Modal -->
        <div class="modal inmodal" id="myModal4" tabindex="-1" role="dialog"  aria-hidden="true">
          <div class="modal-dialog">
              <div class="modal-content animated fadeIn">
                  <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                      <i class="fa fa-clock-o modal-icon"></i>
                      <h4 class="modal-title">Modal title</h4>
                      <small>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</small>
                  </div>
                  <div class="modal-body">
                      <p><strong>Lorem Ipsum is simply dummy</strong> text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown
                          printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting,
                          remaining essentially unchanged.</p>
                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
                      <button type="button" class="btn btn-primary">Save changes</button>
                  </div>
              </div>
          </div>
        </div>
</div>
    <!-- Modal End -->
    @stop
  @section('scripts')
    <!-- page js -->
    <script type="text/javascript" src="{{ asset('js/jackpotScripts/jackpotFunctions.js') }}"></script>

<script>
    $(document).ready(function(){
//setInterval(loadClima,30000);

function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
    results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}
  var prodId = getParameterByName('estatus');

  if (prodId==='ok') {
      swal({
        title: "Autorización Exitosa!",
        type: "success"
      }).then(function() {
           window.location.href = "{{URL('/transacciones')}}";
        });
  }
  if (prodId==='error'){
    swal({
        title: "Autorización Rechazada!",
        type: "error"
      }).then(function() {
           window.location.href = "{{URL('/transacciones')}}";
          });
  }
  

});

$.get( "{{URL('/divisas')}}",function(data){
  for(var i=0;i < data.length; i++){
  $("#monedas").append('<option value="'+data[i].mon_id+'">'+data[i].mon_nombre+'</option>');
  }
});//Fin del desplegable divisa

//function loadClima(){
  //@if ($rol==4)
//location.reload();
//@endif
//}

function justNumbers(e){
        var keynum = window.event ? window.event.keyCode : e.which;
        if ((keynum == 8) /*|| (keynum == 46) || (keynum == 44)*/)
        return true;

        return /\d/.test(String.fromCharCode(keynum));
}

function justNumber2(e){
        var keynum = window.event ? window.event.keyCode : e.which;
        if ((keynum == 8) || (keynum == 46) || (keynum == 44))
        return true;

        return /\d/.test(String.fromCharCode(keynum));
}


function format(input){

            var num = input.value.replace(/\./g,'');
            if(!isNaN(num)){
                  num = num.toString().split('').reverse().join('').replace(/(?=\d*\.?)(\d{3})/g,'$1.');
                  num = num.split('').reverse().join('').replace(/^[\.]/,'');
                  input.value = num;
            }else{
                  //$("#msg-formato").html('Solo se permiten valores númericos');
                  //input.value = input.value.replace(/[^\d\.]*/g,'');
                  num = num.toString().split('').reverse().join('').replace(/(?=\d*\.?)(\d{3})/g,'$1.');
                  num = num.split('').reverse().join('').replace(/^[\.]/,'');
                  input.value = num;
            }

  }



</script>

     <script>
            $(document).ready(function() {

                $('#fecha_desde').datepicker({
                    todayBtn: "linked",
                    keyboardNavigation: false,
                    forceParse: false,
                    calendarWeeks: true,
                    format: 'dd/mm/yyyy',
                    autoclose: true

                });

                $('#fecha_hasta').datepicker({
                    ignoreReadonly: true,
                    todayBtn: "linked",
                    keyboardNavigation: false,
                    forceParse: false,
                    calendarWeeks: true,
                    format: 'dd/mm/yyyy',
                    autoclose: true
                });

            });

            $(document).ready(function() {
    $('#datatab').DataTable({
      responsive: true,
      "language": idioma,
      "order": [ 0, "desc" ]

    });
  });

        </script>
        <script>
			$('#frmReverso').submit(function(){
				$('#frmReverso input').attr('readonly', 'readonly');
				$("#btnSubmitReverse").prop('disabled',true);
				$("#btnSubmitCancel").prop('disabled',true);
				$("#divLoading").show();
				$("#btnCloseReversar").hide();
				return true;
			});	
			$('#frmValidar').submit(function(){
				$('#frmValidar input').attr('readonly', 'readonly');
				$("#btnSubmitfrmValidar").prop('disabled',true);
				$("#divLoading").show();
				$("#btnCloseValidar").hide();
				return true;
			});				
        </script>		
   
    <!-- end page js -->
    @endsection
