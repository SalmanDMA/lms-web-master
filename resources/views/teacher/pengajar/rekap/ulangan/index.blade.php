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
    Ulangan
  </x-slot>

  @php
    $title =
        'Daftar ulangan kelas ' .
        $subclasses->class->name .
        ' - ' .
        $subclasses->name .
        ' Pelajaran ' .
        $learning->course->courses_title;
  @endphp

  <x-datatable :title="$title">
    <table class="table" id="table-ulangan-rekap">
      <thead>
        <tr>
          <th>#</th>
          <th>Judul Ulangan</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody id="ulanganTableBodyData">
        @forelse ($class_exam as $item)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->title }}</td>
            <td>
              <div class="d-flex justify-content-start">
                <a href="{{ route('teacher.pengajar.rekap.v_detail_ulangan_submission', ['learning_id' => $learning_id, 'ulangan_id' => $item->id]) }}"
                  class="btn btn-primary btn-sm">
                  <i class="bi bi-eye"></i> Lihat
                </a>
              </div>
            </td>
          </tr>
        @empty
          <tr id="emptyMessageRow">
            <td colspan="3" class="text-center">Data tidak ditemukan. Silakan tambah ulangan.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </x-datatable>

  <script>
    const classExam = @json($class_exam);

    $(document).ready(function() {
      console.log(classExam);
      if (classExam.length > 0) {
        console.log('masuk');
        initializeDataTable();
      }
    })

    function initializeDataTable() {
      $('#table-ulangan-rekap').DataTable({
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
