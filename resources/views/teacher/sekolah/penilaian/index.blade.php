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
    Penilaian
  </x-slot>

  @php
    $title = 'Daftar Penilaian ' . $class_level['name'] . ' Pelajaran ' . $courses_name['name'];
  @endphp

  <div class="card">
    <div class="card-header bg-primary text-white">
      <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-between">
        <span class="mb-0 fs-4 fw-bold text-center text-md-start">{{ $title }}</span>
      </div>
    </div>

    <div class="card-body">
      <div class="row mt-3">
        <div class="card col-12 col-md-3 mb-3 mb-md-0">
          <div class="card-header bg-primary text-white d-flex justify-content-center justify-content-md-start">
            <span class="fw-bold text-center text-md-start">Total Mengerjakan</span>
          </div>
          <div class="card-body border rounded">
            <h3 class="mb-0 mt-3 fs-1 text-center fw-bolder">
              {{ $total_user_responses }} / {{ $exams->examSetting->maximum_user }}
            </h3>
          </div>
        </div>
        <div class="card col-12 col-md-3 mb-3 mb-md-0">
          <div class="card-header bg-primary text-white d-flex justify-content-center justify-content-md-start">
            <span class="fw-bold text-center text-md-start">Nilai Tertinggi</span>
          </div>
          <div class="card-body border rounded">
            <h3 class="mb-0 mt-3 fs-1 text-center fw-bolder">
              {{ $highest_grade ?? 0 }}
            </h3>
          </div>
        </div>
        <div class="card col-12 col-md-3 mb-3 mb-md-0">
          <div class="card-header bg-primary text-white d-flex justify-content-center justify-content-md-start">
            <span class="fw-bold text-center text-md-start">Nilai Terendah</span>
          </div>
          <div class="card-body border rounded">
            <h3 class="mb-0 mt-3 fs-1 text-center fw-bolder">
              {{ $lowest_grade ?? 0 }}
            </h3>
          </div>
        </div>
        <div class="card col-12 col-md-3 mb-3 mb-md-0">
          <div class="card-header bg-primary text-white d-flex justify-content-center justify-content-md-start">
            <span class="fw-bold text-center text-md-start">Nilai Rata-rata</span>
          </div>
          <div class="card-body border rounded">
            <h3 class="mb-0 mt-3 fs-1 text-center fw-bolder">
              {{ $average_grade ?? 0 }}
            </h3>
          </div>
        </div>
      </div>

      <div class="mt-0 mt-md-3 d-flex justify-content-end">
        <div style="min-width: 200px;">
          <label class="form-label">Status Penilaian</label>
          <select class="form-select" id="courseFilter">
            <option value="" selected>- Semua -</option>
            <option value="sudah_dinilai">Sudah Dinilai</option>
            <option value="belum_dinilai">Belum Dinilai</option>
          </select>
        </div>
      </div>

      <x-datatable title="Penilaian">
        <div class="table-responsive pt-3">
          <table class="table" id="penilaian">
            <thead>
              <tr>
                <th>NiSN</th>
                <th>Nama</th>
                <th>Nilai Akhir</th>
                <th>Pengerjaan</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($student_reports as $report)
                <tr data-status="{{ is_null($report['final_grade']) ? 'belum_dinilai' : 'sudah_dinilai' }}">
                  <td>{{ $report['nisn'] }}</td>
                  <td>{{ $report['name'] }}</td>
                  <td>
                    @if ($report['final_grade'])
                      @if ($report['final_grade'] > 0 && $report['final_grade'] <= 70)
                        <span class="grade-low">{{ $report['final_grade'] }}</span>
                      @else
                        <span class="grade-high">{{ $report['final_grade'] }}</span>
                      @endif
                    @else
                      -
                    @endif
                  </td>
                  <td>
                    {{ $report['total_attempts'] }} pengerjaan
                  </td>
                  <td>
                    <a href="{{ route('teacher.sekolah.v_penilaian_student', ['ujian_id' => $ujian_id, 'student_id' => str_replace('/', '-', $report['student_id'])]) }}"
                      class="btn btn-primary btn-sm">Lihat Pengerjaan</a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center">Data tidak ditemukan.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </x-datatable>

    </div>
  </div>

  <style>
    .card-body {
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .grade-low {
      color: red;
    }

    .grade-high {
      color: green;
    }
  </style>

  <script>
    const studentReports = @json($student_reports);

    $(document).ready(function() {
      const filterCourse = () => {
        const filterValue = $('#courseFilter').val();
        let rows = $('#penilaian tbody tr');
        let visibleRows = 0;

        rows.each(function() {
          const row = $(this);
          if (filterValue === '' || row.data('status') === filterValue) {
            row.show();
            visibleRows++;
          } else {
            row.hide();
          }
        });

        if (visibleRows === 0) {
          $('#penilaian tbody').append(
            '<tr class="no-data"><td colspan="5" class="text-center">Data tidak ditemukan, silahkan filter berdasarkan yang lain.</td></tr>'
          );
        } else {
          $('#penilaian tbody .no-data').remove();
        }
      };

      $('#courseFilter').on('change', filterCourse);

      function initializeDataTable() {
        $('#penilaian').DataTable({
          responsive: true,
          pagingType: 'simple',
          dom: "<'row'<'col-3'l><'col-9'f>>" +
            "<'row dt-row'<'col-sm-12'tr>>" +
            "<'row'<'col-4'i><'col-8'p>>",
          language: {
            info: 'Halaman _PAGE_ dari _PAGES_',
            lengthMenu: '_MENU_ ',
            search: '',
            searchPlaceholder: 'Cari..'
          }
        });
      }

      if (studentReports.length > 0) {
        initializeDataTable();
      }
    });
  </script>
</x-app-layout>
