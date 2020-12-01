@extends('layouts.appLogin')

@section('contenido')

    <div class="middle-box text-center loginscreen animated fadeInDown">

        <div>
            <div>

                <h1 class="logo-name" style="visibility: hidden;"><img alt="Banplus"  src="{{asset('img/logo-meritop-fullcolor.svg')}}" width="300px" /></h1>

            </div>
            @include('success')
            <p>Para ingresar coloque su acceso proporcionado

            </p>
            <form class="m-t" method="POST" action="{{ route('login') }}">
                                        {{ csrf_field() }}
                <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                    <input id="email" type="email" class="form-control" placeholder="Correo Electrónico" name="email" value="{{ old('email') }}" required autofocus>
                        @if ($errors->has('email'))
                            <span class="help-block">
                                <strong>{{ $errors->first('email') }}</strong>
                            </span>
                        @endif
                </div>
                <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                    <input id="password" type="password" placeholder="Contraseña" class="form-control" name="password" required>
                        @if ($errors->has('password'))
                            <span class="help-block">
                                <strong>{{ $errors->first('password') }}</strong>
                            </span>
                        @endif
                </div>
                <div class="form-group">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Recordar Contraseña
                        </label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary block full-width m-b">Ingresar</button>

                <a href="{{ route('password.request') }}"><small>Olvido de contraseña?</small></a>
            </form>
            <p class="m-t"> <small>Meritop C.A. &copy; 2018</small> </p>
        </div>
    </div>

@endsection