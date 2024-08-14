@extends('templates.home')

@section('title', 'Klinik Pratama Nuryana Husada - Beranda')

@section('content')
    <div class="relative h-screen w-full bg-cover" style="background-image: url('{{ asset('images/banner.jpg') }}')">
        <div class="bg-gradient-to-b from-black-dark/40 to-black-dark/80 w-full h-screen pt-28">
            <div class="max-w-screen-md text-center text-white mx-auto">
                <h1 class="text-6xl mb-8 font-roboto">Klinik Pratama Nuryana Husada</h1>
                <p class="mb-12 font-poppins">Lorem ipsum dolor sit amet consectetur adipisicing elit. Doloribus est tempore rerum facere dolorem maxime omnis, possimus error commodi perspiciatis veritatis unde, dolore laborum quaerat voluptate dolor culpa ex suscipit.</p>
                <x-button type="a" href="javascript:void(0)" tint="bg-primary text-white hover:bg-primary-dark">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                        <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                        <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                    </svg>
                    Buat Janji Temu
                </x-button>
            </div>
        </div>
    </div>

    <section class="w-full p-8">
        <div class="max-w-screen-lg mx-auto">
            <h2 class="text-primary font-bold text-center text-3xl mb-8">Pelayanan Kami</h2>
            <div class="flex flex-col lg:flex-row gap-8 leading-loose">
                <div class="p-4 border rounded-lg bg-white">
                    <h3 class="text-2xl mb-3">Pemeriksaan kesehatan</h3>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Commodi nulla laboriosam dolorem dignissimos hic voluptatem velit quas corporis, veritatis maiores, iure sunt? Hic saepe et quasi natus. Cumque, repellat quibusdam?</p>
                </div>
                <div class="p-4 border rounded-lg bg-white">
                    <h3 class="text-2xl mb-3">Khitan Modern</h3>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Nesciunt, ad recusandae eos odio accusamus, dolores aut, in nisi quos accusantium animi et temporibus iure ea! Vitae odit commodi ipsam eum.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="p-4 bg-primary text-center text-white">
        &copy; 2024 Klinik Pratama Nuryana Husada
    </footer>
@endsection