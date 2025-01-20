@extends('templates.base')

@section('body')
    <main class="flex flex-col md:flex-row items-stretch justify-stretch h-screen">
        <div class="flex flex-col items-center justify-center flex-1 bg-primary text-white">
            <h2 class="text-4xl">Antrian berikutnya</h2>
            <p class="font-bold" style="font-size: 12rem" id="antrianNext">{{ $next }}</p>
        </div>
        <div class="flex flex-col items-center justify-center flex-1 bg-white text-primary">
            <h2 class="text-4xl">Sedang Dilayani</h2>
            <p class="font-bold" style="font-size: 12rem" id="antrianSekarang">{{ $current }}</p>
        </div>
    </main>
@endsection

@push('scripts')
<script>
    window.addEventListener('DOMContentLoaded', function() {
        window.Echo.channel('antrian')
            .listen('.update', (e) => {
                const data = JSON.parse(e.message);
                document.getElementById('antrianNext').innerText = data.next;
                document.getElementById('antrianSekarang').innerText = data.current;
            });
        console.log(window.Echo);
    });
</script>
@endpush