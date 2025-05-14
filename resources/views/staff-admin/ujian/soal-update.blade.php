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
                            <li class="breadcrumb-item"><a href="/staff-administrator/ujian/soal">Soal Ujian</a></li>
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

                <form action="/staff-administrator/question" method="POST" enctype="multipart/form-data">
                    @method('PUT')
                    @csrf
                    <input type="hidden" name="id" id="id" value="{{ $question->id }}" required />

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
                                    <option value="{{ $schoolExam->id }}" {{ $schoolExam->id == $question->school_exam_id ? 'selected' : '' }}>
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
                                        <option value="{{ $examSection->id }}" {{ $examSection->id === $question->section_id ? 'selected' : '' }}>
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
                                        @if ($question->category_id == $categoryQuestion->id)
                                            <option value="{{ $categoryQuestion->id }}" selected>
                                                {{ $categoryQuestion->id }} - {{ $categoryQuestion->name }}
                                            </option>
                                        @else
                                            <option value="{{ $categoryQuestion->id }}">
                                                {{ $categoryQuestion->id }} - {{ $categoryQuestion->name }}
                                            </option>
                                        @endif
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
                                    @foreach ($questionTypeData as $questionType)
                                        @if ($question->question_type == $questionType)
                                            <option value="{{ $questionType }}" selected>{{ $questionType }}</option>
                                        @else
                                            <option value="{{ $questionType }}">{{ $questionType }}</option>
                                        @endif
                                    @endforeach
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
                                    value="{{ $question->grade_method }}" required />
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
                                    @foreach ($difficultyData as $difficult)
                                        @if ($question->difficult == $difficult)
                                            <option value="{{ $difficult }}" selected>{{ $difficult }}</option>
                                        @else
                                            <option value="{{ $difficult }}">{{ $difficult }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="form-group">
                                <label for="point" class="form-label">
                                    Total Poin <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control" name="point" id="point"
                                    value="{{ $question->point }}" required />
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="question_text" class="form-label">
                            Pertanyaan <span class="text-danger">*</span>
                        </label>
                        <textarea name="question_text" id="question_text">{{ $question->question_text }}</textarea>
                    </div>
                    <div id="choices-inputs">
                        <div class="row align-items-center">
                            @foreach ($question->choices as $questionChoice)
                                @if ($question->question_type == 'Pilihan Ganda')
                                    <div class="col-12 col-sm-10">
                                        <div class="form-group">
                                            <label for="choice_text" class="form-label">
                                                Pilihan {{ $loop->iteration }} <span class="text-danger">*</span>
                                            </label>
                                            <textarea name="choice_text[]" id="choice_text-{{ $questionChoice->id }}">
                                                {{ $questionChoice->choice_text }}
                                            </textarea>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-2">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="is_true[]"
                                                value="true" {{ $questionChoice->is_true ? 'checked' : '' }}
                                                required>
                                            <label class="form-check-label">
                                                <i class="bi bi-check2"></i>
                                            </label>
                                        </div>
                                    </div>


                                    <script>
                                        ClassicEditor.create(document.querySelector(`#choice_text-{{ $questionChoice->id }}`)).catch((error) => {
                                            console.error(error);
                                        });
                                    </script>
                                @elseif ($question->question_type == 'Pilihan Ganda Complex')
                                    <div class="col-12 col-sm-10">
                                        <div class="form-group">
                                            <label for="choice_text" class="form-label">
                                                Pilihan {{ $loop->iteration }} <span class="text-danger">*</span>
                                            </label>
                                            <textarea name="choice_text[]" id="choice_text-{{ $questionChoice->id }}">
                                            {{ $questionChoice->choice_text }}
                                        </textarea>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-2">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="is_true[]"
                                                value="true" {{ $questionChoice->is_true ? 'checked' : '' }}>
                                            <label class="form-check-label">
                                                <i class="bi bi-check2"></i>
                                            </label>
                                        </div>
                                    </div>


                                    <script>
                                        ClassicEditor.create(document.querySelector(`#choice_text-{{ $questionChoice->id }}`)).catch((error) => {
                                            console.error(error);
                                        });
                                    </script>
                                @elseif ($question->question_type == 'True False')
                                    @if ($loop->first)
                                        <label class="form-label">
                                            Jawaban <span class="text-danger">*</span>
                                        </label>
                                        <input type="hidden" name="choice_text[]" value="True" required>
                                        <input type="hidden" name="choice_text[]" value="False" required>
                                    @endif

                                    @if ($loop->first)
                                        <div class="form-group">
                                    @endif

                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="is_true[]"
                                            value="true" id="{{ strtolower($questionChoice->choice_text) }}"
                                            {{ $questionChoice->is_true ? 'checked' : '' }}>
                                        <label class="form-check-label" for="{{ strtolower($questionChoice->choice_text) }}">
                                            {{ $questionChoice->choice_text === 'True' ? 'Benar' : 'Salah' }}
                                        </label>
                                    </div>

                                    @if ($loop->last)
                                        </div>
                                    @endif
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Ubah</button>
                </form>
            </div>
        </div>
    </section>

    <script>
        ClassicEditor.create(document.querySelector("#question_text")).catch((error) => {
            console.error(error);
        });
    </script>
</x-app-layout>
