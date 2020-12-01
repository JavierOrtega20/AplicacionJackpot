@if ($message = Session::get('success'))
        <div class="alert alert-success">
            <p>{{ $message }}</p>
        </div>
@endif

@if ($message = Session::get('sessionExists'))
        <div class="alert alert-danger">
            <p>{{ $message }}</p>
        </div>
@endif
