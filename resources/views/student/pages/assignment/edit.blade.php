@extends('student.layout.app')

@section('title', 'Dashboard')
@section('nav_title', 'Dashboard')


@section('content')

    <div class="w-full max-w-lg">
        <div class="flex items-center mb-4">
            <button class="rounded-full bg-blue-900 p-2 text-white" onclick="history.back()">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 12H5M12 19l-7-7 7-7" />
                </svg>
            </button>
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

        <!-- Task Submission -->
        <div id="assignment-history-submit" class=" bg-white shadow-md rounded-lg p-6">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-center">Edit pengumpulan tugas</h2>

            </div>

            <form action="{{ route('student.submission.update', $submission->id) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <h4 class="text-xl font-bold text-[#001951] w-fit">Catatan Untuk Tugas anda
                    <sup class="text-destructive">
                        ({{ $assignment->collection_type === 'All' || $assignment->collection_type === 'Catatan' ? '*Wajib' : 'Opsional' }})
                    </sup>
                </h4>
                <input type="hidden" name="assignment_id" value="{{ $assignment->id }}">

                <textarea id="submission_content" name="submission_content">{{ $submission->submission_content }}</textarea>

                <h4 class="text-xl font-bold text-[#001951] w-fit">Catatan Untuk guru
                    <sup class="text-destructive">
                        (Opsional)
                    </sup>
                </h4>
                <textarea id="submission_note" name="submission_note">{{ $submission->submission_note }}</textarea>


                <div class="inline pt-4">
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
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
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
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H5a1 1 0 110-2h3V6a1 1 0 011-1z"
                            clip-rule="evenodd" />
                    </svg>
                    Tambahkan Lampiran
                </button>
                <div class="w-full flex justify-center">

                    @if ($assignment->due_date < now())
                        <p class="text-red-600 text-center mt-4">Sudah melewati batas akhir pengerjaan</p>
                    @elseif ($assignment->limit_submit < 1)
                        <p class="text-red-600 text-center mt-4">Sudah melebihi batas pengumpulan</p>
                    @else
                        <button type="submit" id="submit-assignment"
                            class="btn bg-[#0058a8] text-white font-bold p-2 rounded-md mt-4 mx-auto">Kirimkan
                            Tugas</button>
                    @endif
                </div>
            </form>
        </div>


        <script src="https://cdn.ckeditor.com/ckeditor5/34.2.0/classic/ckeditor.js"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                ClassicEditor.create(document.querySelector("#submission_content")).catch((error) => {
                    console.error(error)
                });
                ClassicEditor.create(document.querySelector("#submission_note")).catch((error) => {
                    console.error(error)
                });

                const dropdownButtons = document.querySelectorAll('.dropdown-btn');
                dropdownButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const submissionId = this.getAttribute('data-id');
                        toggleDropdown(submissionId);
                    });
                });
            });
            const MAX_FILE_LIMIT = {{ $assignment->max_attach }};
            let submission_attach = {{ count($submission->submission_attachments) }}

            let resourceIndex = 1;

            function addLampiran() {
                // Get current resource count
                const resourceContainer = document.querySelector('#resources-container');
                const resourceContainerCount = document.querySelectorAll('#resources-container > .resource').length;
                // Check if current count exceeds limit
                if (resourceContainerCount >= MAX_FILE_LIMIT || resourceContainerCount >= MAX_FILE_LIMIT - submission_attach) {

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

    @endsection
