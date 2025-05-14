@extends('student.layout.app')

@section('title', 'Edit Profile')
@section('nav_title', 'Edit Profile')

@section('content')

    @php
        $userImage = str_replace('storage/public/', 'public/', $user->image_path);
    @endphp

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
                <form action="{{ route('student.profile.update') }}" method="POST" enctype="multipart/form-data">
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
                        <h2 class="text-lg font-bold">Data Diri</h2>

                        <!-- Upload Foto Profil -->
                        <div class="relative flex justify-center mt-4">
                            <label for="file-upload" class="cursor-pointer">
                                <div
                                    class="w-20 h-20 bg-gray-300 rounded-full flex items-center justify-center border-4 border-white">
                                    @if ($user->image_path && Storage::exists($userImage))
                                        <img src="{{ Storage::url($userImage) }}" alt="Avatar" class="rounded-circle"
                                            style="width: 100px; height: 100px">
                                    @else
                                        <svg class="w-10 h-10 text-gray-600" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 14c3.31 0 6 2.69 6 6H6c0-3.31 2.69-6 6-6zM12 4a4 4 0 110 8 4 4 0 010-8z" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="absolute bottom-0 left-30 bg-white rounded-full p-1 shadow-md">
                                    <img src="{{ asset('assets/static/images/samples/camera.png') }}" class="w-5 h-5"
                                        alt="kamera">
                                </div>
                            </label>
                            <input id="file-upload" name="image_path" type="file" class="hidden" />
                        </div>
                        <h3 class="mt-4 text-xl font-semibold">{{ $user->fullname }}</h3>
                        <p class="text-sm">Kelas {{ $class }} {{ $subClasses }}</p>
                    </div>

                    <!-- Formulir Input -->
                    <div class="bg-white rounded-b-lg shadow-md p-6">
                        <div class="space-y-4">
                            <!-- Input Nama Siswa -->
                            <div>
                                <input type="text" placeholder="Nama Siswa" name="fullname"
                                    value="{{ $user->fullname }}"
                                    class="w-full border border-gray-300 rounded-lg p-2 text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <!-- Input NISN -->
                            <div>
                                <input type="text" placeholder="24362952" name="nisn"
                                    value="{{ $user->is_student->nisn }}"
                                    class="w-full border border-gray-300 rounded-lg p-2 text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <!-- Input Nomor HP -->
                            <div class="flex">
                                <span
                                    class="inline-flex items-center px-3 rounded-l-md bg-gray-200 border border-gray-300 text-gray-600">+62</span>
                                <input type="tel" placeholder="867329436" name="phone" value="{{ $user->phone }}"
                                    class="w-full border border-gray-300 rounded-r-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <!-- Input Email -->
                            <div>
                                <input type="email" placeholder="xxx@gmail.com" name="email"
                                    value="{{ $user->email }}"
                                    class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <!-- Input Wali Kelas -->
                            <div>
                                <input type="text" placeholder="Belum ada wali kelas" name="guardian"
                                    value="{{ $user->is_student_sub_clasess->guardian ?? ' - ' }}"
                                    class="w-full border border-gray-300 rounded-lg p-2 bg-gray-200 text-gray-600"
                                    disabled />
                            </div>
                            <!-- Pilihan Agama -->
                            <div>
                                <select name="religion"
                                    class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    @if ($user->religion)
                                        <option value="{{ $user->religion }}" selected disabled>{{ $user->religion }}
                                        </option>
                                    @endif
                                    <option value="Islam">Islam</option>
                                    <option value="Katolik">Katolik</option>
                                    <option value="Protestan">Protestan</option>
                                    <option value="Hindu">Hindu</option>
                                    <option value="Budha">Buddha</option>
                                    <option value="Konghucu">Konghucu</option>
                                </select>
                            </div>
                            <div>
                                <textarea name="address" placeholder="Alamat" class="w-full border border-gray-300 rounded-lg p-2" cols="50"
                                    rows="3">
                                {{ $user->address }}
                            </textarea>
                            </div>

                            <!-- Tombol Simpan -->
                        </div>
                    </div>
                    <div class="pt-4">
                        <button type="submit"
                            class="w-full bg-blue-600 text-white rounded-lg p-2 font-semibold hover:bg-blue-700 transition">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
