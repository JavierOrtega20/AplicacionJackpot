@extends('layouts.app')
@section('titulo', 'Transacciones')
{{--     @include('flash::message')
 --}}
@section('contenido')

{!! Form::model($transacciones, ['method' => 'PATCH','route' => ['transacciones.update',$transacciones->idTrans],'class'=>'form-horizontal']) !!}

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
              <strong>Cargar cuenta</strong>
              </li>
            </ol>
            </div>
            <div class="col-lg-4">
              <div class="title-action">
                <a href={{route('transacciones.index')}} class="btn btn-white"><i class="fa fa-times"></i> Cancelar </a>
                <button class="btn btn-primary" data-toggle="modal" data-target="#detalleTransaccion"><i class="fa fa-check"></i> Editar Transacción </button>
              </div>
          </div>

        </div>

        <div class="wrapper wrapper-content animated fadeInRight ecommerce">

          @include('error')
          @include('success')

            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <h5>Cargo de cuenta al miembro</h5>
                        </div>
                         <div class="ibox-content">




                              <div class="hr-line-dashed"></div>

                              <div class="form-group"><label class="col-sm-2 control-label">Número de cédula <span class="text-danger">*</span></label>

                                  <div class="col-sm-10">
                                    {!! Form::text('dni', null, array('placeholder' => 'Cedula','class' => 'form-control input-lg m-b', 'value' =>'$transacciones->dni')) !!}
                                     <span class="help-block m-b-none">Inserte el número de cédula sin puntos o caracteres especiales.</span>
                                  </div>
                              </div>

                              <div class="hr-line-dashed"></div>
                              <div class="form-group"><label class="col-sm-2 control-label">Tarjeta de membresía <span class="text-danger">*</span></label>

                                  <div class="col-sm-10">
                                    {!! Form::text('carnet', null, array('placeholder' => 'Tarjeta de membresía','class' => 'form-control input-lg m-b', 'value' =>'$transacciones->carnet')) !!}
                                     <span class="help-block m-b-none">Inserte el número Tarjeta de membresía sin puntos o caracteres especiales.</span>
                                  </div>
                              </div>

                              <div class="hr-line-dashed"></div>
                              <div class="form-group"><label class="col-sm-2 control-label">Monto <span class="text-danger">*</span></label>

                                  <div class="col-sm-10">
                                    {!! Form::text('monto', null, array('placeholder' => 'Monto en Bolívares','class' => 'form-control input-lg m-b', 'value' =>'$transacciones->monto')) !!}
                                  
                                     <span class="help-block m-b-none">Inserte el monto sin puntos o caracteres especiales.</span>
                                  </div>
                              </div>
                                <div class="hr-line-dashed"></div>
                                <div class="form-group"><label class="col-sm-2 control-label">Bancos <span class="text-danger">*</span></label>

{{--                                   <div class="col-sm-10">
 --}}                             {{--      
                                    <select id="fk_id_banco" name="fk_id_banco" class="form-control input-lg m-b" data-bv-field="status" required="required">
                                     <option value="">Seleccione un Banco...</option>
                                      @foreach ($bancos as $value)
                                      <option value="{{ $value->id }}" @isset($transacciones)@if($transacciones -> idBanco == $value -> id) selected="selected" @endif @endisset>{{ $value->descripcion }}</option>
                                      @endforeach
                                    </select> --}}
                                 {{-- 
                                    <p class="text-danger">No hay categorías registradas.</p>
                                    <p>Si desea registrar una categoría haga click <a href="{{ route(transacciones.index) }}" title="Categorias">aquí</a>.</p> --}}
{{--                                   </div>
 --}}


                                    {{-- <div class="col-sm-10"><select class="form-control input-lg m-b" name="bank">
                                        <option>Banplus</option>
                                        <option>option 2</option>
                                        <option>option 3</option>
                                        <option>option 4</option>
                                    </select>

                                    </div> --}}
                                </div>





                        </div>
                    </div>
                </div>
            </div>

        </div>
                                  



  {!! Form::close() !!}

@stop