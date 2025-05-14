<x-app-layout>
    <x-slot:title>
        {{ $title }}
        </x-slot>
        <div class="page-heading">
            <div class="page-title">
                <div class="row">
                    <div class="col-12 col-md-6 order-md-1 order-last">
                        <h3>Form Ujian</h3>
                    </div>
                    <div class="col-12 col-md-6 order-md-2 order-first">
                        <nav aria-label="breadcrumb" class="breadcrumb-header float-lg-end float-start">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/exam-list">Daftar Ujian</a></li>
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
            </div>

            @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="/staff-curriculum/school-exam" method="POST">
                @csrf

                {{-- General --}}

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Umum</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Judul Ujian<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="title" id="title"
                                        value="{{ old('title') }}" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="type">Tipe<span class="text-danger">*</span></label>
                                    <select class="form-control" name="type" id="type" required>
                                        <option value="">Pilih Tipe Ujian</option>
                                        @foreach ($examTypes as $type)
                                        <option value="{{ $type }}" {{ old('type')==$type ? 'selected' : '' }}>
                                            {{ $type }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="description">Deskripsi<span class="text-danger">*</span></label>
                                    <textarea class="form-control ckeditor" name="description"
                                        id="description">{{ old('description') }}</textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="instruction">Instruksi<span class="text-danger">*</span></label>
                                    <textarea class="form-control ckeditor" name="instruction"
                                        id="instruction">{{ old('instruction') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Akademik --}}

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Akademik</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="course">Mata Pelajaran<span class="text-danger">*</span></label>
                                    <select class="form-control" name="course" id="course" required>
                                        <option value="">Pilih Mata Pelajaran</option>
                                        @foreach ($courses as $course)
                                        <option value="{{ $course->id }}" {{ old('course')==$course->id ?
                                            'selected' : '' }}>
                                            {{ $course->courses_title }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="class_level">Tingkat Kelas<span class="text-danger">*</span></label>
                                    <select class="form-control" name="class_level" id="class_level" required>
                                        <option value="">Pilih Tingkat Kelas</option>
                                        @foreach ($classes as $class)
                                        <option value="{{ $class->id }}" {{ old('class')==$class->id ? 'selected' :
                                            '' }}>
                                            {{ $class->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="academic_year">Tahun Akademik<span class="text-danger">*</span></label>
                                    <select class="form-control" name="academic_year" id="academic_year" required>
                                        <option value="">Pilih Tahun Akademik</option>
                                        @foreach ($academicYears as $academicYear)
                                        <option value="{{ $academicYear->year }}" {{
                                            old('academicYear')==$academicYear->year ?
                                            'selected' : '' }}>
                                            {{ $academicYear->year }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="semester">Semester<span class="text-danger">*</span></label>
                                    <select class="form-control" name="semester" id="semester" required>
                                        <option value="">Pilih Semester</option>
                                        @foreach ($semester as $item)
                                        <option value="{{ $item }}" {{ old('semester') == $item ? 'selected' : '' }}>
                                            {{ $item }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pengaturan --}}

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Pengaturan</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_time">Waktu Mulai</label>
                                    <input type="datetime-local" class="form-control" name="start_time" id="start_time"
                                        value="{{ old('start_time') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_time">Waktu Selesai</label>
                                    <input type="datetime-local" class="form-control" name="end_time" id="end_time"
                                        value="{{ old('end_time') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="duration">Durasi (menit)<span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" name="duration" id="duration"
                                        value="{{ old('duration') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="repeat_chance">Kesempatan Ulang<span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="repeat_chance" id="repeat_chance"
                                        value="{{ old('repeat_chance') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="device">Perangkat yang Diperbolehkan<span class="text-danger">*</span></label>
                            <select class="form-select" name="device" id="device" required>
                                <option value="" {{ old('device')=='' ? 'selected' : '' }}>Pilih Perangkat</option>
                                <option value="Web" {{ old('device')=='Web' ? 'selected' : '' }}>Web</option>
                                <option value="Mobile" {{ old('device')=='Mobile' ? 'selected' : '' }}>Mobile</option>
                                <option value="All" {{ old('device')=='All' ? 'selected' : '' }}>Semua</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="maximum_user">Maksimum Pengguna<span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="maximum_user" id="maximum_user"
                                        value="{{ old('maximum_user') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="token">Token<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="token" id="token"
                                        value="{{ old('token') }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="publication_status">Status Publikasi<span class="text-danger">*</span></label>
                            <select class="form-select" name="publication_status" id="publication_status">
                                <option value="published" {{ old('publication_status')=='published' ? 'selected' : ''
                                    }}>Dipublikasikan</option>
                                <option value="unpublished" {{ old('publication_status')=='unpublished' ? 'selected'
                                    : '' }}>Belum Dipublikasikan</option>
                            </select>
                        </div>

                        <div class="row pt-3 pb-2">
                            <div class="col-3 form-group">
                                <div class="form-check">
                                    <input type="hidden" name="is_random_question" value="0">
                                    <input class="form-check-input" type="checkbox" name="is_random_question"
                                        id="is_random_question" value="1" {{ old('is_random_question')=='1' ? 'checked'
                                        : '' }}>
                                    <label class="form-check-label" for="is_random_question">Acak Soal</label>
                                </div>
                            </div>

                            <div class="col-3 form-group">
                                <div class="form-check">
                                    <input type="hidden" name="is_random_answer" value="0">
                                    <input class="form-check-input" type="checkbox" name="is_random_answer"
                                        id="is_random_answer" value="1" {{ old('is_random_answer')=='1' ? 'checked' : ''
                                        }}>
                                    <label class="form-check-label" for="is_random_answer">Acak Jawaban</label>
                                </div>
                            </div>

                            <div class="col-3 form-group">
                                <div class="form-check">
                                    <input type="hidden" name="is_show_score" value="0">
                                    <input class="form-check-input" type="checkbox" name="is_show_score"
                                        id="is_show_score" value="1" {{ old('is_show_score')=='1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_show_score">Tampilkan Skor</label>
                                </div>
                            </div>

                            <div class="col-3 form-group">
                                <div class="form-check">
                                    <input type="hidden" name="is_show_result" value="0">
                                    <input class="form-check-input" type="checkbox" name="is_show_result"
                                        id="is_show_result" value="1" {{ old('is_show_result')=='1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_show_result">Tampilkan Hasil</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-select" name="status" id="status">
                                <option value="active" {{ old('status')=='active' ? 'selected' : '' }}>Aktif</option>
                                <option value="inactive" {{ old('status')=='inactive' ? 'selected' : '' }}>Tidak Aktif
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mb-5">
                    <button type="submit" class="btn btn-primary mt-3">Simpan</button>
                </div>
            </form>
        </section>
        <script>
            const initializeCKEditors = () => {
                $('[id^="choices"][id$="[choice_text]"]:not(.ckeditor-initialized)').each(function() {
                ClassicEditor.create(this).then(editor => {
                $(this).addClass('ckeditor-initialized');
                }).catch(console.error);
                });
            };

            initializeCKEditors();

            ClassicEditor.create($('#description')[0]).catch(console.error);
            ClassicEditor.create($('#instruction')[0]).catch(console.error);
        </script>
</x-app-layout>