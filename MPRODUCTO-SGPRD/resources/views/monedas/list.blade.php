@extends('layouts.app')
@section('titulo', 'Monedas')

@section('contenido')

        <div class="row wrapper border-bottom white-bg page-heading">
          <div class="col-lg-8">
            <h2><i class="fa fa-university"></i>   Monedas</h2>
            <ol class="breadcrumb">
              <li>
              <a href="{{ url('home') }}">Panel</a>
              </li>
              <li>Monedas
              </li>
              <li class="active">
              <strong>Listado</strong>
              </li>
            </ol>
            </div>
            @permission('user-create')
            <div class="col-lg-4">
              <div class="title-action">
                <a href="{{ url('create/monedas') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Nuevo </a>
              </div>
          </div>
          @endpermission
        </div>



        <div class="wrapper wrapper-content ecommerce">
          @include('flash::message')
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox">
                        <div class="ibox-content">
                          <h2>
                             {{ count($monedas) }} <span class="text-navy"> Monedas</span>
                          </h2>
                          <div class="hr-line-dashed"></div>
                          <div class="table-responsive">
                            <table class="footable table table-stripped toggle-arrow-tiny" data-page-size="10">
                                <thead>
                                <tr>
                                    <th>Divisa</th>
                                    <th>Símbolo</th>
                                    <th>Descripciíon</th>
                                    <th>Estatus</th>
                                    <th>Acción</th>
                                </tr>
                                </thead>

                                <tbody>
                                  @foreach($monedas as $element)
                                  <tr>
                                    <td>{{ $element -> mon_nombre }}</td>
                                    <td class="text-capitalize">{{ $element -> mon_simbolo}}</td>
                                    <td class="text-capitalize">{{ $element -> mon_observaciones }}</td>
                                    <td class="text-capitalize">{{ $element -> mon_status }}</td>


                                    <td class="col-lg-3">
                                      <div class="btn-group">
                                          <button class="btn-white btn btn-sm" data-toggle="modal" data-target="#detalle" onclick="show_monedas ('{{ $element -> mon_id}}')" >
                                            Ver
                                          </button>
                                        @permission('user-edit')
                                          <a class="btn-white btn btn-sm" data-target="#detalle_edit" href="{{ route('monedas.edit', $element -> mon_id) }}">
                                          Editar
                                          </a>
                                        @endpermission

                                        @if($element->mon_status == "ACTIVO")
                                          <a class="btn-white btn btn-sm" onclick ="return confirm('¿Desea desactivar la divisa {{$element->mon_nombre}}?')" href="{{ route('monedas.desactivar',$element->mon_id) }}">
                                           Desactivar
                                          </a>
                                            @else
                                          <a class="btn-white btn btn-sm" onclick ="return confirm('¿Desea activar la divisa {{$element->mon_nombre}}?')" href="{{ route('monedas.activar',$element->mon_id) }}">
                                          Activar
                                          </a>
                                        @endif
                                        
                                       </a>

                                      </div>
                                    </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="7">
                                        <ul class="pagination pull-right"></ul>
                                    </td>
                                </tr>
                                </tfoot>
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
                          <i class="fa fa-university modal-icon"></i>
                          <h2 class="modal-title">Detalle Monedas</h2>
                          </div>
                          <div class="ibox-content">
                            <ul class="unstyled">
                                  <div id="divisa"></div>
                                  <div id="simbolo"></div>
                                  <div id="descripcion"></div>
                            </ul>
                          </div>

                      <div class="modal-footer">
                          <button type="button" class="btn btn-white" data-dismiss="modal">Cerrar</button>
                      </div>
                  </div>
              </div>
        </div>


  <div class="modal inmodal" id="detalle_edit" tabindex="-1" role="dialog"  aria-hidden="true">
              <div class="modal-dialog">
                  <div class="modal-content animated fadeIn">
                      <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                          <i class="fa fa-university modal-icon"></i>
                          <h2 class="modal-title">Detalle Monedas</h2>
                          </div>
                              <div class="ibox-content">
                                <ul class="unstyled">
                                      <div id="id"></div>
                                      <div id="divisa"></div>
                                      <div id="rif"></div>
                                      <div id="telefono1"></div>
                                      <div id="telefono2"></div>
                                      <div id="telefono2_e"></div>
                                      <div id="contacto"></div>
                                </ul>
                              </div>

                              <div class="modal-footer">
                                <!--<div id="botonMod"></div>-->
                                <button type="button" class="btn btn-primary" data-dismiss="modal"  onclick="mod_banco()">Editar</button>
                                  <button type="button" class="btn btn-white" data-dismiss="modal">Cerrar</button>
                              </div>
                  </div>
              </div>
        </div>
     @stop
  @section('scripts')

  @if(session('status')=='ok'))
  <script type="text/javascript">
  swal("Operación Exitosa", "Se ha realizado la operacion solicitada exitosamente.", "success");
  </script>
  @endif

  @if(session('status')=='duplicado'))
  <script type="text/javascript">
  swal("Error", "La divisa que intenta crear ya se encuentra registrada, por favor verifique los datos ingresados", "error");
  </script>
  @endif

  @if(session('status')=='error'))
  <script type="text/javascript">
  swal("Error", "Comuniquese con el Administrador del Sistema", "error");
  </script>
  @endif

  @if(session('status')=='activo'))
  <script type="text/javascript">
  swal("Operación exitosa", "Divisa activada Satisfactoriamente", "success");
  </script>
  @endif

  @if(session('status')=='inactivo'))
  <script type="text/javascript">
  swal("Operación Exitosa", "Divisa desactivada Satisfactoriamente", "success");
  </script>
  @endif

    <!-- page js -->
    <script type="text/javascript" src="{{ asset('js/jackpotScripts/jackpotFunctions.js') }}"></script>
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
    <!-- end page js -->
    @endsection
