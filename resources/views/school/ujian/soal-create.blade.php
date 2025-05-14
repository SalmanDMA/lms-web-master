<x-app-layout>
    <x-slot:title>
        {{ $title }}
    </x-slot>
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Soal Ujian</h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-lg-end float-start">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/staff-curriculum/soal-ujian">Soal Ujian</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">{{ $title }}</h4>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="m-0 p-0">
                            @foreach ($errors->all() as $error)
                                <li style="list-style: none">{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="/staff-curriculum/question" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="school_exam_id" class="form-label">
                                    Ujian <span class="text-danger">*</span>
                                </label>
                                <select class="choices form-select" name="school_exam_id" id="school_exam_id"
                                    value="{{ old('school_exam_id') }}" required>
                                    <option value="" selected disabled>Pilih Ujian</option>
                                    @foreach ($schoolExamData as $schoolExam)
                                        <option value="{{ $schoolExam->id }}">
                                            {{ $schoolExam->id }} - {{ $schoolExam->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="section_id" class="form-label">
                                    Bagian Ujian
                                </label>
                                <select class="choices form-select" name="section_id" id="section_id"
                                    value="{{ old('section_id') }}" required>
                                    <option value="" selected disabled>Pilih Bagian Ujian</option>
                                    @foreach ($examSectionData as $examSection)
                                        <option value="{{ $examSection->id }}">
                                            {{ $examSection->id }} - {{ $examSection->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="category_id" class="form-label">
                                    Kategori Soal <span class="text-danger">*</span>
                                </label>
                                <select class="choices form-select" name="category_id" id="category_id"
                                    value="{{ old('category_id') }}" required>
                                    <option value="" selected disabled>Pilih Kategori Soal</option>
                                    @foreach ($categoryQuestionData as $categoryQuestion)
                                        <option value="{{ $categoryQuestion->id }}">
                                            {{ $categoryQuestion->id }} - {{ $categoryQuestion->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="question_type" class="form-label">
                                    Jenis Soal <span class="text-danger">*</span>
                                </label>
                                <select class="choices form-select" name="question_type" id="question_type"
                                    value="{{ old('question_type') }}" required>
                                    <option value="" selected disabled>Pilih Jenis Soal</option>
                                    <option value="Pilihan Ganda">Pilihan Ganda</option>
                                    <option value="Pilihan Ganda Complex">Pilihan Ganda Complex</option>
                                    <option value="True False">True False</option>
                                    <option value="Essay">Essay</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="grade_method" class="form-label">
                                    Metode Penilaian <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" name="grade_method" id="grade_method"
                                    value="{{ old('grade_method') }}" required />
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="form-group">
                                <label for="difficult" class="form-label">
                                    Kesulitan <span class="text-danger">*</span>
                                </label>
                                <select class="choices form-select" name="difficult" id="difficult"
                                    value="{{ old('difficult') }}" required>
                                    <option value="" selected disabled>Pilih Kesulitan</option>
                                    <option value="Mudah">Mudah</option>
                                    <option value="Sedang">Sedang</option>
                                    <option value="Sulit">Sulit</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="form-group">
                                <label for="point" class="form-label">
                                    Total Poin <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control" name="point" id="point"
                                    value="{{ old('point') }}" required />
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="question_text" class="form-label">
                            Pertanyaan <span class="text-danger">*</span>
                        </label>
                        <textarea name="question_text" id="question_text">{{ old('question_text') }}</textarea>
                    </div>
                    <div id="choices"></div>
                    <div id="choices-inputs"></div>
                    <div class="form-group">
                        <label for="file-count" class="form-label">Jumlah Lampiran</label>
                        <select class="choices form-select" id="file-count" value="{{ old('file-count') }}">
                            <option value="">Tanpa Lampiran</option>
                            @for ($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div id="file-inputs"></div>
                    <button type="submit" class="btn btn-primary mt-3">Tambah</button>
                </form>
            </div>
        </div>
    </section>

    <script>
        ClassicEditor.create(document.querySelector("#question_text")).catch((error) => {
            console.error(error);
        });

        document.getElementById('question_type').addEventListener('change', function() {
            const selectedType = this.value;
            const choices = document.getElementById('choices');
            const choicesInputs = document.getElementById('choices-inputs');

            while (choices.firstChild) {
                choices.removeChild(choices.firstChild);
            }

            while (choicesInputs.firstChild) {
                choicesInputs.removeChild(choicesInputs.firstChild);
            }

            if (
                selectedType == 'Pilihan Ganda' ||
                selectedType == 'Pilihan Ganda Complex'
            ) {
                const newCount = document.createElement('div');
                newCount.classList.add('form-group');
                newCount.innerHTML =
                    `<label for="choice-count" class="form-label">
                        Jumlah Pilihan <span class="text-danger">*</span>
                    </label>
                    <select class="choices form-select" id="choice-count" required>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="6">6</option>
                        <option value="7">7</option>
                        <option value="8">8</option>
                        <option value="9">9</option>
                        <option value="10">10</option>
                    </select>`;

                choices.appendChild(newCount);

                createChoice('2', selectedType);
            } else if (selectedType == 'True False') {
                const newLabel = document.createElement('div');
                newLabel.innerHTML =
                    `<label class="form-label">
                        Jawaban <span class="text-danger">*</span>
                    </label>
                    <input type="hidden" name="choice_text[]" value="True" required>
                    <input type="hidden" name="choice_text[]" value="False" required>`;

                choices.appendChild(newLabel);

                const newTrueFalse = document.createElement('div');
                newTrueFalse.classList.add('form-group');
                newTrueFalse.innerHTML =
                    `<div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="is_true[]" value="true" id="true" required>
                        <label class="form-check-label" for="true">
                            Benar
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="is_true[]" value="true" id="false" required>
                        <label class="form-check-label" for="false">
                            Salah
                        </label>
                    </div>`;

                choices.appendChild(newTrueFalse);
            }
        });

        document.getElementById('choices').addEventListener('input', function(event) {
            const target = event.target;

            if (target.id === 'choice-count') {
                const selectedType = document.getElementById('question_type').value;
                createChoice(target.value, selectedType);
            }
        });

        document.getElementById('file-count').addEventListener('input', function() {
            const fileCount = parseInt(this.value);
            const fileInputs = document.getElementById('file-inputs');

            while (fileInputs.firstChild) {
                fileInputs.removeChild(fileInputs.firstChild);
            }

            for (let i = 0; i < fileCount; i++) {
                const newFileInput = document.createElement('div');
                newFileInput.classList.add('row');
                newFileInput.innerHTML =
                    `<div class="col-12 col-md-6">
                        <div class="form-group">
                            <label class="form-label">Types <span class="text-danger">*</span></label>
                            <select class="choices form-select" name="types[]" value="{{ old('types') }}" required>
                                <option value="" selected disabled>Pilih Tipe</option>
                                <option value="image">Image</option>
                                <option value="document">Document</option>
                                <option value="archive">Archive</option>
                                <option value="audio">Audio</option>
                                <option value="video">Video</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label class="form-label">File</label>
                            <input type="file" class="form-control" name="files[]" value="{{ old('files') }}" required />
                        </div>
                    </div>`;

                fileInputs.appendChild(newFileInput);
            }
        });

        function createChoice(count, type) {
            const choiceCount = parseInt(count);
            const choiceInputs = document.getElementById('choices-inputs');
            let selectedType = '';

            while (choiceInputs.firstChild) {
                choiceInputs.removeChild(choiceInputs.firstChild);
            }

            if (type == 'Pilihan Ganda') {
                selectedType = 'radio';
            } else if (type == 'Pilihan Ganda Complex') {
                selectedType = 'checkbox';
            } else {
                return false;
            }

            for (let i = 0; i < choiceCount; i++) {
                const newChoiceInput = document.createElement('div');
                newChoiceInput.classList.add('row');
                newChoiceInput.classList.add('align-items-center');
                newChoiceInput.innerHTML =
                    `<div class="col-12 col-sm-10">
                        <div class="form-group">
                            <label for="choice_text" class="form-label">Pilihan <span class="text-danger">*</span></label>
                            <textarea name="choice_text[]" value="{{ old('choice_text') }}" id="choice_text-${i}"></textarea>
                        </div>
                    </div>
                    <div class="col-12 col-sm-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="${selectedType}" name="is_true[]" value="true" required>
                            <label class="form-check-label">
                                <i class="bi bi-check2"></i>
                            </label>
                        </div>
                    </div>`;

                choiceInputs.appendChild(newChoiceInput);

                ClassicEditor.create(document.querySelector(`#choice_text-${i}`)).catch((error) => {
                    console.error(error);
                });
            }
        }
    </script>
</x-app-layout>
