@extends('student.layout.app')

@section('title', 'Reset password')
@section('nav_title', 'Reset password')

@section('content')


    <div class="w-full max-w-md">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
        @if (session('message'))
            <div class="alert {{ session('alertClass') }} alert-dismissible fade show" role="alert">
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <div class="flex items-center justify-center min-h-screen">
            <div class="w-96">
                <!-- Header Biru -->
                <form action="{{ route('student.update.password') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="bg-blue-900 text-white rounded-t-lg p-6 text-center relative">
                        <!-- Tombol Kembali -->
                        <a href="{{ route('student.profile') }}" class="btn-back absolute left-4 top-6">
                            <svg class="w-6 h-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                        <!-- Judul -->
                        <h2 class="text-lg font-bold">Reset password</h2>

                    </div>

                    <!-- Formulir Input -->
                    <div class="bg-white rounded-b-lg shadow-md p-6">
                        <div class="space-y-4">

                            <div>
                                <input type="password" placeholder="Password lama" name="old_password" id="old_password"
                                    class="w-full border border-gray-300 rounded-lg p-2 text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>

                            <div>
                                <input type="password" placeholder="Password baru" name="new_password" id="password"
                                    class="w-full border border-gray-300 rounded-lg p-2 text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>

                            <div>
                                <input type="password" placeholder="Konfirmasi password baru"
                                    name="new_password_confirmation" id="password_confirmation"
                                    class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <button type="submit"
                                class="w-full bg-blue-600 text-white rounded-lg p-2 font-semibold hover:bg-blue-700 transition">
                                Simpan
                            </button>
                        </div>
                    </div>
                    <div class="pt-4">
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
