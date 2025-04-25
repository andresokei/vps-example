@extends('layouts.custom')

@section('content')

{{-- === SECCIÓN DE ESTILOS CSS PARA COHERENCIA === --}}
<style>
     body {
         background-color: #f8f9fa; /* Fondo general suave y coherente */
         font-family: 'Nunito', sans-serif; /* Ejemplo de fuente más amigable (si la usas en tu layout) */
         color: #333; /* Color de texto general oscuro */
     }

    .container {
        /* Centrar contenido y asegurar altura mínima */
        display: flex;
        flex-direction: column; /* Alinear elementos en columna */
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 30px; /* Añadir padding para evitar que el contenido toque los bordes en pantallas pequeñas */
        text-align: center; /* Asegurar texto centrado */
    }

    h1 {
        color: #4A73F1; /* Color del título principal (puedes usar un azul amigable de la paleta) */
        font-weight: bold;
        margin-bottom: 20px; /* Espacio debajo del título */
    }

    p {
        color: #555; /* Color del párrafo */
        font-size: 1.1em; /* Tamaño de fuente ligeramente mayor */
        margin-bottom: 30px; /* Espacio debajo del párrafo */
    }

    .btn-primary {
        /* Estilo de botón consistente */
        background-color: #6bb9f0; /* Color primario */
        border-color: #6bb9f0; /* Borde a juego */
        color: white; /* Texto blanco */
        padding: 12px 25px; /* Tamaño del padding */
        font-size: 1.1em; /* Tamaño de fuente */
        border-radius: 25px; /* Bordes redondeados (píldora) */
        transition: all 0.2s ease-in-out; /* Transición suave */
        box-shadow: 0 4px 8px rgba(0,0,0,0.1); /* Sombra */
        text-decoration: none; /* Asegurar que no tenga subrayado si es un enlace */
        display: inline-block; /* Asegurar que se comporte como un bloque inline para aplicar padding/margin */
    }
     .btn-primary:hover,
     .btn-primary:focus {
         background-color: #a0d6b4; /* Cambio de color al pasar ratón/enfocar */
         border-color: #a0d6b4;
         box-shadow: 0 6px 12px rgba(0,0,0,0.15);
         transform: translateY(-2px); /* Ligeramente levantado */
         color: white; /* Asegurar que el color del texto se mantenga blanco al pasar el ratón */
     }
     .btn-primary:active {
         transform: translateY(0); /* Sin levantamiento al clicar */
         box-shadow: 0 2px 5px rgba(0,0,0,0.1);
     }

</style>


<div class="container"> {{-- La clase container ya tiene estilos flexbox --}}
    <h1>¡Respuestas guardadas exitosamente!</h1>
    <p>Gracias por completar el test.</p>
    <a href="{{ route('dashboard') }}" class="btn btn-primary">Volver al Dashboard</a> {{-- Asegúrate de que la clase btn-primary esté bien aplicada --}}
</div>
@endsection