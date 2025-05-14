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

    @php
        $title = 'Tugas ' . $student->student->fullname;
    @endphp

    <x-slot:title>
        {{ $title }}
    </x-slot>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <span class="mb-0 fs-2 fw-bold text-center text-md-start">Daftar Pengerjaan</span>
        </div>
        <div class="card-body">

            <x-datatable :title="$title">

                <table class="table" id="table-tugas">
                    <thead>
                        <tr>
                            <th>Pengerjaan</th>
                            <th>Status</th>
                            <th>Nilai</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tugasTableBodyData">
                        @forelse ($submissions as $item)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($item->submitted_at)->translatedFormat('l, d-M-Y H:i') }}
                                </td>
                                <td>
                                    @if (
                                        \Carbon\Carbon::parse($item->submitted_at) >
                                            \Carbon\Carbon::parse($assignment->due_date . ' ' . $assignment->end_time))
                                        <span class="badge bg-danger">Terlambat</span>
                                    @else
                                        <span class="badge bg-success">Tepat Waktu</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($item->grades[0]->knowledge != null && $item->grades[0]->skills != null)
                                        @if ($item->grades[0]->is_main)
                                            <div class="d-flex flex-column align-items-start">
                                                <div class="mb-2">
                                                    <span class="fw-bold">Pengetahuan: </span>
                                                    <span
                                                        class="badge bg-primary">{{ $item->grades[0]->knowledge }}</span>
                                                </div>
                                                <div>
                                                    <span class="fw-bold">Keterampilan: </span>
                                                    <span class="badge bg-success">{{ $item->grades[0]->skills }}</span>
                                                </div>
                                            </div>
                                        @else
                                            <span class="badge bg-secondary">Bukan Nilai Utama</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">Belum Dinilai</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-start gap-2">
                                        <a href="{{ route('teacher.pengajar.pembelajaran.rubah_nilai', ['learning_id' => $learning_id, 'id' => $assignment_id, 'student_id' => str_replace('/', '-', $student_id), 'submission_id' => $item->id]) }}"
                                            class="btn btn-primary btn-sm">
                                            <i class="bi bi-mortarboard"></i> Koreksi & Nilai
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#modalNilai{{ $item->id }}"
                                            @if (
                                                \Carbon\Carbon::parse($item->submitted_at) >
                                                    \Carbon\Carbon::parse($assignment->due_date . ' ' . $assignment->end_time)) disabled @endif>
                                            <i class="bi bi-floppy"></i> Jadikan Nilai Utama
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            {{-- Modal Jadikan Nilai --}}
                            <div class="modal fade" id="modalNilai{{ $item->id }}" tabindex="-1"
                                aria-labelledby="modalNilaiLabel{{ $item->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalNilaiLabel{{ $item->id }}">
                                                Konfirmasi Nilai Utama</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Apakah Anda yakin ingin menjadikan tugas ini sebagai nilai utama?</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Batal</button>
                                            <form
                                                action="{{ route('teacher.pengajar.pembelajaran.simpan_nilai_utama', ['learning_id' => $learning_id, 'id' => $assignment_id, 'student_id' => str_replace('/', '-', $student_id), 'submission_id' => $item->id]) }}"
                                                method="POST">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-danger">Simpan</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>


                        @empty
                            <tr id="emptyMessageRow">
                                <td colspan="4" class="text-center">Data tidak ditemukan. Silakan tambah tugas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-datatable>
        </div>
    </div>

    <script>
        $(document).ready(function() {


            function initializeDataTableStudent() {
                $('#table-student').DataTable({
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

            initializeDataTableStudent();
        });
    </script>

</x-app-layout>
