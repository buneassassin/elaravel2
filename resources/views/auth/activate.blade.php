@extends('layouts.app')

@section('content')
<div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="card shadow-sm" style="width: 100%; max-width: 500px;">
        <div class="card-body">
            <h3 class="card-title text-center mb-3">Activar Cuenta</h3>
            <p class="text-center text-muted mb-4">Ingresa el código de activación que recibiste en tu correo.</p>
            <form action="{{ route('user.activate') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="activation_code" class="form-label">Código de Activación</label>
                    <input type="text" name="activation_code" id="activation_code" class="form-control" placeholder="Ingresa tu código" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-success">Activar Cuenta</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
