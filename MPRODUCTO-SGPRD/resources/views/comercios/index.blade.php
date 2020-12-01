@extends('layouts.app')
@section('titulo', 'Comercios')
   
@section('contenido')
        <div class="row wrapper border-bottom white-bg page-heading">
          <div class="col-lg-8">
            <h2><i class="fa fa-diamond"></i>   Comercios</h2>
            <ol class="breadcrumb">
              <li>
              <a href="{{ url('home') }}">Panel</a>
              </li>
              <li>Comercios
              </li>
              <li class="active">
              <strong>Listado</strong>
              </li>
            </ol>
            </div>
            @permission('comercio-create')
            <div class="col-lg-4">
              <div class="title-action">
                <a href="{{ route('comercios.create', 0) }}" class="btn btn-primary"><i class="fa fa-plus"></i> Nuevo</a>
              </div>
          </div>
          @endpermission
        </div>

        <div class="wrapper wrapper-content ecommerce">
           @include('success')
           @include('flash::message')
           <div class="ibox-content m-b-sm border-bottom ">
                <div class="row">
                  
                 <form method="post" action=" {{ url('comercios') }} ">
                        {{ csrf_field() }}
                        <input type="hidden" name="_method" value="get">
                            <div class="panel-body">           
                                <div class="form-inline" >

                                  
                                  <div class="form-group" >
                                        <label class="control-label" for="descripcion">Nombre: </label><br>
                                        <div class="input-group">
                                        {!! Form::text('descripcion', null, ['class'=>'input-sm form-control']) !!}
                                      </div>
                                        
                                </div>
                             
  
                                <div class="form-group">
                                        <label class="control-label" for="razon_social">Razon Social: </label><br>
                                        <div class="input-group">
                                        {!! Form::text('razon_social', null, ['class'=>'input-sm form-control']) !!}

                                        </div>
                                        
                                </div>

                                <div class="form-group">
                                        <label class="control-label" for="rif">Rif: </label><br>
                                        <div class="input-group">
                                        {!! Form::text('rif', null, ['class'=>'input-sm form-control']) !!}

                                        </div>
                                        
                                </div>
                              
                                  <button type="submit" class="btn btn-primary" style="margin-top: 18px;">Buscar
                                  </button>
                                  <button type="button" id="refresh" class="btn btn-primary" style="margin-top: 18px;">Borrar
                                  </button>

                               
                              </div>
                            </div>
                    </form>
              </div>
            </div>



            <div class="row">
                <div class="col-lg-12">
                        <div class="ibox-content">
                          <h2>
                            <span class="text-navy">Últimos</span>
                              {{$countComer}}
                              <span class="text-navy">Comercios Registrados</span>
                          </h2>

                          <span class="text-navy" >
                            <a href="{{ url('ComercioExcel') }}" class="btn btn-primary" id="descargar">
                              <i class="fa fa-book"></i> 
                              Descargar
                            </a>
                          </span>

                          <div class="hr-line-dashed"></div>
                          <div class="table-responsive">
          <table id="datatab" class="table">
                                <thead>
                                <tr>
                                    <th >Nombre</th>
                                    <th  id="acc">Razón Social</th>
                                    <th >Rif</th>
									<th >Sucls.</th>
                                    <th id="acc">Cuenta Principal</th>
                                    <th hidden>Dirección</th>
                                    <th >Teléfono</th>
                                    <th id="corr">Correo</th>
                                    <th id="acc">Acción</th>

                                </tr>
                                </thead>
                                <tbody>
                                  @foreach($comercios as $element)
                                  <tr>
                                    <td >{{ $element -> fulldescripcion }}</td>
                                    <td >{{ $element -> razon_social }}</td>
                                    <td >{{ $element -> rif}} </td>
                                    <td>
									@if($element->sucursales > 0)
										Si <strong>({{ $element -> sucursales }})</strong>
									@else
										No
									@endif									
									</td>
                                    <td>
                                      {{  substr($element -> num_cta_princ,10,20) }} 
                                    </td>
                                    <td hidden>{{ $element -> direccion }}</td>
                                    <td>{{ $element -> telefono1 }}</td>
                                    <td>{{ $element -> email }}</td>
                                    <td>
                                      <div class="btn-group" >
                                          <button class="btn-white btn btn-sm" data-toggle="modal" data-target="#detalle" onclick="show_comercio('{{ $element -> IdComer}}')" title="Ver">
                                            <i class="fa fa-eye"></i> 
                                             
                                          </button>
                                          @permission('comercio-edit')
                                          <a class="btn-white btn btn-sm" data-target="#detalle_edit" href="{{ route('comercios.edit',[$element -> IdComer, 0]) }}" title="Editar">
                                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                            
                                         </a>
                                          @if ($element->deleted_at == null)
                                          
                                        <a class="btn-white btn btn-sm" onclick ="return confirm('¿Desea desactivar el comercio: {{$element->descripcion}}, Razón Social: {{$element->razon_social}}?')" href="{{ route('comercios.delete',[$element->IdComer,0]) }}" title="Desactivar">
                                          <i class="fa fa-trash-o" aria-hidden="true"></i>
                                         
                                        </a>
                                          @else
                                        <a class="btn-white btn btn-sm" onclick ="return confirm('¿Desea activar el comercio: {{$element->descripcion}}, Razón Social: {{$element->razon_social}}?')" href="{{ route('comercios.restore',[$element->IdComer,0]) }}" title="Activar">
                                          <i class="fa fa-plus-square-o" aria-hidden="true"></i>
                                        
                                        </a>
                                      @endif
                                     </a>
                                         @endpermission
                                      </div>
                                    </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                            </table>
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
                          <i class="fa fa fa-diamond modal-icon"></i>
                          <h2 class="modal-title">Detalle del Comercio</h2>
                          </div>
                          <div class="ibox-content">
                            <ul class="unstyled">
                                  <!--<div id="id"></div>-->
                                  <div id="descripcion"></div>
                                  <div id="razon_social"></div>
                                  <div id="rif"></div>
                                  <div id="direccion"></div>
                                  <div id="telefono1"></div>
                                  <div id="telefono2"></div>
                                  <div id="email"></div>
                                  <div id="propina_act"></div>
                                  <div id="num_cta_princ"></div>
                                  <div id="num_cta_secu"></div>
                                  <div id="num_cta_princ_dolar"></div>
                                  <div id="num_cta_secu_dolar"></div>
                                  <div id="num_cta_princ_euro"></div>
                                  <div id="num_cta_secu_euro"></div>
                                  <div id="estatus"></div>
                                  
                            </ul>
                          </div>

                      <div class="modal-footer">
                          <button type="button" class="btn btn-white" data-dismiss="modal">Cerrar</button>
                      </div>
                  </div>
              </div>
        </div>
     @stop
      
     @section('scripts')
    <!-- page js -->
    <script type="text/javascript" src="{{ asset('js/jackpotScripts/jackpotFunctions.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function(){
    $('#datatab').DataTable({
      responsive: true,
      "language": idioma,

    });

    $('#refresh').on('click', function(){
     location = window.location;
      
    });
  });
    </script>

    <!-- end page js -->
    @endsection
   

