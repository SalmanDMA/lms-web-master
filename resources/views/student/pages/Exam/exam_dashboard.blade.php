@extends('student.layout.app')

@section('title', 'Ujian')
@section('nav_title', 'Ujian')
@php
    $emptyData = count($schoolExamData) === 0 && count($classExamData) === 0;
@endphp

@section('content')

    <!-- Search bar -->
    <form class="mt-4 flex gap-2 items-center w-full md:w-1/2 xl:w-1/3" onsubmit="searchExam(event)">
        <input type="text" id="searchUjian" placeholder="Temukan Ujian"
               class="border border-blue-900 rounded-lg p-2 w-full bg-[#001951] text-white placeholder-white focus:outline-none">
        <button class="bg-[#001951] p-2 rounded-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M10 19a9 9 0 100-18 9 9 0 000 18z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35" />
            </svg>
        </button>
    </form>

    <!-- Dropdown for selection -->
    <div class="mt-4 w-full md:w-1/2 xl:w-1/3">
        <select id="filterExam" onchange="filterExam()" class="border border-blue-900 rounded-lg p-2 w-full text-blue-900 bg-white focus:outline-none">
            <option value="" disabled selected>Kategori Ujian</option>
            <option value="">Semua Ujian</option>
            @foreach ($availableCourse as $course)
                <option value="{{ $course }}">{{ $course }}</option>
            @endforeach
        </select>
    </div>

    <div class="flex flex-col items-center justify-center mt-5 space-y-4 w-full md:w-1/2">
        @foreach ($schoolExamData as $school)
            <a href="/student/exam-detail-school/{{ $school['id'] }}"
               class="bg-[#001951] text-white flex justify-between items-center p-4 rounded-lg shadow-md w-full max-w-md exam-item"
               data-course="{{ $school['courses_title'] }}"
               data-name="{{ $school['exam_id']['title'] }}">
                <!-- Left side (Icon and Info) -->
                <div class="flex items-center space-x-4">
                    <!-- Icon -->
                    <img src="{{ asset('assets/static/images/samples/exam-icon.png') }}" alt="">
                    <!-- Text Info -->
                    <div>
                        <h2 class="font-bold">{{ $school['exam_id']['title'] }}</h2>

                        <p class="text-sm">{{ date('H:i:s', strtotime($school['examSetting']['end_time'])) }} -
                            {{ date('d-m-Y', strtotime($school['examSetting']['end_time'])) }}</p>

                    </div>
                </div>
                <!-- Right side (Status) -->
                <div>
                    <p class="text-blue-200 underline">Active</p>
                </div>
            </a>
        @endforeach
        @foreach ($classExamData as $class)
                <a href="/student/exam-detail-class/{{ $class['id'] }}"
                   class="bg-[#001951] text-white flex justify-between items-center p-4 rounded-lg shadow-md w-full max-w-md exam-item"
                   data-course="{{ $class['courses_title'] }}"
                   data-name="{{ $class['title'] }}">
                    <!-- Left side (Icon and Info) -->
                    <div class="flex items-center space-x-4">
                        <!-- Icon -->
                        <img src="{{ asset('assets/static/images/samples/exam-icon.png') }}" alt="">
                        <!-- Text Info -->
                        <div>
                            <h2 class="font-bold">{{ $class['title'] }}</h2>

                            <p class="text-sm">{{ date('H:i:s', strtotime($class['exam_setting']['end_time'])) }} -
                                {{ date('d-m-Y', strtotime($class['exam_setting']['end_time'])) }}</p>

                        </div>
                    </div>
                    <!-- Right side (Status) -->
                    <div>
                        <p class="text-blue-200 underline">Active</p>
                    </div>
                </a>
        @endforeach

        <div id="noItemsMessage" class="flex flex-col items-center w-full" style="display: none;">
            <img src="{{ asset('assets/static/images/svg-loaders/not_found_exam.svg') }}" class="mb-3" alt="exam-not-found">
            <div class="text-center text-primary font-bold flex flex-col">
                <span id="changedMsgNoItems">Untuk saat ini, belum ada ujian/ulangan yang dijadwalkan.</span>
                <span>Selamat belajar dan semoga sukses!</span>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const emptyExam = @json($emptyData);
            if (emptyExam) {
                document.getElementById("noItemsMessage").style.display = '';
            }

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
        });

        function searchExam(event) {
            event.preventDefault();
            const searchValue = document.getElementById("searchUjian").value;
            const examItems = document.querySelectorAll('.exam-item');
            let matchFound = false;

            examItems.forEach(item => {
                const examTitle = item.getAttribute('data-name');

                if ( searchValue === "" || examTitle.toLowerCase().includes(searchValue.toLowerCase()) ) {
                    item.style.display = '';
                    matchFound = true;
                } else {
                    item.style.display = 'none';
                }
            })

            document.getElementById("noItemsMessage").style.display = matchFound ? 'none' : '';
            if (!matchFound) {
                document.getElementById("changedMsgNoItems").innerHTML = `Untuk saat ini, belum ada ujian/ulangan ${ searchValue } yang dijadwalkan.`
            }
        }

        function filterExam() {
            const selectedElement = document.getElementById("filterExam");
            const selectedValue = selectedElement.value;
            let matchFound = false;

            const examItems = document.querySelectorAll('.exam-item');
            examItems.forEach(item => {
                const courseTitle = item.getAttribute('data-course');

                if ( selectedValue === "" || courseTitle === selectedValue ) {
                    item.style.display = '';
                    matchFound = true;
                } else {
                    item.style.display = 'none';
                }
            });

            document.getElementById("noItemsMessage").style.display = matchFound ? 'none' : '';
            if (!matchFound) {
                document.getElementById("changedMsgNoItems").innerHTML = `Untuk saat ini, belum ada ujian/ulangan ${ selectedValue.toLowerCase() } yang dijadwalkan.`
            }
        }
    </script>

@endsection
