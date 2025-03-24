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
    let announcementAudio;

    window.addEventListener('DOMContentLoaded', function() {
        // Preload the announcement sound
        announcementAudio = new Audio('{{ asset("sound/announcement.mp3") }}');
        announcementAudio.load();

        window.Echo.channel('antrian')
            .listen('.update', (e) => {
                const data = JSON.parse(e.message);
                document.getElementById('antrianNext').innerText = data.next;
                document.getElementById('antrianSekarang').innerText = data.current;
                if (data.voice) playAnnouncementAndCall(data.voice);
            });
    });

    function playAnnouncementAndCall(text) {
        if (announcementAudio) {
            announcementAudio.currentTime = 0;
            announcementAudio.play().then(() => {
                announcementAudio.onended = () => {
                    callPatient(text);
                };
            }).catch(error => {
                console.error('Error playing announcement sound:', error);
                callPatient(text);
            });
        } else {
            callPatient(text);
        }
    }

    function callPatient(text) {
        if ('speechSynthesis' in window) {
            var speech = new SpeechSynthesisUtterance(text);
            speech.lang = 'id-ID';
            window.speechSynthesis.speak(speech);
        }
    }
</script>
@endpush