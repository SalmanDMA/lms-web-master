@extends('student.layout.app_wo_navigation')

@section('title', 'Ujian')
@section('nav_title', 'Ujian')
@section('navigation')
@endsection

@php
    $answerData = [];
    $quizTotal = 0;
    $typeData = "ClassExam";
    $examId = "";
    $learningId = "";
    $endTime="";
    $settingDuration = "";

    if (!isset($schoolExamData)) {
        $showScore = $classExamData->exam_setting->is_show_score;
        $titleExam = $classExamData->title;
        $typeExam = $classExamData->type;
        $endTime = $classExamData->exam_setting->end_time;
        $settingDuration = $classExamData->exam_setting->duration;
    } else {
        $typeData = "SchoolExam";
        $learningId = $schoolExamData->id;
        $showScore = $schoolExamData->examSetting->is_show_score;
        $titleExam = $schoolExamData->exam_id->title;
        $typeExam = $schoolExamData->exam_id->type;
        $endTime = $schoolExamData->examSetting->end_time;
        $settingDuration = $schoolExamData->examSetting->duration;
    }

    $results = json_decode(session('answers', json_encode(array_map(function($question) use (&$answerData, &$quizTotal, &$examId, &$typeData) {
        foreach ($question as $item) {
            $answerData[] = [
                'question_id' => $item->id,
                'choice_id' => null,
                'answer_text' => null,
            ];
            $quizTotal += 1;

            if ($typeData === "ClassExam") {
                $examId = $item->exam_id;
            } else if ($typeData === "SchoolExam") {
                $examId = $item->school_exam_id;
            }
        }
    }, $questionData))), true);
@endphp

