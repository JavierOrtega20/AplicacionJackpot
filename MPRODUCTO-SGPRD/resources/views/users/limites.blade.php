@extends('layouts.app')
@section('titulo')
Importar
@endsection
@section('contenido')
        <div class="row wrapper border-bottom white-bg page-heading">
          <div class="col-lg-8">
            <h2><i class="fa fa-users"></i>   Importar Limites</h2>
            <ol class="breadcrumb">
              <li>
              <a href="{{ url('home') }}">Panel</a>
              </li>
              <li>Limites
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
                  <h5>Importar Limites</h5>

              </div>
              <div class="ibox-content">
                  <div class="row">
                      <div class="col-sm-6 b-r"><h3 class="m-t-none m-b">Importacion masiva de Limites</h3>

                          <div id="notificacion_resul_fcdu"></div>

                          {!! Form::open(array('name'=>'carga_limites', 'id'=>'carga_limites', 'route' => 'users.cargar_limites','method'=>'POST', 'class'=>'formarchivo','enctype'=>'multipart/form-data','accept-charset'=>'UTF-8','files'=>'true')) !!}

                           {{ csrf_field() }}

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

                                  <b><input name="archivo" id="archivo" type="file"   class="archivo custom-file-input"  required/><br /><br />
                          </b>
                          </div>

                         
                          <div class="box-footer ">
                                              <button type="submit" class="btn btn-primary col-xs-12">Cargar Datos</button>
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
