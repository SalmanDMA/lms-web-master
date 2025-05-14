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
    Materi
  </x-slot>

  @php
    $title =
        'Daftar materi kelas ' .
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
            Bagikan Materi
          </button>
          <ul class="dropdown-menu border">
            <li><a class="dropdown-item"
                href="{{ route('teacher.pengajar.pembelajaran.v_add_materi', ['learning_id' => $learning_id]) }}">Buat
                Baru</a></li>
            <li><button type="button" class="dropdown-item" data-bs-toggle="modal"
                data-bs-target="#modalAddMateri">Ambil dari Bank Materi</button></li>
          </ul>
        </div>
      </div>
    </div>

    <table class="table" id="table-materi">
      <thead>
        <tr>
          <th>#</th>
          <th>Judul Materi</th>
          <th>Tanggal Dibagikan</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody id="materiTableBodyData">
        @forelse ($materials as $item)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->material_title }}</td>
            <td>
              @if ($item->status == 'Inactive')
                <span class="badge bg-danger">-</span>
              @else
                <span class="badge bg-success">{{ $item->shared_at }}</span>
              @endif
            </td>
            <td>
              @if ($item->status == 'Inactive')
                <span class="badge bg-danger">Tidak Aktif</span>
              @else
                <span class="badge bg-success">Aktif</span>
              @endif
            </td>
            <td>
              <div class="d-flex justify-content-center gap-2">
                <a href="{{ route('teacher.pengajar.pembelajaran.v_materi_detail', ['learning_id' => $learning_id, 'id' => $item->id]) }}"
                  class="btn btn-primary btn-sm">
                  <i class="bi bi-eye"></i> Lihat
                </a>
                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                  data-bs-target="#modalDeleteMateri{{ $item->id }}">
                  <i class="bi bi-trash"></i> Hapus
                </button>
              </div>
            </td>
          </tr>

          {{-- Modal Hapus Materi --}}
          <div class="modal fade" id="modalDeleteMateri{{ $item->id }}" tabindex="-1"
            aria-labelledby="modalDeleteMateriLabel{{ $item->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="modalDeleteMateriLabel{{ $item->id }}">Konfirmasi
                    Hapus Materi</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  Apakah Anda yakin ingin menghapus materi ini?
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                  <form
                    action="{{ route('teacher.pengajar.pembelajaran.delete_materi', ['learning_id' => $learning_id, 'id' => $item->id]) }}"
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
            <td colspan="5" class="text-center">Data tidak ditemukan. Silakan tambah materi.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </x-datatable>

  <!-- Modal Add -->
  <div class="modal fade" id="modalAddMateri" tabindex="-1" aria-labelledby="modalAddMateriLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalAddMateriLabel">Ambil Materi dari Bank</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('teacher.pengajar.pembelajaran.import_materi', ['learning_id' => $learning_id]) }}"
          method="POST">
          @csrf
          <div class="modal-body">
            <div class="mb-3">
              <label for="bank_materi_id" class="form-label">Bank Materi<span class="text-danger">*</span></label>
              <select class="form-select" id="bank_materi_id" name="bank_materi_id">
                <option value="" selected disabled>- Pilih Bank Materi -</option>
                @foreach ($material_banks as $item)
                  <option value="{{ $item->id }}">{{ $item->material_title }}</option>
                @endforeach
              </select>
            </div>
            <div class="mb-3">
              <label for="status" class="form-label">Status<span class="text-danger">*</span></label>
              <select class="form-select" id="status" name="status">
                <option value="" selected disabled>- Pilih Status -</option>
                <option value="Active">Aktif</option>
                <option value="Inactive">Tidak Aktif</option>
              </select>
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
    const materials = @json($materials);
    document.addEventListener('DOMContentLoaded', function() {
      if (materials.length > 0) {
        initializeDataTable();
      }
    });

    function initializeDataTable() {
      $('#table-materi').DataTable({
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
