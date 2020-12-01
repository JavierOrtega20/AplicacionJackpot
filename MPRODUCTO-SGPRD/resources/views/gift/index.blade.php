@extends('layouts.app')
@section('titulo', 'GiftCard')        
@section('contenido')
		<style>
			.img_gift_card {
			  width: 400px;
			  height: 250px;
			  border-radius: 10px;
			}		
		</style>
		<div class="row wrapper border-bottom white-bg page-heading">
            <div class="col-lg-10">
                <h2>GiftCard</h2>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="index.html">Panel</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a>GiftCard</a>
                    </li>
                    <li class="breadcrumb-item active">
                        <strong>Listado</strong>
                    </li>
                </ol>
            </div>
            <div class="col-lg-2">
              <div class="title-action">
                
              </div>
            </div>
        </div>
		<div class="wrapper wrapper-content animated fadeInRight">
		@include('flash::message')
			<div class="row">
				<div class="col-md-12">
					<div class="ibox">					
                        <div class="ibox-content">
                <div class="row">


                 <form method="post" action=" {{ url('gift') }} ">
                        {{ csrf_field() }}
                        <input type="hidden" name="filter" value="true">
                            <div class="panel-body">
                                <div class="form-inline" >
                                  <div class="form-group">
                                    <label class="control-label" for="dateranges">Comercio Emisor:</label><br>
                                    <div class="input-group date">
                                      <select class="input-sm form-control" name="comercio_emisor" id="comercio_emisor">
											<option value="">Seleccione</option>
											@foreach($comercios as $element)
												<option value="{{ $element->rif }}">({{ $element->rif }}) {{ $element->descripcion }}</option>
											@endforeach
                                      </select>
                                    </div>
                                  </div>
                                <button type="submit" class="btn btn-primary" style="margin-top: 18px;">Buscar</button>							
                                </div>
                            </div>
                    </form>
				</div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox">
                        <div class="ibox-content">
                          <h2>
						  {{ $num_resultado }} <span class="text-navy"> Registros</span>
                          </h2>
                          <div class="hr-line-dashed"></div>
                            <div class="table-responsive">
                            <table id="datatab" class="table">
                                <thead>
                                <tr>
									<th NOWRAP>RIF</th>								
                                    <th NOWRAP>Comercio</th>
                                    <th NOWRAP>Nombre</th>
                                    <th NOWRAP>Moneda</th>									
									<th NOWRAP>Accion</th>
                                </tr>
                                </thead>
                                <tbody>
									@foreach ($listgift as $key => $gift)
										<tr>
											<td>{{ $gift->rif }}</td>
											<td>{{ $gift->emisor }}</td>
											<td>{{ $gift->nombregift }}</td>
											<td>{{ $gift->mon_nombre }}</td>											
											<td><a href="{{ route('gift.edit',$gift->id) }}" ><i class="fa fa-pencil-square-o"></i> Editar </a></td>
										</tr>
									@endforeach
                                </tbody>
                            </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>				

                        </div>						
					</div>
					<hr>
				</div>						
			</div>			
		</div>		
@endsection	
@section('scripts')

<script type="text/javascript">
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
				
				$('#datatab').DataTable({
				  responsive: true,
				  "language": idioma,
				  "order": [ 0, "desc" ],
					dom: 'Bfrtip',
					buttons: [
						{ 
							extend: 'excel',
							className: 'btn btn-primary',
							text: 'Descargar',
							title: 'Reporte de Giftcards'						
						}
					]				  

				});				

            });
			
			$('#refresh').on('click', function(){
			 location = window.location;
			  
			});			
</script>

<script src="{!!asset('js/plugins/jsvalidator/jsvalidation.js')!!}"></script>


@endsection	