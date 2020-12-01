<div class="modal inmodal" id="token" tabindex="-1" role="dialog"  aria-hidden="true">
              <div class="modal-dialog">
                  <div class="modal-content animated fadeIn">
                      <div class="modal-header">
                          <button type="button" id="btnCloseValidar" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                          <i class="fa fa-phone modal-icon"></i>
							  <div id="h_no_requiere_pin" style="display: none;">
								  <h2 class="modal-title">Token</h2>
								  <small>Por favor ingrese el número de clave enviada al cliente</small>
							  </div>
							  <div id="h_requiere_pin" style="display: none;">
								<h2 class="modal-title">PIN</h2>
								<small>Por favor ingrese el PIN de Seguridad para transacciones procesadas con Otros Pagos</small>
							  </div>
						  						 
                          </div>
                          <form method="POST" id="frmValidar" action="{{ route('transacciones.autorizar') }}" onsubmit="return validateForm()" method="POST" enctype="multipart/form-data">
                          <div class="ibox-content" id="no_requiere_pin" style="display: none;">
                            <div class="form-group">
                              <label class="col-sm-4 control-label">Introduzca el Código<br> de 6 dígitos</label>
                              <div class="col-sm-8">
								<div id="msgTokenT" style="display:none;" class="text-danger">Ingrese el token enviado al cliente</div>
                                <input type="text" placeholder="000000" maxlength="6" class="form-control input-lg m-b" id="tokenseleT" name="tokenseleT">								
                                <br><br>
                              </div>								
                            </div>
                            <br><br>
                          </div>
						  
                          <div class="ibox-content" id="requiere_pin" style="display: none;">
                            <div class="form-group">
                              <label class="col-sm-4 control-label">Introduzca el PIN<br> de 6 dígitos</label>
                              <div class="col-sm-8">
							  <div id="msgTokenP" style="display:none;"  class="text-danger">Ingrese el PIN de Seguridad</div>
                                <input type="password" placeholder="******" maxlength="6" class="form-control input-lg m-b" id="tokenseleP" name="tokenseleP">								
                                <br><br>
                              </div>								
                            </div>
                            <br><br>
                          </div>						  

                      <div class="modal-footer">
                        {{ csrf_field() }}
                          <input id="validate_pin" type="hidden" name="validate_pin" value="{{ Session::get('token_pin') != null ? '1' : '0' }}">
						  <input id="idTrans" type="hidden" name="idTrans" value="{{Session::get('token_code')}}">
						  <input id="tokensele" type="hidden" name="tokensele" value="">
                          <button type="submit" style="display: none;" id="btnSubmitfrmValidar"></button>
						  <button type="button" id="btnValidate" class="btn btn-block btn-primary" onclick="ValidateForm()"> Aceptar </button>
                        
                      </div>
					</form>
                  </div>
              </div>
            </div>
			<script>
				window.onload = function() {
				  Validation();
				};

				function Validation()
				{
  
				  if($("#validate_pin").val() == 1)
				  {
					$("#requiere_pin").show("slow");
					$("#h_requiere_pin").show("slow");										
				  }
				  else
				  {
					$("#no_requiere_pin").show("slow");
					$("#h_no_requiere_pin").show("slow");					  
				  }						
				}
				
				function ValidateForm(){					
					if($("#validate_pin").val() == 1)
					{
						if ($("#tokenseleP").val() == "") {
							$("#msgTokenP").show("slow");
						}
						else{
							$("#tokensele").val($("#tokenseleP").val());
							$("#btnValidate").prop("disabled", true);
							$("#btnSubmitfrmValidar" ).click();
						}
					}
					else{
						if ($("#tokenseleT").val() == "") {
							$("#msgTokenT").show("slow");
						}
						else{
							$("#tokensele").val($("#tokenseleT").val());
							$("#btnValidate").prop("disabled", true);
							$("#btnSubmitfrmValidar" ).click();
						}						
					}					
				};				
			</script>