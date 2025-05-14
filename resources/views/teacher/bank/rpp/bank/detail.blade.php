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
    Draft Materi
  </x-slot>

  <x-breadcrumb :title="$customTheme->title ?? 'Nama Sekolah'" :subtitle="'TMB Learning Management System'" :breadcrumbs="[['label' => 'Draft RPP', 'url' => route('teacher.bank.rpp')], ['label' => 'Subject', 'url' => null]]" :show-notifications="true" :unread-notifications="$unreadNotifications"
    :customTheme="$customTheme" />

  <x-datatable :title="$data->draft_name" :customTheme="$customTheme">
    <div class="row mb-4">
      <div class="col-md-6">
        <div class="mb-3">
          <a href="{{ route('teacher.bank.v_add_subject_matter', ['rpp_id' => $data->id]) }}"
            class="btn btn-primary-custom">
            Tambah Materi Pokok
          </a>
        </div>
      </div>
    </div>
    <table class="table" id="table-rpp-bank-detail">
      <thead>
        <tr>
          <th class="font-custom">#</th>
          <th class="font-custom">Materi Pokok</th>
          <th class="font-custom">Aksi</th>
        </tr>
      </thead>
      <tbody id="materiPokokTableBody">
        @forelse ($data->subject_matters as $index => $item)
          <tr>
            <td class="font-custom">{{ $loop->iteration }}</td>
            <td class="font-custom">{{ $item->title }}</td>
            <td>
              <div class="d-flex justify-content-center gap-2">
                <a href="{{ route('teacher.bank.v_edit_subject_matter', ['rpp_id' => $data->id, 'id' => $item->id]) }}"
                  class="btn btn-primary btn-sm">
                  <i class="bi bi-pencil-square"></i>
                  Edit
                </a>
                <a href="{{ route('teacher.bank.v_subject_matter_detail', ['rpp_id' => $data->id, 'id' => $item->id]) }}"
                  class="btn btn-success btn-sm">
                  <i class="bi bi-eye"></i> Lihat
                </a>
                <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                  data-bs-target="#deleteModal{{ $item->id }}">
                  <i class="bi bi-trash"></i> Hapus
                </button>
              </div>
            </td>
          </tr>

          {{-- Modal Delete --}}
          <div class="modal fade" id="deleteModal{{ $item->id }}" tabindex="-1"
            aria-labelledby="deleteModalLabel{{ $item->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content modal-content-custom">
                <div class="modal-header">
                  <h5 class="modal-title font-custom" id="deleteModalLabel{{ $item->id }}">Hapus Materi Pokok
                  </h5>
                  <button type="button" class="btn btn-close-custom" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i>
                  </button>
                </div>
                <div class="modal-body font-custom">
                  Apakah anda yakin ingin menghapus materi pokok ini?
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                  <form
                    action="{{ route('teacher.bank.delete_subject_matter', ['rpp_id' => $data->id, 'id' => $item->id]) }}"
                    method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-primary-custom">Hapus</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        @empty
          <tr>
            <td colspan="3" class="text-center font-custom">Data tidak ditemukan. Silahkan tambah materi.</td>
            <td class="d-none"></td>
            <td class="d-none"></td>
          </tr>
        @endforelse
      </tbody>
    </table>

  </x-datatable>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      initializeDataTable();
      generateCustomTheme();
    });

    function initializeDataTable() {
      $("#table-rpp-bank-detail").DataTable({
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
