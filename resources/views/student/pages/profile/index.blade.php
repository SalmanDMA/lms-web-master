@extends('student.layout.app')

@section('title', 'Profile')
@section('nav_title', 'Profile')

@section('content')

    <div class="w-full max-w-md">
        @if (session('message'))
            <div class="alert {{ session('alertClass') }} alert-dismissible fade show" role="alert">
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <div class="flex items-center mb-4">
            <button class="rounded-full bg-blue-900 p-2 text-white backBtn" onclick="history.back()">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 12H5M12 19l-7-7 7-7" />
                </svg>
            </button>
        </div>
        <div class="flex items-center justify-center h-full">
            <div class="w-96">
                <!-- Header Biru -->
                <div class="bg-blue-900 text-white rounded-t-lg p-6 text-center">
                    <!-- Tombol Kembali -->
                    <div class="absolute left-6 top-6">
                        <button>
                            <svg class="w-6 h-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                    </div>
                    <h2 class="text-lg font-bold">Data Diri</h2>
                    <!-- Avatar -->
                    <div class="flex justify-center mt-4">
                        <div
                            class="w-20 h-20 bg-gray-300 rounded-full flex items-center justify-center border-4 border-white">
                            <svg class="w-10 h-10 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 14c3.31 0 6 2.69 6 6H6c0-3.31 2.69-6 6-6zM12 4a4 4 0 110 8 4 4 0 010-8z" />
                            </svg>
                        </div>
                    </div>
                    <!-- Nama dan Kelas -->
                    <h3 class="mt-4 text-xl font-semibold">{{ $user->fullname }}</h3>
                    <p class="text-sm">Kelas {{ $class }} {{ $subClasses }}</p>
                </div>
                <!-- Kontainer Pilihan -->
                <div class="bg-white rounded-b-lg shadow-md p-4 space-y-4">
                    <!-- Ubah Data Diri -->
                    <div class="flex items-center space-x-4">
                        <div>
                            <img src="{{ asset('assets/static/images/samples/profile.png') }}" class="w-6 h-6"
                                alt="profile">
                        </div>
                        <a href="{{ route('student.profile.detail') }}" class="text-gray-800 font-medium">Ubah Data Diri</a>
                    </div>
                    <!-- Reset Kata Sandi -->
                    <div class="flex items-center space-x-4">
                        <div>
                            <img src="{{ asset('assets/static/images/samples/lock.png') }}" class="w-6 h-6" alt="lock">
                        </div>
                        <a href="{{ route('student.update.password') }}" class="text-gray-800 font-medium">Reset Kata
                            Sandi</a>
                    </div>

                </div>
            </div>
        </div>

    @endsection
