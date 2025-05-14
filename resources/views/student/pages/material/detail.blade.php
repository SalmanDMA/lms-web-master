@extends('student.layout.app')

@section('title', 'Dashboard')
@section('nav_title', 'Dashboard')


@section('content')

    <div class="w-full max-w-lg">
        <div class="flex items-center mb-4">
            <button class="rounded-full bg-blue-900 p-2 text-white backBtn" onclick="history.back()">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 12H5M12 19l-7-7 7-7" />
                </svg>
            </button>
        </div>
        <!-- Task Details -->
        <div id="material-detail" class=" bg-white shadow-md rounded-lg p-6">
            <!-- Task Title and Subject -->
            <div class="mb-4">

                <h1 class="text-xl lg:text-3xl text-center font-bold text-[#001951]">{{ $materialData->material_title }}
                </h1>
                <p class="text-[#001951] text-center">{{ $course->courses_title }}</p>
            </div>

            <!-- Task Description -->
            <div class="mb-4">
                <h2 class="text-lg font-semibold">Deskripsi Materi</h2>
                <p class="text-gray-700 text-sm">
                    {!! $materialData->material_description !!}
                </p>
            </div>
            <h2 class="text-xl font-semibold text-center text-blue-900">Lampiran Materi</h2>

            @foreach ($materialData->material_resources as $index => $resource)
                @php
                    $linkUrl = str_replace('storage/public/', '', $resource->resource_url);
                @endphp
                @if ($resource->resource_type == 'video')
                    <div id="resources-container-video-{{ $index }}" data-id="{{ $resource->id }}"
                        class="w-full bg-gray-200 rounded-lg shadow-md overflow-hidden mb-4">
                        <video controls class="w-full">
                            <source src="{{ Storage::url($linkUrl) }}">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                @endif

                @if ($resource->resource_type == 'image')
                    <!-- Image File -->
                    <div id="resources-container-image-{{ $index }}" data-id="{{ $resource->id }}"
                        class="relative
                        bg-gray-200 rounded-lg shadow-md overflow-hidden">
                        <img src="{{ Storage::url($linkUrl) }}" alt="Materi Image" class="w-full object-cover mb-4">
                        <div class="absolute bottom-2 right-2">
                            <a href="{{ Storage::url($linkUrl) }}"
                                class="text-blue-900 p-2 bg-white rounded-full shadow-md">
                                <svg xmlns="{{ asset('assets/static/icon/download-icon.svg') }}" class="h-6 w-6"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                            </a>
                        </div>
                    </div>
                @endif

                @if ($resource->resource_type == 'document')
                    <!-- PDF File -->
                    <div id="resources-container-pdf-{{ $index }}" data-id="{{ $resource->id }}"
                        class="flex items-center
                    justify-between bg-white rounded-lg shadow-md p-4 mb-4">
                        <div class="flex items-center space-x-2">
                            <img src="{{ asset('assets/static/icon/document-icon.svg') }}" alt="document" class="h-6 w-6">
                            <div>
                                <p class="text-blue-900 font-semibold">
                                    {{ $resource->resource_name }}.{{ $resource->resource_extension }}</p>
                                <p class="text-gray-500 text-sm">{{ $course->courses_title }}</p>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <a href="{{ Storage::url($linkUrl) }}" download class="text-blue-900">
                                <img src="{{ asset('assets/static/icon/download-icon.svg') }}" alt="document"
                                    class="h-6 w-6">
                            </a>
                        </div>
                    </div>
                @endif

                @if ($resource->resource_type === 'audio')
                    <!-- MP3 File -->
                    <div id="resources-container-mp3-{{ $index }}" data-id="{{ $resource->id }}"
                        class="flex
                    items-center justify-between bg-white rounded-lg shadow-md p-4 mb-4">
                        <div class="flex items-center space-x-2">
                            <a href="path-to-mp3" class="text-blue-900">
                                <img src="{{ asset('assets/static/icon/audio-icon.svg') }}" alt="audio" class="h-6 w-6">
                            </a>
                            <div>
                                <p class="text-blue-900 font-semibold">
                                    {{ $resource->resource_name }}.{{ $resource->resource_extension }}</p>
                                <p class="text-gray-500 text-sm">{{ $course->courses_title }}</p>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <a href="{{ Storage::url($linkUrl) }}" class="text-blue-900">
                                <img src="{{ asset('assets/static/icon/play-icon.svg') }}" alt="audio" class="h-6 w-6">
                            </a>

                            <a href="{{ Storage::url($linkUrl) }}" download class="text-blue-900">
                                <img src="{{ asset('assets/static/icon/download-icon.svg') }}" alt="download"
                                    class="h-6 w-6">
                            </a>
                        </div>
                    </div>
                @endif

                @if ($resource->resource_type == 'archive')
                    <!-- ZIP File -->
                    <div id="resources-container-zip-{{ $index }}" data-id="{{ $resource->id }}"
                        class="flex
                items-center justify-between bg-white rounded-lg shadow-md p-4 mb-4">
                        <div class="flex items-center space-x-2">
                            <img src="{{ asset('assets/static/icon/document-icon.svg') }}" alt="document" class="h-6 w-6">
                            <div>
                                <p class="text-blue-900 font-semibold">
                                    {{ $resource->resource_name }}.{{ $resource->resource_extension }}</p>
                                <p class="text-gray-500 text-sm">{{ $course->courses_title }}</p>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <a href="{{ Storage::url($linkUrl) }}" download class="text-blue-900">
                                <img src="{{ asset('assets/static/icon/download-icon.svg') }}" alt="download"
                                    class="h-6 w-6 text-blue-900">
                            </a>
                        </div>
                    </div>
                @endif
                @if ($resource->resource_type == 'url')
                    <!-- Link/Tautan -->
                    <a href="{{ $resource->resource_url }}" id="resources-container-link-{{ $index }}"
                        data-id="{{ $resource->id }}"
                        class="flex
                    items-center justify-between bg-white rounded-lg shadow-md p-4 mb-4">
                        <div class="flex items-center space-x-2">
                            <img src="{{ asset('assets/static/icon/link-icon.svg') }}" alt="Icon"
                                class="h-6 w-6 text-blue-900">
                            <p class="text-blue-900 font-semibold">{{ $resource->resource_name }}</p>
                        </div>

                    </a>
                @endif

                @if ($resource->resource_type == 'youtube')
                    {{-- <iframe width="560" height="315" src="{{ $resource->resource_url }}"
                        title="YouTube video player" frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe> --}}
                    <a href="{{ $resource->resource_url }}" div id="resources-container-yt-{{ $index }}"
                        data-id="{{ $resource->id }}"
                        class="flex
                        items-center justify-between bg-white rounded-lg shadow-md p-4 mb-4">
                        <div class="flex items-center space-x-2">
                            <img src="{{ asset('assets/static/icon/link-icon.svg') }}" alt="Icon" />
                            <p class="text-blue-900 h-6 w-6">{{ $resource->resource_name }}</p>
                        </div>

                    </a>
                @endif
            @endforeach
            <!-- Video Player -->
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const customTheme = @json($customTheme);
                const customTextColored = document.querySelectorAll('[class*="text-[#001951]"]');
                const customBgColor = document.querySelectorAll('[class*="bg-[#001951]"]');
                const customTextWhite = document.querySelectorAll('[class*="text-white"]');
                const bgActiveCard = document.querySelectorAll('.bg-blue-900');
                const bgPrimaryCustom = document.querySelectorAll('.bg-primary');
                const customSvgIcon = document.querySelector('.svg-icon path');
                const bgCard = document.querySelectorAll('.bg-card');
                const titleNav = document.querySelector('.titleNav');
                const primaryTxtForeground = document.querySelectorAll('.text-primary-foreground');
                const backBtn = document.querySelector('.backBtn');
                const txtBlue = document.querySelectorAll('.text-blue-900');

                if (customTheme !== null) {
                    customTextColored.forEach(function (element) {
                        element.style.color = customTheme['primary_color'];
                    })

                    customBgColor.forEach(function (element) {
                        element.style.backgroundColor = customTheme['primary_color'];
                    })

                    customTextWhite.forEach(function (element) {
                        element.style.color = customTheme['accent_color'];
                    })

                    bgPrimaryCustom.forEach(function (element) {
                        element.style.backgroundColor = customTheme['primary_color'];
                    })

                    customSvgIcon.style.fill = customTheme['accent_color'];
                    titleNav.innerHTML = customTheme['title'];
                    primaryTxtForeground.forEach(function (element) {
                        element.style.color = customTheme['accent_color'];
                    })

                    bgActiveCard.forEach(function (cardElement) {
                        cardElement.style.backgroundColor = customTheme['accent_color'];

                        cardElement.querySelectorAll('.activeText').forEach(function (txtElement2) {
                            txtElement2.style.color = customTheme['primary_color'];
                        })
                    })

                    bgCard.forEach(function (cardElement) {
                        cardElement.style.backgroundColor = customTheme['secondary_color'];

                        cardElement.querySelectorAll('.nonActiveText').forEach(function (txtElement) {
                            txtElement.style.color = customTheme['accent_color'];
                        })

                        cardElement.addEventListener('mouseenter', function () {
                            cardElement.style.backgroundColor = customTheme['accent_color'];

                            cardElement.querySelectorAll('.nonActiveText').forEach(function (txtElement) {
                                txtElement.style.color = customTheme['primary_color'];
                            })
                        })

                        cardElement.addEventListener('mouseleave', function () {
                            cardElement.style.backgroundColor = customTheme['secondary_color'];

                            cardElement.querySelectorAll('.nonActiveText').forEach(function (txtElement) {
                                txtElement.style.color = customTheme['accent_color'];
                            })
                        })
                    })

                    txtBlue.forEach(function (element) {
                        element.style.color = customTheme['primary_color'];
                    })

                    backBtn.style.background = customTheme['primary_color'];
                }
            })
        </script>

    @endsection
