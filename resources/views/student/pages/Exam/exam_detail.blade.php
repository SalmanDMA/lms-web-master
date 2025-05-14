@extends('student.layout.app')

@section('title', 'Ujian')
@section('nav_title', 'Ujian')

@php
    use Carbon\Carbon;

    $type = "";
    $examId = "";
    $examQuestionId = "";
    $endDateTime = null;

    if (!isset($ClassData)) {
        $type = "SchoolData";
        $examId = $SchoolData->exam_id->id;
        $examQuestionId = $SchoolData->id;
        $endDateTime = Carbon::parse($SchoolData->examSetting->end_time);
        $repeatChance = $SchoolData->examSetting->repeat_chance;
    } else {
        $type = "ClassData";
        $examId = $ClassData->id;
        $endDateTime = Carbon::parse($ClassData->exam_setting->end_time);
        $repeatChance = $ClassData->exam_setting->repeat_chance;
    }

    // Current date-time
    $now = Carbon::now();

    // Determine if the button should be disabled
    $isDisabled = $repeatChance === 0 || ($repeatChance > 0 && $endDateTime <= $now);

    // Determine the button class
    $buttonClass = $isDisabled
        ? 'bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-500 transition duration-200'
        : 'bg-blue-900 text-white px-4 py-2 rounded hover:bg-blue-700 transition duration-200';
@endphp

