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
    <table class="table" id="table-tugas">
      <thead>
        <tr>
          <th>#</th>
          <th>Judul Tugas</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody id="tugasTableBodyData">
        @forelse ($assignments as $item)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->assignment_title }}</td>
            <td>
              <div class="d-flex justify-content-start">
                <a href="{{ route('teacher.pengajar.rekap.v_detail_tugas_submission', ['learning_id' => $learning_id, 'tugas_id' => $item->id]) }}"
                  class="btn btn-primary btn-sm">
                  <i class="bi bi-eye"></i> Lihat
                </a>
              </div>
            </td>
          </tr>
        @empty
          <tr id="emptyMessageRow">
            <td colspan="3" class="text-center">Data tidak ditemukan. Silakan tambah tugas.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </x-datatable>

  <script>
    const assignments = @json($assignments);
    document.addEventListener('DOMContentLoaded', function() {
      if (assignments.length > 0) {
        initializeDataTable();
      }
    });

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
