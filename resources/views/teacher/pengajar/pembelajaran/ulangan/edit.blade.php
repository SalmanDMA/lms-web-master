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
        'Ubah ulangan ' .
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
    <div class="card-body mt-3">
      <ul class="nav nav-tabs" id="mainTab" role="tablist">
        <li class="nav-item" role="presentation" id="pengaturan-tab-item">
          <a class="nav-link active" id="ulangan-tab" data-bs-toggle="tab" href="#ulangan" role="tab"
            aria-controls="ulangan" aria-selected="true">Pengaturan</a>
        </li>
        <li class="nav-item" role="presentation" id="pertanyaan-tab-item">
          <a class="nav-link" id="questions-tab" data-bs-toggle="tab" href="#questions" role="tab"
            aria-controls="questions" aria-selected="false">Pertanyaan</a>
        </li>
        <li class="nav-item" role="presentation">
          <a class="nav-link" id="publikasi-tab" data-bs-toggle="tab" href="#publikasi" role="tab"
            aria-controls="publikasi" aria-selected="false">Publish</a>
        </li>
      </ul>

      <div class="tab-content mt-3" id="mainTabContent">
        <!-- Tab Pengaturan Ulangan -->
        <div class="tab-pane fade show active" id="ulangan" role="tabpanel" aria-labelledby="ulangan-tab">
          <x-ulangan.setting :learning_id="$learning_id" :class_exams="$class_exams" :subclasses="$subclasses" />
        </div>

        <!-- Tab Questions -->
        <div class="tab-pane fade" id="questions" role="tabpanel" aria-labelledby="questions-tab">
          <x-ulangan.question :learning_id="$learning_id" :class_exams="$class_exams" :questions="$questions" :bank_questions="$bank_questions" :levels="$levels"
            :question_type="$question_type" :question_category="$question_category" />
        </div>

        <div class="tab-pane fade" id="publikasi" role="tabpanel" aria-labelledby="publikasi-tab">
          <div class="card shadow">
            <div class="card-body">
              <!-- Alert -->
              @if ($class_exams->is_active)
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                  <strong>Ulangan Aktif</strong>, Silahkan menonaktifkan ulangan sebelumnya jika anda
                  ingin mengubah pengaturan ulangan dan
                  mengelola soal
                </div>
              @else
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                  <strong>Setelah ulangan diaktifkan</strong>, Anda tidak akan bisa mengubah
                  pengaturan ulangan dan
                  kelola
                  soal. Dan ini akan mengirim notifikasi ke siswa.
                </div>
              @endif

              <!-- Form -->
              <form
                action="{{ route('teacher.pengajar.pembelajaran.update_ulangan_is_active', ['learning_id' => $learning_id, 'ulangan_id' => $class_exams->id]) }}"
                method="POST">
                @csrf
                @method('PUT')

                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                    {{ $class_exams->is_active ? 'checked' : '' }}>
                  <label class="form-check-label" for="is_active">Ulangan Aktif</label>
                </div>

                <div class="d-flex justify-content-start gap-3 mt-3">
                  <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                  <a href="{{ route('teacher.pengajar.pembelajaran.v_ulangan', ['learning_id' => $learning_id]) }}"
                    class="btn btn-secondary">Kembali</a>
                </div>

              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    const classExam = @json($class_exams);
    $(document).ready(function() {
      if (classExam.is_active) {
        $('#pengaturan-tab-item').hide();
        $('#pertanyaan-tab-item').hide();
        $('#title-panel').text('( Publish )');
        $('#publikasi-tab').tab('show');
      }

      $('#mainTab a').on('shown.bs.tab', function(event) {
        var activeTab = $(event.target).text();
        $('#title-panel').text('( ' + activeTab + ' )');
      });
    });
  </script>
</x-app-layout>
