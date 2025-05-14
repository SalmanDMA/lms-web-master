@extends('student.layout.app')

@section('title', 'Dashboard')
@section('nav_title', 'Dashboard')

@section('content')
    <div class="w-full max-w-md">
        <!-- Back button -->
        <button class="rounded-full bg-blue-900 p-2 text-white backBtn" onclick="history.back()">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 12H5M12 19l-7-7 7-7" />
            </svg>
        </button>

        <!-- Search bar -->
        <div class="mt-4 flex gap-2 items-center">
            <input type="text" placeholder="Temukan Tugas"
                class="border border-blue-900 rounded-lg p-2 w-full bg-[#001951] text-white placeholder-white focus:outline-none">
            <button class="bg-[#001951] p-2 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19a9 9 0 100-18 9 9 0 000 18z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35" />
                </svg>
            </button>
        </div>

        <!-- Dropdown for selection -->
        <div class="mt-4">
            <select class="border border-blue-900 rounded-lg p-2 w-full text-blue-900 bg-white focus:outline-none">
                <option value="">Filter berdasar mata pelajaran</option>
                @foreach ($courses as $couse)
                    <option value="{{ $couse->id }}">{{ $couse->courses_title }}</option>
                @endforeach
                <!-- Option items could be added here -->
            </select>
        </div>


    </div>
    @if (empty($assignments))
        <div class="flex items-center justify-between w-full max-w-md mt-5">
            <img src="{{ asset('assets/static/images/samples/empty-student.png') }}" alt="kosong">
        </div>
    @endif
    @foreach ($assignments as $assignment)
        <a href="/student/assignment/{{ $assignment->id }}"
            class="bg-[#001951] text-white flex justify-between items-center p-4 rounded-lg shadow-md w-full max-w-md">
            <!-- Left side (Icon and Info) -->
            <div class="flex items-center space-x-4">
                <!-- Icon -->
                <img src="{{ asset('assets/static/images/samples/tugas-icon.png') }}" alt="">
                <!-- Text Info -->
                <div>
                    <h2 class="font-bold">{{ $assignment->assignment_title }}</h2>

                    <p class="text-sm">{{ $assignment->end_time }} -
                        {{ date('d-m-Y', strtotime($assignment->due_date)) }}</p>

                </div>
            </div>
            <!-- Right side (Status) -->
            <div>
                <p class="text-blue-200 underline">Active</p>
            </div>
        </a>
    @endforeach

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

                backBtn.style.background = customTheme['primary_color'];
            }
        })
    </script>
@endsection
