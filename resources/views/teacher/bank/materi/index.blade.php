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
    Bank Materi
  </x-slot>

  <x-breadcrumb :title="$customTheme->title ?? 'Nama Sekolah'" :subtitle="'TMB Learning Management System'" :breadcrumbs="[['label' => 'Bank Materi', 'url' => null]]" :show-notifications="true" :unread-notifications="$unreadNotifications"
    :customTheme="$customTheme" />

  <x-datatable title="Bank Materi" :customTheme="$customTheme">
    <div class="row mb-4 align-items-center">
      <div class="col-12 col-sm-4 col-md-3 d-flex justify-content-start">
        <div class="mb-3">
          <a href="{{ route('teacher.bank.v_add_materi') }}" class="btn btn-primary-custom">
            Tambah Materi
          </a>
        </div>
      </div>

      <div class="col-12 col-sm-8 col-md-9 d-sm-flex justify-content-sm-end">
        <div class="col-12 col-sm-8 row g-3">
          <div class="col-12 col-sm-6">
            <div class="mb-3">
              <label class="form-label font-custom">Filter Pelajaran</label>
              <select class="form-select form-select-custom" id="courseFilter" onchange="filterCourse()">
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
              <select class="form-select form-select-custom" id="levelFilter" onchange="filterLevel()">
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

    <table class="table" id="table-bank-materi">
      <thead>
        <tr>
          <th class="font-custom">#</th>
          <th class="font-custom">Judul Materi</th>
          <th class="font-custom">Mata Pelajaran</th>
          <th class="font-custom">Aksi</th>
        </tr>
      </thead>
      <tbody id="rppTableBodyData">
        @forelse ($materi as $item)
          <tr data-course-id="{{ $item->course->id }}" data-level-name="{{ $item->class_level }}">
            <td class="font-custom">{{ $loop->iteration }}</td>
            <td class="font-custom">{{ $item->material_title }}</td>
            <td class="font-custom">{{ $item->course->courses_title }}</td>
            <td>
              <div class="d-flex justify-content-center gap-2">
                <a href="{{ route('teacher.bank.v_edit_materi', ['id' => $item->id]) }}"
                  class="btn btn-primary btn-sm">
                  <i class="bi bi-pencil-square"></i> Ubah
                </a>
                <button type="button" data-bs-toggle="modal" data-bs-target="#deleteDraftRppModal{{ $loop->index }}"
                  class="btn btn-danger btn-sm">
                  <i class="bi bi-trash"></i> Hapus
                </button>
                <button type="button" data-bs-toggle="modal" data-bs-target="#submitDraftRppModal{{ $loop->index }}"
                  class="btn btn-warning btn-sm">
                  <i class="bi bi-upload"></i> Bagikan
                </button>
              </div>
            </td>
          </tr>


          <!-- Modal Delete -->
          <div class="modal fade" id="deleteDraftRppModal{{ $loop->index }}" tabindex="-1"
            aria-labelledby="deleteDraftRppModalLabel{{ $loop->index }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content modal-content-custom">
                <div class="modal-header">
                  <h5 class="modal-title font-custom" id="deleteDraftRppModalLabel{{ $loop->index }}">Hapus
                    Materi</h5>
                  <button type="button" class="btn btn-close-custom" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i>
                  </button>
                </div>
                <div class="modal-body font-custom">
                  Apakah Anda yakin ingin menghapus materi ini?
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                  <form action="{{ route('teacher.bank.delete_materi', ['id' => $item->id]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-primary-custom">Hapus</button>
                  </form>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Bagikan -->
          <div class="modal fade" id="submitDraftRppModal{{ $loop->index }}" tabindex="-1"
            aria-labelledby="submitDraftRppModalLabel{{ $loop->index }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content modal-content-custom">
                <div class="modal-header">
                  <h5 class="modal-title font-custom" id="submitDraftRppModalLabel{{ $loop->index }}">Bagikan
                    Materi</h5>
                  <button type="button" class="btn btn-close-custom" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i>
                  </button>
                </div>
                <div class="modal-body">
                  <form id="shareMaterialForm{{ $loop->index }}" action="{{ route('teacher.bank.share_materi') }}"
                    method="POST">
                    @csrf
                    <div class="mb-3">
                      <label for="courseSelect{{ $loop->index }}" class="form-label font-custom">Mata
                        Pelajaran</label>
                      <select class="form-select form-select-custom" id="courseSelect{{ $loop->index }}"
                        name="course_id">
                        <option value="" disabled>- Pilih Mata Pelajaran -</option>
                        @foreach ($filteredCourseDataForShare as $course)
                          <option value="{{ $course->id }}" @if ($course->id == $item->course->id) selected @endif>
                            {{ $course->courses_title }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="mb-3">
                      <label for="levelSelect{{ $loop->index }}" class="form-label font-custom">Tingkat
                        Kelas</label>
                      <select class="form-select form-select-custom" id="levelSelect{{ $loop->index }}"
                        name="class_level">
                        <option value="" disabled>- Pilih Tingkat -</option>
                        @foreach ($levels as $level)
                          <option value="{{ $level->id }}" @if ($level->id == $item->class_level) selected @endif>
                            {{ $level->name }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="mb-3">
                      <label for="statusSelect{{ $loop->index }}" class="form-label font-custom">Status</label>
                      <select class="form-select form-select-custom" id="statusSelect{{ $loop->index }}"
                        name="status">
                        <option value="" selected disabled>- Pilih Status -</option>
                        <option value="Active">Aktif</option>
                        <option value="Inactive">Tidak Aktif</option>
                      </select>
                    </div>
                    <input type="hidden" name="materi_json" value="{{ json_encode($item) }}">
                  </form>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                  <button type="submit" class="btn btn-primary-custom"
                    form="shareMaterialForm{{ $loop->index }}">Bagikan</button>
                </div>
              </div>
            </div>
          </div>

        @empty
          <tr id="emptyMessageRow">
            <td colspan="4" class="text-center font-custom">Data tidak ditemukan. Silakan tambah materi.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </x-datatable>

  <script>
    const materi = @json($materi);
    $(document).ready(function() {
      if (materi.length > 0) {
        initializeDataTable();
      }
      generateCustomTheme();
    });

    function initializeDataTable() {
      $('#table-bank-materi').DataTable({
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

    function filterCourse() {
      const courseId = document.getElementById('courseFilter').value;
      const level = document.getElementById('levelFilter').value;
      filterData(courseId, level);
      generateCustomTheme();
    }

    function filterLevel() {
      const courseId = document.getElementById('courseFilter').value;
      const level = document.getElementById('levelFilter').value;
      filterData(courseId, level);
      generateCustomTheme();
    }

    function filterData(courseId, level) {
      let rows = document.querySelectorAll('#rppTableBodyData tr');
      let found = false;
      let index = 1;

      rows.forEach(row => {
        if (!row.dataset.courseId && !row.dataset.levelName) {
          return;
        }
        let courseColumn = row.dataset.courseId;
        let levelColumn = row.dataset.levelName;

        let showRow = (!courseId || courseColumn == courseId) && (!level || levelColumn == level);
        row.style.display = showRow ? '' : 'none';
        if (showRow) {
          row.querySelector('td:first-child').innerText = index++;
          found = true;
        }
      });

      let emptyMessageRow = document.getElementById('emptyMessageRow');
      if (!found) {
        if (!emptyMessageRow) {
          emptyMessageRow = document.createElement('tr');
          emptyMessageRow.id = 'emptyMessageRow';
          emptyMessageRow.innerHTML = `
                <td colspan="4" class="text-center font-custom">Data yang Anda cari tidak ada. Silakan tambah data terlebih dahulu.</td>
            `;
          document.getElementById('rppTableBodyData').appendChild(emptyMessageRow);
        } else {
          emptyMessageRow.style.display = '';
        }
      } else if (emptyMessageRow) {
        emptyMessageRow.style.display = 'none';
      }
    }
  </script>

</x-app-layout>
