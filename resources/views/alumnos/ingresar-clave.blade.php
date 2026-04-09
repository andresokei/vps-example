@extends('layouts.custom')

@section('content')

{{-- Asegúrate de tener Font Awesome para el icono en el mensaje de error --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

{{-- === SECCIÓN DE ESTILOS CSS PARA COHERENCIA === --}}
<style>
     body {
         background-color: #f8f9fa; /* Fondo general suave y coherente */
         font-family: 'Nunito', sans-serif; /* Ejemplo de fuente más amigable (si la usas en tu layout) */
     }

    .card {
        border-radius: 15px; /* Bordes redondeados consistentes */
        border: none; /* Sin borde por defecto */
        overflow: hidden; /* Oculta cualquier contenido que se desborde por los bordes redondeados */
        box-shadow: 0 8px 15px rgba(0,0,0,0.1); /* Sombra más pronunciada y suave */
    }

    .card-header {
         /* Adaptar el estilo de cabecera de la otra vista */
        background: linear-gradient(45deg, #6bb9f0, #a0d6b4); /* Gradiente suave y cálido */
        color: #333; /* Color de texto para contraste */
        font-weight: bold; /* Texto en negrita */
        text-align: center;
        padding: 20px; /* Aumentar padding */
        border-bottom: none; /* Eliminar borde inferior si el gradiente llega hasta abajo */
    }

    .card-header h4 {
        margin-bottom: 0; /* Ajustar margen si es necesario */
        color: #333; /* Color de texto consistente con el header */
        font-weight: bold;
    }

    .card-body {
        padding: 30px; /* Aumentar padding interior */
    }

    .form-group label {
        font-weight: bold;
        color: #555; /* Color de etiqueta consistente */
        margin-bottom: 8px; /* Espacio debajo de la etiqueta */
    }

    .form-control {
        /* Estilo de inputs consistente */
        border-radius: 8px; /* Bordes redondeados para inputs */
        border-color: #ccc; /* Color de borde suave */
        box-shadow: 0 1px 3px rgba(0,0,0,0.05); /* Sombra sutil */
        padding: 10px 15px; /* Padding interior */
    }
     .form-control:focus {
         border-color: #81d4fa; /* Borde azul claro al enfocar */
         box-shadow: 0 1px 5px rgba(129, 212, 250, 0.5); /* Sombra azul al enfocar */
     }


    .btn-primary {
        /* Estilo de botón consistente con la otra vista */
        background-color: #6bb9f0; /* Color primario */
        border-color: #6bb9f0; /* Borde a juego */
        color: white; /* Texto blanco */
        padding: 12px 25px; /* Tamaño del padding */
        font-size: 1.1em; /* Tamaño de fuente */
        border-radius: 25px; /* Bordes redondeados (píldora) */
        transition: all 0.2s ease-in-out; /* Transición suave */
        box-shadow: 0 4px 8px rgba(0,0,0,0.1); /* Sombra */
    }
     .btn-primary:hover,
     .btn-primary:focus {
         background-color: #a0d6b4; /* Cambio de color al pasar ratón/enfocar */
         border-color: #a0d6b4;
         box-shadow: 0 6px 12px rgba(0,0,0,0.15);
         transform: translateY(-2px); /* Ligeramente levantado */
     }
     .btn-primary:active {
         transform: translateY(0); /* Sin levantamiento al clicar */
         box-shadow: 0 2px 5px rgba(0,0,0,0.1);
     }


     .alert-danger {
         /* Estilo para el mensaje de error */
         border-radius: 8px; /* Bordes redondeados */
         box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3); /* Sombra roja */
         font-weight: bold;
         display: flex; /* Para alinear icono */
         align-items: center;
     }
      .alert-danger .fas {
         margin-right: 10px; /* Espacio entre icono y texto */
      }


</style>


<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="col-md-6">
        <div class="card shadow-sm"> {{-- Clases de sombra ya definidas en CSS --}}
             <div class="card-header"> {{-- Clases de cabecera ya definidas en CSS --}}
                 <h4>Ingresar Clave de Acceso</h4>
             </div>
             <div class="card-body"> {{-- Clases de cuerpo ya definidas en CSS --}}
                @if ($errors->has('clave_acceso'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> {{ $errors->first('clave_acceso') }}
                    </div>
                @elseif (session('error'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('test.verificar') }}" method="POST">
                    @csrf
                    <div class="form-group mb-4">
                        <label for="clave_acceso">Clave de Acceso:</label> {{-- Clases de etiqueta ya definidas --}}
                        <input type="text" name="clave_acceso" id="clave_acceso" class="form-control" placeholder="Ingresa la clave de acceso" required> {{-- Clases de input ya definidas --}}
                    </div>
                     {{-- El botón ahora usa la clase btn-primary con los estilos definidos --}}
                    <button type="submit" class="btn btn-primary mx-auto d-block" style="width: 180px;">Verificar</button>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection
