@extends('layouts.app')
@section('titulo', ' Crear Bancos')

@section('contenido')

<form method="POST" action="{{ route('bancos.store') }}" method="POST" enctype="multipart/form-data" class="form-horizontal" >
                              {{ csrf_field() }}

 <div class="row wrapper border-bottom white-bg page-heading">
          <div class="col-lg-8">
            <h2><i class="fa fa-university"></i>   Bancos</h2>
            <ol class="breadcrumb">
              <li>
              <a href="{{ url('home') }}">Panel</a>
              </li>
              <li>Bancos
              </li>
              <li class="active">
              <strong>Crear nuevo</strong>
              </li>
            </ol>
            </div>
            <div class="col-lg-4">
              <div class="title-action">
                <a href="{{route('bancos.index')}}" class="btn btn-white"><i class="fa fa-times"></i> Cancelar </a>
                <button type="submit" class="btn btn-primary" id="form-validation"><span class="btn-label">
                            <i class="fa fa-check"></i>
                        </span>Crear</button>
              </div>
          </div>

        </div>
        <div class="wrapper wrapper-content animated fadeInRight ecommerce">

                   @include('flash::message')


            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <h5>Crear nuevo banco</h5>
                        </div>
                        <div class="ibox-content">
                            
                              <div class="hr-line-dashed"></div>
                                  <div class="form-group"><label class="col-sm-2 control-label">Nombre <span class="text-danger">*</span> </label>

                                  <div class="col-sm-10">
                                    <input type="text" placeholder="Nombre" name="descripcion" id="descripcion" class="form-control input-lg m-b">
                                  </div>
                              </div>
                              <div class="hr-line-dashed"></div>
                              <div class="form-group"><label class="col-sm-2 control-label">Rif <span class="text-danger">*</span></label>
                                  <div class="col-sm-10">
                                    <input type="text" placeholder="Rif" name="rif" id="rif" class="form-control input-lg m-b" maxlength ="11">
                                     <span class="help-block m-b-none">Inserte el número de Rif sin carácteres especiales con solo estas iniciales. Ej: J, G, C.</span>
                                  </div>
                              </div>

                              <div class="hr-line-dashed"></div>
                              <div class="form-group"><label class="col-sm-2 control-label">Teléfono 1 <span class="text-danger">*</span></label>

                                  <div class="col-sm-10">
                                    <input type="text" placeholder="Teléfono 1" name="telefono1" id="telefono1" class="form-control input-lg m-b" onkeypress="return justNumbers(event);" >
                                  </div>
                              </div>
                              <div class="hr-line-dashed"></div>
                              <div class="form-group"><label class="col-sm-2 control-label">Teléfono 2</label>

                                  <div class="col-sm-10">
                                    <input type="text" placeholder="Teléfono 2" name="telefono2" id="telefono2" class="form-control input-lg m-b" onkeypress="return justNumbers(event);">
                                  </div>
                              </div>
                              <div class="hr-line-dashed"></div>
                              <div class="form-group"><label class="col-sm-2 control-label">Contacto <span class="text-danger">*</span></label>
                                  <div class="col-sm-10">
                                    <input type="text" placeholder="Contacto" name="contacto" id="contacto" class="form-control input-lg m-b">
                                  </div>
                              </div>

                              <div class="hr-line-dashed"></div>
                              <div class="form-group">
                                <div class="title-action">
                                  <a href="{{route('bancos.index')}}" class="btn btn-white"><i class="fa fa-times"></i> Cancelar </a>
                                  <button type="submit" class="btn btn-primary" id="form-validation"><span class="btn-label">
                                              <i class="fa fa-check"></i>
                                          </span>Crear</button>
                                </div>
                              </div>


                           
                        </div>
                    </div>
                </div>
            </div>

        </div>
         </form>
{{-- 
        <div class="modal inmodal" id="detalle" tabindex="-1" role="dialog"  aria-hidden="true">
              <div class="modal-dialog">
                  <div class="modal-content animated fadeIn">
                      <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                          <i class="fa fa-university modal-icon"></i>
                          <h2 class="modal-title">Banco cargado con éxito</h2>
                          </div>
                          <div class="ibox-content">
                            <ul class="unstyled">
                              <li><h3><span class="font-normal">ID: </span>3254545</h3></li>
                              <li><h3><span class="font-normal">Nombre: </span>Banplus Banco Universal C.A.</h3></li>
                              <li><h3><span class="font-normal">Rif: </span>J-00042303-2</h3></li>
                              <li><h3><span class="font-normal">Dirección: </span>Paseo Enrique Eraso, entrada de San Román, Torre La Noria, planta baja. Las Mercedes</h3></li>
                              <li><h3><span class="font-normal">Ciudad: </span>Caracas</h3></li>
                              <li><h3><span class="font-normal">Estado: </span>Miranda</h3></li>
                              <li><h3><span class="font-normal">Teléfono: </span>+58 212 9090712</h3></li>
                            </ul>
                          </div>

                      <div class="modal-footer">
                        <button type="button" class="btn btn-block btn-primary" data-dismiss="modal">Cerrar, e ir al listado</button>

                      </div>
                  </div>
              </div>
            </div> --}}



@stop

@section('scripts')
<script src="{!!asset('js/plugins/jsvalidator/jsvalidation.js')!!}"></script>
{!! JsValidator::formRequest('App\Http\Requests\BancoRequest') !!}

    <script type="text/javascript">


function justNumbers(e)
        {
        var keynum = window.event ? window.event.keyCode : e.which;
        if ((keynum == 8) || (keynum == 46))
        return true;
         
        return /\d/.test(String.fromCharCode(keynum));
        }
</script>
@endsection


