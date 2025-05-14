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
    Tugas
  </x-slot>

  @php
    $title =
        'Daftar tugas kelas ' .
        $subclasses->class->name .
        ' - ' .
        $subclasses->name .
        ' Pelajaran ' .
        $learning->course->courses_title;
  @endphp

  <x-datatable :title="$title">
    <div class="row mb-4">
      <div class="col-md-12">
        <div class="dropdown">
          <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown"
            aria-expanded="false">
            Bagikan tugas
          </button>
          <ul class="dropdown-menu border">
            <li><a class="dropdown-item"
                href="{{ route('teacher.pengajar.pembelajaran.v_add_tugas', ['learning_id' => $learning_id]) }}">Buat
                Baru</a></li>
            <li><button type="button" class="dropdown-item" data-bs-toggle="modal"
                data-bs-target="#modalAddTugas">Ambil dari Bank Tugas</button></li>
          </ul>
        </div>
      </div>
    </div>

    <table class="table" id="table-tugas">
      <thead>
        <tr>
          <th>#</th>
          <th>Judul Tugas</th>
          <th>Batas Pengumpulan</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody id="tugasTableBodyData">
        @forelse ($assignments as $item)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->assignment_title }}</td>
            <td>{{ $item->due_date }} - {{ $item->end_time }}</td>
            <td>
              <div class="d-flex justify-content-center gap-2">
                <a href="{{ route('teacher.pengajar.pembelajaran.v_tugas_detail', ['learning_id' => $learning_id, 'id' => $item->id]) }}"
                  class="btn btn-primary btn-sm">
                  <i class="bi bi-eye"></i> Lihat
                </a>
                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                  data-bs-target="#modalDeleteTugas{{ $item->id }}">
                  <i class="bi bi-trash"></i> Hapus
                </button>
              </div>
            </td>
          </tr>

          {{-- Modal Hapus Tugas --}}
          <div class="modal fade" id="modalDeleteTugas{{ $item->id }}" tabindex="-1"
            aria-labelledby="modalDeleteTugasLabel{{ $item->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="modalDeleteTugasLabel{{ $item->id }}">Konfirmasi
                    Hapus Tugas</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  Apakah Anda yakin ingin menghapus tugas ini?
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                  <form
                    action="{{ route('teacher.pengajar.pembelajaran.delete_tugas', ['learning_id' => $learning_id, 'id' => $item->id]) }}"
                    method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                  </form>
                </div>
              </div>
            </div>
          </div>

        @empty
          <tr id="emptyMessageRow">
            <td colspan="4" class="text-center">Data tidak ditemukan. Silakan tambah tugas.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </x-datatable>

  <!-- Modal Add -->
  <div class="modal fade" id="modalAddTugas" tabindex="-1" aria-labelledby="modalAddTugasLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalAddTugasLabel">Ambil Tugas dari Bank</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('teacher.pengajar.pembelajaran.import_tugas', ['learning_id' => $learning_id]) }}"
          method="POST">
          @csrf
          <div class="modal-body">
            <div class="mb-3">
              <label for="bank_assignment_id" class="form-label">Bank Tugas<span class="text-danger">*</span></label>
              <select class="form-select" id="bank_assignment_id" name="bank_assignment_id">
                <option value="" selected disabled>- Pilih Bank Tugas -</option>
                @foreach ($assignment_banks as $item)
                  <option value="{{ $item->id }}">{{ $item->assignment_title }}</option>
                @endforeach
              </select>
            </div>
            <div class="row mb-3">
              <div class="mb-3 mb-sm-0 col-12 col-sm-6">
                <label for="due_date" class="form-label">Batas Tanggal Tugas<span class="text-danger">*</span></label>
                <input type="date" name="due_date" id="due_date" class="form-control">
              </div>
              <div class="col-12 col-sm-6">
                <label for="end_time" class="form-label">Batas Waktu Tugas<span class="text-danger">*</span></label>
                <input type="time" name="end_time" id="end_time" class="form-control">
              </div>
            </div>
            <div class="mb-3">
              <label for="status" class="form-label">Status<span class="text-danger">*</span></label>
              <select class="form-select" id="status" name="status">
                <option value="" selected disabled>- Pilih Status -</option>
                <option value="Active">Aktif</option>
                <option value="Inactive">Tidak Aktif</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Bentuk Pengumpulan<span class="text-danger">*</span></label>
              <div class="form-check">
                <input type="checkbox" class="form-check-input" name="collection_type[]"
                  id="collection_type_catatan" value="Catatan">
                <label for="collection_type_catatan" class="form-check-label">Catatan</label>
              </div>
              <div class="form-check">
                <input type="checkbox" class="form-check-input" name="collection_type[]"
                  id="collection_type_lampiran" value="Lampiran">
                <label for="collection_type_lampiran" class="form-check-label">Lampiran</label>
              </div>
            </div>
            <div class="mb-3">
              <label for="publication_status" class="form-label">Status Publikasi<span
                  class="text-danger">*</span></label>
              <select name="publication_status" id="publication_status" class="form-select"
                onchange="handlePublicationStatusChange()">
                <option value="">- Select Status -</option>
                <option value="Publikasikan Sekarang">Publikasikan Sekarang</option>
                <option value="Jadwalkan">Jadwalkan</option>
                <option value="Tidak Publikasikan">Tidak Publikasikan</option>
              </select>
            </div>

            <div class="mb-3" id="schedule-date-time" style="display: none;">
              <label for="shared_at" class="form-label">Jadwal Publikasi<span class="text-danger">*</span></label>
              <input type="datetime-local" name="shared_at" id="shared_at" class="form-control">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Bagikan</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    const assignments = @json($assignments);
    document.addEventListener('DOMContentLoaded', function() {
      if (assignments.length > 0) {
        initializeDataTable();
      }
    });

    function handlePublicationStatusChange() {
      const publicationStatus = document.getElementById('publication_status').value;
      const scheduleDateTime = document.getElementById('schedule-date-time');

      if (publicationStatus === 'Jadwalkan') {
        scheduleDateTime.style.display = 'block';
      } else {
        scheduleDateTime.style.display = 'none';
      }
    }

    function initializeDataTable() {
      $('#table-tugas').DataTable({
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
