 @extends('layouts.app')
  @section('titulo', 'Crear')
  {{--     @include('flash::message')
  --}}

  <style>
          .propina4,.dinamicoPropina{display:none;}
  </style>
  @section('contenido')

  <form method="POST" action="{{ route('transacciones.store') }}" method="POST" enctype="multipart/form-data" class="form-horizontal" id="transaccionForm">
<input type="hidden" id="Transar" name="Transar" value="">
  {{ csrf_field() }}
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
                <strong>Nueva autorización</strong>
                </li>
              </ol>
              </div>
              <div class="col-lg-4">
                <div class="title-action">
                  <a href={{route('transacciones.index')}} class="btn btn-white"><i class="fa fa-times"></i> Cancelar </a>

                  <!--<a class="btn btn-primary" type="submit" name="Submit" value="Enviar autorización" onclick="javascript:this.form.submit();this.disabled= true;" data-toggle="modal" data-target="#token" onclick="show_transacciones('1')"/><i class="fa fa-check"></i> Enviar autorización</a>-->

                  @if($rol == 4)
                    <button class="btn btn-primary" type="button" id="Submit" name="Submit" value="Enviar autorización" onclick="validarT();" /><i class="fa fa-check"></i> Enviar autorización</button>
                  @else
                    <button class="btn btn-primary" type="button" id="Submit" name="Submit" value="Enviar autorización" onclick="validarTT();" /><i class="fa fa-check"></i> Enviar autorización</button>
                  @endif

            <!--onclick="javascript:this.form.submit();this.disabled= true;"-->
                </div>
            </div>

          </div>

          <div class="wrapper wrapper-content animated fadeInRight ecommerce">

            @include('error')
            @include('success')
            <div class="alert alert-info" role="alert">Estimado cliente, si presenta problemas para autorizar, agradecemos comunicarse con el <strong>Centro de Atención President's</strong> al número telefónico que posee como comercio President Pay.</div>
              <div class="row">
                  <div class="col-lg-12">
                      <div class="ibox float-e-margins">
                          <div class="ibox-title">
                              <h5>Cargo de autorización al miembro</h5>
                          </div>
                          <div class="ibox-content">


                                @if ($rol==4)

                                <div class="hr-line-dashed"></div>

                                <div class="form-group"><label class="col-sm-2 control-label">Comercios <span class="text-danger">*</span></label>

                                    <div class="col-sm-10">
                                    <select class="form-control select2 input-lg m-b" id="fk_id_comercio" name="fk_id_comercio" style="width: 100%;">
                                        <option value="">Seleccione un comercio</option>
                                        @foreach($comercios as $element)
                                        <option value="{{ $element->id }}">{{ $element->descripcion }}</option>
                                        @endforeach
                                      </select>
									  <div id="msgComercio" class="text-danger" ></div>
                                    </div>
                                </div>
								@else
									<input id="fk_id_comercio" name="fk_id_comercio" type="hidden" value="{{ $comercio->id  }}">
								

                                @endif

                                  <div class="hr-line-dashed"></div>


                                  <div class="form-group"><label class="col-sm-2 control-label">Número de Cédula <span class="text-danger">*</span></label>

                                      <div class="col-sm-10">
                                        <input type="text" placeholder="Cédula" name="cedula" id="cedula" class="form-control input-lg m-b" maxlength="10" onkeyup="validadoCed();this.value=Text(this.value);" onblur="consultaDatos();">
                                        <div id="msgCedula" class="text-danger" ></div>
                                        <!--<span class="help-block m-b-none">Inserte el número de cédula sin puntos o caracteres especiales.</span>-->
                                      </div>
                                  </div>

                                  <input type="hidden" placeholder="Crédito Disponible" name="creditodisponibleOcult" id="creditodisponibleOcult" class="form-control input-lg m-b" maxlength="16" readonly="true">
								  <input type="hidden" name="cod_emisor" id="cod_emisor">

                                {{--@else
                                  <div class="form-group"><label class="col-sm-2 control-label">Número de Cédula <span class="text-danger">*</span></label>

                                      <div class="col-sm-10">
                                        <input type="text" placeholder="Cédula" name="cedula" id="cedula" class="form-control input-lg m-b" maxlength="10" onkeyup="validadoCed();this.value=Text(this.value);">
                                        <div id="msgCedula" class="text-danger" ></div>
                                        <!--<span class="help-block m-b-none">Inserte el número de cédula sin puntos o caracteres especiales.</span>-->
                                      </div>
                                  </div>

                                @endif--}}

                                 <div class="hr-line-dashed"></div>
                                      <div class="form-group"><label class="col-sm-2 control-label">Tarjeta de Membresía <span class="text-danger">*</span></label>
                                            <div class="col-md-2">
                                              <select class="form-control input-lg m-b" name="mon_nombre" id="monedass" >
                                                <option value="" selected>Moneda</option>
                                              </select>
                                              <div id="msgMoneda" class="text-danger" ></div>
                                            </div>
                                          <div class="col-sm-10 col-md-8 ">

                                              <div class="col-md-5">
                                                <select name="carnet" id="carnetSelect" class="form-control input-lg m-b disabled" >
                                                <option value="" >Seleccione</option>
                                              </select>
                                              <!--<input type="hidden" onchange="change_tipo(this.value)" placeholder="Tarjeta de Membresía" name="carnet" id="carnet" class="form-control input-lg m-b" maxlength="16" onkeyup="this.value=Numero(this.value);validadoCarnet()" onblur="validarMinimo(this.value)" readonly="">-->
                                              <!--<input type="text" onchange="change_tipo(this.value)" placeholder="Tarjeta de Membresía" name="carnetVisual" id="carnetVisual" class="form-control input-lg m-b" maxlength="16" readonly="">-->
                                              <div id="msgCarnet" class="text-danger"></div>
                                              <!--<span class="help-block m-b-none">Inserte el número Tarjeta de Membresía sin puntos o caracteres especiales.</span>-->
                                              
                                            </div>

                                              
                                          </div>
                                      </div>


                                @if($rol == 4)
                                    <div class="hr-line-dashed"></div>
                                    <div class="form-group"><label class="col-sm-2 control-label">Límite de Crédito</label>
                                        <div class="col-sm-10">
                                          <input type="text" placeholder="Límite de Crédito" name="limitecredito" id="limitecredito" class="form-control input-lg m-b" maxlength="20" readonly="true">
                                          <div id="msgLimite" class="text-danger"></div>
                                        </div>
                                    </div>

                                    <div class="hr-line-dashed"></div>

                                    <div class="form-group"><label class="col-sm-2 control-label">Crédito Disponible</label>
                                        <div class="col-sm-10">
                                          <input type="text" placeholder="Crédito Disponible" name="creditodisponible" id="creditodisponible" class="form-control input-lg m-b" maxlength="20" readonly="true">
                                          <div id="msgDisponible" class="text-danger"></div>
                                        </div>
                                    </div>

                                    

                                @endif
								
								@if($rol == 3)
									<div id="id_credito_disponible_comercio" style="display:none">
										<div class="hr-line-dashed"></div>
										<div class="form-group"><label class="col-sm-2 control-label">Crédito Disponible</label>
											<div class="col-sm-10">
											  <input type="text" placeholder="Crédito Disponible" name="creditodisponible" id="creditodisponible" class="form-control input-lg m-b" maxlength="20" readonly="true">
											  <div id="msgDisponible" class="text-danger"></div>
											</div>
										</div>									
									</div>								
								@endif

                               



                              <div class="hr-line-dashed"></div>
                                <div class="form-group"><label class="col-sm-2 control-label">Monto <span class="text-danger">*</span></label>

                                    <div class="col-sm-10">
                                      <!--<input required="required" type="text" placeholder="Monto en Bolívares" name="monto" id="monto" class="form-control input-lg m-b" onkeypress="return justNumbers(event);" maxlength ="10" value=",00"  onblur="format(this)" >-->

                                        <input required="required" type="text" placeholder="Monto" name="monto" id="monto" class="form-control input-lg m-b"  maxlength ="20" onkeypress="return justNumbers(event);" onblur="format(this)" onkeyup="validadoMonto()">
                                      <div id="msgMonto" class="text-danger"></div>
                                      <!--<span class="help-block m-b-none">Inserte el monto sin puntos o caracteres especiales. Limite 8 digitos, ej: 10.000.000</span>-->
                                    </div>

                                    <!--<div class="col-sm-2">
                                    <input type="text" name="decMonto" id="decMonto" onkeypress="return justNumbers(event);" class="form-control input-lg m-b" maxlength="2" placeholder="Dec." value="00">
                                    </div>-->
                                </div>

                                <input id="comercioPropina" type="hidden" name="comercioPropina" >
                                <div class="form-group dinamicoPropina"><label class="col-sm-2 control-label">Propina <span class="text-danger">*</span></label>
                                    <div class="col-sm-10" >
                                      <input id="val_prop" type="hidden" name="prop">
                                          <div class="col-sm-3 checks"><label> <input class="resetP" type="radio" value="1" name="propina" id="propina"> 5% </label></div>
                                          <div class="col-sm-3 checks"><label> <input class="resetP" type="radio" value="2" name="propina" id="propina"> 10% </label></div>
                                          <div class="col-sm-3 checks"><label> <input class="resetP" type="radio" value="3" name="propina" id="propina"> 15% </label></div>
                                          <div class="col-sm-3 checks"><label> <input class="resetP" type="radio" value="4" name="propina" id="propina"> Otro monto </label></div>
                                          <div class="dinamico">
                                            <div class="propina4">
                                              <div class="col-sm-13">
                                                <input type="text" placeholder="Monto de Propina" name="propina_monto" id="propina_monto" class="form-control input-lg m-b" onkeypress="return justNumbers(event);" maxlength ="20"   onblur="format(this)">

                                                  <span class="help-block m-b-none">Seleccione el porcentaje de la propina, en caso de (Otro Monto) no debe exceder del 30%.</span>
                                              </div>
                                              <!--<div class="col-sm-2">
                                                  <input type="text" name="decPropina" id="decPropina" onkeypress="return justNumbers(event);" class="form-control input-lg m-b" maxlength="2" placeholder="Dec." value="00">
                                              </div>-->
                                            </div>
                                          </div>

                                    </div>
                                </div>

                              @if($rol != 4)
                                @if($comercio->propina_act == FALSE || empty($comercio->propina_act))

                                @else
                                <div class="hr-line-dashed"></div>
                                <div class="form-group"><label class="col-sm-2 control-label">Propina <span class="text-danger">*</span></label>

                                    <div class="col-sm-10">
                                      <input id="val_prop" type="hidden" name="prop">
                                          <div class="col-sm-3 checks"><label> <input type="radio" value="1" name="propina" id="propina"> 5% </label></div>
                                          <div class="col-sm-3 checks"><label> <input type="radio" value="2" name="propina" id="propina"> 10% </label></div>
                                          <div class="col-sm-3 checks"><label> <input type="radio" value="3" name="propina" id="propina"> 15% </label></div>
                                          <div class="col-sm-3 checks"><label> <input type="radio" value="4" name="propina" id="propina"> Otro monto </label></div>
                                          <div class="dinamico">
                                            <div class="propina4">
                                              <div class="col-sm-13">
                                                <input type="text" placeholder="Monto de Propina" name="propina_monto" id="propina_monto" class="form-control input-lg m-b" onkeypress="return justNumbers(event);" maxlength ="20"   onblur="format(this)">

                                                  <span class="help-block m-b-none">Seleccione el porcentaje de la propina, en caso de (Otro Monto) no debe exceder del 30%.</span>
                                              </div>
                                              <!--<div class="col-sm-2">
                                                  <input type="text" name="decPropina" id="decPropina" onkeypress="return justNumbers(event);" class="form-control input-lg m-b" maxlength="2" placeholder="Dec." value="00">
                                              </div>-->
                                            </div>
                                          </div>

                                    </div>
                                </div>
                                @endif
                              @else
                                @if(!empty($comercio->propina_act) && $rol == 4)
                                            @if($comercio->propina_act == FALSE )

                                            @else
                                            <div class="hr-line-dashed"></div>
                                            <div class="form-group"><label class="col-sm-2 control-label">Propina <span class="text-danger">*</span></label>

                                                <div class="col-sm-10">
                                                  <input id="val_prop" type="hidden" name="prop">
                                                      <div class="col-sm-3 checks"><label> <input type="radio" value="1" name="propina" id="propina"> 5% </label></div>
                                                      <div class="col-sm-3 checks"><label> <input type="radio" value="2" name="propina" id="propina"> 10% </label></div>
                                                      <div class="col-sm-3 checks"><label> <input type="radio" value="3" name="propina" id="propina"> 15% </label></div>
                                                      <div class="col-sm-3 checks"><label> <input type="radio" value="4" name="propina" id="propina"> Otro monto </label></div>
                                                      <div class="dinamico">
                                                        <div class="propina4">
                                                          <div class="col-sm-13">
                                                            <input type="text" placeholder="Monto de Propina" name="propina_monto" id="propina_monto" class="form-control input-lg m-b" onkeypress="return justNumbers(event);" maxlength ="20"   onblur="format(this)">

                                                              <span class="help-block m-b-none">Seleccione el porcentaje de la propina, en caso de (Otro Monto) no debe exceder del 30%.</span>
                                                          </div>
                                                          <!--<div class="col-sm-2">
                                                              <input type="text" name="decPropina" id="decPropina" onkeypress="return justNumbers(event);" class="form-control input-lg m-b" maxlength="2" placeholder="Dec." value="00">
                                                          </div>-->
                                                        </div>
                                                      </div>

                                                </div>
                                            </div>
                                            @endif
                                @endif
                              @endif


                                  {{-- <div class="hr-line-dashed"></div>
                                  <div class="form-group"><label class="col-sm-2 control-label">Bancos <span class="text-danger">*</span></label>

                                    <div class="col-sm-10"> --}}
  {{--                                     @if (count($bancos)>0)
  --}}
                                      {{-- <select id="fk_id_banco" readonly="readonly" name="fk_id_banco" class="form-control input-lg m-b" data-bv-field="status" required="required">
                                      </select> --}}
                                    {{--  @else
                                      <p class="text-danger">No hay categorías registradas.</p>
                                      <p>Si desea registrar una categoría haga click <a href="{{ route(transacciones.index) }}" title="Categorias">aquí</a>.</p>
                                      @endif --}}
                                {{--     </div>
                                  </div> --}}

                                  <div class="hr-line-dashed"></div>
                                  <div class="form-group">
                                    <div class="title-action">
                                      <a href={{route('transacciones.index')}} class="btn btn-white"><i class="fa fa-times"></i> Cancelar </a>

                                      @if($rol == 4)
                                        <button class="btn btn-primary" type="button" id="Submit2" name="Submit2" value="Enviar autorización" onclick="validarT();" /><i class="fa fa-check"></i> Enviar autorización</button>
                                      @else
                                        <button class="btn btn-primary" type="button" id="Submit2" name="Submit2" value="Enviar autorización" onclick="validarTT();" /><i class="fa fa-check"></i> Enviar autorización</button>
                                      @endif

                                    </div>
                                  </div>


                          </div>
                      </div>
                  </div>
              </div>

          </div>
                                      </form>
                                      @include('transacciones.validar')




  @stop


    @section('scripts')
      <!-- page js -->
      <script src="{!!asset('js/jackpotScripts/jackpotFunctions.js')!!}"></script>
	  <script src="{!!asset('js/plugins/select2/js/select2.min.js')!!}"></script>
		<script type="text/javascript">
		  $('.select2').select2();
		</script>	  
      <!--<script src="{{-- asset('js/plugins/iCheck/icheck.min.js')--}}"></script>
	
	<script src="{!!asset('js/plugins/jasny/jasny-bootstrap.min.js')!!}"></script>

          <script>
              $(document).ready(function () {
                  $('.i-checks').iCheck({
                      checkboxClass: 'icheckbox_square-green',
                      radioClass: 'iradio_square-green',
                  });
              });
          </script>-->
          @if(!empty(Session::get('token_code')) && Session::get('token_code') >=0)


  <script>


	  

