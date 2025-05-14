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

  @if (isset($message) || session('message'))
  <div class="alert {{ !empty($alertClass) ? $alertClass : (!empty(session('alertClass')) ? session('alertClass') : '') }} alert-dismissible fade show" role="alert">
    {{ !empty($message) ? $message : session('message') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  <x-slot:title>
    Ujian Sekolah
    </x-slot>

    <x-datatable title="Daftar Ujian Sekolah">
      <div class="row mb-4 align-items-center">
        <div class="col-12 d-sm-flex justify-content-sm-end mb-3">
          <div class="col-12 col-sm-8 row g-3">
            <div class="col-12 col-sm-6">
              <div>
                <label class="form-label">Filter Pelajaran</label>
                <select class="form-select" id="courseFilter" onchange="filterData()">
                  <option value="" selected>Pilih Mata Pelajaran</option>
                  @foreach ($courses as $course)
                  <option value="{{ $course->id }}">{{ $course->courses_title }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-12 col-sm-6">
              <div>
                <label class="form-label">Filter Tingkat</label>
                <select class="form-select" id="levelFilter" onchange="filterData()">
                  <option value="" selected>Pilih Tingkat</option>
                  @foreach ($levels as $level)
                  <option value="{{ $level->id }}">{{ $level->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
          <div class="col-12 col-sm-2 row g-3">
            <div class="col-12 d-flex justify-content-end align-items-end">
              <div>
                <a href="{{ route('staff_curriculum.sekolah.v_ujian_create') }}" class="btn btn-primary">Tambah</a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <table class="table" id="table-ujian-bank">
        <thead>
          <tr>
            <th>#</th>
            <th>Nama Ujian</th>
            <th>Status</th>
            <th>Waktu</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody id="ujian-body-table">
          @forelse ($school_exams as $item)
          <tr>
            <td data-course-id="{{ !empty($item->courses_name['id']) ? $item->courses_name['id'] : '' }}"
              data-level-id="{{ !empty($item->class_level['id']) ? $item->class_level['id'] : '' }}">
              {{ $loop->iteration }}</td>
            <td>{{ $item->title }}</td>
            <td>{{ $item->status }}</td>
            <td>
              @php
              $duration = $item?->examSetting?->duration ? date('H:i:s', strtotime($item?->examSetting?->duration)) :
              '00:00:00';
              [$hours, $minutes, $seconds] = explode(':', $duration);

              $output = '';

              if ((int) $hours > 0) {
              $output .= (int) $hours . ' jam ';
              }

              if ((int) $minutes > 0) {
              $output .= (int) $minutes . ' menit ';
              }

              if ((int) $seconds > 0) {
              $output .= (int) $seconds . ' detik';
              }
              echo trim($output);
              @endphp
            </td>
            <td>
              <div class="d-flex justify-content-center gap-2">
                <a href="{{ route('staff_curriculum.sekolah.v_ujian_detail', ['id' => $item->id]) }}"
                  class="btn btn-primary">Lihat</a>

                <a href="{{ route('staff_curriculum.sekolah.v_ujian_penilaian', ['id' => $item->id]) }}"
                  class="btn btn-warning">Penilaian</a>

                <form action="{{ route('staff_curriculum.sekolah.delete_ujian', ['id' => $item->id]) }}" method="POST" onsubmit="return confirm('Hapus data?')">
                  @method('DELETE')
                  @csrf
                  <button type="submit" class="btn btn-danger">
                    Hapus
                  </button>
                </form>
              </div>
            </td>
          </tr>

          @empty
          <tr id="empty-row">
            <td colspan="5" class="text-center">Data tidak ditemukan. Silakan tambah ujian.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </x-datatable>

    <script>
      const ujian = @json($school_exams);
    $(document).ready(function() {
      if (ujian.length > 0) {
        initializeDataTable();
      }
    });

    function filterData() {
      var courseFilter = $('#courseFilter').val();
      var levelFilter = $('#levelFilter').val();
      var emptyRow = $('#empty-row');

      var hasVisibleRows = false;

      $('#ujian-body-table tr').each(function() {
        var row = $(this);
        var courseCell = row.find('td[data-course-id]');
        var levelCell = row.find('td[data-level-id]');

        if (courseCell.length && levelCell.length) {
          var courseText = courseCell.attr('data-course-id');
          var levelText = levelCell.attr('data-level-id');

          var courseMatch = courseFilter === "" || courseText.indexOf(courseFilter) !== -1;
          var levelMatch = levelFilter === "" || levelText.indexOf(levelFilter) !== -1;

          if (courseMatch && levelMatch) {
            row.show();
            hasVisibleRows = true;
            if (emptyRow.length > 0) {
              emptyRow.remove();
            }
          } else {
            row.hide();
          }
        }
      });

      if (!hasVisibleRows) {
        if (emptyRow.length === 0) {
          $('#ujian-body-table').append(
            '<tr id="empty-row"><td colspan="5" class="text-center">Data tidak ditemukan. Silakan tambah ujian.</td></tr>'
          );
        }
      } else {
        if (emptyRow.length > 0) {
          emptyRow.remove();
        }
      }
    }



    function initializeDataTable() {
      $('#table-ujian-bank').DataTable({
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
    </script>


</x-app-layout>