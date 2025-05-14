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

  @if (isset($message))
    <div class="alert {{ $alertClass }} alert-dismissible fade show" role="alert">
      {{ $message }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <x-slot:title>
    Daftar Pengajuan RPP
  </x-slot>

  <x-datatable title="Daftar Pengajuan RPP">
    <div class="row mb-4">
      <div class="col-md-12">
        <div class="dropdown">
          <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown"
            aria-expanded="false">
            Ajukan RPP
          </button>
          <ul class="dropdown-menu border">
            <li><a class="dropdown-item" href="{{ route('teacher.pengajar.v_add_rpp') }}">Buat Baru</a></li>
            <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalAddRpp">Ambil
                dari Bank RPP</button></li>
          </ul>
        </div>
      </div>
    </div>
    <div class="row mb-4">
      <div class="col-md-4">
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
      <div class="col-md-4">
        <div class="mb-3">
          <label class="form-label">Filter Status</label>
          <select class="form-select" id="statusFilter" onchange="filterData()">
            <option value="" selected>Pilih Status</option>
            @foreach ($status as $item)
              <option value="{{ $item }}">{{ $item }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="col-md-4">
        <div class="mb-3">
          <label class="form-label">Filter Tahun Ajaran</label>
          <select class="form-select" id="yearFilter" onchange="filterData()">
            <option value="" selected>Pilih Tahun Ajaran</option>
            @foreach ($academic_years as $item)
              <option value="{{ $item->id }}">{{ $item->year }}</option>
            @endforeach
          </select>
        </div>
      </div>
    </div>
    <table class="table" id="table-rpp">
      <thead>
        <tr>
          <th>#</th>
          <th>Pelajaran</th>
          <th>Judul</th>
          <th>Tahun Ajaran</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody id="rppTableBodyData">
        @forelse ($rpp as $item)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td data-course-id="{{ $item->courses }}">{{ $item->course_title }}</td>
            <td>{{ $item->draft_name }}</td>
            <td>{{ $item->academic_year['name'] }}</td>
            <td>{{ $item->status }}</td>
            <td>
              <div class="d-flex justify-content-center gap-2">
                <a href="{{ route('teacher.pengajar.v_rpp_detail', ['id' => $item->id]) }}"
                  class="btn btn-primary btn-sm">
                  <i class="bi bi-eye"></i> Lihat
                </a>
              </div>
            </td>
          </tr>
        @empty
          <tr id="emptyMessageRow">
            <td colspan="6" class="text-center">Data tidak ditemukan. Silakan ajukan rpp.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </x-datatable>

  <!-- Modal Add -->
  <div class="modal fade" id="modalAddRpp" tabindex="-1" aria-labelledby="modalAddRppLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalAddRppLabel">Ambil RPP dari Bank</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('teacher.pengajar.ajukan_rpp') }}" method="POST">
          @csrf
          <div class="modal-body">
            <div class="mb-3">
              <label for="rpp_bank_id" class="form-label">Bank Rpp<span class="text-danger">*</span></label>
              <select class="form-select" id="rpp_bank_id" name="rpp_bank_id">
                <option value="" selected disabled>- Pilih Bank Rpp -</option>
                @foreach ($rpp_bank as $item)
                  <option value="{{ $item->id }}">{{ $item->draft_name }}</option>
                @endforeach
              </select>
              <div class="invalid-feedback">
                Mata pelajaran harus dipilih.
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Tahun Ajaran<span class="text-danger">*</span></label>
              <select class="form-select" id="academic_year" name="academic_year">
                <option value="" selected disabled>- Pilih Tahun Ajaran -</option>
                @foreach ($academic_years as $item)
                  <option value="{{ $item->id }}">{{ $item->year }}</option>
                @endforeach
              </select>
              <div class="invalid-feedback">
                Tahun Ajaran harus dipilih.
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Semester<span class="text-danger">*</span></label>
              <select class="form-select" id="semester" name="semester">
                <option value="" selected disabled>- Pilih Semester -</option>
                <option value="ganjil">Ganjil</option>
                <option value="genap">Genap</option>
              </select>
              <div class="invalid-feedback">
                Tahun Ajaran harus dipilih.
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Ajukan</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    const rpp = @json($rpp);

    function filterData() {
      const courseFilter = document.getElementById('courseFilter');
      const statusFilter = document.getElementById('statusFilter');
      const academicYearFilter = document.getElementById('academicYearFilter');

      const selectedCourse = courseFilter ? courseFilter.value : '';
      const selectedStatus = statusFilter ? statusFilter.value : '';
      const selectedAcademicYear = academicYearFilter ? academicYearFilter.value : '';

      const rows = document.querySelectorAll('#rppTableBodyData tr');
      let foundData = false;

      const emptyMessageRow = document.getElementById('emptyMessageRow');
      if (emptyMessageRow) {
        emptyMessageRow.remove();
      }

      rows.forEach(row => {
        const courseCell = row.querySelector('td:nth-child(2)');
        const statusCell = row.querySelector('td:nth-child(5)');
        const academicYearCell = row.querySelector('td:nth-child(4)');

        const courseText = courseCell ? courseCell.getAttribute('data-course-id') : '';
        const statusText = statusCell ? statusCell.textContent.trim() : '';
        const academicYearText = academicYearCell ? academicYearCell.textContent.trim() : '';

        const showRow = (!selectedCourse || courseText.includes(selectedCourse)) &&
          (!selectedStatus || statusText.includes(selectedStatus)) &&
          (!selectedAcademicYear || academicYearText.includes(selectedAcademicYear));

        if (showRow) {
          row.style.display = '';
          foundData = true;
        } else {
          row.style.display = 'none';
        }
      });

      if (!foundData) {
        const newRow = document.createElement('tr');
        newRow.id = 'emptyMessageRow';
        newRow.innerHTML = '<td colspan="6" class="text-center">Data tidak ditemukan. Silakan ajukan rpp.</td>';
        document.getElementById('rppTableBodyData').appendChild(newRow);
      }
    }

    document.addEventListener('DOMContentLoaded', function() {
      if (rpp.lengt > 0) {
        initializeDataTable();
      }
    });

    function initializeDataTable() {
      $('#table-rpp').DataTable({
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
