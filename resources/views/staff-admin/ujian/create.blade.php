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
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="/staff-administrator/school-exam" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Judul Ujian</label>
                                <input type="text" class="form-control" name="title" id="title" value="{{ old('title') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="description">Deskripsi</label>
                                <input type="text" class="form-control" name="description" id="description" value="{{ old('description') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_time">Waktu Mulai</label>
                                <input type="date" class="form-control" name="start_time" id="start_time" value="{{ old('start_time') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end_time">Waktu Selesai</label>
                                <input type="date" class="form-control" name="end_time" id="end_time" value="{{ old('end_time') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="duration">Durasi (menit)</label>
                                <input type="text" class="form-control" name="duration" id="duration" value="{{ old('duration') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="repeat_chance">Kesempatan Ulang</label>
                                <input type="number" class="form-control" name="repeat_chance" id="repeat_chance" value="{{ old('repeat_chance') }}" required>
                            </div>
                        </div>
                    </div>

                    {{-- Dropdown --}}
                    <div class="form-group">
                        <label for="course">Mata Pelajaran</label>
                        <input type="text" class="form-control" name="course" id="course" value="{{ old('course') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="status">Status Ujian</label>
                        <select class="form-select" name="status" id="status">
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="device">Perangkat yang Diperbolehkan</label>
                        <input type="text" class="form-control" name="device" id="device" value="{{ old('device') }}">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="maximum_user">Maksimum Pengguna</label>
                                <input type="number" class="form-control" name="maximum_user" id="maximum_user" value="{{ old('maximum_user') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="token">Token</label>
                                <input type="text" class="form-control" name="token" id="token" value="{{ old('token') }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="publication_status">Status Publikasi</label>
                        <select class="form-select" name="publication_status" id="publication_status">
                            <option value="published" {{ old('publication_status') == 'published' ? 'selected' : '' }}>Dipublikasikan</option>
                            <option value="unpublished" {{ old('publication_status') == 'unpublished' ? 'selected' : '' }}>Belum Dipublikasikan</option>
                        </select>
                    </div>

                    {{-- Dropdown --}}
                    <div class="form-group">
                        <label for="class_level">Tingkat Kelas</label>
                        <input type="number" class="form-control" name="class_level" id="class_level" value="{{ old('class_level') }}">
                    </div>

                    {{-- Dropdown --}}
                    <div class="form-group">
                        <label for="academic_year">Tahun Akademik</label>
                        <input type="number" class="form-control" name="academic_year" id="academic_year" value="{{ old('academic_year') }}">
                    </div>

                    <div class="form-group">
                        <label for="semester">Semester</label>
                        <input type="number" class="form-control" name="semester" id="semester" value="{{ old('semester') }}">
                    </div>

                    <div class="form-group">
                        <label for="is_random_question" class="form-label">Acak Soal</label>
                        <select class="form-select" name="is_random_question" id="is_random_question">
                            <option value="1" {{ old('is_random_question') == '1' ? 'selected' : '' }}>Ya</option>
                            <option value="0" {{ old('is_random_question') == '0' ? 'selected' : '' }}>Tidak</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="is_random_answer" class="form-label">Acak Jawaban</label>
                        <select class="form-select" name="is_random_answer" id="is_random_answer">
                            <option value="1" {{ old('is_random_answer') == '1' ? 'selected' : '' }}>Ya</option>
                            <option value="0" {{ old('is_random_answer') == '0' ? 'selected' : '' }}>Tidak</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="is_show_score" class="form-label">Tampilkan Skor</label>
                        <select class="form-select" name="is_show_score" id="is_show_score">
                            <option value="1" {{ old('is_show_score') == '1' ? 'selected' : '' }}>Ya</option>
                            <option value="0" {{ old('is_show_score') == '0' ? 'selected' : '' }}>Tidak</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="is_show_result" class="form-label">Tampilkan Hasil</label>
                        <select class="form-select" name="is_show_result" id="is_show_result">
                            <option value="1" {{ old('is_show_result') == '1' ? 'selected' : '' }}>Ya</option>
                            <option value="0" {{ old('is_show_result') == '0' ? 'selected' : '' }}>Tidak</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="type" class="form-label">Tipe</label>
                        <input type="text" class="form-control" name="type" id="type" value="{{ old('type') }}">
                    </div>

                    <div class="form-group">
                        <label for="instruction" class="form-label">Instruksi</label>
                        <textarea class="form-control" name="instruction" id="instruction">{{ old('instruction') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" name="status" id="status">
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">Simpan</button>
                </form>
            </div>
        </div>
    </section>
</x-app-layout>
