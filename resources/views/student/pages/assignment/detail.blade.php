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
        <div class="flex justify-between mt-4 bg-white shadow-md p-4 rounded-lg mb-4">
            <button class="text-primary font-semibold" onclick="openTab(event, 'assignment-detail')">Detail Tugas</button>
            <button class="text-gray-500" onclick="openTab(event, 'assignment-submit')">Kirim Tugas</button>
            <button class="text-gray-500" onclick="openTab(event, 'assignment-history')">Riwayat Pengerjaan</button>
            <button class="text-gray-500" onclick="openTab(event, 'assignment-attach')">Lampiran</button>
        </div>
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('message'))
            <div class="alert {{ session('alertClass') }} alert-dismissible fade show " role="alert">
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <!-- Task Details -->
        <div id="assignment-detail" class="tab-content bg-white shadow-md rounded-lg p-6">
            <!-- Task Title and Subject -->
            <div class="mb-4">
                {{-- @dd($assignment); --}}
                <h1 class="text-xl lg:text-3xl text-center font-bold text-[#001951]">{{ $assignment->assignment_title }}
                </h1>
                <p class="text-[#001951] text-center">{{ $course->courses_title }}</p>
            </div>

            <!-- Task Description -->
            <div class="mb-4">
                <h2 class="text-lg font-semibold">Deskripsi Tugas</h2>
                <p class="text-gray-700 text-sm">
                    {!! $assignment->assignment_description !!}
                </p>
            </div>

            <!-- Task Instructions -->
            <div class="mb-4">
                <h2 class="text-lg font-semibold">Instruksi Pengerjaan Tugas :</h2>
                {!! $assignment->instruction !!}
            </div>

            <!-- Submission Deadline -->
            <div class="mb-4">
                <h2 class="text-lg font-semibold">Batas Pengumpulan :</h2>
                <div class="flex items-center space-x-2">
                    <span
                        class="bg-red-500 text-white text-sm font-semibold px-3 py-1 rounded-md">{{ date('H:i', strtotime($assignment->end_time)) }}</span>
                    <span
                        class="bg-red-500 text-white text-sm font-semibold px-3 py-1 rounded-md">{{ date('D, d-m-Y', strtotime($assignment->due_date)) }}</span>
                </div>
            </div>

            <!-- Submission Opportunity -->
            <div class="mb-4">
                <h2 class="text-lg font-semibold">Kesempatan Mengirim Tugas :</h2>
                <span
                    class="bg-blue-900 text-white text-sm font-semibold px-3 py-1 rounded-md">{{ count($submissions) }}/{{ $assignment->limit_submit }}
                </span>
            </div>

            <!-- Task Settings -->
            <div class="mb-4">
                <h2 class="text-lg font-semibold">Pengaturan Pengerjaan :</h2>
                <div class="flex space-x-2">
                    <p class=" badge bg-blue-900 text-white px-4 py-2 rounded-md">{{ $assignment->collection_type }}</p>
                </div>
            </div>
        </div>

        <!-- Task Submission -->
        <div id="assignment-submit" class="tab-content hidden bg-white shadow-md rounded-lg p-6">
            <form action="{{ route('student.assignment.submit') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <h4 class="text-xl font-bold text-[#001951] w-fit">Catatan Untuk Tugas anda
                    <sup class="text-destructive">
                        ({{ $assignment->collection_type === 'All' || $assignment->collection_type === 'Catatan' ? '*Wajib' : 'Opsional' }})
                    </sup>
                </h4>
                <input type="hidden" name="assignment_id" value="{{ $assignment->id }}">

                <textarea id="submission_content" name="submission_content">-</textarea>


                <div class="inline">
                    <h4 class="text-xl font-bold text-[#001951] mt-3 w-fit">Lampiran Untuk Tugas anda
                        <sup class="text-destructive">
                            ({{ $assignment->collection_type === 'All' || $assignment->collection_type === 'Lampiran' ? '*Wajib' : 'Opsional' }})
                        </sup>
                    </h4>
                </div>
                <span>Maksimal {{ $assignment->max_attach }} lampiran dalam satu kali kirim tugas</span>
                <div id="resources">
                    <!-- Lampiran Template (Initial) -->
                    <div id="resources-container">
                        <div class="flex items-center space-x-4 mb-4 resource" data-index="0">
                            <!-- Dropdown Jenis Lampiran -->
                            <div class="w-1/2">
                                <select name="resources[0][file_type]"
                                    class="w-full p-2 border rounded-md focus:outline-none focus:ring focus:border-blue-300"
                                    onchange="changeInputType(0, this)">
                                    <option value="audio">Audio</option>
                                    <option value="video">Video</option>
                                    <option value="archive">Arsip</option>
                                    <option value="image">Gambar</option>
                                    <option value="document">Dokumen</option>
                                    <option value="url">URL</option>
                                    <option value="youtube">Youtube</option>
                                </select>
                            </div>
                            <!-- Input File -->
                            <div class="flex-grow mt-7">
                                <input type="file" name="resources[0][file_url]"
                                    class="w-full p-2 border rounded-md focus:outline-none focus:ring focus:border-blue-300"
                                    id="resource_input_0" />
                                <input type="hidden" name="resources[0][file_name]" value="Resource" class="form-control">

                                <small class="text-gray-500" id="file_info_0">Ekstensi yang diterima: .mp3, .mpeg, .aac,
                                    .wav</small>

                            </div>
                            <!-- Button Remove -->
                            <button type="button" class="text-red-600" onclick="removeLampiran(this)">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                <p id="warning-message" class="text-red-600 mt-2 hidden">Anda telah mencapai batas maksimum lampiran!</p>
                <button type="button" id="add-lampiran"
                    class="flex items-center text-white bg-blue-900 px-4 py-2 rounded-md" onclick="addLampiran()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H5a1 1 0 110-2h3V6a1 1 0 011-1z"
                            clip-rule="evenodd" />
                    </svg>
                    Tambahkan Lampiran
                </button>
                <div class="w-full flex justify-center">
                    @php
                    
                        $hasGrades = false;
                        foreach ($submissions as $submission) {
                            if (
                                !is_null($submission->grades[0]->knowledge) ||
                                !is_null($submission->grades[0]->skills)
                            ) {
                                $hasGrades = true;
                                break;
                            }
                        }
                    
                    @endphp
                    @if ($assignment->due_date < now())
                        <p class="text-red-600 text-center mt-4">Sudah melewati batas akhir pengerjaan</p>
                    @elseif ($assignment->limit_submit < 1)
                        <p class="text-red-600 text-center mt-4">Sudah melebihi batas pengumpulan</p>
                    @else
                        <button type="submit" id="submit-assignment"
                            class="btn bg-[#0058a8] text-white font-bold p-2 rounded-md mt-4 mx-auto {{ $hasGrades ? 'opacity-50 cursor-not-allowed' : '' }}"
                            {{ $hasGrades ? 'disabled' : '' }}>Kirimkan
                            Tugas</button>
                    @endif
                </div>
            </form>
        </div>
        <!-- Task History -->
        <div id="assignment-history" class="tab-content hidden bg-white shadow-md rounded-lg p-6">
            @if (empty($submissions))
                <p class="text-center">Tidak ada riwayat pengerjaan</p>
            @endif
            @foreach ($submissions as $index => $submission)
                <div class="mt-4" id="submission-{{ $index }}" data-index="{{ $index }}">
                    <button id="dropdown-btn-{{ $index }}" data-id="{{ $index }}"
                        onclick="toggleDropdown({{ $index }})"
                        class="w-full text-left flex justify-between items-center bg-gray-100 p-2 rounded-md">
                        <div class="flex flex-col items-start">
                            <h5 class="font-semibold">Riwayat Pengerjaan Tugas</h5>
                            <span>{{ date('H:i', strtotime($submission->submitted_at)) }} -
                                {{ date('D, d-m-Y', strtotime($submission->submitted_at)) }}</span>
                        </div>
                        <span id="arrow-icon-{{ $index }}">
                            <!-- Icon Arrow Down -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </span>
                    </button>
                    <!-- Detail Pengerjaan -->
                    <div id="dropdown-content-{{ $index }}" class="hidden mt-4">
                        <!-- Lampiran Section -->
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold">Lampiran</h3>
                            <p class="text-sm text-gray-500">Ingin tambah lampiran? <a
                                    href={{ route('student.assignment.edit', ['assignment_id' => $submission->assignment_id, 'submission_id' => $submission->id]) }}
                                    class="text-blue-600">tambah lampiran</a>
                            </p>
                            @foreach ($submission->submission_attachments as $index => $resource)
                                <div class="flex items-center mt-2 space-x-2" id="resource-{{ $resource->id }}"
                                    data-id="{{ $resource->id }}">
                                    @php
                                        $linkUrl = str_replace('storage/public/', '', $resource->file_url);
                                    @endphp
                                    <a href="{{ Storage::url($linkUrl) }}"
                                        class="bg-gray-100 p-2 rounded-md text-blue-600">{{ $resource->file_name }}.{{ $resource->file_extension }}</a>

                                    <button type="button" class="text-red-500"
                                        onclick="openModal('deleteResourceModal{{ $index }}')">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                {{-- Delete Modal --}}

                                <div id="deleteResourceModal{{ $index }}"
                                    class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title"
                                    role="dialog" aria-modal="true">
                                    <div
                                        class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                        <!-- Background overlay -->
                                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                                            aria-hidden="true">
                                        </div>

                                        <!-- Modal panel -->
                                        <div
                                            class="relative inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                                            <div class="sm:flex sm:items-start">
                                                <div
                                                    class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                                    <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                        aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </div>
                                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                                    <h3 class="text-lg leading-6 font-medium text-gray-900"
                                                        id="modal-title">
                                                        Konfirmasi Hapus
                                                        Lampiran</h3>
                                                    <div class="mt-2">
                                                        <p class="text-sm text-gray-500">Apakah Anda yakin ingin menghapus
                                                            lampiran
                                                            ini?</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                                <form
                                                    action="{{ route('student.assignmentresource.delete', ['id' => $resource->id]) }}"
                                                    method="POST" class="sm:ml-3 sm:w-auto">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:text-sm">
                                                        Hapus
                                                    </button>
                                                </form>
                                                <button type="button"
                                                    onclick="closeModal('deleteResourceModal{{ $index }}')"
                                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                                                    Batal
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            <input type="hidden" name="deleted_resources" id="deleted_resources" value="">
                            <input type="hidden" name="existing_resources" id="existing_resources" value="">
                        </div>

                        <!-- Deskripsi Section -->
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold">Deskripsi</h3>
                            <p class="text-sm text-gray-500">Ingin ubah catatan? <a
                                    href={{ route('student.assignment.edit', ['assignment_id' => $submission->assignment_id, 'submission_id' => $submission->id]) }}
                                    class="text-blue-600">ubah
                                    catatan</a></p>
                            <textarea class="w-full border rounded-md p-2 mt-2 text-sm" id="submission_content_edit_{{ $index }}"
                                name="submission_content" readonly>
                                {!! $submission->submission_content !!}
                            </textarea>
                        </div>

                        <!-- Catatan Section -->
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold">Catatan untuk guru</h3>
                            <p class="text-sm text-gray-500">Ingin ubah catatan? <a
                                    href={{ route('student.assignment.edit', ['assignment_id' => $submission->assignment_id, 'submission_id' => $submission->id]) }}
                                    class="text-blue-600">ubah
                                    catatan</a></p>
                            <textarea class="w-full border rounded-md p-2 mt-2 text-sm" id="submission_note_edit_{{ $index }}"
                                name="submission_note" readonly>{{ $submission->submission_note ? $submission->submission_note : '-' }}</textarea>
                        </div>

                        <!-- Nilai Tugas Section -->
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold">Nilai Tugas</h3>

                            @if ($submission->grades[0]->knowledge == null && $submission->grades[0]->skills == null)
                                <p class="text-sm text-gray-500">- Tidak Ada -</p>
                            @else
                                <div>
                                    <p class="text-sm text-gray-500"> Pengetahuan: {{ $submission->grades[0]->knowledge }}
                                    </p>
                                    <p class="text-sm text-gray-500"> Keterampilan: {{ $submission->grades[0]->skills }}
                                    </p>
                                </div>
                            @endif

                        </div>

                        <!-- Catatan Dari Guru Section -->
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold">Catatan Dari Guru</h3>
                            <textarea class="w-full border rounded-md p-2 mt-2 text-sm" readonly>{!! $submission->feedback ? $submission->feedback : '-' !!}</textarea>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>


        <!-- Task Attach -->
        <div id="assignment-attach" class="tab-content hidden bg-white shadow-md rounded-lg p-6">
            @foreach ($assignment->assignment_attachments as $index => $resource)
                @php
                    $linkUrl = str_replace('storage/public/', '', $resource->file_url);
                @endphp
                @if ($resource->file_type == 'video')
                    <div id="resources-container-video-{{ $index }}" data-id="{{ $resource->id }}"
                        class="w-full bg-gray-200 rounded-lg shadow-md overflow-hidden mb-4">
                        <video controls class="w-full">
                            <source src="{{ Storage::url($linkUrl) }}">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                @endif

                @if ($resource->file_type == 'image')
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

                @if ($resource->file_type == 'document')
                    <!-- PDF File -->
                    <div id="resources-container-pdf-{{ $index }}" data-id="{{ $resource->id }}"
                        class="flex items-center
                justify-between bg-white rounded-lg shadow-md p-4 mb-4">
                        <div class="flex items-center space-x-2">
                            <img src="{{ asset('assets/static/icon/document-icon.svg') }}" alt="document"
                                class="h-6 w-6">
                            <div>
                                <p class="text-blue-900 font-semibold">
                                    {{ $resource->file_name }}.{{ $resource->file_extension }}</p>
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

                @if ($resource->file_type === 'audio')
                    <!-- MP3 File -->
                    <div id="resources-container-mp3-{{ $index }}" data-id="{{ $resource->id }}"
                        class="flex
                items-center justify-between bg-white rounded-lg shadow-md p-4 mb-4">
                        <div class="flex items-center space-x-2">
                            <a href="path-to-mp3" class="text-blue-900">
                                <img src="{{ asset('assets/static/icon/audio-icon.svg') }}" alt="audio"
                                    class="h-6 w-6">
                            </a>
                            <div>
                                <p class="text-blue-900 font-semibold">
                                    {{ $resource->file_name }}.{{ $resource->file_extension }}</p>
                                <p class="text-gray-500 text-sm">{{ $course->courses_title }}</p>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <a href="{{ Storage::url($linkUrl) }}" class="text-blue-900">
                                <img src="{{ asset('assets/static/icon/play-icon.svg') }}" alt="audio"
                                    class="h-6 w-6">
                            </a>

                            <a href="{{ Storage::url($linkUrl) }}" download class="text-blue-900">
                                <img src="{{ asset('assets/static/icon/download-icon.svg') }}" alt="download"
                                    class="h-6 w-6">
                            </a>
                        </div>
                    </div>
                @endif

                @if ($resource->file_type == 'archive')
                    <!-- ZIP File -->
                    <div id="resources-container-zip-{{ $index }}" data-id="{{ $resource->id }}"
                        class="flex
            items-center justify-between bg-white rounded-lg shadow-md p-4 mb-4">
                        <div class="flex items-center space-x-2">
                            <img src="{{ asset('assets/static/icon/document-icon.svg') }}" alt="document"
                                class="h-6 w-6">
                            <div>
                                <p class="text-blue-900 font-semibold">
                                    {{ $resource->file_name }}.{{ $resource->file_extension }}</p>
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
                @if ($resource->file_type == 'url')
                    <!-- Link/Tautan -->
                    <a href="{{ $resource->file_url }}" id="resources-container-link-{{ $index }}"
                        data-id="{{ $resource->id }}"
                        class="flex
                items-center justify-between bg-white rounded-lg shadow-md p-4 mb-4">
                        <div class="flex items-center space-x-2">
                            <img src="{{ asset('assets/static/icon/link-icon.svg') }}" alt="Icon"
                                class="h-6 w-6 text-blue-900">
                            <p class="text-blue-900 font-semibold">{{ $resource->file_name }}</p>
                        </div>

                    </a>
                @endif

                @if ($resource->file_type == 'youtube')
                    {{-- <iframe width="560" height="315" src="{{ $resource->file_url }}"
                    title="YouTube video player" frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe> --}}
                    <a href="{{ $resource->file_url }}" div id="resources-container-yt-{{ $index }}"
                        data-id="{{ $resource->id }}"
                        class="flex
                    items-center justify-between bg-white rounded-lg shadow-md p-4 mb-4">
                        <div class="flex items-center space-x-2">
                            <img src="{{ asset('assets/static/icon/link-icon.svg') }}" alt="Icon" />
                            <p class="text-blue-900 h-6 w-6">{{ $resource->file_name }}</p>
                        </div>

                    </a>
                @endif
            @endforeach
        </div>

        <script src="https://cdn.ckeditor.com/ckeditor5/34.2.0/classic/ckeditor.js"></script>
        <script>
            const customTheme = @json($customTheme);
            document.addEventListener("DOMContentLoaded", function() {
                ClassicEditor.create(document.querySelector("#submission_content")).catch((error) => {
                    console.error(error)
                });
                // document.querySelectorAll("textarea[id^='submission_content_edit_']").forEach((textarea) => {
                //     ClassicEditor.create(textarea)
                //         .catch((error) => {
                //             console.error(error);
                //         });
                // });
                // document.querySelectorAll("textarea[id^='submission_note_edit_']").forEach((textarea) => {
                //     ClassicEditor.create(textarea)
                //         .catch((error) => {
                //             console.error(error);
                //         });
                // });

                const dropdownButtons = document.querySelectorAll('.dropdown-btn');
                dropdownButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const submissionId = this.getAttribute('data-id');
                        toggleDropdown(submissionId);
                    });
                });

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
                        cardElement.style.color = customTheme['primary_color'];

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

                    const txtBlue = document.querySelectorAll('.text-primary');
                    const txtGray = document.querySelectorAll('.text-gray-500');

                    txtGray.forEach(function (element) {
                        element.style.color = customTheme['secondary_color'];
                    })

                    txtBlue.forEach(function (element) {
                        element.style.color = customTheme['primary_color'];
                    })
                }
            });
            const MAX_FILE_LIMIT = {{ $assignment->max_attach }};
            let resourceIndex = 1;


            function addLampiran() {
                // Get current resource count
                const resourceContainer = document.querySelector('#resources-container');
                const resourceContainerCount = document.querySelectorAll('#resources-container > .resource').length;
                // Check if current count exceeds limit
                if (resourceContainerCount >= MAX_FILE_LIMIT) {
                    // Show warning message
                    document.getElementById('add-lampiran').classList.add('hidden');
                    document.getElementById('warning-message').classList.remove('hidden');
                    return;
                }
                // Template for new lampiran element
                const lampiranTemplate = `
                <div class="resource flex items-center space-x-4 mb-4" data-index="${resourceIndex}">
                    <!-- Dropdown Jenis Lampiran -->
                    <div class="w-1/2">
                        <select onchange="changeInputType(${resourceIndex}, this)" name="resources[${resourceIndex}][file_type]" class="w-full p-2 border rounded-md focus:outline-none focus:ring focus:border-blue-300">
                            <option value="audio">Audio</option>
                            <option value="video">Video</option>
                            <option value="archive">Arsip</option>
                            <option value="image">Gambar</option>
                            <option value="document">Dokumen</option>
                            <option value="url">URL</option>
                            <option value="youtube">Youtube</option>
                        </select>
                    </div>
                    <!-- Input File or Text -->
                    <div class="flex-grow">
                        <input id="resource_input_${resourceIndex}" type="file" name="resources[${resourceIndex}][file_url]" class="w-full p-2 border rounded-md focus:outline-none focus:ring focus:border-blue-300" />
                            <input type="hidden" name="resources[${resourceIndex}][file_name]" class="form-control" value="Resource Image">

                        <small id="file_info_${resourceIndex}" class="text-gray-500">Ekstensi yang diterima: .mp3, .mpeg, .aac, .wav</small>
                    </div>
                    <!-- Button Remove -->
                    <button type="button" class="text-red-600" onclick="removeLampiran(this)">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                `;

                resourceContainer.insertAdjacentHTML('beforeend', lampiranTemplate);
                resourceIndex++;

                // Hide warning message if it's visible
                document.getElementById('warning-message').classList.add('hidden');
            }

            function removeLampiran(button) {
                // Remove the specific lampiran row
                button.parentElement.remove();


                // Check current number of lampiran and hide warning if under limit
                const currentLampiranCount = document.querySelectorAll('#resources-container > .resource').length;
                if (currentLampiranCount <= MAX_FILE_LIMIT) {
                    document.getElementById('add-lampiran').classList.remove('hidden');
                    document.getElementById('warning-message').classList.add('hidden');
                }
            }

            window.changeInputType = function(index, select) {
                const fileType = select.value;

                const fileInput = document.querySelector(`#resource_input_${index}`);
                const fileInfo = document.querySelector(`#file_info_${index}`);

                if (fileType === 'url') {
                    fileInput.setAttribute('type', 'url');
                    fileInfo.textContent = 'URL harus valid, contoh: https://www.google.com';
                } else if (fileType === 'youtube') {
                    fileInput.setAttribute('type', 'url');
                    fileInfo.textContent = 'Masukkan URL YouTube';
                } else {
                    fileInput.setAttribute('type', 'file');
                    fileInfo.textContent =
                        'URL Youtube Harus Valid, contoh https://www.youtube.com/watch?v=abc123';

                    switch (fileType) {
                        case 'audio':
                            fileInput.setAttribute('accept', 'audio/mp3,audio/mpeg,audio/aac,audio/wav');
                            fileInfo.textContent = 'File yang diterima: .mp3, .wav, .mpeg';
                            break;
                        case 'video':
                            fileInput.setAttribute('accept', 'video/mp4,video/mkv,video/mpeg');
                            fileInfo.textContent = 'File yang diterima: .mp4, .mkv, .mpeg';
                            break;
                        case 'image':
                            fileInput.setAttribute('accept', 'image/png,image/jpg,image/jpeg');
                            fileInfo.textContent = 'File yang diterima: .jpg, .jpeg, .png';
                            break;
                        case 'archive':
                            fileInput.setAttribute('accept',
                                'application/zip,application/rar,application/x-zip-compressed,application/x-rar-compressed'
                            );
                            fileInfo.textContent = 'File yang diterima: .zip, .rar';
                            break;
                        case 'document':
                            fileInput.setAttribute('accept', 'application/pdf,application/msword');
                            fileInfo.textContent = 'File yang diterima: .pdf, .doc, .docx';
                            break;
                        default:
                            fileInput.removeAttribute('accept');
                            fileInfo.textContent = '';
                            break;
                    }
                }
            }

            window.removeExistingResource = function(index) {
                const row = document.querySelector(`#resource-row-${index}`);
                if (row) {
                    const resourceId = row.getAttribute('data-id');
                    deletedResources.push(resourceId);
                    updateDeletedResourcesInput();
                    row.remove();
                }
            }

            function updateDeletedResourcesInput() {
                document.getElementById('deleted_resources').value = JSON.stringify(deletedResources);
            }

            window.removeResource = function(index) {
                document.querySelector(`.resource[data-index="${index}"]`).remove();
            }


            function toggleDropdown(index) {
                // Gunakan ID unik untuk setiap submission
                const dropdownContent = document.getElementById(`dropdown-content-${index}`);

                const arrowIcon = document.getElementById(`arrow-icon-${index}`);

                // Toggle visibility of dropdown content
                dropdownContent.classList.toggle('hidden');

                // Rotate arrow icon
                if (dropdownContent.classList.contains('hidden')) {
                    arrowIcon.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
                `;
                } else {
                    arrowIcon.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 15l-7-7-7 7" />
                </svg>
                `;
                }
            }

            function openModal(modalId) {
                document.getElementById(modalId).classList.remove('hidden');
            }

            function closeModal(modalId) {
                document.getElementById(modalId).classList.add('hidden');
            }
        </script>
        <script>
            function openTab(evt, tabName) {
                var i, tabcontent, tablinks;

                // Hide all tab content
                tabcontent = document.getElementsByClassName("tab-content");
                for (i = 0; i < tabcontent.length; i++) {
                    tabcontent[i].style.display = "none";
                }

                // Reset all tab button styles
                tablinks = document.getElementsByTagName("button");
                for (i = 0; i < tablinks.length; i++) {
                    tablinks[i].classList.remove("text-primary");
                    tablinks[i].classList.remove("font-semibold");
                    tablinks[i].classList.add("text-gray-500");
                }

                // Show the current tab, and set button to active state
                document.getElementById(tabName).style.display = "block";
                evt.currentTarget.classList.add("text-primary");
                evt.currentTarget.classList.add("font-semibold");
                evt.currentTarget.classList.remove("text-gray-500");


                if (customTheme !== null) {
                    const txtBlue = document.querySelectorAll('.text-primary');
                    const txtGray = document.querySelectorAll('.text-gray-500');
                    txtGray.forEach(function (element) {
                        element.style.color = customTheme['secondary_color'];
                    })

                    txtBlue.forEach(function (element) {
                        element.style.color = customTheme['primary_color'];
                    })
                }
            }

            // Set default tab open (Detail Tugas)
            document.getElementById("assignment-detail").style.display = "block";
        </script>
    @endsection
