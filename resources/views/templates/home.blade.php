@extends('templates.base')

@section('body')
    <header class="bg-primary text-white font-roboto sticky top-0 z-50">
        <nav class="flex lg:flex-row flex-col lg:items-center">
            <a href="{{ route('home.index') }}" class="hover:underline inline-block p-4 font-bold">Klinik Pratama Nuryana Husada</a>
            <ul class="flex flex-col lg:flex-row lg:items-center lg:ml-auto">
                <li>
                    <a href="javascript:void(0)" class="hover:underline inline-block p-4">Informasi Layanan</a>
                </li>
                <li>
                    <a href="javascript:void(0)" class="hover:underline inline-block p-4">Jam Buka</a>
                </li>
                <li>
                    <x-button type="a" href="javascript:void(0)" tint="bg-cream-light text-black hover:bg-cream m-4">Daftar</x-button>
                </li>
            </ul>
        </nav>
    </header>
    @yield('content')
@endsection