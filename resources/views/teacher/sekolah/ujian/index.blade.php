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
    <div class="alert {{ $alertClass || session('alertClass') }} alert-dismissible fade show" role="alert">
      {{ $message || session('message') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <x-slot:title>
    Ujian Sekolah
  </x-slot>

  <x-datatable title="Daftar Ujian Sekolah">
    <div class="row mb-4 align-items-center">

      <div class="col-12 d-sm-flex justify-content-sm-end">
        <div class="col-12 col-sm-8 row g-3">
          <div class="col-12 col-sm-6">
            <div class="mb-3">
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
            <div class="mb-3">
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
            <td data-course-id="{{ $item->courses_name['id'] }}" data-level-id="{{ $item->class_level['id'] }}">
              {{ $loop->iteration }}</td>
            <td>{{ $item->title }}</td>
            <td>{{ $item->status }}</td>
            <td>
              @php
                $duration = $item->examSetting->duration;
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

                @if (session('role_teacher') === 'PENGELOLA')
                  <a href="{{ route('teacher.sekolah.v_ujian_detail', ['ujian_id' => $item->id]) }}"
                    class="btn btn-primary btn-sm"><i class="bi bi-pencil-square"></i> Detail</a>
                @endif

                @if (session('role_teacher') === 'PENILAI')
                  <a href="{{ route('teacher.sekolah.v_penilaian', ['ujian_id' => $item->id]) }}"
                    class="btn btn-warning btn-sm" title="Ubah Tugas">
                    <i class="bi bi-book"></i> Penilaian
                  </a>
                @endif

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
