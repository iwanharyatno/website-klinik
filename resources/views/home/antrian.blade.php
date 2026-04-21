@extends('templates.base')
@section('title', 'Antrian Pemeriksaan')

@push('head')
    <style>
        .active-patient>div.cell {
            background: #16a34a !important;
            color: #fde047 !important;
        }
    </style>
@endpush

@section('body')
    <div class="pt-4 pr-4 m-0 bg-[url('/images/background.jpg')] bg-cover bg-center min-h-screen">
        <header class="bg-purple-400 flex rounded-br-3xl">
            <div class="flex p-2 gap-4 ml-auto items-center">
                <img src="{{ asset('images/logo.png') }}" alt="" class="w-20 h-20">
                <div class="text-white text-center">
                    <h1 class="text-6xl font-bold">RUMAH SUNAT NURYANA HUSADA</h1>
                    <p class="text-2xl font-bold">KEBUMEN - BATURRADEN</p>
                </div>
            </div>
            <div class="ml-auto flex font-bold items-center gap-4 bg-sky-400 text-white p-4 rounded-tl-3xl rounded-br-3xl">
                @php
                    $date = \Carbon\Carbon::now();

                    $d = $date->translatedFormat('d F Y');
                    $day = $date->translatedFormat('l');

                    $time = $date->format('H:i');
                @endphp
                <div>
                    <p class="text-4xl uppercase">{{ $day }}</p>
                    <p class="text-2xl text-yellow-300">{{ $d }}</p>
                </div>
                <div class="text-4xl" id="dynamicTime">
                    {{ $time }}
                </div>
            </div>
        </header>
        <main class="flex items-center justify-center min-h-[80vh] gap-4">
            <div class="min-h-[70vh] w-1/4 ml-4">
                <h2
                    class="text-5xl font-bold uppercase text-center p-2 bg-gradient-to-r from-blue-200 to-blue-500 rounded-full mb-8">
                    Antrian</h2>
                <div class="overflow-hidden text-4xl">
                    <div class="w-full gap-4" id="patientList">
                        @if ($pasienSekarang)
                            <div class="font-bold text-green-600 flex gap-4 items-center mb-4 active-patient"
                                id="RM{{ $pasienSekarang->kode }}">
                                <div class="cell p-3 text-center rounded-full">
                                    <span>{{ $pasienSekarang->no_antrian }}</span>
                                </div>
                                <div class="cell p-3 text-center rounded-full w-full">
                                    <span>{{ $pasienSekarang->pasien->nama_pasien }}</span>
                                </div>
                            </div>
                        @endif
                        @foreach ($pasienMenunggu as $item)
                            <div class="font-bold text-green-600 flex gap-4 items-center mb-4" id="RM{{ $item->kode }}">
                                <div class="cell p-3 text-center bg-yellow-300 rounded-full">
                                    <span>{{ $item->no_antrian }}</span>
                                </div>
                                <div class="cell w-full p-3 bg-yellow-300 rounded-full text-center">
                                    <span>{{ $item->pasien->nama_pasien }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div id="videoContainer" class="relative w-9/12 min-h-[70vh]">
                @foreach ($multimedia as $item)
                    @if ($item->jenis === 'video-mp4')
                        <video src="{{ asset(\Storage::url($item->isi)) }}"
                            class="media-item absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-full"
                            style="z-index: {{ $loop->index == 0 ? 1 : -1 }}" data-type="mp4"></video>
                    @elseif ($item->jenis === 'video-youtube')
                        @php
                            // Extract YouTube ID from URL (or use it directly if 'isi' only contains the ID)
                            preg_match(
                                '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i',
                                $item->isi,
                                $match,
                            );
                            $ytId = $match[1] ?? $item->isi;
                        @endphp

                        <div class="media-item absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-full aspect-video"
                            style="z-index: {{ $loop->index == 0 ? 1 : -1 }}" data-type="youtube"
                            data-yt-id="{{ $ytId }}" id="yt-wrapper-{{ $loop->index }}">

                            <div id="yt-player-{{ $loop->index }}" class="w-full h-full pointer-events-none"></div>
                        </div>
                    @endif
                @endforeach
            </div>
        </main>
        <footer class="relative p-4 flex items-center bg-gradient-to-r from-blue-400 to-green-400 text-white">
            <div class="overflow-hidden whitespace-nowrap">
                <span class="marquee-text inline-block animate-marquee min-w-[100vw] text-4xl">
                    {{ $teksPanjangGabung }}
                </span>
            </div>
        </footer>
    </div>
@endsection

@push('scripts')
    <script>
        const dynamicTime = document.querySelector('#dynamicTime');

        function updateTime() {
            const currentDate = new Date();
            const hours = currentDate.getHours().toString();
            const minutes = currentDate.getMinutes().toString();

            dynamicTime.innerText = hours.padStart(2, "0") + ":" + minutes.padStart(2, "0");

            setTimeout(updateTime, 1000);
        }

        updateTime();
    </script>

    <script>
        let announcementAudio;

        window.addEventListener('DOMContentLoaded', function() {
            announcementAudio = new Audio('{{ asset('sound/announcement.mp3') }}');
            announcementAudio.load();

            const patientList = document.querySelector('#patientList');

            const addNewPatient = function(data, before) {
                const content = `
<div class="font-bold text-green-600 flex gap-4 items-center mb-4" id="RM${data.rekam_medis.kode}">
    <div class="cell p-3 text-center bg-yellow-300 rounded-full">
        <span>${data.rekam_medis.no_antrian}</span>
    </div>
    <div class="cell w-full p-3 bg-yellow-300 rounded-full text-center">
        <span>${data.rekam_medis.pasien.nama_pasien}</span>
    </div>
</div>
`;
                if (before) {
                    patientList.innerHTML = content + patientList.innerHTML;
                    return;
                }
                patientList.innerHTML += content;
            }

            window.Echo.channel('antrian')
                .listen('.update', (e) => {
                    const data = JSON.parse(e.message);
                    console.log(data);
                    if (data.type == 'status') {
                        playAnnouncementAndCall(data.voice);

                        const patientElement = document.querySelector('#RM' + data.rekam_medis.kode);

                        if (patientElement) {
                            patientElement.classList.add('animate-blink');
                            patientElement.classList.add('active-patient');
                        } else {
                            addNewPatient(data, true);
                            document.querySelector('#RM' + data.rekam_medis.kode).classList.add(
                                'animate-blink');
                            document.querySelector('#RM' + data.rekam_medis.kode).classList.add(
                                'active-patient');
                        }

                        setTimeout(function() {
                            document.querySelector('.active-patient').classList.remove('animate-blink');
                        }, 5000);

                        const currentActivePatient = document.querySelector('.active-patient');
                        if (currentActivePatient) {
                            if (currentActivePatient.getAttribute('id') == 'RM' + data.rekam_medis.kode) return;
                            currentActivePatient.remove();
                        }
                    } else if (data.type == 'insert') {
                        addNewPatient(data);
                    } else if (data.type == 'delete') {
                        document.querySelector('#RM' + data.rekam_medis.kode).remove();
                    }
                });
        });

        function playAnnouncementAndCall(text) {
            if (announcementAudio) {
                announcementAudio.currentTime = 0;
                announcementAudio.volume = 0.8;
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const marqueeText = document.querySelector('.marquee-text');
            const container = marqueeText.parentElement;

            const textWidth = marqueeText.offsetWidth;
            const containerWidth = container.offsetWidth;

            const baseSpeed = 15;
            const duration = (textWidth / containerWidth) * baseSpeed;

            marqueeText.style.animationDuration = `${duration}s`;

            marqueeText.classList.add('animate-marquee');
        });
    </script>

    <script src="https://www.youtube.com/iframe_api"></script>
<script>
    let ytApiReady = false;
    let videoList, currentMedia;
    const VIDEO_VOLUME = 0.5; // 0.0 to 1.0

    // 1. YouTube API Initialization Callback
    function onYouTubeIframeAPIReady() {
        ytApiReady = true;
        const ytElements = document.querySelectorAll('[data-type="youtube"]');
        
        ytElements.forEach(el => {
            const ytId = el.getAttribute('data-yt-id');
            const playerDiv = el.querySelector('div');
            
            el.ytPlayer = new YT.Player(playerDiv.id, {
                videoId: ytId,
                playerVars: {
                    'autoplay': 0,
                    'controls': 0, // Hide controls for a clean queue screen
                    'disablekb': 1,
                    'modestbranding': 1,
                    'rel': 0
                },
                events: {
                    'onStateChange': function(event) {
                        // When YouTube video ends (State 0), play next
                        if (event.data === YT.PlayerState.ENDED) {
                            playNext();
                        }
                    }
                }
            });
        });
    }

    // 2. Helper functions to handle both media types seamlessly
    function playMedia(el) {
        if (el.getAttribute('data-type') === 'mp4') {
            el.volume = VIDEO_VOLUME;
            el.play();
        } else if (el.getAttribute('data-type') === 'youtube' && el.ytPlayer && typeof el.ytPlayer.playVideo === 'function') {
            el.ytPlayer.setVolume(VIDEO_VOLUME * 100); // YT uses 0-100 scale
            el.ytPlayer.playVideo();
        }
    }

    function pauseMedia(el) {
        if (el.getAttribute('data-type') === 'mp4') {
            el.pause();
        } else if (el.getAttribute('data-type') === 'youtube' && el.ytPlayer) {
            el.ytPlayer.pauseVideo();
        }
    }

    function isMediaPaused(el) {
        if (el.getAttribute('data-type') === 'mp4') {
            return el.paused;
        } else if (el.getAttribute('data-type') === 'youtube' && el.ytPlayer && typeof el.ytPlayer.getPlayerState === 'function') {
            // YT.PlayerState.PLAYING is 1
            return el.ytPlayer.getPlayerState() !== 1; 
        }
        return true;
    }

    // 3. Main Logic
    document.addEventListener('DOMContentLoaded', function() {
        videoList = document.querySelector('#videoContainer');
        
        // Attach 'ended' listeners to all local MP4s
        document.querySelectorAll('[data-type="mp4"]').forEach(mp4 => {
            mp4.addEventListener('ended', playNext);
        });

        document.addEventListener('click', function(event) {
            if (!document.webkitIsFullScreen) {
                document.documentElement.requestFullscreen().catch(err => console.log("Fullscreen ignored"));
            }

            if (!currentMedia) {
                currentMedia = videoList.children[0];
                playMedia(currentMedia);
            } else {
                if (isMediaPaused(currentMedia)) {
                    playMedia(currentMedia);
                } else {
                    pauseMedia(currentMedia);
                }
            }
        });
    });

    const playNext = function() {
        let nextMedia = currentMedia.nextElementSibling;

        if (!nextMedia) {
            nextMedia = videoList.children[0]; // Loop back to start
        }

        nextMedia.classList.add('animate-fade-in');
        currentMedia.classList.add('animate-fade-out');

        nextMedia.style.zIndex = '1';
        
        playMedia(nextMedia);

        setTimeout(function() {
            nextMedia.classList.remove('animate-fade-in');
            currentMedia.classList.remove('animate-fade-out');
            currentMedia.style.zIndex = '-1';

            // Reset the previous video to the beginning
            if (currentMedia.getAttribute('data-type') === 'mp4') {
                currentMedia.currentTime = 0;
            } else if (currentMedia.getAttribute('data-type') === 'youtube' && currentMedia.ytPlayer) {
                currentMedia.ytPlayer.seekTo(0);
                currentMedia.ytPlayer.pauseVideo();
            }

            currentMedia = nextMedia;
        }, 1000);
    };
</script>
@endpush
