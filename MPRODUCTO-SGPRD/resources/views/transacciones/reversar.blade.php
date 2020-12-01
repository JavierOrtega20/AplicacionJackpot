<!-- Reverso -->
        <div class="modal inmodal" id="detalleReverso" tabindex="-1" role="dialog"  aria-hidden="true">
          <div class="modal-dialog">
              <div class="modal-content animated fadeIn">
                  <div class="modal-header">
                      <button type="button" id="btnCloseReversar"  class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                      <i class="fa fa-exclamation-circle modal-icon"></i>
                      <h4 class="modal-title">Reversar Autorización</h4>
                      <small>Desea reversar la transacción?</small>
                  </div>
                  <div class="modal-body">
                      <p><strong>Esta acci&oacute;n es irreversible, cancelar&aacute; la transacci&oacute;n y el Banco no har&aacute; el pago correspondiente a su comercio.</p>
                  </div>
                   <form method="POST" id="frmReverso" action="{{ route('transacciones.reversar') }}" method="POST" enctype="multipart/form-data">
                    {{ csrf_field() }}
                  <div class="modal-footer">
                    <input id="idTransR" type="hidden" name="idTrans">
                      <button type="button" id="btnSubmitCancel" class="btn btn-white" data-dismiss="modal">Cancelar</button>
                      <button type="submit" id="btnSubmitReverse" class="btn btn-warning">Si, deseo reversar</button>
                  </div>
                  </form>
              </div>
          </div>
        </div>
    <!-- Reverso End -->
	
