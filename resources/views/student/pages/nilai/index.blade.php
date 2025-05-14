@extends('student.layout.app')

@section('title', 'Dashboard')
@section('nav_title', 'Dashboard')

@section('content')
    <div class="w-full max-w-md">
        <button class="rounded-full bg-blue-900 p-2 text-white backBtn" onclick="history.back()">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 12H5M12 19l-7-7 7-7" />
            </svg>
        </button>

        <!-- Search bar -->
        <form class="mt-4 flex gap-2 items-center" onsubmit="searchNilaiData(event)">
            <input type="text" id="searchNilai" placeholder="Temukan Nilai Ujian"
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
        <div class="mt-4 flex flex-col md:flex-row">
            <select id="filterNilai" onchange="filterNilai()" class="border border-blue-900 rounded-lg p-2 w-full md:w-1/2 text-blue-900 bg-white focus:outline-none">
                <option value="" disabled selected>Kategori Nilai Ujian</option>
                <option value="">Semua Jenis Nilai Ujian</option>
                @foreach ($coursesType as $courseType)
                    <option value="{{ $courseType }}">{{ $courseType }}</option>
                @endforeach
            </select>
            <div class="mt-2 md:mx-2 md:mt-0"></div>
            <select id="typeNilai" onchange="typeNilai()" class="border border-blue-900 rounded-lg p-2 w-full md:w-1/2 text-blue-900 bg-white focus:outline-none">
                <option value="" disabled selected>Tipe Nilai Ujian</option>
                <option value="">Semua Tipe Nilai Ujian</option>
                <option value="exam">Ujian Sekolah</option>
                <option value="class-exam">Ujian Kelas</option>
                <option value="submission">Tugas</option>
            </select>
        </div>
    </div>
    @if(empty($scoreData))
        <div class="flex flex-col items-center justify-center mt-10">
            <img src="{{ asset('assets/static/images/svg-loaders/not_found_exam.svg') }}" alt="Empty Nilai" class="w-full">
            <span class="font-semibold text-[#001951] mt-2">Tidak ada data nilai</span>
        </div>
    @else
        <div id="scoreContainer" class="w-full max-w-md">
            @foreach($scoreData as $data)
                @if (
                   ($data->response !== null && $data->response->class_exam !== null && $data->response->class_exam->exam_setting->is_show_score)
                || ($data->response !== null && $data->response->school_exam !== null && $data->response->school_exam->examSetting->is_show_score)
                || ($data->submission !== null && $data->submission->assignment->is_visibleGrade)
                )
                    <div class="bg-[#001951] text-white flex justify-between items-center p-4 my-2 rounded-lg shadow-md nilai-item"
                         data-course="{{
                            $data->response !== null
                                ? (
                                    isset($data->response->class_exam->course)
                                        ? $data->response->class_exam->course->courses_title
                                        : (
                                            isset($data->response->school_exam->course)
                                                ? $data->response->school_exam->course->courses_title
                                                : ""
                                          )
                                  )
                                : (
                                    $data->submission !== null
                                        ? isset($data->submission->assignment->learning->course)
                                            ?
                                                $data->submission->assignment->learning->course->courses_title
                                                :
                                                ""
                                        : ""
                                  )
                        }}"
                         data-name="{{
                            $data->response !== null
                                ? (
                                    $data->response->class_exam !== null
                                        ? $data->response->class_exam->title
                                        : (
                                            $data->response->school_exam !== null
                                                ? $data->response->school_exam->title
                                                : ""
                                        )
                                  )
                                : (
                                    $data->submission !== null
                                        ? $data->submission->assignment->assignment_title
                                        : ""
                                  )
                        }}"
                        >
                        <div class="flex items-center jutify-between">
                            <div class="mr-2 md:mr-5">
                                <img src="{{ asset('assets/static/images/svg-loaders/task-score.svg') }}" alt="icon-task-nilai">
                            </div>
                            <div class="flex flex-col">
                                <div class="flex mb-2 md:mb-0 flex-col md:flex-row">
                                    @if ($data->response !== null)
                                        @if ($data->response->class_exam !== null)
                                            @if (isset($data->response->class_exam->course))
                                                <div class="text-sm md:text-base">{{ $data->response->class_exam->course->courses_title}}</div>
                                                <div class="mx-2 text-sm md:text-base hidden md:block">-</div>
                                            @endif
                                            <div class="font-bold text-sm md:text-base">{{ $data->response->class_exam->title }}</div>
                                        @elseif ($data->response->school_exam !== null)
                                            @if (isset($data->response->school_exam->course))
                                                <div class="text-sm md:text-base">{{$data->response->school_exam->course->courses_title }}</div>
                                                <div class="mx-2 text-sm md:text-base hidden md:block">-</div>
                                            @endif
                                            <div class="font-bold text-sm md:text-base">{{ $data->response->school_exam->title }}</div>
                                        @endif
                                    @elseif ($data->submission !== null)
                                        @if(isset($data->submission->assignment->learning->course))
                                            <div class="text-sm md:text-base">{{ $data->submission->assignment->learning->course->courses_title }}</div>
                                            <div class="mx-2 text-sm md:text-base hidden md:block">-</div>
                                        @endif
                                        <div class="font-bold text-sm md:text-base">{{ $data->submission->assignment->assignment_title }}</div>
                                    @endif
                                </div>
                                <div class="text-xs md:text-sm mb-2">
                                    @if ($data->knowledge !== null && $data->skills !== null)
                                        <div class="flex flex-col md:flex-row">
                                            <div>Pengetahuan : {{ $data->knowledge }}</div>
                                            <div class="mx-2 hidden md:block">-</div>
                                            <div>Keterampilan : {{ $data->skills }}</div>
                                        </div>
                                    @elseif ($data->exam !== null)
                                        Nilai : {{ $data->exam }}
                                    @elseif ($data->class_exam !== null)
                                        Nilai : {{ $data->class_exam }}
                                    @else
                                        Nilai : Coming soon
                                    @endif
                                </div>
                                <div class="font-semibold text-sm md:text-base">{{ $data->formatted_graded_at }}</div>
                            </div>
                        </div>
                        <div class="">
                            @if (($data->knowledge !== null && $data->skills !== null) || $data->exam !== null || $data->class_exam !== null)
                                <img src="{{ asset('assets/static/images/svg-loaders/check-nilai.svg') }}" alt="nilai-checked">
                            @else
                                <img src="{{ asset('assets/static/images/svg-loaders/inprogress-nilai.svg') }}" alt="nilai-inprogress">
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
        <div class="flex flex-col items-center justify-center mt-10" id="scoreNotFound" style="display: none;">
            <img src="{{ asset('assets/static/images/svg-loaders/not_found_exam.svg') }}" alt="Empty Nilai" class="w-auto">
            <span class="font-semibold text-[#001951] mt-2 text-score-not-found"></span>
        </div>
        <div id="loadingDataModal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-20 hidden">
            <div class="bg-primary p-6 rounded-md shadow-md text-center items-center flex-col flex justify-center">
                <img src="{{ asset('assets/static/images/svg-loaders/tail-spin.svg') }}" class="mb-3" alt="confirm-submit-icon">
                <p class="text-white">Mohon di tunggu ...</p>
            </div>
        </div>
    @endif

    <script>

        function showModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        function searchNilaiData(event) {
            event.preventDefault();
            const searchValue = document.getElementById('searchNilai').value;
            const nilaiItems = document.querySelectorAll('.nilai-item');
            let matchFound = false;

            nilaiItems.forEach(item => {
                const nilaiTitle = item.getAttribute('data-name');

                if ( searchValue === "" || nilaiTitle.toLowerCase().includes(searchValue.toLowerCase()) ) {
                    item.style.display = '';
                    matchFound = true;
                } else {
                    item.style.display = 'none';
                }
            })

            const scoreNotFound = document.getElementById("scoreNotFound");
            if (scoreNotFound !== null) {
                scoreNotFound.style.display = matchFound ? 'none' : '';
                document.querySelector(".text-score-not-found").innerHTML = "Data nilai dengan judul '" + searchValue + "' tidak ditemukan.";
            }
        }

        function filterNilai() {
            const selectedElement = document.getElementById("filterNilai");
            const selectedValue = selectedElement.value;
            let matchFound = false;

            const nilaiItems = document.querySelectorAll('.nilai-item');
            nilaiItems.forEach(item => {
                const courseTitle = item.getAttribute('data-course');

                if ( selectedValue === "" || courseTitle === selectedValue ) {
                    item.style.display = '';
                    matchFound = true;
                } else {
                    item.style.display = 'none';
                }
            });

            const scoreNotFound = document.getElementById("scoreNotFound");
            if (scoreNotFound !== null) {
                scoreNotFound.style.display = matchFound ? 'none' : '';
                document.querySelector(".text-score-not-found").innerHTML = "Data nilai dengan kategori '" + selectedValue + "' tidak ditemukan.";
            }
        }

        function formatDateScore(dateTime) {
            const date = new Date(dateTime);

            const daysNames = {
                'Sunday': 'Minggu',
                'Monday': 'Senin',
                'Tuesday': 'Selasa',
                'Wednesday': 'Rabu',
                'Thursday': 'Kamis',
                'Friday': 'Jumat',
                'Saturday': 'Sabtu'
            };

            const dayOfWeek = daysNames[date.toLocaleString('en-US', { weekday: 'long' })];
            const dateFormatted = date.getDate().toString().padStart(2, '0') + ' ' + (date.getMonth() + 1).toString().padStart(2, '0') + ' ' + date.getFullYear();
            return dayOfWeek + ', ' + dateFormatted;
        }

        function generateViewNilai(data) {
            const nilaiContainer = document.getElementById('scoreContainer');
            nilaiContainer.innerHTML = '';
            data.forEach((data) => {
                const isVisible = (
                    (data.response?.class_exam?.exam_setting?.is_show_score) ||
                    (data.response?.school_exam?.examSetting?.is_show_score) ||
                    (data.submission?.assignment?.is_visibleGrade)
                );

                if (isVisible) {
                    const courseTitle = data.response
                        ? (data.response.class_exam?.course?.courses_title || data.response.school_exam?.course?.courses_title || "")
                        : (data.submission?.assignment?.learning?.course?.courses_title || "");

                    const name = data.response
                        ? (data.response.class_exam?.title || data.response.school_exam?.title || "")
                        : (data.submission?.assignment?.assignment_title || "");

                    const title = data.response
                        ? (data.response.class_exam?.title || data.response.school_exam?.title || "")
                        : (data.submission?.assignment?.assignment_title || "");

                    const nilai =
                        data.knowledge !== null && data.knowledge !== undefined && data.skills !== null && data.skills !== undefined
                            ?
                            `
                                <div class="flex flex-col md:flex-row">
                                    <div>Pengetahuan : ${data.knowledge}</div>
                                    <div class="mx-2 hidden md:block">-</div>
                                    <div>Keterampilan: ${data.skills}</div>
                                </div>
                            `
                            :
                            data.exam !== null && data.exam !== undefined
                                ?
                                `Nilai : ${data.exam}`
                                :
                                data.class_exam !== null && data.class_exam !== undefined
                                    ?
                                    `Nilai : ${data.class_exam}`
                                    :
                                    'Nilai : Coming soon';

                    const formattedGradedAt = formatDateScore(data.graded_at);

                    const imgSrc = ((data.knowledge !== null && data.knowledge !== undefined && data.skills !== null && data.skills !== undefined) || (data.exam !== null && data.exam !== undefined) || (data.class_exam !== null && data.class_exam !== undefined))
                        ? `{{ asset('assets/static/images/svg-loaders/check-nilai.svg') }}`
                        : `{{ asset('assets/static/images/svg-loaders/inprogress-nilai.svg') }}`;

                    const html = `
                    <div class="bg-[#001951] text-white flex justify-between items-center p-4 my-2 rounded-lg shadow-md nilai-item" data-course="${courseTitle}" data-name="${name}">
                        <div class="flex items-center jutify-between">
                            <div class="mr-2 md:mr-5">
                                <img src="{{asset('assets/static/images/svg-loaders/task-score.svg')}}" alt="icon-task-nilai">
                            </div>
                            <div class="flex flex-col">
                                <div class="flex mb-2 md:mb-0 flex-col md:flex-row">
                                    ${data.response
                                        ? (data.response.class_exam
                                            ? `<div class="text-sm md:text-base">${courseTitle}</div><div class="mx-2 text-sm md:text-base hidden md:block ${courseTitle === '' ? 'hidden' : ''}">-</div><div class="font-bold text-sm md:text-base">${title}</div>`
                                            : (data.response.school_exam
                                                ? `<div class="text-sm md:text-base">${courseTitle}</div><div class="mx-2 text-sm md:text-base hidden md:block ${courseTitle === '' ? 'hidden' : ''}">-</div><div class="font-bold text-sm md:text-base">${title}</div>`
                                                : ''))
                                        : (data.submission
                                            ? `<div class="text-sm md:text-base">${courseTitle}</div><div class="mx-2 text-sm md:text-base hidden md:block ${courseTitle === '' ? 'hidden' : ''}">-</div><div class="font-bold text-sm md:text-base">${title}</div>`
                                            : '')
                                    }
                                </div>
                                <div class="text-xs md:text-sm mb-2">
                                    ${nilai}
                                </div>
                                <div class="font-semibold text-sm md:text-base">${formattedGradedAt}</div>
                            </div>
                        </div>
                        <div>
                            <img src="${imgSrc}" alt="nilai-status">
                        </div>
                    </div>
                `;

                    nilaiContainer.innerHTML += html;
                    const scoreNotFound = document.getElementById("scoreNotFound");
                    if (scoreNotFound !== null) {
                        scoreNotFound.style.display = 'none';
                        document.getElementById('filterNilai').value = "";
                    }
                }
            });
            checkCustomTheme();
        }

        function typeNilai() {
            const selectedElement = document.getElementById("typeNilai");
            const selectedValue = selectedElement.value;
            showModal('loadingDataModal');
            fetch(`/api/v1/mobile/student/score-list?search=${selectedValue.toString()}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'authorization': 'Bearer {{ session('token') }}'
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal('loadingDataModal');
                    generateViewNilai(data.data);
                } else {
                    const scoreNotFound = document.getElementById("scoreNotFound");
                    if (scoreNotFound !== null) {
                        scoreNotFound.style.display = '';
                        document.querySelector(".text-score-not-found").innerHTML = "Data nilai dengan tipe '" + selectedValue + "' tidak ditemukan.";
                    }
                }
            })
            .catch(error => {
                const scoreNotFound = document.getElementById("scoreNotFound");
                if (scoreNotFound !== null) {
                    scoreNotFound.style.display = '';
                    document.querySelector(".text-score-not-found").innerHTML = "Data nilai dengan tipe '" + selectedValue + "' tidak ditemukan.";
                }
            })
        }

        function checkCustomTheme() {
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
        }

        document.addEventListener('DOMContentLoaded', () => {
           checkCustomTheme();
        });
    </script>
@endsection
