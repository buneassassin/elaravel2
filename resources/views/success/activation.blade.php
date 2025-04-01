@extends('layouts.app')

@section('content')
<div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="card shadow-sm" style="width: 100%; max-width: 500px;">
        <div class="card-body text-center">
            <h3 class="card-title mb-3">¡Cuenta Activada!</h3>
            <p class="text-success">{{ $message }}</p>
            <!-- Aquí el botón que redirige al login -->
            <a href="http://192.168.119.213:4200/login" class="btn btn-primary mt-3">Ir al Login</a>
        </div>
    </div>
</div>
@endsection