$(document).ready(function() {

	var url = window.location;

	var pat = /create/;

	if(pat.test(url) == true){
	  url=String(url);

	  url=url.replace("/transacciones/create",'');

	} 
			
    var code = {{Session::get('token_code')}}
    $('#token').modal('show');
    IdTransModal=$("#IdTransModal").val();
	validate_pin=$("#validate_pin").val();
    show_transacciones(IdTransModal,validate_pin);
    setInterval(() => {
      $.get(url + '/checkStatus/'+code, function(res){

		var urlS = window.location;
		var patS = /create/;
		if(patS.test(urlS) == true){
		  urlS=String(urlS);

		  urlS=urlS.replace("/transacciones/create",'');

		}
		if(res.status != 1 && res.status != 0 && res.status != 10)
		{
			location.href = urlS +'/transacciones?estatus='+"error";
		}
		else{

			if(res.status == 0)
			{
				
				location.href = urlS + '/transacciones?estatus='+"ok";
			}
		} 
      })
    }, 7000);
});




  </script>
  @endif



      <script type="text/javascript">
          window.rol = {{$rol}}
          window.currentCarnets=[];
		  function BuscarCarnetEnArregloLocal(value)
		  {
            var currency = currentCarnets.find(function(carnet){return carnet.carnet == value});
            if(currency){

             // $("#monedas").val(currency.mon_id);
              $("#creditodisponible").val(currency.disponible);
              $("#creditodisponibleOcult").val(currency.disponible);
              $("#limitecredito").val((currency.limite));
			  $("#Transar").val(currency.transar);
			  $("#cod_emisor").val(currency.cod_emisor);
			  
			  
				if(currency.carnet.substr(-20,4) == "6540")
				{
				  $("#id_credito_disponible_comercio").css("display", "block");				  
				}
				else{
					$("#id_credito_disponible_comercio").css("display", "none");
				}	
            }			  
		  }
          $('#carnetSelect').change(function(e){
			  BuscarCarnetEnArregloLocal($(e.target).val());
          })
          function clearselect() {
            document.querySelector("#carnetSelect").innerHTML = "";
          }
          function consultaDatos(){
			  
	//var newURL = window.location.protocol + "//" + window.location.host + "/" + window.location.pathname + window.location.search;
	//console.log(window.location.pathname); 
	
			  var PathMonedas = window.location.pathname.replace('transacciones/create', '');
			  //alert(PathMonedas);

              var cedula = $("#cedula").val();
              var comercio = $("#fk_id_comercio").val();			  
			  if(comercio != "" && cedula != "")
			  {
				  var limite = "";
				  var disponible = "";
				  var carnet = "";
				  var transar = true;
					$.get(PathMonedas + 'monedas/consultaDatos/' + cedula+ '/'+ comercio, function(res){
					  if(res.data){
						var arr = Array.from(new Set(res.data.map(JSON.stringify))).map(JSON.parse);
						var options = '';
						if(arr.length > 1)
						{
							options = '<option value="">Seleccione</option>';
							arr.forEach(function(moneda){
							  options += '<option value="' + moneda.mon_id + '">' + moneda.mon_nombre + '</option>';
							});						
						}
						else{
							options += '<option value="' + arr[0].mon_id + '">' + arr[0].mon_nombre + '</option>';
						}	
						

						$('#monedass').html(options);
						$('#monedass').change(function(e){
						  var moneda = e.target.value; 
						  if(moneda != ''){
							var carnets = currentCarnets.filter(function(carnet){
							  return carnet.mon_id == moneda;
							});
							var options = '<option value="">Seleccione</option>';
							carnets.forEach(function(carnet){
								options += '<option value="' + carnet.carnet + '">' + carnet.carnet.substr(-20,4) + ' XXXX XXXX ' + carnet.carnet.substr(-4) + '</option>'
							});
							$('#carnetSelect').html(options);
							//$('#carnetSelect').val(carnet[0].carnet).trigger('change');

						  }
						});
					  }
					});

					$.get("consultaDatos/"+ cedula+ '/' + comercio, function(respuesta){
					  console.log(comercio);
					  clearselect();
					  if (!respuesta['fallido'] && respuesta.length) {
						/**/
						$("#msgCedula").html("");
						if(respuesta[0].id == 0)
						{
							currentCarnets= [];
							
						  if(rol == 4){
							  $("#msgCedula").html("El comercio no se encuentra afiliado, agradecemos comunicarse con el Centro de Atención Banplus Pay");
							}else{
								$("#msgCedula").val('');
								  swal({
									title: "Notificación",
									text: "El comercio no se encuentra afiliado, agradecemos comunicarse al Centro de Atención President vía  Whatsapp al 0412 Banplus (2267587), llamada al 0212-9092003, correo electrónico Presidentclub@banplus.com.",
									allowOutsideClick: false,
									allowEscapeKey: false,
									type: "warning"
								  }).then(function(result){

								})								
							}								
						}
						else
						{
						  if(respuesta.length){
							currentCarnets=respuesta
							if(respuesta.length > 1)
							{
								var options = '<option value="">Seleccione</option>';
								respuesta.forEach(function(carnet){
									options += '<option value="' + String(carnet.carnet) + '">' + String(carnet.carnet).substr(-20,4) + ' XXXX XXXX ' + String(carnet.carnet).substr(-4) + '</option>';
								});
								
								$("#limitecredito").val(limite);
								$("#creditodisponible").val(disponible);
								$("#creditodisponibleOcult").val(disponible);
								$("#carnetVisual").val(carnet.substr(-20,4)+' XXXX XXXX '+carnet.substr(-4));
								$("#msgCedula").val('');								
							}
							else{
									options += '<option value="' + respuesta[0].carnet + '">' + respuesta[0].carnet.substr(-20,4) + ' XXXX XXXX ' + respuesta[0].carnet.substr(-4) + '</option>';
									BuscarCarnetEnArregloLocal(respuesta[0].carnet);
								}
							$('#carnetSelect').html(options);
						  }else{
							currentCarnets= [];
						  }								
						}					 
					  }else{
						  $("#limitecredito").val('');
						  $("#creditodisponible").val('');
						  $("#creditodisponibleOcult").val('');
						  $("#carnet").val('');
						  $("#carnetVisual").val('');
						  $("#cod_emisor").val('');
						$("#Transar").val('');
						  if(rol == 4){
							  $("#msgCedula").html("No existe la cédula del cliente");
							}else{
							  //cedula invalida

							swal({
							  title: "Notificación",
							  text: "Por favor valide el número de cédula que está ingresando, en caso de persistir este mensaje el cliente debe comunicarse al Centro de Atención President vía  Whatsapp al 0412 Banplus (2267587), llamada al 0212-9092003, correo electrónico Presidentclub@banplus.com.",
							  allowOutsideClick: false,
							  allowEscapeKey: false,
							  type: "warning"
							}).then(function(result){
							  if(result.value){
								  console.log($("#cedula").val());
								  var dni = $("#cedula").val();
								  console.log("DNI: " + dni);
								  
									var url = window.location;


									var pat = /create/;

									if(pat.test(url) == true){
										url=String(url);

										url=url.replace("/transacciones/create",'');
									}								  
									$.ajax({

									  url: url + "/EmailCedulaInvalida",
									  data: {ci: dni},
									  method: "GET",
									  succes: onSuccess
									});//end ajax
									function onSuccess(res){
									  if(res === 'ok'){
										window.location.href = "{{URL('/transacciones')}}";
									  }else{
										window.location.href = "{{URL('/home')}}";
									  }
									}//end function onSuccess
								}//end if result.value
							  })//end .then
							}//end else

					  }


					});				  
			  }
          }

          function validadoCed(){

              $("#msgCedula").html("");

          }

          function validadoCarnet(){
              $("#msgCarnet").html("");
          }

          function validadoMonto(){
              $("#msgMonto").html("");
          }
		  
			$('#fk_id_comercio').on('change', function() {
			  $("#msgComercio").html("");
			  consultaDatos();
			});

					  
			$('#monedass').on('change', function() {
			  $("#msgMoneda").html("");
			});
			
			$('#carnetSelect').on('change', function() {
			  $("#msgCarnet").html("");
			});				

          function validarTT(){

              var monto = $("#monto").val();
              var disponible = $("#creditodisponibleOcult").val();
              var propina = 0;
              var disp = "";
              var mont = "";
			  var cod_emisor = $("#cod_emisor").val();
			  var preautorizar = false;			  

              if(monto){
                  monto = monto.split(".",10);
                  for(var i = 0;i<monto.length;i++){
                      mont = mont.concat(monto[i])
                  }
              }else{
                mont = '0';
              }

              if(disponible){
                disponible = disponible.split(".",10);
                for(var i = 0;i<disponible.length;i++){
                    disp = disp.concat(disponible[i])
                }
              }else{
                disp = '0';
              }

              mont = mont.replace(",",".");
              monto = parseFloat(mont);
              disp = disp.replace(",",".");
              disponible = parseFloat(disp);


              if($('input:radio[name=propina]:checked').val()){
                  /*propina al 5% */
                  if($('input:radio[name=propina]:checked').val() == 1){
                      propina = (monto * 5) / 100;
                      monto = monto + propina;
                  }else if($('input:radio[name=propina]:checked').val() == 2){
                      propina = (monto * 10) / 100;
                      monto = monto + propina;
                  }else if($('input:radio[name=propina]:checked').val() == 3){
                      propina = (monto * 15) / 100;
                      monto = monto + propina;
                  }else if($('input:radio[name=propina]:checked').val() == 4){

                      var propina_monto = $("#propina_monto").val();
                      propina_monto = propina_monto.replace(".","");
                      propina_monto = propina_monto.replace(",",".");
                      propina_monto = parseFloat(propina_monto);

                      monto = monto + propina_monto;

                  }

              }


              if($("#cedula").val()==""){
                  $("#msgCedula").html("Debe ingresar identificación");
              }

              if($("#carnet").val()==""){
                  //$("#msgCarnet").html("Debe ingresar la Tarjeta de Membresía");
              }

              if($("#monto").val()==""){
                  $("#msgMonto").html("Debe ingresar un monto");
              }

              if($("#monto").val() < '0,5'){
                  $("#msgMonto").html("El monto mínimo a ingresar es de 0,5 BsS");
              }
			  
			  if($("#monedass").val() == "")
			  {
				  $("#msgMoneda").html("Seleccione Moneda");
			  }	

              if($("#carnetSelect").val()==""){
                  $("#msgCarnet").html("Debe seleccionar un producto");
              }			  
			  
			if($("#Transar").val() == "false"){
				if(rol != 3){				
				   $("#msgCedula").html("El cliente no esta autorizado para realizar transacciones con este producto");
				}
			}			  

            var url = window.location;

            var pat = /create/;

            if(pat.test(url) == true){
              url=String(url);

              url=url.replace("/transacciones/create",'');

            }            
   
            if($("#Transar").val() == "false"){
              console.log($("#Transar").val());
			  $("#msgCedula").val('');
              swal({
                title: "Notificación",
                text: "Agradecemos informar al cliente que debe comunicarse al Centro de Atención President vía  Whatsapp al 0412 Banplus (2267587), llamada al 0212-9092003, correo electrónico Presidentclub@banplus.com.",
                allowOutsideClick: false,
                allowEscapeKey: false,
                type: "warning"
              }).then(function(result){
                if(result.value){
                    var dni = $("#cedula").val();
                    var monto = $("#monto").val();
					  var producto = $("#carnetSelect").val();
					  				

                      $.ajax({
                        url: url + "/ClienteRestriccion",
                        data: {
                          ci: dni,
                          monto: monto,
						  producto: producto,
                          
                        },
                        method: "GET",
                        succes: onSuccess
                      });//end ajax
                      function onSuccess(res){
                        if(res === 'ok'){
                          window.location.href = "{{URL('/transacciones')}}";
                        }else{
                          window.location.href = "{{URL('/home')}}";
                        }
                      }
                  }
                })
            }
            else{
				if(cod_emisor != "174")
				{
					if(monto > disponible){
					console.log( $("#monto").val() );
					swal({
					  title: "Notificación",
					  text: "Agradecemos informar al cliente que debe comunicarse al Centro de Atención President vía  Whatsapp al 0412 Banplus (2267587), llamada al 0212-9092003, correo electrónico Presidentclub@banplus.com.",
					  allowOutsideClick: false,
					  allowEscapeKey: false,
					  type: "warning"
					}).then(function(result){
					  if(result.value){
						  var dni = $("#cedula").val();
						  var monto = $("#monto").val();
						  var producto = $("#carnetSelect").val();
						  
							var url = window.location;


							var pat = /create/;

							if(pat.test(url) == true){
								url=String(url);

								url=url.replace("/transacciones/create",'');
							}					  

							$.ajax({
							  url: url + "/MontoExcedido",
							  data: {
								ci: dni,
								monto: monto,
								producto: producto,
							  },
							  method: "GET",
							  succes: onSuccess
							});//end ajax
							function onSuccess(res){
							  if(res === 'ok'){
								window.location.href = "{{URL('/transacciones')}}";
							  }else{
								window.location.href = "{{URL('/home')}}";
							  }
							}//end function onSuccess
						}//end if result.value
					  })//end .then

					  //$("#msgMonto").html("El monto sobrepasa el disponible, por favor verifique la suma del monto y la propina");
				  }
				  else{
					  preautorizar  = true;
				  }
				}
				else{
					preautorizar  = true;
				}
            }

              if($("#cedula").val()!="" &&
                $("#carnet").val()!="" &&
                $("#monto").val()!="" &&
				$("#carnetSelect").val()!="" &&
				$("#monedass").val()!="" &&
               $("#Transar").val() != "false" &&
                $("#monto").val() >= '0,5' && preautorizar == true){
                    $("#Submit").prop("disabled",true);
					$("#Submit2").prop("disabled",true);
					$("#divLoading").show();
                    $("#transaccionForm").submit();
              }

          }


          function validarT(){			  			  

              var monto = $("#monto").val();
              var disponible = $("#creditodisponible").val();
              var limitecredito = $("#limitecredito").val();
              var propina = 0;
              var limitC = "";
              var disp = "";
              var mont = "";
			  var cod_emisor = $("#cod_emisor").val();
			  var preautorizar = false;

              monto = monto.split(".",10);
              disponible = disponible.split(".",10);
              limitecredito = limitecredito.split(".",10);

              for(var i = 0;i<monto.length;i++){
                mont = mont.concat(monto[i])
              }

              for(var i = 0;i<disponible.length;i++){
                disp = disp.concat(disponible[i])
              }

              for(var i = 0;i<limitecredito.length;i++){
                limitC = limitC.concat(limitecredito[i])
              }

              mont = mont.replace(",",".");
              monto = parseFloat(mont);
              disp = disp.replace(",",".");
              disponible = parseFloat(disp);
              limitC = limitC.replace(",",".");
              limitecredito = parseFloat(limitC);


              if($('input:radio[name=propina]:checked').val()){
                  /*propina al 5% */
                  if($('input:radio[name=propina]:checked').val() == 1){
                      propina = (monto * 5) / 100;
                      monto = monto + propina;
                  }else if($('input:radio[name=propina]:checked').val() == 2){
                      propina = (monto * 10) / 100;
                      monto = monto + propina;
                  }else if($('input:radio[name=propina]:checked').val() == 3){
                      propina = (monto * 15) / 100;
                      monto = monto + propina;
                  }else if($('input:radio[name=propina]:checked').val() == 4){

                      var propina_monto = $("#propina_monto").val();
                      propina_monto = propina_monto.replace(".","");
                      propina_monto = propina_monto.replace(",",".");
                      propina_monto = parseFloat(propina_monto);

                      monto = monto + propina_monto;

                  }

              }

              if($("#cedula").val()==""){
                  $("#msgCedula").html("Debe ingresar identificación");
              }

              if($("#carnetSelect").val()==""){
                  $("#msgCarnet").html("Debe seleccionar un producto");
              }

              if($("#monto").val()==""){
                  $("#msgMonto").html("Debe ingresar un monto");
              }

              if($("#monto").val() < '0,5'){
                  $("#msgMonto").html("El monto mínimo a ingresar es de 0,5 BsS");
              }
			  
			  if(cod_emisor != "174")
			  {
				  if(monto > disponible){
					  $("#msgMonto").html("El monto sobrepasa el disponible");
				  }
					else{
						preautorizar = true;
					}	
			  }
			  else{
				  preautorizar = true;
			  }
			  
			  if($("#fk_id_comercio").val() == "")
			  {
				  $("#msgComercio").html("Debe seleccionar un comercio antes de continuar");
			  }
			  
			  if($("#monedass").val() == "")
			  {
				  $("#msgMoneda").html("Seleccione Moneda");
			  }			  

            if($("#Transar").val() == "false"){
              $("#msgCedula").html("El cliente no esta autorizado para realizar transacciones");
            }
              /*if(disponible > limitecredito){
                  //$("#msgLimite").html("El Disponible no puede ser mayor al Limite");
                  $("#msgDisponible").html("El Disponible no puede ser mayor al Limite");
              }*/
			  
              if($("#cedula").val()!="" &&
                $("#carnet").val()!="" &&
                $("#monto").val()!="" &&
				$("#fk_id_comercio").val() != "" &&
				$("#monedass").val() != "" &&
				$("#carnetSelect").val()!= "" &&
               $("#Transar").val() != "false" &&
			   preautorizar == true &&
                $("#monto").val() >= '0,5'/*&&
                disponible <= limitecredito*/){
                    $("#Submit").prop("disabled",true);
					$("#Submit2").prop("disabled",true);
					$("#divLoading").show();
                    $("#transaccionForm").submit();
              }




          }

          /*if (!('#button').hasAttr('disabled'))
          $('#button').attr('onclick', 'someFunction();');
          else
          $('#button').removeattr('onclick');*/


      </script>


      <script type="text/javascript">

    function change_tipo($id) {


      // alert($id);
      var URLactual = window.location;
      var g=String(URLactual);
      var expresionRegular = 'transacciones/';
      var base = g.split(expresionRegular,1);
      var url =base+expresionRegular+'getbanco/'+$id;
      // alert(url);
      $.ajax({
      data:'',
      url:url,
      method:'get',
      cache:false,
      processData:false,
      contentType: false,
      success: function (data) {
        var select = $('#fk_id_banco');
        select.empty();
        // select.append("<option value=''>Seleccione un Banco...</option>");
        for(var i in data['data']){
          // console.log(data['data'][i].bancosId);
          select.append("<option value='"+data['data'][i].bancosId+ "'>" +data['data'][i].descripcion+"</option>");

        }
      }
    })

    }

    function justNumbers(e){

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

    function Numero(string){//solo numeros
      var out = '';
      //Se añaden los numeros validas
      var filtro = '1234567890';//Caracteres validos

      for (var i=0; i<string.length; i++)
        if (filtro.indexOf(string.charAt(i)) != -1)
        out += string.charAt(i);
      return out;
    }

    function Text(string){//solo numeros
      var out = '';
      //Se añaden los numeros validas
      var filtro = 'EVev1234567890';//Caracteres validos

      for (var i=0; i<string.length; i++)
        if (filtro.indexOf(string.charAt(i)) != -1)
        out += string.charAt(i);
      return out;
    }

    function validarMinimo(val){
      alert('aca')
    }

    // function change_tipo($id) {
    //   var URLactual = window.location;
    //   var g=String(URLactual);
    //   var expresionRegular = 'users/create';
    //   var base = g.split(expresionRegular,1);
    //   var url =base+'getpais/'+$id;
    //   console.log(url);
    //   $.ajax({
    //    data:'',
    //    url:url,
    //    method:'get',
    //    cache:false,
    //    processData:false,
    //    contentType: false,
    //    success: function (data) {
    //     var select = $('#fk_id_country');
    //     select.empty();
    //     select.append("<option value=''>Seleccione un país...</option>");
    //     for(var i in data['data']){
    //       select.append("<option value='"+data['data'][i].id+ "'>" +data['data'][i].name+"</option>");

    //     }
    //   }
    // })

    // }


    $.get( "{{URL('/divisas')}}",function(data){
      for(var i=0;i < data.length; i++){		  
      $("#monedas").append('<option value="'+data[i].mon_id+'">'+data[i].mon_nombre+'</option>');
      }
    });//Fin del desplegable divisa

      </script>
        <script>
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
