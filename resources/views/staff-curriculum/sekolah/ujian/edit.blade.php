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
    $title = 'Detail Ujian Kelas ' . (!empty($class_level['name']) ? $class_level['name'] : '-') . ' Pelajaran ' . (!empty($courses_name['name']) ? $courses_name['name'] : '-');
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
        <li class="nav-item" role="presentation" id="sections-tab-item">
          <a class="nav-link" id="sections-tab" data-bs-toggle="tab" href="#sections" role="tab"
            aria-controls="sections" aria-selected="false">Bagian Ujian</a>
        </li>
        <li class="nav-item" role="presentation" id="participants-tab-item">
          <a class="nav-link" id="participants-tab" data-bs-toggle="tab" href="#participants" role="tab"
            aria-controls="participants" aria-selected="false">Peserta</a>
        </li>
        <li class="nav-item" role="presentation" id="teachers-tab-item">
          <a class="nav-link" id="teachers-tab" data-bs-toggle="tab" href="#teachers" role="tab"
            aria-controls="teachers" aria-selected="false">Guru</a>
        </li>
        <li class="nav-item" role="presentation">
          <a class="nav-link" id="publikasi-tab" data-bs-toggle="tab" href="#publikasi" role="tab"
            aria-controls="publikasi" aria-selected="false">Publish</a>
        </li>
      </ul>

      <div class="tab-content mt-3" id="mainTabContent">
        <!-- Tab Pengaturan Ujian -->
        <div class="tab-pane fade show active" id="ujian" role="tabpanel" aria-labelledby="ujian-tab">
          <x-ujian.staff.setting :ujian_id="$ujian_id" :exam="$exam" :class_level="$class_level" />
        </div>

        <!-- Tab sections -->
        <div class="tab-pane fade" id="sections" role="tabpanel" aria-labelledby="sections-tab">
          <x-ujian.staff.sections :ujian_id="$ujian_id" :exam="$exam" :sections="$exam_sections" />
        </div>

         <!-- Tab Participants -->
        <div class="tab-pane fade" id="participants" role="tabpanel" aria-labelledby="participants-tab">
          <x-ujian.staff.participants :ujian_id="$ujian_id" :exam="$exam" :participants="$students" :participantOptions="$studentOptions" />
        </div>

         <!-- Tab Teachers -->
        <div class="tab-pane fade" id="teachers" role="tabpanel" aria-labelledby="teachers-tab">
          <x-ujian.staff.teachers :ujian_id="$ujian_id" :exam="$exam" :teachers="$teachers" :teacherOptions="$teacherOptions" />
        </div>

        <div class="tab-pane fade" id="publikasi" role="tabpanel" aria-labelledby="publikasi-tab">
          <div class="card shadow">
            <div class="card-body">
              <!-- Alert -->
              @if (strtolower($exam->status) == 'active')
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
              <form action="{{ route('staff_curriculum.sekolah.edit_ujian_is_active', ['id' => $exam->id]) }}"
                method="POST">
                @csrf
                @method('PUT')

                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                    {{ strtolower($exam->status) == 'active' ? 'checked' : '' }}>
                  <label class="form-check-label" for="is_active">Ujian Aktif</label>
                </div>

                <div class="d-flex justify-content-start gap-3 mt-3">
                  <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                  <a href="{{ route('staff_curriculum.sekolah.v_ujian') }}" class="btn btn-secondary">Kembali</a>
                </div>

              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <a href="{{ route('staff_curriculum.sekolah.v_ujian') }}" class="btn btn-primary">Kembali</a>
    </div>
  </div>

  <script>
    const exam = @json($exam);
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