@section('content')

    <div class="flex justify-between items-center p-4 mt-3">
        <a href="/student/school-exam"
            class="flex items-center justify-center w-10 h-10 text-white bg-blue-900 rounded-full hover:bg-blue-900 focus:outline-none backBtn">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <!-- Tabs Centered -->
        <div class="flex-1 flex justify-center space-x-8">
            <button id="tab-detail"
                class="py-2 px-4 text-lg font-medium text-gray-600 hover:text-blue-900 border-b-2 border-transparent hover:border-blue-900 focus:outline-none">
                Detail Ujian
            </button>
            <button id="tab-riwayat"
                class="py-2 px-4 text-lg font-medium text-gray-600 hover:text-blue-900 border-b-2 border-transparent hover:border-blue-900 focus:outline-none">
                Riwayat Pengerjaan
            </button>
        </div>
    </div>

    <div class="p-8">
        <!-- Content for selected tab -->
        <div class="flex justify-center items-center p-8 bg-white">

            <div id="tab-content" class="w-full justify-center">

            </div>
            <div id="loadingDataModal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-20 hidden">
                <div class="bg-primary p-6 rounded-md shadow-md text-center items-center flex-col flex justify-center">
                    <img src="{{ asset('assets/static/images/svg-loaders/tail-spin.svg') }}" class="mb-3" alt="confirm-submit-icon">
                    <p class="text-white">Mohon di tunggu ...</p>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Get the tabs and content elements
        const tabDetail = document.getElementById('tab-detail');
        const tabRiwayat = document.getElementById('tab-riwayat');
        const tabContent = document.getElementById('tab-content');
        const btnLanjutkan = document.getElementById('btnLanjutkan');
        const typeDataExam = @json($type);
        const riwayatPengerjaanData = @json($riwayatPengerjaan);
        const customTheme = @json($customTheme);

        // Function to switch between tabs
        function showTabDetail() {
            // Change active tab style
            tabDetail.classList.add('border-blue-600', 'text-blue-600');
            tabRiwayat.classList.remove('border-blue-600', 'text-blue-600');
            tabRiwayat.classList.add('text-gray-600');

            // Update content for Detail Ujian
            tabContent.innerHTML = `
                <h1 class="text-base font-bold mb-4 text-center sm:text-lg">{{ $type === "ClassData" ? $ClassData->title :  $SchoolData->exam_id->title }}</h1>
                <p class="text-lg text-gray-700 text-center sm:text-sm">{{ $type === "ClassData" ? $ClassData->type :  $SchoolData->exam_id->type }}</p>
                <p class="text-lg font-bold mt-3 sm:text-xs">Deskripsi Ujian : </p>
                <p class="text-lg text-gray-700 mt-3">{!! $type === "ClassData" ? $ClassData->description : $SchoolData->exam_id->description !!} </p>
                <p class="text-lg font-bold mt-5">Waktu Pengerjaan : </p>
                <div class="flex items-center space-x-1 mt-2 justify-between">
                       <span class="bg-red-500 text-white px-2 py-1 rounded">Mulai</span>
                       <span class="bg-red-500 text-white px-2 py-1 rounded" id="startTime">{{ $type === "ClassData" ? $ClassData->exam_setting->start_time_formatted : $SchoolData->examSetting->start_time_formatted }}</span>
               </div>
                <div class="flex items-center space-x-1 mt-2 justify-between">
                       <span class="bg-red-500 text-white px-2 py-1 rounded">Selesai</span>
                       <span class="bg-red-500 text-white px-2 py-1 rounded" id="endTime">{{ $type === "ClassData" ? $ClassData->exam_setting->end_time_formatted : $SchoolData->examSetting->end_time_formatted }}</span>
                </div>
                <div class="flex justify-between mt-5">
            <div>
                <p class="text-lg font-bold">Durasi Pengerjaan:</p>
                <p class="text-lg text-gray-700 bg-green-600 text-white w-24 text-center rounded ">{{ $type === "ClassData" ? $ClassData->exam_setting->duration : $SchoolData->examSetting->duration }}</p>
            </div>
            <div class="w-1/3">
                <p class="text-lg font-bold text-left">Total Point:</p>
                <p class="text-lg text-gray-700 bg-green-600 text-white w-24 text-center rounded ">{{ $type === "ClassData" ? $ClassData->exam_setting->total_point : $SchoolData->examSetting->total_point }} Point</p>
            </div>
        </div>
        <div class="flex justify-between mt-5">
            <div>
                <p class="text-lg font-bold">Mode:</p>
                <p class="text-lg text-gray-700 bg-green-600 text-white w-24 text-center rounded " >Buka Buku</p>
            </div>
            <div class="w-1/3">
                <p class="text-lg font-bold">Total Pengerjaan:</p>
                @php
                $repeatChance = $type === "ClassData" ?
                    $ClassData->exam_setting->repeat_chance :
                    $SchoolData->examSetting->repeat_chance;
                $classColor = $repeatChance === 0 ? 'bg-red-600' : 'bg-green-600';
                @endphp
                <p class="text-lg text-gray-700 text-white w-12 text-center rounded {{ $classColor }} ">0/{{ $repeatChance }}</p>
            </div>
        </div>
        <div class="mt-7 text-center">
            <button id="lanjutkan" onclick="showTabToken()"
                class="{{ $buttonClass }}"
                {{ $isDisabled ? 'disabled' : '' }}
            >
                Lanjutkan
            </button>
        </div>
            `;
        }

        function showTabToken() {
            tabDetail.classList.add('border-blue-600', 'text-blue-600');
            tabRiwayat.classList.remove('border-blue-600', 'text-blue-600');
            tabRiwayat.classList.add('text-gray-600');

            // Update content for Detail Ujian
            tabContent.innerHTML = `
                <h1 class="text-3xl font-bold mb-4 text-center">{{ $type === "ClassData" ? $ClassData->title :  $SchoolData->exam_id->title }}</h1>
                <p class="text-lg text-gray-700 text-center">{{ $type === "ClassData" ? $ClassData->type :  $SchoolData->exam_id->type }}</p>
                <p class="text-lg font-bold mt-3">Deskripsi Ujian : </p>
                <p class="text-lg text-gray-700 mt-3">{!! $type === "ClassData" ? $ClassData->description : $SchoolData->exam_id->description !!} </p>
                <p class="text-lg font-bold mt-3">Intruksi Dari Pihak Sekolah : </p>
                <p class="text-lg text-gray-700 mt-3">{!! $type === "ClassData" ? $ClassData->instruction : $SchoolData->exam_id->instruction !!} </p>
                <p class="text-lg font-bold mt-3">Intruksi Pengerjaan : </p>
                <ul class="list-disc list-inside text-gray-700 mt-2 space-y-2">
                    <li>Pastikan koneksi internet stabil.</li>
                    <li>Tidak diperbolehkan membuka aplikasi lainnya pada saat melaksanakan Ulangan.</li>
                    <li>Berdoa sebelum ujian, kerjakan yang mudah terlebih dahulu.</li>
                    <li>Waktu akan dimulai saat tombol kerjakan ditekan.</li>
                </ul>
                <p class="text-lg font-bold mt-3">Token Ujian : </p>
                <div id="loadingDataModal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-20 hidden">
                    <div class="bg-primary p-6 rounded-md shadow-md text-center items-center flex-col flex justify-center">
                        <img src="{{ asset('assets/static/images/svg-loaders/tail-spin.svg') }}" class="mb-3" alt="confirm-submit-icon">
                        <p class="text-white">Mohon di tunggu ...</p>
                    </div>
                </div>
                <div id="alertBoxToken" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative my-2 hidden" role="alert">
                    <ul class="m-0 p-0">
                            <li style="list-style: none" id="errorMsgToken"></li>
                    </ul>
                    <span id="closeButtonToken2" onclick="closeModal('alertBoxToken')" class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer">
                        <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                    </span>
                </div>
                @if ($type === "ClassData")
                    <form action="{{ route('token.verify', ['class_id' => $ClassData->id]) }}" method="POST" id="tokenForm">
                        @csrf
                        <input
                         type="text"
                         name="inputToken"
                         id="tokenUjian"
                         class="w-full p-2 border border-gray-300 rounded mt-2 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                         placeholder="Masukkan token ujian"/>
                        <div class="mt-7 text-center">
                            <button type="submit" id="kerjakan" class="bg-green-600 text-white px-4 py-2  rounded hover:bg-green-700 transition duration-200 disabled:bg-gray-400" {{ $ClassData->disabled_exam ? 'disabled' : '' }}>
                                Kerjakan
                            </button>
                        </div>
                    </form>
                @elseif ($type === "SchoolData")
                    <form action="{{ route('token.verify', ['school_id' => $SchoolData->exam_id->id, 'id' => $SchoolData->id]) }}" method="POST" id="tokenForm">
                        @csrf
                        <input
                         type="text"
                         name="inputToken"
                         id="tokenUjian"
                         class="w-full p-2 border border-gray-300 rounded mt-2 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                         placeholder="Masukkan token ujian"/>
                        <div class="mt-7 text-center">
                            <button type="submit" id="tokenBtn" class="bg-green-600 text-white px-4 py-2  rounded hover:bg-green-700 transition duration-200 disabled:bg-gray-400" {{ $SchoolData->disabled_exam ? 'disabled' : '' }}>
                                Kerjakan
                            </button>
                        </div>
                    </form>
                @endif
            `;
            let typeExam = @json($type);
            function showModal(modalId) {
                document.getElementById(modalId).classList.remove('hidden');
            }
            function closeModal(modalId) {
                document.getElementById(modalId).classList.add('hidden');
            }

            const submitBtn = document.getElementById("tokenForm");
            submitBtn.addEventListener('submit', function(event) {
                event.preventDefault();
                closeModal('alertBoxToken');

                let bodyData;
                let tokenUjian = document.getElementById('tokenUjian').value;
                if (typeExam === "ClassData") {
                    bodyData = JSON.stringify({
                        token: tokenUjian,
                        status: 'pengerjaan',
                        exam_id: @json($examId)
                    })
                } else if (typeExam === "SchoolData") {
                    bodyData = JSON.stringify({
                        token: tokenUjian,
                        status: 'pengerjaan',
                        school_exam_id: @json($examId)
                    })
                }

                showModal('loadingDataModal');
                fetch('/api/v1/mobile/student/verify-token-exam', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'authorization': 'Bearer {{ session('token') }}'
                    },
                    body: bodyData
                }).then(response => response.json())
                    .then(data => {
                        closeModal('loadingDataModal');
                        document.getElementById('tokenUjian').value = '';
                        if (data.success) {
                            localStorage.setItem("responseId", data.data.id);
                            localStorage.setItem("examId", data.data.exam_id);
                            localStorage.setItem("schoolExamId", data.data.school_exam_id);
                            let urlExam = "";
                            if (typeExam === "ClassData") {
                                urlExam = '/student/exam-detail-class/' + @json($examId) + '/question'
                            } else if (typeExam === "SchoolData") {
                                urlExam = '/student/exam-detail-school/' + @json($examId) + '/' + @json($examQuestionId) + '/question'
                            }
                            const examWindow = window.open(
                                urlExam,
                                'examWindow',
                                'popup=yes,toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=800,height=600'
                            );

                            if (!examWindow || examWindow.closed || typeof examWindow.closed == 'undefined') {
                                alert("Popup blocked! Please allow popups for this site.");
                            }
                        } else {
                            if (data.message === "Anda masih dalam tahap pengerjaan.") {
                                let urlExam = "";
                                if (typeExam === "ClassData") {
                                    urlExam = '/student/exam-detail-class/' + @json($examId) + '/question'
                                } else if (typeExam === "SchoolData") {
                                    urlExam = '/student/exam-detail-school/' + @json($examId) + '/' + @json($examQuestionId) + '/question'
                                }
                                const examWindow = window.open(
                                    urlExam,
                                    'examWindow',
                                    'popup=yes,toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=800,height=600'
                                );

                                if (!examWindow || examWindow.closed || typeof examWindow.closed == 'undefined') {
                                    alert("Popup blocked! Please allow popups for this site.");
                                }
                            } else {
                                document.getElementById('errorMsgToken').innerHTML = data.message;
                                showModal('alertBoxToken');
                            }
                        }
                    })
                    .catch((error) => {
                        console.log(error);
                        closeModal('loadingDataModal');
                        document.getElementById('errorMsgToken').innerHTML = error.message;
                        showModal('alertBoxToken');
                    })
            });

            const tokenBtn = document.querySelector('#tokenBtn');
            tokenBtn.style.background = customTheme['primary_color'];
            tokenBtn.style.color = customTheme['accent_color'];
        }

        function showTabRiwayat() {
            // Change active tab style
            tabRiwayat.classList.add('border-blue-600', 'text-blue-600');
            tabDetail.classList.remove('border-blue-600', 'text-blue-600');
            tabDetail.classList.add('text-gray-600');

            if (riwayatPengerjaanData.length > 0) {
                tabContent.innerHTML = `
                    @foreach($riwayatPengerjaan as $dataPengerjaan)
                        <div onclick="showRiwayatDetail('{{ $dataPengerjaan->id }}', '{{ $type === "ClassData" ? $dataPengerjaan->exam_id : $dataPengerjaan->school_exam_id }}')" class="cursor-pointer">
                            <div class="bg-white rounded-lg shadow-md p-4 mb-4">
                                <div class="flex justify-between items-center">
                                    <p class="text-base font-bold mb-2">{{ $type === "ClassData" ? $dataPengerjaan->class_exam->title : $dataPengerjaan->school_exam->title }}</p>
                                    <p class="text-xs text-gray-500 ms-8 text-white p-2 rounded rounded-lg {{ $dataPengerjaan->status === "pengerjaan" ? "bg-red-600" : "bg-green-600" }}">{{ $dataPengerjaan->status === "pengerjaan" ? "Tidak Selesai" : "Selesai" }}</p>
                                </div>
                                <div class="flex text-xs">
                                    <div>{{ Carbon::parse($dataPengerjaan->created_at)->format('d/m/Y H:i:s')  }}</div>
                                    <div class="font-bold mx-3">-</div>
                                    <div>{{ $dataPengerjaan->status === "pengerjaan" ? "-" : Carbon::parse($dataPengerjaan->updated_at)->format('d/m/Y H:i:s') }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                `
            }else {
                tabContent.innerHTML = '<p>Tidak Ada riwayat pengerjaan.</p>';
            }
        }

        function showRiwayatDetail(id, idQuestion) {
            showModal("loadingDataModal");
            fetch(`/api/v1/mobile/student/${idQuestion}/question`, {
                method: "get",
                headers: {
                    "Content-Type": "application/json",
                    "authorization": "Bearer {{ session('token') }}"
                },
            })
                .then(responseQuestion => responseQuestion.json())
                .then(dataQuestion => {
                    if (dataQuestion.success) {
                        fetch("/api/v1/mobile/student/answer", {
                            method: "get",
                            headers: {
                                "Content-Type": "application/json",
                                "authorization": "Bearer {{ session('token') }}"
                            }
                        })
                            .then(response => response.json())
                            .then(data => {
                                closeModal("loadingDataModal");
                                if (data.success) {
                                    const dataResult = [];
                                    data.data.forEach(item => {
                                        if (item.response_id === id) {
                                            dataResult.push(item);
                                        }
                                    })
                                    tabContent.innerHTML = dataQuestion.data.map(itemGroup => `
                                        <div class="bg-white shadow-md rounded-lg p-6 mb-4">
                                            <div class="flex justify-between md:flex-row flex-col border-solid border-b-2 border-black pb-2 mb-5 items-center">
                                                <span class="font-bold text-base md:text-lg">Pertanyaan</span>
                                                <span class="bg-slate-500 text-white px-2 py-1 text-sm md:text-base rounded">
                                                    ${(() => {
                                                    switch (itemGroup.question_type) {
                                                        case 'True False': return 'Benar Salah';
                                                        case 'Essay': return 'Esai';
                                                        case 'Pilihan Ganda Complex': return 'Pilihan Ganda Kompleks';
                                                        default: return itemGroup.question_type;
                                                    }
                                                })()}
                                                </span>
                                            </div>
                                            ${itemGroup.question_attachments.length > 0 ? itemGroup.question_attachments.map(attachment => `
                                                ${attachment.file_type === 'image' ? `
                                                    <div class="attachment" id="attachmentImage">
                                                        <img src="${attachment.file_url.replace('storage/public/', '/storage/')}" alt="${attachment.file_name}" class="my-2">
                                                    </div>
                                                ` : attachment.file_type === 'audio' ? `
                                                    <div class="attachment" id="attachmentAudio">
                                                        <audio controls class="my-2 w-full">
                                                            <source src="${attachment.file_url.replace('storage/public/', '/storage/')}" type="audio/mpeg">
                                                        </audio>
                                                    </div>
                                                ` : ''}
                                            `).join('') : ''}
                                            ${itemGroup.question_text}
                                            ${(() => {
                                                    switch (itemGroup.question_type) {
                                                        case "True False":
                                                            return `
                                                            <div class="flex flex-col mt-4">
                                                                ${itemGroup.choices.map(choice => `
                                                                    <label class="inline-flex items-center">
                                                                        <input type="radio" class="form-radio" name="answers_true_false_[${itemGroup.id}][choice_id]" value="${choice.id}" id="${choice.id}" disabled>
                                                                        <span class="ml-2">${choice.choice_text}</span>
                                                                        ${choice.is_true ? '<div class="ms-2"><img src="/assets/static/images/svg-loaders/checklist-analysis.svg" alt="${choice.id}-isTrue"></div>' : ''}
                                                                    </label>
                                                                `).join('')}
                                                            </div>
                                                        `;
                                                        case "Pilihan Ganda":
                                                            return `
                                                            <div class="flex flex-col mt-4">
                                                                ${itemGroup.choices.map(choice => `
                                                                    <label class="inline-flex items-center">
                                                                        <input type="radio" class="form-radio" name="answers_pilihan_ganda_[${itemGroup.id}][choice_id]" value="${choice.id}" id="${choice.id}" disabled>
                                                                        <span class="ml-2">${choice.choice_text}</span>
                                                                        ${choice.is_true ? '<div class="ms-2"><img src="/assets/static/images/svg-loaders/checklist-analysis.svg" alt="${choice.id}-isTrue"></div>' : ''}
                                                                    </label>
                                                                `).join('')}
                                                            </div>
                                                        `;
                                                        case "Pilihan Ganda Complex":
                                                            return `
                                                            <div class="flex flex-col mt-4">
                                                                ${itemGroup.choices.map(choice => `
                                                                    <label class="inline-flex items-center">
                                                                        <input type="checkbox" class="form-radio" name="answers_pilihan_ganda_complex_[${itemGroup.id}][choice_id]" value="${choice.id}" id="${choice.id}" disabled>
                                                                        <span class="ml-2">${choice.choice_text}</span>
                                                                        ${choice.is_true ? '<div class="ms-2"><img src="/assets/static/images/svg-loaders/checklist-analysis.svg" alt="${choice.id}-isTrue"></div>' : ''}
                                                                    </label>
                                                                `).join('')}
                                                            </div>
                                                        `;
                                                        default:
                                                            return `
                                                            <div class="mt-4">
                                                                <div class="mb-2"><span class="">Jawaban yang Diisi</span></div>
                                                                <textarea id="exam_user_${itemGroup.id}" name="user[${itemGroup.id}][answer_text]"></textarea>
                                                            </div>
                                                        `;
                                                    }
                                                })()}
                                        </div>
                                    `).join('');

                                    function initializeCKEditorForTextarea(textarea, initialData = '') {
                                        ClassicEditor.create(textarea)
                                            .then(editor => {
                                                editor.setData(initialData);
                                                editor.enableReadOnlyMode('feature-id');
                                            })
                                            .catch(error => {
                                                console.error(error);
                                            });
                                    }

                                    const hasAnswerText = dataResult.some(result => result.answer_text !== null && result.answer_text !== undefined);
                                    let textAreaUpdated = false;
                                    if (dataResult.length > 0) {
                                        dataResult.forEach(result => {
                                            if (result.choice_id !== null) {
                                                const element = document.querySelector(`input[value="${result.choice_id}"]`);
                                                if (element) {
                                                    element.checked = true;
                                                }
                                            } else {
                                                document.querySelectorAll('textarea[id^="exam_user_"]').forEach((textarea) => {
                                                    initializeCKEditorForTextarea(textarea, result.answer_text);
                                                });
                                                textAreaUpdated = true;
                                                // document.querySelectorAll('textarea[id^="exam_answer_"]').forEach((textarea) => {
                                                //     ClassicEditor.create(textarea)
                                                //         .then(editor => {
                                                //             editor.enableReadOnlyMode( 'feature-id' );
                                                //         })
                                                //         .catch((error) => {
                                                //             console.error(error);
                                                //         });
                                                // });
                                            }
                                        });
                                    } else {
                                        const textareas = document.querySelectorAll('textarea[id^="exam_user_"]');
                                        textareas.forEach((textarea) => {
                                            initializeCKEditorForTextarea(textarea);
                                        });
                                        textAreaUpdated = true;
                                    }

                                    if (!textAreaUpdated && hasAnswerText) {
                                        const textareas = document.querySelectorAll('textarea[id^="exam_user_"]');
                                        textareas.forEach((textarea) => {
                                            initializeCKEditorForTextarea(textarea);
                                        });
                                    }
                                }
                            })
                            .catch((error) => {
                                closeModal("loadingDataModal");
                                console.error(error);
                            });
                    }
                })
                .catch((error) => {
                    closeModal("loadingDataModal");
                    console.error(error);
                });
        }

        function showModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        document.addEventListener('DOMContentLoaded', () => {
            showTabDetail();
            // Ambil data waktu dari PHP
            const endTime = "{{ $type === "ClassData" ? $ClassData->exam_setting->end_time : $SchoolData->examSetting->end_time }}";
            const startTime = "{{ $type === "ClassData" ? $ClassData->exam_setting->start_time : $SchoolData->examSetting->start_time }}";

            // Format waktu menjadi HH:MM
            const formatTime = (dateTimeStr) => {
                const date = new Date(dateTimeStr);
                const hours = date.getHours().toString().padStart(2, '0');
                const minutes = date.getMinutes().toString().padStart(2, '0');
                return `${hours}:${minutes}`;
            };

            // Format tanggal menjadi 'Hari, Tanggal Bulan Tahun'
            const formatDate = (dateTimeStr) => {
                const date = new Date(dateTimeStr);
                const hari = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
                const bulan = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus",
                    "September", "Oktober", "November", "Desember"
                ];

                const day = hari[date.getDay()];
                const dayNumber = date.getDate();
                const month = bulan[date.getMonth()];
                const year = date.getFullYear();

                return `${day}, ${dayNumber} ${month} ${year}`;
            };

            // Format waktu dan tanggal
            const FormatTimeEnd = formatTime(endTime);
            const FormatDateEnd = formatDate(endTime);
            const FormatTimeStart = formatTime(startTime);
            const FormatDateStart = formatDate(startTime);
            document.getElementById('startTime').innerText = `${FormatTimeStart} | ${FormatDateStart}`;
            document.getElementById('endTime').innerText = `${FormatTimeEnd} | ${FormatDateEnd}`;
            startTimeValue = `${formatTime(startTime)} | ${formatDate(startTime)}`;
            endTimeValue = `${formatTime(endTime)} | ${formatDate(endTime)}`;

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
                document.getElementById('lanjutkan').style.background = customTheme['primary_color'];
            }
        });

        // Add event listeners to switch between tabs
        tabDetail.addEventListener('click', showTabDetail);
        tabRiwayat.addEventListener('click', showTabRiwayat);
    </script>



@endsection
