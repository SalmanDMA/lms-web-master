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
        'Daftar ulangan ' .
        $subclasses->class->name .
        ' - ' .
        $subclasses->name .
        ' Pelajaran ' .
        $learning->course->courses_title;
  @endphp

  <x-datatable :title="$title">
    <div class="row mb-4">
      <div class="col-12">
        <a href="{{ route('teacher.pengajar.pembelajaran.v_add_ulangan', ['learning_id' => $learning_id]) }}"
          class="btn btn-primary" type="button">
          Buat ulangan
        </a>
      </div>
    </div>

    <table class="table" id="table-ulangan">
      <thead>
        <tr>
          <th>#</th>
          <th>Nama Ulangan</th>
          <th>Waktu Mulai</th>
          <th>Waktu Selesai</th>
          <th>Jumlah Soal</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody id="ulanganTableBodyData">
        @forelse ($class_exams as $item)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->title }}</td>
            <td>{{ \Carbon\Carbon::parse($item->exam_setting->start_time)->translatedFormat('l, d-M-Y H:i') }}
            </td>
            <td>{{ \Carbon\Carbon::parse($item->exam_setting->end_time)->translatedFormat('l, d-M-Y H:i') }}
            </td>
            <td>{{ $item->question_count ?? 0 }} Soal</td>
            <td>
              <div class="d-flex justify-content-start gap-2">
                <a href="{{ route('teacher.pengajar.pembelajaran.v_ulangan_detail', ['learning_id' => $learning_id, 'ulangan_id' => $item->id]) }}"
                  class="btn btn-primary btn-sm" title="Ubah Tugas">
                  <i class="bi bi-pencil"></i> Ubah
                </a>
                <a href="{{ route('teacher.pengajar.pembelajaran.v_penilaian', ['learning_id' => $learning_id, 'ulangan_id' => $item->id]) }}"
                  class="btn btn-warning btn-sm" title="Ubah Tugas">
                  <i class="bi bi-book"></i> Penilaian
                </a>
                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                  data-bs-target="#modalDeleteUlangan{{ $item->id }}" title="Hapus Tugas">
                  <i class="bi bi-trash"></i> Hapus
                </button>
              </div>
            </td>
          </tr>

          {{-- Modal Hapus Ulangan --}}
          <div class="modal fade" id="modalDeleteUlangan{{ $item->id }}" tabindex="-1"
            aria-labelledby="modalDeleteUlanganLabel{{ $item->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="modalDeleteUlanganLabel{{ $item->id }}">
                    Konfirmasi Hapus Ulangan
                  </h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  Apakah Anda yakin ingin menghapus ulangan ini?
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                  <form
                    action="{{ route('teacher.pengajar.pembelajaran.delete_ulangan', ['learning_id' => $learning_id, 'ulangan_id' => $item->id]) }}"
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
            <td colspan="6" class="text-center">Data tidak ditemukan. Silakan tambah ulangan.</td>
          </tr>
        @endforelse
      </tbody>

    </table>
  </x-datatable>

</x-app-layout>
