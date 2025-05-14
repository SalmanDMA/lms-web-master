@extends('student.layout.app')

@section('title', 'Dashboard')
@section('nav_title', 'Dashboard')

@section('content')


    {{-- Exam --}}
    @if (empty($school_exams) || empty($class_exams))
        <div class="flex items-center justify-between w-full max-w-md mt-5">
            <img src="{{ asset('assets/static/images/samples/empty-student.png') }}" alt="kosong">
        </div>
    @endif
    {{-- <div class="flex items-center justify-between w-full max-w-md mt-5">
      <h2 class="font-semibold text-xl text-[#001951]">Ujian</h2>
      <a href="/student/exam">Lihat Semua</a>
    </div>
    @foreach ($school_exams as $sch)
    <a href="/student/exam/{{ $sch->id }}" class="bg-[#001951] text-white flex justify-between items-center p-4 rounded-lg shadow-md w-full max-w-md">
      <!-- Left side (Icon and Info) -->
      <div class="flex items-center space-x-4">
          <!-- Icon -->
              <img src="{{ asset("assets/static/images/samples/exam-icon.png") }}" alt="">
          <!-- Text Info -->
          <div>
              <h2 class="font-bold">{{ $sch->exam_id->title }}</h2>

              <p class="text-sm">{{ date("H:i", strtotime($sch->examSetting->end_time)) }} - {{ date("d-m-Y", strtotime($sch->examSetting->end_time) )}}</p>
          </div>
      </div>
      <!-- Right side (Status) -->
      <div>
          <p class="text-blue-200 underline">{{ $sch->exam_id->status }}</p>
      </div>
    </a>
    @endforeach --}}
    {{-- @foreach ($class_exams as $cls)
        <a href="/student/exam/{{ $cls->id }}"
            class="bg-[#001951] text-white flex justify-between items-center p-4 rounded-lg shadow-md w-full max-w-md">
            <!-- Left side (Icon and Info) -->
            <div class="flex items-center space-x-4">
                <!-- Icon -->
                <img src="{{ asset('assets/static/images/samples/exam-icon.png') }}" alt="">
                <!-- Text Info -->
                <div>
                    <h2 class="font-bold">{{ $cls->title }}</h2>

                    <p class="text-sm">{{ date('H:i', strtotime($cls->exam_setting->end_time)) }} -
                        {{ date('d-m-Y', strtotime($cls->exam_setting->end_time)) }}</p>
                </div>
            </div>
            <!-- Right side (Status) -->
            <div>
                <p class="text-blue-200 underline">{{ $cls->is_active == 1 ? 'Active' : 'Inactive' }}</p>
            </div>
        </a>
    @endforeach --}}
    {{-- End Exam --}}

    {{-- Assignment --}}
    @if (empty($assignments))
        <div class="flex items-center justify-between w-full max-w-md mt-5">
            <img src="{{ asset('assets/static/images/samples/empty-student.png') }}" alt="kosong">
        </div>
    @endif
    <div class="flex items-center justify-between w-full max-w-md mt-5">
        <h2 class="font-semibold text-xl text-[#001951]">Tugas</h2>
        <a href="/student/assignment">Lihat Semua</a>
    </div>
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

                    <p class="text-sm">{{ $assignment->end_time }} - {{ date('d-m-Y', strtotime($assignment->due_date)) }}
                    </p>

                </div>
            </div>
            <!-- Right side (Status) -->
            <div>
                <p class="text-blue-200 underline">Active</p>
            </div>
        </a>
    @endforeach
    {{-- Material --}}
    @if (empty($materials))
        <div class="flex items-center justify-between w-full max-w-md mt-5">
            <img src="{{ asset('assets/static/images/samples/empty-student.png') }}" alt="kosong">
        </div>
    @endif
    <div class="flex items-center justify-between w-full max-w-md mt-5">
        <h2 class="font-semibold text-xl text-[#001951]">Materi</h2>
        <a href="/student/material">Lihat Semua</a>
    </div>
    @foreach ($materials as $material)
        <a href="/student/material/{{ $material->id }}"
            class="bg-[#001951] text-white flex justify-between items-center p-4 rounded-lg shadow-md w-full max-w-md">
            <!-- Left side (Icon and Info) -->
            <div class="flex items-center space-x-4">
                <!-- Icon -->
                <img src="{{ asset('assets/static/images/samples/materi-icon.png') }}" alt="">
                <!-- Text Info -->
                <div>
                    <h2 class="font-bold">{{ $material->material_title }}</h2>
                    @foreach ($courses as $course)
                        <p class="text-sm">{{ $material->learning->course === $course->id ? $course->courses_title : '' }}
                        </p>
                    @endforeach
                </div>
            </div>
            <!-- Right side (Status) -->
            <div>
                <p class="text-blue-200 underline">{{ $material->status }}</p>
            </div>
        </a>
    @endforeach
    {{-- End Material --}}


    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const customTheme = @json($customTheme);
            const customTextColored = document.querySelectorAll('[class*="text-[#001951]"]');
            const customBgColor = document.querySelectorAll('[class*="bg-[#001951]"]');
            const customTextWhite = document.querySelectorAll('[class*="text-white"]');
            const bgPrimaryCustom = document.querySelectorAll('.bg-primary');
            const customSvgIcon = document.querySelector('.svg-icon path');
            const bgCard = document.querySelectorAll('.nonActiveCard');
            const titleNav = document.querySelector('.titleNav');
            const primaryTxtForeground = document.querySelectorAll('.text-primary-foreground');

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

                bgCard.forEach(function (cardElement) {
                    cardElement.style.backgroundColor = customTheme['secondary_color'];

                    cardElement.querySelectorAll('.text-foreground').forEach(function (txtElement) {
                        txtElement.style.color = customTheme['accent_color'];
                    })

                    cardElement.addEventListener('mouseenter', function () {
                        cardElement.style.backgroundColor = customTheme['accent_color'];

                        cardElement.querySelectorAll('.text-foreground').forEach(function (txtElement) {
                            txtElement.style.color = customTheme['primary_color'];
                        })
                    })

                    cardElement.addEventListener('mouseleave', function () {
                        cardElement.style.backgroundColor = customTheme['secondary_color'];

                        cardElement.querySelectorAll('.text-foreground').forEach(function (txtElement) {
                            txtElement.style.color = customTheme['accent_color'];
                        })
                    })
                })

                customSvgIcon.style.fill = customTheme['accent_color'];
                titleNav.innerHTML = customTheme['title'];
                primaryTxtForeground.forEach(function (element) {
                    element.style.color = customTheme['accent_color'];
                })
            }
        })
    </script>
@endsection
