<!-- Log -->
        <div class="modal inmodal" id="log" tabindex="-1" role="dialog"  aria-hidden="true">
          <div class="modal-dialog modal-lg" style="width: 950px;">
              <div class="modal-content animated fadeIn">
                  <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                      <i class="fa fa-exclamation-circle modal-icon"></i>
                      <h4 class="modal-title" id="TituloTransaccionId">Historial de la transacción </h4>
                  </div>
                  <div class="modal-body">
					<div class="container">
						<div class="row">
						  <div class="col-md-3 p-3 mb-2 bg-primary text-white">
							Acción
						  </div>
						  <div class="col-md-3 p-3 mb-2 bg-primary text-white">
							Fecha
						  </div>
						  <div class="col-md-2 p-3 mb-2 bg-primary text-white">
							Usuario
						  </div>					  
						</div>	
					</div>
					<div id="idTable" class="container">
							
					</div>					
                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn btn-white" data-dismiss="modal">Cancelar</button>
                  </div>
              </div>
          </div>
        </div>
<!-- Log End -->