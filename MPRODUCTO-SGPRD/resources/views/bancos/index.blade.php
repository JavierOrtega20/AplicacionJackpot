@extends('layouts.app')
@section('titulo', 'Bancos')

@section('contenido')

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
              <strong>Listado</strong>
              </li>
            </ol>
            </div>
            @permission('banco-create')
            <div class="col-lg-4">
              <div class="title-action">
                <a href="{{ route('bancos.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Nuevo </a>
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
                             {{ count($bancos) }} <span class="text-navy"> Bancos</span>
                          </h2>
                          <div class="hr-line-dashed"></div>
                          <div class="table-responsive">
                            <table class="footable table table-stripped toggle-arrow-tiny" data-page-size="10">
                                <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Rif</th>
                                    <th>Teléfono 1</th>
                                    <th>Teléfono 2</th>
                                    <th>Contacto</th>
                                    <th>Acción</th>

                                </tr>
                                </thead>
                                <tbody>
                                  @foreach($bancos as $element)
                                  <tr>
                                    <td>{{ $element -> descripcion }}</td>
                                    <td class="text-capitalize">{{ $element -> rif}}</td>
                                    <td class="text-capitalize">{{ $element -> telefono1 }}</td>
                                    <td class="text-capitalize">{{ $element -> telefono2 }}</td>
                                    
                                    <td class="text-capitalize">{{ $element -> contacto }}</td>
                                    <td class="col-lg-3">
                                      <div class="btn-group">
                                          <button class="btn-white btn btn-sm" data-toggle="modal" data-target="#detalle" onclick="show_banco('{{ $element -> id}}')" >
                                            Ver 
                                          </button>
                                        @permission('banco-edit')
                                          <a class="btn-white btn btn-sm" data-target="#detalle_edit" href="{{ route('bancos.edit', $element -> id) }}">
                                          Editar
                                          </a>
                                        @endpermission
                
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
                          <h2 class="modal-title">Detalle del banco</h2>
                          </div>
                          <div class="ibox-content">
                            <ul class="unstyled">
                                  <div id="descripcion"></div>
                                  <div id="rif"></div>
                                  <div id="telefono1"></div>
                                  <div id="telefono2"></div>
                                  
                                  <div id="contacto"></div>
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
                          <h2 class="modal-title">Detalle del banco</h2>
                          </div>
                              <div class="ibox-content">
                                <ul class="unstyled">
                                      <div id="id"></div>
                                      <div id="descripcion"></div>
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


   

