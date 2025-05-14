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
    Kelas Mengajar
  </x-slot>

  <div class="card mt-3">
    <div class="card-header bg-primary text-white">
      <div class="d-flex align-items-center justify-content-between">
        <span class="mb-0 fs-2 fw-bold">Kelas Mengajar</span>
      </div>
    </div>
    <div class="card-body">
      @if (count($groupedData) > 0)
        <div class="d-flex align-items-center justify-content-end">
          <div class="my-4">
            <label class="form-label">Filter Pelajaran</label>
            <select class="form-select" id="courseFilter" onchange="filterData()">
              <option value="" selected>Pilih Mata Pelajaran</option>
              @foreach ($courses as $item)
                <option value="{{ $item['course']['id'] }}">{{ $item['course']['name'] }}</option>
              @endforeach
            </select>
          </div>
        </div>
      @endif

      <div id="classesContainer" class="row">
        @if (count($groupedData) == 0)
          <div class="col-12 mt-4">
            <div class="alert alert-warning text-center" role="alert">
              Data tidak ditemukan. Silakan daftarkan pelajaran Anda terlebih dahulu.
            </div>
          </div>
        @else
          @foreach ($groupedData as $teacherName => $subclasses)
            @foreach ($subclasses as $subclassName => $data)
              {{-- @dd($data['learning_id']); --}}
              <div class="col-12 col-md-6 mb-4 class-item" data-course-id="{{ $data['course_id'] }}">
                <div class="card">
                  <div class="card-header d-flex gap-3 align-items-center bg-primary text-white">
                    @if (is_object($data['teacher']))
                      @if ($data['waliKelasImage'])
                        @php
                          $linkUrl = str_replace('storage/', '', $data['waliKelasImage']);
                        @endphp
                        <img src="{{ Storage::url($linkUrl) }}" alt="Avatar" class="rounded-circle"
                          style="width: 100px; height: 100px">
                      @else
                        <div class="bg-secondary rounded-circle">
                          <i class="bi bi-person-fill text-white d-flex justify-content-center align-items-center"
                            style="width: 100px; height: 100px; font-size: 50px"></i>
                        </div>
                      @endif
                    @else
                      <div class="bg-secondary rounded-circle">
                        <i class="bi bi-person-fill text-white d-flex justify-content-center align-items-center"
                          style="width: 100px; height: 100px; font-size: 50px"></i>
                      </div>
                    @endif
                    <div>
                      @if (is_object($data['teacher']))
                        <h5 class="card-title mb-0">{{ $data['teacher']->fullname }}</h5>
                      @else
                        <h5 class="card-title mb-0">-</h5>
                      @endif
                      <small>{{ $data['class_name'] }} - {{ $data['subclassName'] }} ( {{ $data['course_name'] }}
                        )</small>
                    </div>
                  </div>
                  <div class="card-body border rounded">
                    <a href="{{ route('teacher.pengajar.pembelajaran.v_materi', ['learning_id' => $data['learning_id']]) }}"
                      class="info-box d-flex align-items-center my-3">
                      <div
                        class="info-icon bg-info text-white d-flex align-items-center justify-content-center rounded-circle me-3">
                        <i class="bi bi-book d-flex justify-content-center align-items-center fs-2"></i>
                      </div>
                      <div class="info-text">
                        <h6 class="mb-0">Materi</h6>
                        <p class="mb-0">{{ count($data['materials']) }}</p>
                      </div>
                    </a>
                    {{-- @dd($data['learning_id']); --}}
                    <a href="{{ route('teacher.pengajar.pembelajaran.v_tugas', ['learning_id' => $data['learning_id']]) }}"
                      class="info-box d-flex align-items-center mb-3">
                      <div
                        class="info-icon bg-success text-white d-flex align-items-center justify-content-center rounded-circle me-3">
                        <i class="bi bi-list-task d-flex justify-content-center align-items-center fs-2"></i>
                      </div>
                      <div class="info-text">
                        <h6 class="mb-0">Tugas</h6>
                        <p class="mb-0">{{ count($data['assignments']) }}</p>
                      </div>
                    </a>

                    <a href="{{ route('teacher.pengajar.pembelajaran.v_ulangan', ['learning_id' => $data['learning_id']]) }}"
                      class="info-box d-flex align-items-center">
                      <div
                        class="info-icon bg-warning text-white d-flex align-items-center justify-content-center rounded-circle me-3">
                        <i class="bi bi-pencil d-flex justify-content-center align-items-center fs-2"></i>
                      </div>
                      <div class="info-text">
                        <h6 class="mb-0">Ulangan</h6>
                        <p class="mb-0">{{ count($data['class_exams']) }}</p>
                      </div>
                    </a>
                  </div>
                </div>
              </div>
            @endforeach
          @endforeach
        @endif
      </div>

      <!-- Alert when no results are found -->
      <div id="noResultsAlert" class="alert alert-warning text-center d-none mt-4" role="alert">
        Kelas yang Anda cari tidak ditemukan. Silakan filter berdasarkan pelajaran lain.
      </div>

    </div>
  </div>

  <script>
    $(document).ready(function() {
      window.filterData = function() {
        var selectedCourseId = $('#courseFilter').val();
        var isAnyVisible = false;

        if (selectedCourseId === '') {
          $('.class-item').show();
          isAnyVisible = $('.class-item:visible').length > 0;
        } else {
          $('.class-item').each(function() {
            var itemCourseId = $(this).data('course-id');
            if (itemCourseId == selectedCourseId) {
              $(this).show();
              isAnyVisible = true;
            } else {
              $(this).hide();
            }
          });
        }

        if (!isAnyVisible) {
          $('#noResultsAlert').removeClass('d-none');
        } else {
          $('#noResultsAlert').addClass('d-none');
        }
      }
    });
  </script>


  <style>
    .info-box {
      border: 1px solid #ddd;
      padding: 10px;
      border-radius: 5px;
    }

    .info-icon {
      width: 50px;
      height: 50px;
    }
  </style>
</x-app-layout>