@section('content')
    <div class="p-4 mt-3 w-full lg:w-4/6 flex flex-col justify-center">
        <div id="content-exam" class=""></div>
        <div id="confirmModal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-20 hidden">
            <div class="bg-primary p-6 rounded-md shadow-md text-center items-center flex-col flex justify-center">
                <img src="{{ asset('assets/static/images/svg-loaders/confirm-submit.svg') }}" class="mb-3" alt="confirm-submit-icon">
                <div class="font-semibold text-white">Kumpulkan Ujian?</div>
                <p class="text-white">Pastikan telah mengisi dengan benar</p>
                <div class="flex justify-between w-full">
                    <button onclick="closeModal('confirmModal')" class="mt-4 px-4 bg-white text-primary rounded hover:bg-slate-200 transition duration-200">Batalkan</button>
                    <button onclick="confirmSubmit(event)" class="mt-4 px-4 bg-blue-500 text-white rounded hover:bg-blue-700 transition duration-200">Lanjutkan</button>
                </div>
            </div>
        </div>

        <div id="loadingModal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-20 hidden">
            <div class="bg-primary p-6 rounded-md shadow-md text-center items-center flex-col flex justify-center">
                <img src="{{ asset('assets/static/images/svg-loaders/tail-spin.svg') }}" class="mb-3" alt="confirm-submit-icon">
                <p class="text-white">Mengirim jawaban ...</p>
            </div>
        </div>

        <div id="loadingDataModal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-20 hidden">
            <div class="bg-primary p-6 rounded-md shadow-md text-center items-center flex-col flex justify-center">
                <img src="{{ asset('assets/static/images/svg-loaders/tail-spin.svg') }}" class="mb-3" alt="confirm-submit-icon">
                <p class="text-white">Mohon di tunggu ...</p>
            </div>
        </div>

        <div id="errorModal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-20 hidden">
            <div class="bg-primary p-6 rounded-md shadow-md text-center items-center flex-col flex justify-center">
                <img src="{{ asset('assets/static/images/svg-loaders/confirm-submit.svg') }}" class="mb-3" alt="confirm-submit-icon">
                <p class="text-white">Anda belum mengisi soal dengan lengkap!</p>
                <div class="flex justify-end w-full">
                    <button onclick="closeModal('errorModal')" class="mt-4 px-4 bg-white text-primary rounded hover:bg-slate-200 transition duration-200">Isi Kembali</button>
                </div>
            </div>
        </div>

        <div id="errorMaxModal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-20 hidden">
            <div class="bg-primary p-6 rounded-md shadow-md text-center items-center flex-col flex justify-center">
                <img src="{{ asset('assets/static/images/svg-loaders/confirm-submit.svg') }}" class="mb-3" alt="confirm-submit-icon">
                <p class="text-white" id="errMsgMaxModal">Anda telah mencapai batas percobaan.</p>
                <div class="flex justify-end w-full">
                    <button onclick="selesai()" class="mt-4 px-4 bg-white text-primary rounded hover:bg-slate-200 transition duration-200">Kembali</button>
                </div>
            </div>
        </div>

        <script src="https://cdn.ckeditor.com/ckeditor5/34.2.0/classic/ckeditor.js"></script>
        <script>
            let answerData = @json($answerData);
            let exam_id = @json($examId);
            let totalQuiz = @json($quizTotal);
            let tabQuiz = document.getElementById('content-exam');
            let showScoreData = @json($showScore);
            let titleExam = @json($titleExam);
            let typeExam = @json($typeExam);
            let studentName = @json($studentName);
            let idPengumpulan = '';
            let typeData = @json($typeData);
            let learningId = @json($learningId);
            let endTime = @json($endTime);
            let settingDuration = @json($settingDuration);
            let customTheme = @json($customTheme);

            function countdown(endDateTime, durationTime) {
                const endDateTimeData = new Date(endDateTime);
                const now = new Date();

                const isSameDate =  now.getFullYear() === endDateTimeData.getFullYear() &&
                                    now.getMonth() === endDateTimeData.getMonth() &&
                                    now.getDate() === endDateTimeData.getDate();

                let getTimeEndDateTime = endDateTimeData.getHours() * 3600 * 1000 + endDateTimeData.getMinutes() * 60 * 1000 + endDateTimeData.getSeconds() * 1000;

                if (getTimeEndDateTime === 0) {
                    getTimeEndDateTime = 24 * 3600 * 1000;
                }

                const currentTime = now.getHours() * 3600 * 1000 + now.getMinutes() * 60 * 1000 + now.getSeconds() * 1000;

                const [hours, minutes, seconds] = durationTime.split(":").map(Number);
                const convertDurationData = (hours * 3600 + minutes * 60 + seconds) * 1000;

                let getDurationEndTime = getTimeEndDateTime - currentTime;

                if (!isSameDate && getDurationEndTime < 0) {
                    getDurationEndTime += 24 * 3600 * 1000;
                }

                let countdownTime = Math.min(getDurationEndTime, convertDurationData);

                let hasSubmitted = false;

                function onCountdownComplete() {
                    if (!hasSubmitted) {
                        hasSubmitted = true;
                        confirmSubmit(new Event('submit'), true);
                        document.getElementById("countdown").innerText = 'Waktu Habis!';
                    }
                }

                if (countdownTime <= 0) {
                    onCountdownComplete();
                    return;
                }

                const timer = setInterval(() => {
                    const hours = String(Math.floor((countdownTime / (1000 * 60 * 60)) % 24)).padStart(2, "0");
                    const minutes = String(Math.floor((countdownTime / (1000 * 60)) % 60)).padStart(2, "0");
                    const seconds = String(Math.floor((countdownTime / 1000) % 60)).padStart(2, "0");

                    document.getElementById("countdown").innerText = `${hours}:${minutes}:${seconds}`;

                    countdownTime -= 1000;

                    if (countdownTime <= 0) {
                        clearInterval(timer);
                        onCountdownComplete();
                    }
                }, 1000);
            }

            function showQuestion() {
                tabQuiz.innerHTML = `
                @if(empty($questionData))
                    <div class="text-center p-6 rounded-md w-1/2">
                        No questions available.
                    </div>
                @else
                    <div class="bg-white shadow-md rounded-lg p-6 mb-12 flex justify-between">
                        <div class="font-bold text-sm md:text-base w-1/2 xl:w-auto">${titleExam}</div>
                        <div class="flex items-center">
                            <img src="{{ asset('assets/static/images/svg-loaders/time-logo.svg') }}" class="w-5 h-5 lg:w-6 lg:h-6" alt="timer">
                            <div class="ms-1">
                                <span class="text-sm md:text-base" id="countdown"></span>
                            </div>
                        </div>
                    </div>
                    <form method="POST" onsubmit="handleSubmit(event)">
                        @csrf
                        @foreach($questionData as $questionGroup)
                            @foreach($questionGroup as $index => $question)
                                <div class="bg-white shadow-md rounded-lg p-6 mb-4">
                                    <div class="flex justify-between md:flex-row flex-col border-solid border-b-2 border-black pb-2 mb-5 items-center">
                                        <span class="font-bold text-base md:text-lg">Pertanyaan</span>
                                        <span class="bg-slate-500 text-white px-2 py-1 text-sm md:text-base rounded">
                                            @switch($question->question_type)
                                                @case('True False')
                                                    {{ 'Benar Salah' }}
                                                @break
                                                @case('Essay')
                                                    {{ 'Esai' }}
                                                @break
                                                @case('Pilihan Ganda Complex')
                                                    {{ 'Pilihan Ganda Kompleks' }}
                                                @break
                                                @default
                                                    {{ $question->question_type }}
                                            @endswitch
                                        </span>
                                    </div>
                                    @if (count($question->question_attachments) > 0)
                                        @foreach($question->question_attachments as $attachment)
                                            @php
                                                $linkUrl = str_replace('storage/public/', '', $attachment->file_url);
                                            @endphp
                                            @if ($attachment->file_type === 'image')
                                                <div class="attachment" id="attachmentImage">
                                                    <img src="{{ Storage::url($linkUrl) }}" alt="{{ $attachment->file_name }}" class="my-2">
                                                </div>
                                            @elseif ( $attachment->file_type === "audio")
                                                <div class="attachment" id="attachmentAudio">
                                                    <audio controls class="my-2 w-full">
                                                        <source src="{{ Storage::url($linkUrl) }}" type="audio/mpeg">
                                                    </audio>
                                                </div>
                                            @endif
                                        @endforeach
                                    @endif
                                    {!! $question->question_text !!}
                                    @if ($question->question_type === "True False")
                                        <div class="flex flex-col mt-4">
                                            @foreach($question->choices as $choice)
                                                <label class="inline-flex items-center">
                                                <input
                                                    type="radio"
                                                    class="form-radio"
                                                    name="answers_true_false_[{{ $index }}][choice_id]"
                                                    value="{{ $choice->id }}"
                                                    @if(isset($answerData[$index]->choice_id) && $answerData[$index]->choice_id == $choice->id) checked @endif
                                                    onclick="handleAnswer(event, '{{ $question->id }}', '{{ $choice->id }}', '{{ $choice->choice_text }}')">
                                                    <span class="ml-2">{!! $choice->choice_text !!}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @elseif($question->question_type === "Pilihan Ganda")
                                        <div class="flex flex-col mt-4">
                                            @foreach($question->choices as $choice)
                                                <label class="inline-flex items-center">
                                                <input
                                                type="radio"
                                                class="form-radio"
                                                name="answers_pilihan_ganda[{{ $index }}][choice_id]"
                                                value="{{ $choice->id }}"
                                                @if(isset($answerData[$index]->choice_id) && $answerData[$index]->choice_id == $choice->id) checked @endif
                                                onclick="handleAnswer(event, '{{ $question->id }}', '{{ $choice->id }}', '{{ $choice->choice_text }}')">
                                                    <span class="ml-2">{!! $choice->choice_text !!}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @elseif($question->question_type === "Pilihan Ganda Complex")
                                        <div class="flex flex-col mt-4">
                                            @foreach($question->choices as $choice)
                                                <label class="inline-flex items-center">
                                                <input
                                                type="checkbox"
                                                class="form-radio"
                                                name="answers_pilihan_ganda_complex[{{ $index }}][choice_id]"
                                                value="{{ $choice->id }}"
                                                @if(isset($answerData[$index]->choice_id) && $answerData[$index]->choice_id == $choice->id) checked @endif
                                                onchange="handleAnswer(event, '{{ $question->id }}', '{{ $choice->id }}', '{{ $choice->choice_text }}')">
                                                <span class="ml-2">{!! $choice->choice_text !!}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="mt-4">
                                            <textarea
                                                id="exam_content_{{ $index }}"
                                                name="answers_essay_[{{ $index }}][answer_text]"
                                                onchange="handleAnswer(event, '{{ $question->id }}')"
                                                placeholder="Masukkan Jawaban">
                                                {{ old('answers['.$index.'][answer_text]', isset($answerData[$index]->answer_text) ? $answerData[$index]->answer_text : '') }}
                                            </textarea>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        @endforeach
                        <div class="flex justify-end">
                            <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition duration-200" type="submit" id="submitButtonAnswer">
                                Submit
                            </button>
                        </div>
                    </form>
                @endif
                `

                countdown(endTime, settingDuration);
            }

            function classifyScore(totalPoints, obtainedPoints) {
                const percentage = (obtainedPoints / totalPoints) * 100;
                let classification = "";
                let messageClassification = "";

                if (percentage >= 90) {
                    classification = "Excellent";
                    messageClassification = "Kerja bagus! Teruskan ya!";
                } else if (percentage >= 70) {
                    classification = "Good";
                    messageClassification = "Bagus, terus kembangkan ya!";
                } else if (percentage >= 50) {
                    classification = "Fair";
                    messageClassification = "Sudah cukup baik, mari kita berusaha lebih baik lagi!";
                } else {
                    classification = "Poor";
                    messageClassification = "Sedikit lagi! Mari kita tinggkatkan usaha kita.";
                }

                return {
                    status: classification,
                    message: messageClassification,
                    score: obtainedPoints + "/" + totalPoints + " (" + percentage.toFixed(2) + "%)"
                }
            }

            function notShowScore(statusSubmit, messageStatusSubmit) {
                tabQuiz.innerHTML = ""
                tabQuiz.innerHTML = `
                    <div id="alertBoxStatusSubmit" class="border px-4 py-3 rounded relative my-2 flex justify-between ${statusSubmit ? "bg-green-600 border-green-400 text-white" : "bg-red-600 border-red-400 text-white"}" role="alert">
                        <ul class="m-0 p-0">
                            <li style="list-style: none" class="font-bold">${statusSubmit ? "Success": "Failed" }</li>
                            <li style="list-style: none">${messageStatusSubmit}</li>
                        </ul>
                        <span id="closeButtonStatusSubmit" class="top-0 bottom-0 right-0 mx-4 my-3 cursor-pointer bg-white rounded-full">
                            <svg class="fill-current h-6 w-6 ${statusSubmit ? "text-green-600" : "text-red-600"}" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                        </span>
                    </div>
                    <div class="flex flex-col items-center">
                        <span class="font-bold text-base md:text-lg"> ${ titleExam } </span>
                        <span class="text-sm md:text-base"> ${ typeExam } </span>
                        <div class="my-10">
                            <img src="{{ asset('assets/static/images/svg-loaders/NA-score.svg') }}" class="w-full h-full" alt="empty-score">
                        </div>
                        <div>
                            <button type="button" class="bg-primary text-white px-4 py-2 rounded hover:bg-blue-700 transition duration-200" onclick="lanjutkanPengumpulan()">Lanjutkan</button>
                        </div>
                    </div>
                `
                const closeButton = document.getElementById("closeButtonStatusSubmit");
                closeButton.addEventListener("click", function () {
                    const alertBox = document.getElementById("alertBoxStatusSubmit");
                    alertBox.classList.add('hidden');
                });
            }

            function showScore(statusSubmit, messageStatusSubmit) {
                showModal('loadingDataModal');
                tabQuiz.innerHTML = ""
                let urlAnalytic = "";
                if (typeData === "ClassExam") {
                    urlAnalytic = `/api/v1/mobile/student/${exam_id}/analytic`
                } else if (typeData === "SchoolExam") {
                    urlAnalytic = `/api/v1/mobile/student/${exam_id}/analytic-exam`
                }

                fetch(urlAnalytic, {
                    method: "GET",
                    headers: {
                        "Content-Type": "application/json",
                        'authorization': 'Bearer {{ session('token') }}'
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const dataResult = data.data.find(item => item.response_id === idPengumpulan);
                        if (dataResult) {
                            closeModal('loadingDataModal');
                            const totalPoint = dataResult['total_points'];
                            const obtainedPoint = dataResult['obtained_points'];
                            const scoreClassification = classifyScore(totalPoint, obtainedPoint);

                            let svgImageHtml = "";

                            if (scoreClassification.status === "Excellent") {
                                svgImageHtml = `
                                <div class="mt-3">
                                    <img src="{{ asset('assets/static/images/svg-loaders/excellent-score.svg') }}" class="" alt="excellent-score">
                                </div>
                            `
                            } else if (scoreClassification.status === "Good") {
                                svgImageHtml = `
                                <div class="mt-3">
                                    <img src="{{ asset('assets/static/images/svg-loaders/good-score.svg') }}" class="" alt="good-score">
                                </div>
                            `
                            } else if (scoreClassification.status === "Fair") {
                                svgImageHtml = `
                                <div class="mt-3">
                                    <img src="{{ asset('assets/static/images/svg-loaders/fair-score.svg') }}" class="" alt="fair-score">
                                </div>
                            `
                            } else {
                                svgImageHtml = `
                                <div class="mt-3">
                                    <img src="{{ asset('assets/static/images/svg-loaders/poor-score.svg') }}" class="" alt="poor-score">
                                </div>
                            `
                            }
                            tabQuiz.innerHTML = `
                            <div id="alertBoxStatusSubmit" class="border px-4 py-3 rounded relative my-2 flex justify-between ${statusSubmit ? "bg-green-600 border-green-400 text-white" : "bg-red-600 border-red-400 text-white"}" role="alert">
                                <ul class="m-0 p-0">
                                    <li style="list-style: none" class="font-bold">${statusSubmit ? "Success": "Failed" }</li>
                                    <li style="list-style: none">${messageStatusSubmit}</li>
                                </ul>
                                <span id="closeButtonStatusSubmit" class="top-0 bottom-0 right-0 mx-4 my-3 cursor-pointer bg-white rounded-full">
                                    <svg class="fill-current h-6 w-6 ${statusSubmit ? "text-green-600" : "text-red-600"}" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                                </span>
                            </div>
                            <div class="flex justify-center flex-col items-center text-primary">
                                <div class="font-bold text-lg">${ titleExam }</div>
                                <div class="">${ typeExam}</div>
                                ${svgImageHtml}
                                <div class="mt-2 flex flex-col items-center">
                                    <div class="font-bold text-lg">${scoreClassification.score}</div>
                                    <div>${scoreClassification.message}</div>
                                </div>
                                <div class="mt-2">
                                    <button type="button" class="bg-primary text-white px-4 py-2 rounded hover:bg-blue-700 transition duration-200" onclick="lanjutkanPengumpulan()">Lanjutkan</button>
                                </div>
                            </div>
                        `
                            const closeButton = document.getElementById("closeButtonStatusSubmit");
                            closeButton.addEventListener("click", function () {
                                const alertBox = document.getElementById("alertBoxStatusSubmit");
                                alertBox.classList.add('hidden');
                            });
                        }
                    }
                })
                .catch((error) => {
                    console.error(error);
                })
            }

            const formatTime = (dateTimeStr) => {
                const date = new Date(dateTimeStr);
                const hours = date.getHours().toString().padStart(2, '0');
                const minutes = date.getMinutes().toString().padStart(2, '0');
                return `${hours}:${minutes}`;
            };

            const formatDate = (dateTimeStr) => {
                const date = new Date(dateTimeStr);
                const dayNumber = date.getDate();
                const month = date.getMonth();
                const year = date.getFullYear();

                return `${dayNumber}/${month}/${year}`;
            };

            function showPengumpulan() {
                showModal('loadingDataModal');
                tabQuiz.innerHTML = ""
                let urlAnalyticPengumpulan = "";
                if (typeData === "ClassExam") {
                    urlAnalyticPengumpulan = `/api/v1/mobile/student/${exam_id}/analytic`
                } else if (typeData === "SchoolExam") {
                    urlAnalyticPengumpulan = `/api/v1/mobile/student/${exam_id}/analytic-exam`
                }
                fetch(urlAnalyticPengumpulan, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'authorization': 'Bearer {{ session('token') }}'
                    }
                }).then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const dataResult = data.data.find(item => item.response_id === idPengumpulan);
                        if (dataResult) {
                            const endTime = dataResult['end_time'];
                            const startTime = dataResult['start_time'];
                            closeModal('loadingDataModal');
                            tabQuiz.innerHTML = `
                                <div class="bg-white shadow-md rounded-lg p-6 mb-4">
                                    <div class="font-bold text-lg md:text-xl text-primary text-center">${ titleExam }</div>
                                    <div class="text-primary text-center text-sm md:text-base">${ typeExam }</div>
                                    <div class="mt-4 flex justify-start flex-col">
                                        <div class="my-2">
                                            <div class="font-bold text-base md:text-lg">Siswa :</div>
                                            <div class="ms-2 md:ms-5 text-sm md:text-base text-capitalize">${ dataResult['student_name'] }</div>
                                        </div>
                                        <div class="my-2">
                                            <div class="font-bold text-base md:text-lg">Waktu pengerjaan :</div>
                                            <div class="flex justify-between w-11/12 xl:w-1/2">
                                                <span class="ms-2 md:ms-5 text-sm md:text-base">Mulai : </span>
                                                <span class="ms-2 md:ms-5 text-sm md:text-base">${formatTime(startTime)} | ${formatDate(startTime)}</span>
                                            </div>
                                            <div class="flex justify-between w-11/12 xl:w-1/2">
                                                <span class="ms-2 md:ms-5 text-sm md:text-base">Selesai : </span>
                                                <span class="ms-2 md:ms-5 text-sm md:text-base">${formatTime(endTime)} | ${formatDate(endTime)}</span>
                                            </div>
                                        </div>
                                        <div class="my-2">
                                            <div class="font-bold text-base md:text-lg">Point Diperoleh :</div>
                                            <div class="flex justify-between w-4/5 md:w-1/2">
                                                <span class="ms-2 md:ms-5 text-sm md:text-base">Pilihan ganda : </span>
                                                <span class="ms-2 md:ms-5 text-sm md:text-base">${ dataResult['obtained_points'] }</span>
                                            </div>
                                            <div class="flex justify-between w-4/5 md:w-1/2">
                                                <span class="ms-2 md:ms-5 text-sm md:text-base font-bold">Total Poin : </span>
                                                <span class="ms-2 md:ms-5 text-sm md:text-base">${ dataResult['total_points'] }</span>
                                            </div>
                                        </div>
                                        <div class="my-2">
                                            <div class="font-bold text-base md:text-lg">Pertanyaan : </div>
                                            <div class="flex justify-between w-4/6 md:w-1/2">
                                                <span class="ms-2 md:ms-5 text-sm md:text-base">Pilihan Ganda : </span>
                                                <span class="ms-2 md:ms-5 text-sm md:text-base">${ dataResult['question_count']['Pilihan Ganda'] }</span>
                                            </div>
                                            <div class="flex justify-between w-4/6 md:w-1/2">
                                                <span class="ms-2 md:ms-5 text-sm md:text-base">Pilihan Ganda Kompleks : </span>
                                                <span class="ms-2 md:ms-5 text-sm md:text-base">${ dataResult['question_count']['Pilihan Ganda Complex'] }</span>
                                            </div>
                                            <div class="flex justify-between w-4/6 md:w-1/2">
                                                <span class="ms-2 md:ms-5 text-sm md:text-base">Benar Salah : </span>
                                                <span class="ms-2 md:ms-5 text-sm md:text-base">${ dataResult['question_count']['True False'] }</span>
                                            </div>
                                            <div class="flex justify-between w-4/6 md:w-1/2">
                                                <span class="ms-2 md:ms-5 text-sm md:text-base">Esai : </span>
                                                <span class="ms-2 md:ms-5 text-sm md:text-base">${ dataResult['question_count']['Essay'] }</span>
                                            </div>
                                            <div class="flex justify-between w-4/6 md:w-1/2">
                                                <span class="ms-2 md:ms-5 text-sm md:text-base">Jumlah Pertanyaan : </span>
                                                <span class="ms-2 md:ms-5 text-sm md:text-base">${ dataResult['question_count']['Total'] }</span>
                                            </div>
                                        </div>
                                        <div class="my-2">
                                            <div class="font-bold text-base md:text-lg">Jawaban</div>
                                            <div class="flex justify-between w-4/5 md:w-1/2">
                                                <span class="ms-2 md:ms-5 text-sm md:text-base">Jumlah Diisi : </span>
                                                <span class="ms-2 md:ms-5 text-sm md:text-base">${ dataResult['answers']['filled'] }</span>
                                            </div>
                                            <div class="flex justify-between w-4/5 md:w-1/2">
                                                <span class="ms-2 md:ms-5 text-sm md:text-base">Jumlah kosong : </span>
                                                <span class="ms-2 md:ms-5 text-sm md:text-base">${ dataResult['answers']['unanswered'] }</span>
                                            </div>
                                            <div class="flex justify-between w-4/5 md:w-1/2">
                                                <span class="ms-2 md:ms-5 text-sm md:text-base">Jumlah Benar : </span>
                                                <span class="ms-2 md:ms-5 text-sm md:text-base">${ dataResult['answers']['correct'] }</span>
                                            </div>
                                            <div class="flex justify-between w-4/5 md:w-1/2">
                                                <span class="ms-2 md:ms-5 text-sm md:text-base">Jumlah Salah : </span>
                                                <span class="ms-2 md:ms-5 text-sm md:text-base">${ dataResult['answers']['incorrect'] }</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-2 flex justify-center">
                                        <button type="button" class="bg-yellow-400 text-white px-4 py-2 rounded hover:bg-yellow-700 transition duration-200 me-4 text-sm md:text-base rounded" onclick="lanjutkanAnalytic()">Analisis</button>
                                        <button type="button" class="bg-primary text-white px-4 py-2 rounded hover:bg-blue-700 transition duration-200 text-sm md:text-base rounded" onclick="selesai()">Selesai</button>
                                    </div>
                                </div>
                            `
                        }
                    }
                })
                .catch((error) => {
                    console.error(error);
                })
            }

            function lanjutkanAnalytic() {
                showAnalytic();
            }

            function showAnalytic() {
                showModal('loadingDataModal');
                tabQuiz.innerHTML = "";
                fetch('/api/v1/mobile/student/answer', {
                    method: 'get',
                    headers: {
                        'Content-Type': 'application/json',
                        'authorization': 'Bearer {{ session('token') }}'
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeModal('loadingDataModal');
                        const dataResult = [];
                        data.data.forEach(item => {
                            if (item.response_id === idPengumpulan) {
                                dataResult.push(item);
                            }
                        })
                        tabQuiz.innerHTML = `
                        @foreach($questionData as $questionGroup)
                            @foreach ($questionGroup as $index => $question)
                                <div class="bg-white shadow-md rounded-lg p-6 mb-4">
                                    <div class="flex justify-between md:flex-row flex-col border-solid border-b-2 border-black pb-2 mb-5 items-center">
                                        <span class="font-bold text-base md:text-lg">Pertanyaan</span>
                                        <span class="bg-slate-500 text-white px-2 py-1 text-sm md:text-base rounded">
                                            @switch($question->question_type)
                                                @case('True False')
                                                    {{ 'Benar Salah' }}
                                                    @break
                                                @case('Essay')
                                                    {{ 'Esai' }}
                                                    @break
                                                @case('Pilihan Ganda Complex')
                                                    {{ 'Pilihan Ganda Kompleks' }}
                                                    @break
                                                @default
                                                    {{ $question->question_type }}
                                                @endswitch
                                            </span>
                                    </div>
                                    @if (count($question->question_attachments) > 0)
                                        @foreach($question->question_attachments as $attachment)
                                            @php
                                                $linkUrl = str_replace('storage/public/', '', $attachment->file_url);
                                            @endphp
                                            @if ($attachment->file_type === 'image')
                                            <div class="attachment" id="attachmentImage">
                                                <img src="{{ Storage::url($linkUrl) }}" alt="{{ $attachment->file_name }}" class="my-2">
                                                                </div>
                                                            @elseif ( $attachment->file_type === "audio")
                                                <div class="attachment" id="attachmentAudio">
                                                    <audio controls class="my-2 w-full">
                                                        <source src="{{ Storage::url($linkUrl) }}" type="audio/mpeg">
                                                    </audio>
                                                </div>
                                            @endif
                                        @endforeach
                                    @endif
                                    {!! $question->question_text !!}
                                    @if ($question->question_type === "True False")
                                        <div class="flex flex-col mt-4">
                                            @foreach($question->choices as $choice)
                                                <label class="inline-flex items-center">
                                                    <input
                                                        type="radio"
                                                        class="form-radio"
                                                        name="answers_true_false_[{{ $index }}][choice_id]"
                                                        value="{{ $choice->id }}"
                                                        id="{{ $choice->id }}"
                                                        disabled
                                                    />
                                                    <span class="ml-2">{!! $choice->choice_text !!}</span>
                                                    @if ($choice->is_true)
                                                        <div class="ms-2">
                                                            <img src="{{ asset('assets/static/images/svg-loaders/checklist-analysis.svg') }}" alt="{{ $choice->id }}-isTrue">
                                                        </div>
                                                    @endif
                                                </label>
                                            @endforeach
                                        </div>
                                    @elseif($question->question_type === "Pilihan Ganda")
                                        <div class="flex flex-col mt-4">
                                            @foreach($question->choices as $choice)
                                                <label class="inline-flex items-center">
                                                <input
                                                    type="radio"
                                                    class="form-radio"
                                                    name="answers_pilihan_ganda_[{{ $index }}][choice_id]"
                                                    value="{{ $choice->id }}"
                                                    id="{{ $choice->id }}"
                                                    disabled
                                                />
                                                    <span class="ml-2">{!! $choice->choice_text !!}</span>
                                                    @if ($choice->is_true)
                                                        <div class="ms-2">
                                                            <img src="{{ asset('assets/static/images/svg-loaders/checklist-analysis.svg') }}" alt="{{ $choice->id }}-isTrue">
                                                        </div>
                                                    @endif
                                                </label>
                                            @endforeach
                                        </div>
                                    @elseif($question->question_type === "Pilihan Ganda Complex")
                                        <div class="flex flex-col mt-4">
                                            @foreach($question->choices as $choice)
                                                <label class="inline-flex items-center">
                                                <input
                                                    type="checkbox"
                                                    class="form-radio"
                                                    name="answers_pilihan_ganda_complex_[{{ $index }}][choice_id]"
                                                    value="{{ $choice->id }}"
                                                    id="{{ $choice->id }}"
                                                    disabled
                                                />
                                                    <span class="ml-2">{!! $choice->choice_text !!}</span>
                                                    @if ($choice->is_true)
                                                        <div class="ms-2">
                                                            <img src="{{ asset('assets/static/images/svg-loaders/checklist-analysis.svg') }}" alt="{{ $choice->id }}-isTrue">
                                                        </div>
                                                    @endif
                                                </label>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="mt-4">
                                            <div class="mb-2">
                                                <span class="">Jawaban yang Diisi</span>
                                            </div>
                                            <textarea
                                                id="exam_user_{{ $index }}"
                                                name="user[{{ $index }}][answer_text]"
                                             >
                                            </textarea>
                                        </div>

                                        {{--<div class="mt-4">--}}
                                        {{--    <div class="flex items-center mb-2">--}}
                                        {{--        <img src="{{ asset('assets/static/images/svg-loaders/checklist-analysis.svg') }}" alt="checklist-analysis">--}}
                                        {{--        <span class="ms-2"> Jawaban benar </span>--}}
                                        {{--    </div>--}}
                                        {{--    <textarea--}}
                                        {{--        id="exam_answer_{{ $index }}"--}}
                                        {{--        name="answers[{{ $index }}][answer_text]"--}}
                                        {{--    >--}}
                                        {{--    </textarea>--}}
                                        {{--</div>--}}
                                     @endif
                                </div>
                            @endforeach
                        @endforeach
                        <div class="flex justify-end">
                            <button type="button" class="bg-primary text-white px-4 py-2 rounded hover:bg-blue-700 transition duration-200 text-sm md:text-base rounded" onclick="selesai()">Selesai</button>
                        </div>
                        `

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
                    console.error(error);
                })
            }

            function lanjutkanPengumpulan() {
                showPengumpulan();
            }

            document.addEventListener("DOMContentLoaded", (event) => {
                showQuestion();

                const customTextColored = document.querySelectorAll('[class*="text-[#001951]"]');
                const customBgColor = document.querySelectorAll('[class*="bg-[#001951]"]');
                const customTextWhite = document.querySelectorAll('[class*="text-white"]');
                const bgActiveCard = document.querySelectorAll('.bg-blue-900');
                const bgPrimaryCustom = document.querySelectorAll('.bg-primary');
                const customSvgIcon = document.querySelector('.svg-icon path');
                const bgCard = document.querySelectorAll('.bg-card');
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
                }

                document.querySelectorAll('textarea[id^="exam_content_"]').forEach((textarea) => {
                    ClassicEditor.create(textarea)
                        .then(editor => {
                            editor.model.document.on('change:data', () => {
                                textarea.value = editor.getData();
                                const event = new Event('change');
                                textarea.dispatchEvent(event)
                            })
                        })
                        .catch((error) => {
                        console.error(error)
                    });
                });
            });

            function handleAnswer(event, questionId, choiceId = null, choiceText = null) {
                const question = answerData.find(q => q.question_id === questionId);

                if (question) {
                    if (event.target.type === "textarea") {
                        question.answer_text = event.target.value;
                        delete question.choice_id;
                    } else if (event.target.type === "checkbox") {
                       if (!event.target.checked) {
                           const choiceIndex = answerData.findIndex(q => q.choice_id === choiceId);
                           answerData.splice(choiceIndex, 1);
                       } else {
                           if (question.choice_id === null) {
                               question.choice_id = choiceId;
                               question.answer_text = choiceText;
                           } else {
                                answerData.push({
                                    question_id: questionId,
                                    choice_id: choiceId,
                                    answer_text: choiceText
                                })
                           }
                       }
                    } else {
                        question.choice_id = event.target.type === "radio" ? event.target.value : choiceId;
                        question.answer_text = choiceText;
                    }
                }
                localStorage.setItem('answers', JSON.stringify(answerData));
            }

            function confirmSubmit(event, timesUp = false) {
                event.preventDefault();
                const uniqueQuestionIds = new Set(answerData.filter(answer => answer.answer_text !== null).map(answer => answer.question_id));
                const countUniqueQuestionIds = uniqueQuestionIds.size;
                const submitButton = document.getElementById('submitButtonAnswer');
                let filteredAnswerData = answerData.filter(answer => answer.answer_text !== null && answer.answer_text !== undefined);

                if (countUniqueQuestionIds < totalQuiz && timesUp === false) {
                    closeModal('confirmModal');
                    showModal('errorModal');
                } else {
                    closeModal('confirmModal');
                    showModal('loadingModal');

                    idPengumpulan = localStorage.getItem("responseId");
                    storageExamId = localStorage.getItem("examId");
                    storageSchoolExamId = localStorage.getItem("schoolExamId");
                    let bodyAnswer;
                    if (timesUp === true && filteredAnswerData.length === 0) {
                        bodyAnswer = JSON.stringify({
                            response_id: idPengumpulan,
                            exam_id: storageExamId === "null"? null : storageExamId,
                            school_exam_id: storageSchoolExamId === "null"? null : storageSchoolExamId,
                        })
                    } else if (timesUp === true && filteredAnswerData.length > 0) {
                        bodyAnswer = JSON.stringify({
                            response_id: idPengumpulan,
                            exam_id: storageExamId === "null"? null : storageExamId,
                            school_exam_id: storageSchoolExamId === "null"? null : storageSchoolExamId,
                            answers: filteredAnswerData
                        })
                    } else {
                        bodyAnswer = JSON.stringify({
                            response_id: idPengumpulan,
                            exam_id: storageExamId === "null"? null : storageExamId,
                            school_exam_id: storageSchoolExamId === "null"? null : storageSchoolExamId,
                            answers: answerData
                        })
                    }

                    fetch('/api/v1/mobile/student/answer', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'authorization': 'Bearer {{ session('token') }}'
                        },
                        body: bodyAnswer
                    })
                        .then(response => response.json())
                        .then(dataAnswer => {
                            localStorage.removeItem("responseId");
                            localStorage.removeItem("examId");
                            localStorage.removeItem("schoolExamId");
                            if (dataAnswer.success) {
                                if (submitButton !== null) {
                                    submitButton.disabled = false;
                                }

                                let bodyReqResponse = {};
                                if (typeData === "ClassExam") {
                                    bodyReqResponse = {
                                        exam_id: exam_id,
                                        status: "pengumpulan"
                                    }
                                } else if (typeData === "SchoolExam") {
                                    bodyReqResponse = {
                                        school_exam_id: exam_id,
                                        status: "pengumpulan"
                                    }
                                }

                                fetch('/api/v1/mobile/student/response', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'authorization': 'Bearer {{ session('token') }}'
                                    },
                                    body: JSON.stringify(bodyReqResponse)
                                })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success && data.message === "Berhasil menyimpan jawaban.") {
                                            closeModal('loadingModal');
                                            if (showScoreData) {
                                                showScore(dataAnswer.success, dataAnswer.message);
                                            } else {
                                                notShowScore(dataAnswer.sucess, dataAnswer.message);
                                            }
                                        } else if (data.success === false && data.message === "Anda tidak dapat mengirim submission yang melebihi deadline.") {
                                            closeModal('loadingModal');
                                            if (showScoreData) {
                                                showScore(dataAnswer.success, dataAnswer.message);
                                            } else {
                                                notShowScore(dataAnswer.sucess, dataAnswer.message);
                                            }
                                        } else {
                                            document.getElementById('errMsgMaxModal').innerHTML = data.message;
                                            closeModal('loadingModal');
                                            if (submitButton !== null) {
                                                submitButton.disabled = false;
                                            }
                                            showModal('errorMaxModal');
                                        }
                                    })
                                    .catch((error) => {
                                        if (submitButton !== null) {
                                            submitButton.disabled = false;
                                        }
                                        closeModal('loadingModal');
                                    })
                            }
                        }).catch((errorAnswer) => {
                            if (submitButton !== null) {
                                submitButton.disabled = false;
                            }
                            closeModal('loadingModal');
                        });
                }
            }

            function handleSubmit(event) {
                event.preventDefault();
                showModal('confirmModal');
                const submitButton = document.getElementById('submitButtonAnswer');
                if (submitButton !== null) {
                    submitButton.disabled = true;
                }
            }

            function showModal(modalId) {
                document.getElementById(modalId).classList.remove('hidden');
            }

            function closeModal(modalId) {
                document.getElementById(modalId).classList.add('hidden');
                const submitButton = document.getElementById('submitButtonAnswer');
                if (submitButton !== null) {
                    submitButton.disabled = false;
                }
            }

            function selesai() {
                window.close();
            }
        </script>
    </div>
@endsection
