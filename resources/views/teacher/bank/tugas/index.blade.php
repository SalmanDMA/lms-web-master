@include('teacher.custom-theme', ['customTheme' => $customTheme])

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
    Bank Tugas
  </x-slot>

  <x-breadcrumb :title="$customTheme->title ?? 'Nama Sekolah'" :subtitle="'TMB Learning Management System'" :breadcrumbs="[['label' => 'Bank Tugas', 'url' => null]]" :show-notifications="true" :unread-notifications="$unreadNotifications"
    :customTheme="$customTheme" />

  <x-datatable title="Bank Tugas" :customTheme="$customTheme">
    <div class="row mb-4 align-items-center">
      <div class="col-12 col-sm-4 col-md-3 d-flex justify-content-start">
        <div class="mb-3">
          <a href="{{ route('teacher.bank.v_add_tugas') }}" class="btn btn-primary-custom">
            Buat Baru
          </a>
        </div>
      </div>

      <div class="col-12 col-sm-8 col-md-9 d-sm-flex justify-content-sm-end">
        <div class="col-12 col-sm-8 row g-3">
          <div class="col-12 col-sm-6">
            <div class="mb-3">
              <label class="form-label font-custom">Filter Pelajaran</label>
              <select class="form-select form-select-custom" id="courseFilter" onchange="filterData()">
                <option value="" selected>Pilih Mata Pelajaran</option>
                @foreach ($courses as $course)
                  <option value="{{ $course->id }}">{{ $course->courses_title }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-12 col-sm-6">
            <div class="mb-3">
              <label class="form-label font-custom">Filter Tingkat</label>
              <select class="form-select form-select-custom" id="levelFilter" onchange="filterData()">
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

    <table class="table" id="table-tugas-bank">
      <thead>
        <tr>
          <th class="font-custom">#</th>
          <th class="font-custom">Judul Tugas</th>
          <th class="font-custom">Mata Pelajaran</th>
          <th class="font-custom">Aksi</th>
        </tr>
      </thead>
      <tbody id="tugas-body-table">
        @forelse ($tugas as $item)
          <tr>
            <td class="font-custom">{{ $loop->iteration }}</td>
            <td class="font-custom">{{ $item->assignment_title }}</td>
            <td class="font-custom" data-course-id="{{ $item->courses_name['id'] }}"
              data-level-id="{{ $item->class_level['id'] }}">
              {{ $item->courses_name['name'] }}</td>
            <td>
              <div class="d-flex justify-content-center gap-2">
                <a href="{{ route('teacher.bank.v_edit_tugas', $item->id) }}" class="btn btn-primary btn-sm"><i
                    class="bi bi-pencil-square"></i> Ubah</a>
                <!-- Button untuk modal hapus -->
                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                  data-bs-target="#modalDeleteTugas{{ $item->id }}"><i class="bi bi-trash"></i>
                  Hapus</button>
                <!-- Button untuk modal bagikan -->
                <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                  data-bs-target="#modalBagikanTugas{{ $item->id }}"><i class="bi bi-upload"></i>
                  Bagikan</button>
              </div>
            </td>
          </tr>

          {{-- Modal Hapus Tugas --}}
          <div class="modal fade" id="modalDeleteTugas{{ $item->id }}" tabindex="-1"
            aria-labelledby="modalDeleteTugasLabel{{ $item->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content modal-content-custom">
                <div class="modal-header">
                  <h5 class="modal-title font-custom" id="modalDeleteTugasLabel{{ $item->id }}">Konfirmasi
                    Hapus Tugas</h5>
                  <button type="button" class="btn btn-close-custom" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i>
                  </button>
                </div>
                <div class="modal-body font-custom">
                  Apakah Anda yakin ingin menghapus tugas ini?
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                  <form action="{{ route('teacher.bank.delete_tugas', $item->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-primary-custom">Hapus</button>
                  </form>
                </div>
              </div>
            </div>
          </div>

          {{-- Modal Bagikan Tugas --}}
          <div class="modal fade" id="modalBagikanTugas{{ $item->id }}" tabindex="-1"
            aria-labelledby="modalBagikanTugasLabel{{ $item->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <form action="{{ route('teacher.bank.share_tugas') }}" method="POST">
                <div class="modal-content modal-content-custom">
                  <div class="modal-header">
                    <h5 class="modal-title font-custom" id="modalBagikanTugasLabel{{ $item->id }}">Bagikan
                      Tugas</h5>
                    <button type="button" class="btn btn-close-custom" data-bs-dismiss="modal">
                      <i class="bi bi-x-lg"></i>
                    </button>
                  </div>
                  <div class="modal-body">
                    @csrf
                    <div class="mb-3">
                      <label for="courseSelect{{ $item->id }}" class="form-label font-custom">Mata
                        Pelajaran<span class="text-danger">*</span></label>
                      <select class="form-select form-select-custom" id="courseSelect{{ $item->id }}"
                        name="course_id">
                        <option value="" disabled>- Pilih Mata Pelajaran -</option>
                        @foreach ($filteredCourseDataForShare as $course)
                          <option value="{{ $course->id }}" @if ($course->id == $item->course->id) selected @endif>
                            {{ $course->courses_title }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="mb-3 row">
                      <div class="col-6">
                        <label for="due_date{{ $item->id }}" class="form-label font-custom">Tanggal
                          Tenggat<span class="text-danger">*</span></label>
                        <input type="date" class="form-control form-input-custom"
                          id="due_date{{ $item->id }}" name="due_date"
                          value="{{ $item->due_date ? \Carbon\Carbon::parse($item->due_date)->format('Y-m-d') : '' }}">
                      </div>
                      <div class="col-6">
                        <label for="end_time{{ $item->id }}" class="form-label font-custom">Waktu
                          Tenggat<span class="text-danger">*</span></label>
                        <input type="time" class="form-control form-input-custom"
                          id="end_time{{ $item->id }}" name="end_time">
                      </div>
                    </div>
                    <div class="mb-3">
                      <label class="form-label font-custom">Tipe Koleksi<span class="text-danger">*</span></label><br>
                      <div class="form-check form-check-inline">
                        <input class="form-check-input form-checkbox-custom" type="checkbox"
                          id="catatan{{ $item->id }}" name="collection_type[]" value="Catatan">
                        <label class="form-check-label font-custom" for="catatan{{ $item->id }}">Catatan</label>
                      </div>
                      <div class="form-check form-check-inline">
                        <input class="form-check-input form-checkbox-custom" type="checkbox"
                          id="lampiran{{ $item->id }}" name="collection_type[]" value="Lampiran">
                        <label class="form-check-label font-custom"
                          for="lampiran{{ $item->id }}">Lampiran</label>
                      </div>
                    </div>
                    <div class="mb-3">
                      <label for="levelSelect{{ $item->id }}" class="form-label font-custom">
                        Kelas<span class="text-danger">*</span></label>
                      <select class="form-select form-select-custom" id="levelSelect{{ $item->id }}"
                        name="class_level">
                        <option value="" disabled>- Pilih Tingkat -</option>
                        @foreach ($levels as $level)
                          <option value="{{ $level->id }}" @if ($level->id == $item->class_level) selected @endif>
                            {{ $level->name }}</option>
                        @endforeach
                      </select>
                    </div>
                    <input type="hidden" name="tugas_json" value="{{ json_encode($item) }}">
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-custom">Bagikan</button>
                  </div>
              </form>
            </div>
          </div>
          </div>


        @empty
          <tr id="empty-row">
            <td colspan="4" class="text-center font-custom">Data tidak ditemukan. Silakan tambah tugas.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </x-datatable>

  <script>
    const tugas = @json($tugas);
    $(document).ready(function() {
      if (tugas.length > 0) {
        initializeDataTable();
      }
      generateCustomTheme();
    });

    function filterData() {
      var courseFilter = $('#courseFilter').val();
      var levelFilter = $('#levelFilter').val();
      var emptyRow = $('#empty-row');

      var hasVisibleRows = false;

      $('#tugas-body-table tr').each(function() {
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
          $('#tugas-body-table').append(
            '<tr id="empty-row"><td colspan="4" class="text-center font-custom">Data tidak ditemukan. Silakan tambah tugas.</td></tr>'
          );
        }
      } else {
        if (emptyRow.length > 0) {
          emptyRow.remove();
        }
      }
      generateCustomTheme();
    }



    function initializeDataTable() {
      $('#table-tugas-bank').DataTable({
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
        },
        initComplete: function() {
          applyCustomStyles();
        },
        drawCallback: function() {
          applyCustomStyles();
        }
      });
    }
  </script>


</x-app-layout>
