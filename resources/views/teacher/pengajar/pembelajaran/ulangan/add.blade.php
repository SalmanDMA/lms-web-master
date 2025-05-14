<x-app-layout>
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
    <div class="alert {{ session('alertClass') }} alert-dismissible fade show" role="alert">
      {{ session('message') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <x-slot:title>
    Ulangan
  </x-slot>

  @php
    $title =
        'Tambah ulangan ' .
        $subclasses->class->name .
        ' - ' .
        $subclasses->name .
        ' Pelajaran ' .
        $learning->course->courses_title;
  @endphp

  <div class="card">
    <div class="card-header bg-primary text-white">
      <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-between">
        <span class="mb-0 fs-4 fw-bold text-center text-md-start">{{ $title }}</span>
        <span id="title-panel" class="mb-0 fs-4 fw-bold">( Umum )</span>
      </div>
    </div>
    <div class="card-body">
      <form id="main-form"
        action="{{ route('teacher.pengajar.pembelajaran.add_ulangan', ['learning_id' => $learning_id]) }}"
        method="POST" enctype="multipart/form-data" class="mt-3">
        @csrf

        <!-- Tabs -->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
          <li class="nav-item" role="presentation">
            <a class="nav-link active" id="umum-tab" data-bs-toggle="tab" href="#umum" role="tab"
              aria-controls="umum" aria-selected="true">Umum</a>
          </li>
          <li class="nav-item" role="presentation">
            <a class="nav-link" id="pengaturan-tab" data-bs-toggle="tab" href="#pengaturan" role="tab"
              aria-controls="pengaturan" aria-selected="false">Pengaturan</a>
          </li>
        </ul>
        <div class="tab-content mt-3" id="myTabContent">
          <!-- Tab Umum -->
          <div class="tab-pane fade show active" id="umum" role="tabpanel" aria-labelledby="umum-tab">
            <div class="row mb-3">
              <div class="mb-3 mb-sm-0 col-12 col-sm-6">
                <label for="title" class="form-label">Judul Ulangan<span class="text-danger">*</span></label>
                <input type="text" name="title" id="title" class="form-control">
              </div>

              <div class="col-12 col-sm-6">
                <label for="publication_status" class="form-label">Jenis Ulangan<span
                    class="text-danger">*</span></label>
                <select name="type" id="type" class="form-select">
                  <option value="">- Pilih Opsi -</option>
                  <option value="Ulangan Harian">Ulangan Harian</option>
                  <option value="Quiz Prakerja">Quiz Prakerja</option>
                  <option value="Post Test Prakerja">Post Test Prakerja</option>
                </select>
              </div>
            </div>

            <div class="mb-3">
              <label for="description" class="form-label">Deskripsi<span class="text-danger">*</span></label>
              <textarea name="description" id="description" class="form-control" rows="3"></textarea>
            </div>

            <div class="mb-3">
              <label for="instruction" class="form-label">Intruksi<span class="text-danger">*</span></label>
              <textarea name="instruction" id="instruction" class="form-control" rows="3"></textarea>
            </div>
          </div>

          <!-- Tab Pengaturan -->
          <div class="tab-pane fade" id="pengaturan" role="tabpanel" aria-labelledby="pengaturan-tab">
            <div class="row mb-3">
              <div class="mb-3 mb-sm-0 col-12 col-sm-6">
                <label for="start_time" class="form-label">Waktu Mulai<span class="text-danger">*</span></label>
                <input type="datetime-local" name="start_time" id="start_time" class="form-control">
              </div>
              <div class="col-12 col-sm-6">
                <label for="end_time" class="form-label">Waktu Berakhir<span class="text-danger">*</span></label>
                <input type="datetime-local" name="end_time" id="end_time" class="form-control">
              </div>
            </div>

            <div class="row mb-3">
              <div class="mb-3 mb-sm-0 col-12 col-sm-6">
                <label for="repeat_chance" class="form-label">Total Kesempatan<span
                    class="text-danger">*</span></label>
                <input type="number" name="repeat_chance" id="repeat_chance" class="form-control">
              </div>

              <div class="ol-12 col-sm-6">
                <label for="duration" class="form-label">Total Durasi<span class="text-danger">*</span></label>
                <input type="time" name="duration" id="duration" class="form-control">
              </div>
            </div>

            <div class="row mb-3">
              <div class="mb-3 mb-sm-0 col-12 col-sm-6">
                <label for="device" class="form-label">Perangkat<span class="text-danger">*</span></label>
                <select name="device" id="device" class="form-select">
                  <option value="">- Pilih Perangkat -</option>
                  <option value="Web">Web</option>
                  <option value="Mobile">Mobile</option>
                  <option value="All">Semua</option>
                </select>
              </div>

              <div class="col-12 col-sm-6">
                <label for="token" class="form-label">Token<span class="text-danger">*</span></label>
                <input type="text" name="token" id="token" class="form-control">
              </div>
            </div>

            <div class="mb-3">
              <label for="maximum_user" class="form-label">Maksimal Peserta<span class="text-danger">*</span></label>
              <input type="number" name="maximum_user" id="maximum_user" min="1" class="form-control">
            </div>

            <div class="row mb-3">
              <div class="col">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" id="is_random_question" name="is_random_question">
                  <label class="form-check-label" for="is_random_question">Acak soal</label>
                </div>
              </div>
              <div class="col">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" id="is_random_answer" name="is_random_answer">
                  <label class="form-check-label" for="is_random_answer">Acak jawaban</label>
                </div>
              </div>
              <div class="col">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" id="is_show_score" name="is_show_score">
                  <label class="form-check-label" for="is_show_score">Tampilkan skor</label>
                </div>
              </div>
              <div class="col">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" id="is_show_result" name="is_show_result">
                  <label class="form-check-label" for="is_show_result">Tampilkan pengerjaan</label>
                </div>
              </div>
            </div>


            <div class="d-flex justify-content-between">
              <a href="{{ route('teacher.pengajar.pembelajaran.v_ulangan', ['learning_id' => $learning_id]) }}"
                class="btn btn-secondary">Kembali</a>
              <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
          </div>

        </div>

        <input type="hidden" name="class_level" id="class_level" value="{{ $subclasses->class->id }}">

      </form>
    </div>
  </div>

  <script src="https://cdn.ckeditor.com/ckeditor5/34.2.0/classic/ckeditor.js"></script>
  <script>
    ClassicEditor.create($('#description')[0]).catch(console.error);
    ClassicEditor.create($('#instruction')[0]).catch(console.error);
  </script>

  <script>
    $(document).ready(function() {
      $('#myTab a').on('shown.bs.tab', function(event) {
        var activeTab = $(event.target).text();
        $('#title-panel').text('( ' + activeTab + ' )');
      });
    });
  </script>

</x-app-layout>
