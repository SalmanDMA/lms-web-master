<x-app-layout>
    <x-slot:title>
        Edit Soal Ujian
    </x-slot>
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Edit Soal Ujian</h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-lg-end float-start">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/staff-administrator/soal-ujian">Soal Ujian</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Edit Soal Ujian</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Edit Soal Ujian</h4>
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

                <form action="/staff-administrator/school-exam" method="POST">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="id" value="{{ $exam->id }}">

                    <div class="form-group">
                        <label for="title" class="form-label">Judul</label>
                        <input type="text" class="form-control" name="title" id="title" value="{{ old('title', $exam->title) }}">
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="description" id="description">{{ old('description', $exam->description) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="type" class="form-label">Tipe</label>
                        <input type="text" class="form-control" name="type" id="type" value="{{ old('type', $exam->type) }}">
                    </div>

                    <div class="form-group">
                        <label for="instruction" class="form-label">Instruksi</label>
                        <textarea class="form-control" name="instruction" id="instruction">{{ old('instruction', $exam->instruction) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" name="status" id="status">
                            <option value="active" {{ old('status', $exam->status) == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ old('status', $exam->status) == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="start_time" class="form-label">Waktu Mulai</label>
                        <input type="date" class="form-control" name="start_time" id="start_time" value="{{ old('start_time', date('Y-m-d', strtotime($exam->examSetting->start_time))) }}">
                    </div>

                    <div class="form-group">
                        <label for="end_time" class="form-label">Waktu Berakhir</label>
                        <input type="date" class="form-control" name="end_time" id="end_time" value="{{ old('end_time', date('Y-m-d', strtotime($exam->examSetting->start_time))) }}">
                    </div>

                    <div class="form-group">
                        <label for="duration" class="form-label">Durasi</label>
                        <input type="text" class="form-control" name="duration" id="duration" value="{{ old('duration', date('H:i', strtotime($exam->examSetting->duration))) }}">
                    </div>

                    <div class="form-group">
                        <label for="repeat_chance" class="form-label">Kesempatan Mengulang</label>
                        <input type="number" class="form-control" name="repeat_chance" id="repeat_chance" value="{{ old('repeat_chance', $exam->examSetting->repeat_chance) }}">
                    </div>

                    <div class="form-group">
                        <label for="device" class="form-label">Perangkat</label>
                        <input type="text" class="form-control" name="device" id="device" value="{{ old('device', $exam->examSetting->device) }}">
                    </div>

                    <div class="form-group">
                        <label for="maximum_user" class="form-label">Jumlah Maksimal Pengguna</label>
                        <input type="number" class="form-control" name="maximum_user" id="maximum_user" value="{{ old('maximum_user', $exam->examSetting->maximum_user) }}">
                    </div>

                    <div class="form-group">
                        <label for="is_random_question" class="form-label">Acak Soal</label>
                        <select class="form-select" name="is_random_question" id="is_random_question">
                            <option value="1" {{ old('is_random_question', $exam->examSetting->is_random_question) == 1 ? 'selected' : '' }}>Ya</option>
                            <option value="0" {{ old('is_random_question', $exam->examSetting->is_random_question) == 0 ? 'selected' : '' }}>Tidak</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="is_random_answer" class="form-label">Acak Jawaban</label>
                        <select class="form-select" name="is_random_answer" id="is_random_answer">
                            <option value="1" {{ old('is_random_answer', $exam->examSetting->is_random_answer) == 1 ? 'selected' : '' }}>Ya</option>
                            <option value="0" {{ old('is_random_answer', $exam->examSetting->is_random_answer) == 0 ? 'selected' : '' }}>Tidak</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="is_show_score" class="form-label">Tampilkan Skor</label>
                        <select class="form-select" name="is_show_score" id="is_show_score">
                            <option value="1" {{ old('is_show_score', $exam->examSetting->is_show_score) == 1 ? 'selected' : '' }}>Ya</option>
                            <option value="0" {{ old('is_show_score', $exam->examSetting->is_show_score) == 0 ? 'selected' : '' }}>Tidak</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="is_show_result" class="form-label">Tampilkan Hasil</label>
                        <select class="form-select" name="is_show_result" id="is_show_result">
                            <option value="1" {{ old('is_show_result', $exam->examSetting->is_show_result) == 1 ? 'selected' : '' }}>Ya</option>
                            <option value="0" {{ old('is_show_result', $exam->examSetting->is_show_result) == 0 ? 'selected' : '' }}>Tidak</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="course" class="form-label">Mata Pelajaran</label>
                        <input type="text" class="form-control" name="course" id="course" value="{{ old('course', $exam->course) }}">
                    </div>

                    <div class="form-group">
                        <label for="publication_status" class="form-label">Status Publikasi</label>
                        <select class="form-select" name="publication_status" id="publication_status">
                            <option value="published" {{ old('publication_status', $exam->publication_status) == 'published' ? 'selected' : '' }}>Dipublikasikan</option>
                            <option value="draft" {{ old('publication_status', $exam->publication_status) == 'draft' ? 'selected' : '' }}>Draft</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="class_level" class="form-label">Kelas</label>
                        <input type="number" class="form-control" name="class_level" id="class_level" value="{{ old('class_level', $exam->class_level) }}">
                    </div>

                    <div class="form-group">
                        <label for="academic_year" class="form-label">Tahun Akademik</label>
                        <input type="text" class="form-control" name="academic_year" id="academic_year" value="{{ old('academic_year', $exam->academic_year) }}">
                    </div>

                    <div class="form-group">
                        <label for="semester" class="form-label">Semester</label>
                        <input type="number" class="form-control" name="semester" id="semester" value="{{ old('semester', $exam->semester) }}">
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </section>
</x-app-layout>
