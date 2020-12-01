@extends('layouts.app')
@section('titulo')
Importar
@endsection
@section('contenido')
        <div class="row wrapper border-bottom white-bg page-heading">
          <div class="col-lg-8">
            <h2><i class="fa fa-users"></i>   Importar Comercios</h2>
            <ol class="breadcrumb">
              <li>
              <a href="{{ url('home') }}">Panel</a>
              </li>
              <li>Comercios
              </li>
              <li class="active">
              <strong>Importar</strong>
              </li>
            </ol>
            </div>
            <div class="col-lg-4">
              <div class="title-action">

              </div>
          </div>

        </div>

        <div class="wrapper wrapper-content ecommerce">

        @include('error')
        @include('success')

          <div class="ibox float-e-margins">
              <div class="ibox-title">
                  <h5>Importar Comercios</h5>

              </div>
              <div class="ibox-content">
                  <div class="row">
                      <div class="col-sm-6 b-r"><h3 class="m-t-none m-b">Importacion masiva de Comercios</h3>
                         {{--   <p>Ingrese el banco al que pertenecen los miembros e incluya el archivo Excel a exportar.</p> --}}
                           <form role="form">

                          </form>
                          <div id="notificacion_resul_fcdu"></div>

                          {!! Form::open(array('name'=>'f_cargar_datos_comercios', 'id'=>'f_cargar_datos_comercios', 'route' => 'comercios.afiliar_comercios','method'=>'POST', 'class'=>'formarchivo','enctype'=>'multipart/form-data')) !!}

                           <input type="hidden" name="_token" id="_token"  value="<?= csrf_token(); ?>"> 

                           {{-- <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label class="control-label" for="date_added">Banco</label>
                                        <div class="input-group date">
                                          <select class="form-control" name="banco">
                                              <option>Seleccione</option>
                                              @foreach($bancos as $banco)
                                              <option value="{{ $banco->id }}">{{ $banco->descripcion }}</option>
                                              @endforeach
                                          </select>


                                        </div>
                                    </div>
                                </div>
                                
                            </div> --}}

                          <div class="box-body">

                         
                            <p>Examine y seleccione el archivo.</p>
                          <div class="form-group col-xs-12"  >
                                 <b>Seleccione Archivo Excel</b>
                      <div class="input-group date">
                        <span class="input-group-addon">
                          <i class="fa fa-upload"></i>
                        </span>
                      </div>
                      <br>

                                  <b><input name="archivo" id="archivo" type="file" accept=".xls"  class="archivo custom-file-input"  required/><br /><br />
                          </b>
                          </div>

                         
                          <div class="box-footer ">
                                              <button type="button" id="but_upload" onclick="uploadFiles();" class="btn btn-primary col-xs-12">Cargar Datos</button>
                          </div>

                           


                          </div>

                          </form>
                      </div>
                      <div class="col-sm-6"><h4>Importar archivo de Excel</h4>
                          <p class="text-center">
                              <a href="#"><i class="fa fa-file-excel-o big-icon"></i></a>
                          </p>

                      </div>
                  </div>
              </div>
          </div>


        </div>

@endsection

{{--  
<!-- Modal detalleTransaccion -->
    <div class="modal inmodal" id="detalleTransaccion" tabindex="-1" role="dialog"  aria-hidden="true">
      <div class="modal-dialog">
          <div class="modal-content animated fadeIn">
              <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                  <i class="fa fa-credit-card modal-icon"></i>
                  <h2 class="modal-title">Detalle de transacción</h2>
                  </div>
                  <div class="ibox-content">
                    <ul class="unstyled">
                      <li><h3><span class="font-normal">Transacción: </span>3214123545</h3></li>
                      <li><h3><span class="font-normal">Fecha: </span>23/12/2018 10:45pm</h3></li>
                      <li><h3><span class="font-normal">Cédula: </span>15660605</h3></li>
                      <li><h3><span class="font-normal">Miembro: </span>Alberto Zambrano</h3></li>
                      <li><h3><span class="font-normal">Comercio: </span>Pacifico Restaurant</h3></li>
                      <li><h3><span class="font-normal">Monto: </span>32.469.000,00</h3></li>
                      <li><h3><span class="font-normal">Banco: </span>Banplus</h3></li>
                    </ul>
                  </div>

              <div class="modal-footer">
                  <button type="button" class="btn btn-white" data-dismiss="modal">Cerrar</button>
              </div>
          </div>
      </div>
    </div>
<!-- Modal End -->


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
    <!-- Modal End -->


    </div>--}}
{{-- 
    <!-- Mainly scripts -->
    <script src="js/jquery-3.1.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

    <!-- Data picker -->
    <script src="js/plugins/datapicker/bootstrap-datepicker.js"></script>

    <!-- FooTable -->
    <script src="js/plugins/footable/footable.all.min.js"></script>


    <!-- Custom and plugin javascript -->
    <script src="js/inspinia.js"></script>
    <script src="js/plugins/pace/pace.min.js"></script>

        <!-- Page-Level Scripts -->
        <script>
            $(document).ready(function() {

                $('.footable').footable();

                $('#date_added').datepicker({
                    todayBtn: "linked",
                    keyboardNavigation: false,
                    forceParse: false,
                    calendarWeeks: true,
                    autoclose: true
                });

                $('#date_modified').datepicker({
                    todayBtn: "linked",
                    keyboardNavigation: false,
                    forceParse: false,
                    calendarWeeks: true,
                    autoclose: true
                });

            });

        </script>


</body>
</html> --}}
@section('scripts')
<script>
			$(document).ready(function(){

					uploadFiles = function() {
						
						if($('#archivo').val() == '')
						{
							alert("Debe seleccionar un archivo antes de continuar");
						}
						else{
							  if($('#archivo').val().split('.')[1].toLowerCase() != 'xls')
							  {
								  alert("Solo se permiten archivos del tipo Excel con extensión (xls)");
							  }
							  else{

								  // Get the files
								  var file1 = $("#archivo")[0].files[0];

								  var formData = new FormData();
								  formData.append("files", file1);
								  
								  $("#divLoading").show();

								  // You can abort the upload by calling jqxhr.abort();    
								  var jqxhr = $.ajax({
									  url: "{{ $URLServicio }}",
									  type: "POST",
									  contentType: false,
									  data: formData,
									  dataType: "json",
									  cache: false,
									  processData: false,
									  async: false,
									  xhr: function() {
										var xhr = new window.XMLHttpRequest();
										xhr.upload.addEventListener("progress",
										  function(evt) {
											if (evt.lengthComputable) {
											  var progress = Math.round((evt.loaded / evt.total) * 100);

											  // Do something with the progress
											}
										  },
										  false);
										return xhr;
									  }
									})
									.done(function(data, textStatus, jqXhr) {
									  alert("El archivo fue cargado exitosamente");

									  // Clear the input
									  $("#archivo").val();
									  $("#divLoading").hide();
									})
									.fail(function(jqXhr, textStatus, errorThrown) {
									  if (errorThrown === "abort") {
										alert("La carga del archivo fue cancelado");
									  } else {
										//alert("Ocurrio un error al intentar subir el archivo");
										alert("El archivo fue cargado exitosamente, recibira los resultados del procesamiento por correo electrónico.");
										$("#archivo").val('');
										$("#divLoading").hide();
									  }
									})
									.always(function(data, textStatus, jqXhr) {});							
								  
							  }
						}
					};
			});	
		
</script>
@endsection