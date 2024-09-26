@extends('layouts.custom')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="col-md-6">
        <div class="card shadow-sm p-4">
            <div class="card-header text-center" style="background-color: #f1f5fb; padding: 10px;">
                <h4 class="mb-0" style="color: #4A73F1; font-weight: 600;">Ingresar Clave de Acceso</h4>
            </div>
            <div class="card-body">
                <!-- Mensaje de error -->
                @if (session('error'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                    </div>
                @endif

                <!-- Formulario -->
                <form action="{{ route('test.verificar') }}" method="POST">
                    @csrf
                    <div class="form-group mb-4">
                        <label for="clave_acceso" class="font-weight-bold">Clave de Acceso:</label>
                        <input type="text" name="clave_acceso" class="form-control" placeholder="Ingresa la clave de acceso" required>
                    </div>
                    <button type="submit" class="btn btn-primary mx-auto d-block" style="width: 150px; margin-top: 20px;">Verificar</button>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection
