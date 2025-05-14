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
    Ujian
  </x-slot>

  @php
    $title = 'Detail Ujian Kelas ' . $class_level['name'] . ' Pelajaran ' . $courses_name['name'];
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
          <a class="nav-link active" id="ujian-tab" data-bs-toggle="tab" href="#ujian" role="tab"
            aria-controls="ujian" aria-selected="true">Pengaturan</a>
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
        <!-- Tab Pengaturan Ujian -->
        <div class="tab-pane fade show active" id="ujian" role="tabpanel" aria-labelledby="ujian-tab">
          <x-ujian.setting :ujian_id="$ujian_id" :exams="$exams" :class_level="$class_level" />
        </div>

        <!-- Tab Questions -->
        <div class="tab-pane fade" id="questions" role="tabpanel" aria-labelledby="questions-tab">
          <x-ujian.question :ujian_id="$ujian_id" :exams="$exams" :questions="$questions" :bank_questions="$bank_questions" :class_levels="$class_levels"
            :question_type="$question_type" :question_category="$question_category" />
        </div>

        <div class="tab-pane fade" id="publikasi" role="tabpanel" aria-labelledby="publikasi-tab">
          <div class="card shadow">
            <div class="card-body">
              <!-- Alert -->
              @if (strtolower($exams->status) == 'active')
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                  <strong>Ujian Aktif</strong>, Silahkan menonaktifkan ujian sebelumnya jika anda
                  ingin mengubah pengaturan ujian dan
                  mengelola soal
                </div>
              @else
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                  <strong>Setelah ujian diaktifkan</strong>, Anda tidak akan bisa mengubah
                  pengaturan ujian dan
                  kelola
                  soal. Dan ini akan mengirim notifikasi ke siswa.
                </div>
              @endif

              <!-- Form -->
              <form action="{{ route('teacher.sekolah.edit_ujian_is_active', ['ujian_id' => $exams->id]) }}"
                method="POST">
                @csrf
                @method('PUT')

                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                    {{ strtolower($exams->status) == 'active' ? 'checked' : '' }}>
                  <label class="form-check-label" for="is_active">Ujian Aktif</label>
                </div>

                <div class="d-flex justify-content-start gap-3 mt-3">
                  <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                  <a href="{{ route('teacher.sekolah.v_ujian') }}" class="btn btn-secondary">Kembali</a>
                </div>

              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    const exam = @json($exams);
    $(document).ready(function() {
      if (exam.status.toLowerCase() == 'active') {
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
