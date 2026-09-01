{{-- Componente reutilizable para SweetAlert2 --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    @if (session('success'))
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: @json(session('success')),
            timer: 2500,
            showConfirmButton: false
        });
    @endif

    @if (session('error'))
        Swal.fire({
            icon: 'error',
            title: '¡Error!',
            text: @json(session('error')),
            confirmButtonColor: '#3085d6'
        });
    @endif

    @if (session('mensajeinternet'))
        Swal.fire({
            icon: 'error',
            title: '¡Error!',
            text: @json(session('mensajeinternet')),
            confirmButtonColor: '#3085d6'
        });
    @endif

    @if (session('warning'))
        Swal.fire({
            icon: 'warning',
            title: '¡Advertencia!',
            text: @json(session('warning')),
            confirmButtonColor: '#3085d6'
        });
    @endif

    @if (session('info'))
        Swal.fire({
            icon: 'info',
            title: '¡Información!',
            text: @json(session('info')),
            confirmButtonColor: '#3085d6'
        });
    @endif

    @if (session('status'))
        Swal.fire({
            icon: 'info',
            title: '¡Estado!',
            text: @json(session('status')),
            timer: 2500,
            showConfirmButton: false
        });
    @endif
});
</script>
