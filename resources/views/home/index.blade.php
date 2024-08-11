@extends('templates.home')

@section('title', 'Klinik Pratama Nuryana Husada - Beranda')

@section('content')
    <div class="h-screen w-full bg-banner">
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