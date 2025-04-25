@if(!empty($datos))
    <div class="alert alert-info">
        Estudiantes no elegidos por nadie:
        <strong>{{ implode(', ', $datos) }}</strong>
    </div>
@else
    <div class="alert alert-success">
        Todos los estudiantes fueron elegidos al menos una vez.
    </div>
@endif
