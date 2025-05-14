<x-app-layout>
    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if (isset($message) || session('message'))
    <div class="alert {{ $alertClass ?? session('alertClass') }} alert-dismissible fade show" role="alert">
        {{ $message ?? session('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if (session()->has('success'))
    <div class="alert alert-primary alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @php
    $latestStatus = null;
    if (is_array($draft) || $draft instanceof Countable) {
    if (count($draft) > 0) {
    $latestStatus = $draft[0]->status;
    }
    }
    @endphp

    <x-slot:title>
        Detail Pengajuan RPP
        </x-slot>

        <div class="card mt-3">
            <div class="card-header bg-primary">
                <h3 class="mb-0 text-white">Detail Pengajuan RPP</h3>
            </div>
            <div class="card-body mt-3">
                <ul class="nav nav-tabs" id="rppTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="umum-tab" data-bs-toggle="tab"
                            data-bs-target="#umum-content" type="button" role="tab" aria-controls="umum-content"
                            aria-selected="true">Umum</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="daftar-naskah-tab" data-bs-toggle="tab"
                            data-bs-target="#daftar-naskah-content" type="button" role="tab"
                            aria-controls="daftar-naskah-content" aria-selected="false">Daftar Naskah
                            (Draft)</button>
                    </li>
                    <li class="nav-item" role="presentation" style="display: none" id="detail-naskah-tab-wrapper">
                        <button class="nav-link" id="detail-naskah-tab" data-bs-toggle="tab"
                            data-bs-target="#detail-naskah-content" type="button" role="tab"
                            aria-controls="detail-naskah-content" aria-selected="false">Detail Naskah</button>
                    </li>
                </ul>
                <div class="row">
                    <div class="col-12 border d-flex flex-column justify-content-center align-items-center pt-3">
                        @php
                        $linkUrl = str_replace('public/storage/', '', $me->image_path);
                        @endphp

                        @if ($me->image_path && Storage::exists($linkUrl))
                        <img src="{{ Storage::url($linkUrl) }}" alt="Avatar" class="rounded-circle"
                            style="width: 100px; height: 100px">
                        @else
                        <div class="bg-secondary rounded-circle mb-3" style="width: 100px; height: 100px;">
                            <i class="bi bi-person-fill text-white d-flex justify-content-center align-items-center"
                                style="width: 100px; height: 100px; font-size: 50px"></i>
                        </div>
                        @endif
                        <h4 class="text-center">{{ $me->fullname }}</h4>
                    </div>
                    <div class="tab-content pt-3 mt-3 col-12 border" id="rppTabContent">
                        <div class="tab-pane fade show active" id="umum-content" role="tabpanel"
                            aria-labelledby="umum-tab">
                            @if($rpp->status == 'Dalam Proses')
                            <form action="/staff-curriculum/update-rpp-status" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="rpp_id" value="{{ $rpp->id }}">

                                <div class="d-flex align-items-center mb-5" style="gap: 1rem;">
                                    <select class="form-select" id="status" name="status" style="width: 125px;">
                                        <option value="Diterima">Terima</option>
                                        <option value="Ditolak">Tolak</option>
                                    </select>
                                    <button class="btn btn-primary" type="submit">Simpan</button>
                                </div>
                            </form>
                            @endif
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Draft:</label>
                                <p>{{ $rpp->draft_name }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tingkat:</label>
                                <p><span class="badge bg-secondary">{{ $rpp->class_level['name'] }}</span></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Mata Pelajaran:</label>
                                <p>{{ $rpp->courses['name'] }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Status:</label>
                                <p><span class="badge " data-status="{{ $rpp->status }}">{{ $rpp->status }}</span></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tahun Ajaran:</label>
                                <p>{{ $rpp->academic_year['name'] }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Semester:</label>
                                <p><span class="badge" data-semester="{{ $rpp->semester }}">{{ ucfirst($rpp->semester)
                                        }}</span></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Sekolah:</label>
                                <p>{{ ucfirst($me->school->name) }}</p>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="daftar-naskah-content" role="tabpanel"
                            aria-labelledby="daftar-naskah-tab">
                            <x-datatable title="Daftar Naskah ( Draft )">
                                <table class="table" id="table-draft-rpp">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Judul</th>
                                            <th>Status</th>
                                            <th>Tanggal Pengajuan</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($draft as $item)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $item->draft_name }}</td>
                                            <td><span class="badge" data-status="{{ $item->status }}">{{ $item->status
                                                    }}</span></td>
                                            <td>{{
                                                \Carbon\Carbon::parse($item->created_at)->setTimezone('Asia/Jakarta')->format('d-m-Y
                                                H:i:s') }}
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-2">
                                                    <button class="btn btn-primary btn-sm btn-view-draft"
                                                        data-item-id="{{ $item->id }}">
                                                        <i class="bi bi-eye"></i> Lihat
                                                    </button>
                                                    {{-- @if ($latestStatus === 'Dibatalkan' || $latestStatus ===
                                                    'Ditolak')
                                                    <a href="{{ route('teacher.pengajar.v_edit_draft_rpp', ['rpp_id' => $rpp->id, 'rpp_draft_id' => $item->id]) }}"
                                                        class="btn btn-warning btn-sm text-white">
                                                        <i class="bi bi-pencil-square"></i> Ubah
                                                    </a>
                                                    <button class="btn btn-info btn-sm text-white btn-ajukan-ulang"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalAjukanUlang{{ $item->id }}">
                                                        <i class="bi bi-arrow-repeat"></i> Ajukan Ulang
                                                    </button>
                                                    @endif --}}
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Modal Konfirmasi Ajukan Ulang -->
                                        <div class="modal fade" id="modalAjukanUlang{{ $item->id }}" tabindex="-1"
                                            aria-labelledby="modalAjukanUlangLabel{{ $item->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"
                                                            id="modalAjukanUlangLabel{{ $item->id }}">Konfirmasi
                                                            Ajukan Ulang</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Apakah Anda yakin ingin mengajukan ulang draft ini?</p>
                                                        <input type="hidden" id="ajukanUlangId">
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Batal</button>
                                                        <form id="ajukan-ulang-form-delete" method="POST"
                                                            action="{{ route('teacher.pengajar.ajukan_draft_rpp', ['rpp_id' => $rpp->id]) }}">
                                                            @csrf
                                                            <button type="submit" class="btn btn-primary">Ajukan
                                                                Ulang</button>
                                                            <input type="hidden" name="draft"
                                                                value="{{ json_encode($item) }}">
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @empty
                                        <tr id="empty-message-row">
                                            <td colspan="5" class="text-center">Data tidak ditemukan. Silakan
                                                tambah materi.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </x-datatable>
                        </div>

                        <div class="tab-pane fade" id="detail-naskah-content" role="tabpanel"
                            aria-labelledby="detail-naskah-tab">
                            <div id="detail-naskah-wrapper">
                                <div id="draft-details" class="d-none">
                                    <!-- Draft details will be injected here -->
                                </div>
                                <div id="subject-matter-details" class="d-none">
                                    <!-- Subject Matter details will be injected here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form id="update-rpp-draft-status" action="/staff-curriculum/update-rpp-draft-status" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" id="status-val" name="status">
            <input type="hidden" id="rpp-id-val" name="rpp_id" value="{{ $rpp->id }}">
            <input type="hidden" id="rpp-draft-id-val" name="rpp_draft_id">
        </form>

        <!-- Modal Konfirmasi Hapus -->
        <div class="modal fade" id="deleteSubjectMatterModal" tabindex="-1"
            aria-labelledby="deleteSubjectMatterModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteSubjectMatterModalLabel">Konfirmasi Hapus Materi Pokok</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Apakah Anda yakin ingin menghapus materi pokok ini?</p>
                        <input type="hidden" id="deleteSubjectMatterId">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <form id="subject-matter-form-delete" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Konfirmasi Batalkan -->
        <div class="modal fade" id="batalkanDraftRpp" tabindex="-1" aria-labelledby="batalkanDraftRppLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="batalkanDraftRppLabel">Konfirmasi Batalkan Draft RPP</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Apakah Anda yakin ingin membatalkan draft ini?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tidak</button>
                        <form id="delete-form-batalkan-draft" method="POST">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-danger">Iya</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
            const draftData = @json($draft);
  const rppData = @json($rpp);
  const userData = @json($me);

  $(document).ready(function() {
   $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(event) {
    $(event.target).addClass('active');
    if (event.relatedTarget) {
     $(event.relatedTarget).removeClass('active');
    }
   });

   $('.btn-view-draft').on('click', function() {
    const item = draftData.find(d => d.id == $(this).data('item-id'));

    const addUrlDraft =
     "{{ route('teacher.pengajar.v_add_subject_matter', [':rpp_id', ':rpp_draft_id']) }}"
     .replace(':rpp_id', rppData.id)
     .replace(':rpp_draft_id', item.id);

    const pdfUrl =
     "{{ route('teacher.pengajar.download_draft', [':rpp_id', ':rpp_draft_id']) }}"
     .replace(':rpp_id', rppData.id)
     .replace(':rpp_draft_id', item.id);

    const batalkanUrl =
     "{{ route('teacher.pengajar.batalkan_draft_rpp', [':rpp_id', ':rpp_draft_id']) }}"
     .replace(':rpp_id', rppData.id)
     .replace(':rpp_draft_id', item.id);

    const detailNaskahWrapper = $('#detail-naskah-wrapper');
    detailNaskahWrapper.html(`
                    <div id="draft-details">
                        <div class="mb-3 d-flex justify-content-end" style="gap: 1rem;">
                            <button class="btn btn-secondary" id="toggle-subject-matter">Tampilkan Materi Pokok</button>
                            ${
                                ['Dalam Proses'].includes(item.status) ? `
                                    <select class="form-select" id="status" name="status" style="width: 125px;">
                                        <option value="Diterima">Terima</option>
                                        <option value="Ditolak">Tolak</option>
                                    </select>
                                    <button class="btn btn-primary" id="update-status-button" data-id="${item.id}">Simpan</button>
                                ` : ``
                            }
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Draft:</label>
                            <p>${item.draft_name}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tingkat:</label>
                            <p><span class="badge bg-secondary">${item.class_level.name}</span></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Mata Pelajaran:</label>
                            <p>${item.courses.name}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status:</label>
                            <p><span class="badge" data-status="${item.status}">${item.status}</span></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tahun Ajaran:</label>
                            <p>${item.academic_year.name}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Semester:</label>
                            <p><span class="badge" data-semester="${item.semester}">${convertFirstLetterToUppercase(item.semester)}</span></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Sekolah:</label>
                            <p>${userData.school.name}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Dibuat Pada:</label>
                            <p>${formatDate(item.created_at)}</p>
                        </div>
                    </div>
                    <div id="subject-matter-details" class="d-none">
                        <div class="mb-3 d-flex justify-content-end gap-2">
                            <button class="btn btn-primary" id="toggle-draft-details">Tampilkan Detail Naskah</button>
                            <a href="${addUrlDraft}" class="btn btn-secondary">Tambah Materi Pokok</a>
                        </div>
                        ${item.subject_matters.length > 0 ? item.subject_matters.map(subject => {
                            const editUrlDraft = "{{ route('teacher.pengajar.v_edit_subject_matter', [':rpp_id', ':rpp_draft_id', ':id']) }}"
                                .replace(':rpp_id', rppData.id)
                                .replace(':rpp_draft_id', subject.rpp_draft_id)
                                .replace(':id', subject.id);

                            const deleteUrlDraft = "{{ route('teacher.pengajar.delete_subject_matter', [':rpp_id', ':rpp_draft_id', ':id']) }}"
                                .replace(':rpp_id', rppData.id)
                                .replace(':rpp_draft_id', subject.rpp_draft_id)
                                .replace(':id', subject.id);

                            return `
                           <div class="mb-3 border rounded p-3">
                               <div class="row mb-3">
                                   <div class="col-md-6">
                                       <h5 class="font-weight-bold">Judul Materi:</h5>
                                       <p>${subject.title}</p>
                                   </div>
                                   <div class="col-md-6">
                                       <h5 class="font-weight-bold">Alokasi Waktu:</h5>
                                       <p id="timeAllocation">${formatTimeAllocation(subject.time_allocation)}</p>
                                   </div>
                               </div>
                               <div class="row mb-3">
                                   <div class="col-md-12">
                                       <h5 class="font-weight-bold">Tujuan Pembelajaran:</h5>
                                       <div class="border p-3 rounded">${subject.learning_goals}</div>
                                   </div>
                               </div>
                               <div class="row mb-3">
                                   <div class="col-md-12">
                                       <h5 class="font-weight-bold">Aktivitas Pembelajaran:</h5>
                                       <div class="border p-3 rounded">${subject.learning_activity}</div>
                                   </div>
                               </div>
                               <div class="row mb-3">
                                   <div class="col-md-12">
                                       <h5 class="font-weight-bold">Penilaian:</h5>
                                       <div class="border p-3 rounded">${subject.grading}</div>
                                   </div>
                               </div>
                               <div class="d-flex justify-content-end gap-2 mt-3">
                                   <a href="${editUrlDraft}" class="btn btn-primary">Ubah</a>
                                   <button class="btn btn-danger btn-delete" data-delete-url="${deleteUrlDraft}">Hapus</button>
                               </div>
                           </div>
                       `;
                        }).join('') : '<div class="my-3 p-3 rounded bg-danger"><p class="text-center text-white m-0">Tidak ada data. Silakan tambahkan materi pokok.</p></div>'}
                    </div>
                `);

    if (item.status !== 'Dibatalkan') {
     $('#btn-batalkan-draft').on('click', function() {
      const deleteUrl = $(this).data('batalkan-url');
      $('#delete-form-batalkan-draft').attr('action', deleteUrl);
      new bootstrap.Modal($('#batalkanDraftRpp')).show();
     });
    }

    $('.btn-delete').on('click', function() {
     const deleteUrl = $(this).data('delete-url');
     $('#subject-matter-form-delete').attr('action', deleteUrl);
     new bootstrap.Modal($('#deleteSubjectMatterModal')).show();
    });

    $('.btn-ajukan-ulang').on('click', function() {
     const ajukanUrl = $(this).data('ajukan-url');
     $('#ajukan-ulang-form-delete').attr('action', ajukanUrl);
     new bootstrap.Modal($('#modalAjukanUlang')).show();
    });

    addClasses(detailNaskahWrapper.find('[data-status]'), getStatusClass(detailNaskahWrapper
     .find('[data-status]').data('status')));
    addClasses(detailNaskahWrapper.find('[data-semester]'), getSemesterClass(detailNaskahWrapper
     .find('[data-semester]').data('semester')));

    $('#toggle-subject-matter').on('click', function() {
     $('#draft-details, #subject-matter-details').toggleClass('d-none');
    });

    $('#update-status-button').on('click', function() {
        const val = $('#status').find(":selected").val();

        $('#rpp-draft-id-val').val($('#update-status-button').attr('data-id'))
        $('#status-val').val(val)
        $('#update-rpp-draft-status').submit()
    });

    $('#toggle-draft-details').on('click', function() {
     $('#draft-details, #subject-matter-details').toggleClass('d-none');
    });

    $('#detail-naskah-tab-wrapper').show();
    $('#detail-naskah-tab').attr('aria-selected', 'true').addClass('active');
    $('#daftar-naskah-content').removeClass('active show');
    $('#daftar-naskah-tab').attr('aria-selected', 'false').removeClass('active');
    $('#detail-naskah-content').addClass('active show');
   });

   $('[data-status]').each(function() {
    addClasses($(this), getStatusClass($(this).data('status')));
   });

   $('[data-semester]').each(function() {
    addClasses($(this), getSemesterClass($(this).data('semester')));
   });

   if (draftData.length > 0) {
    initializeDataTable();
   }

   function initializeDataTable() {
    const draftTable = $('#draft-table').DataTable({
     language: {
      paginate: {
       previous: 'Sebelumnya',
       next: 'Selanjutnya'
      },
      search: 'Cari:',
      lengthMenu: 'Tampilkan _MENU_ entri',
      info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ entri'
     },
     paging: true,
     searching: true,
     ordering: true,
     responsive: true
    });
   }

   function formatDate(dateString) {
    const options = {
     year: 'numeric',
     month: 'long',
     day: 'numeric'
    };
    return new Date(dateString).toLocaleDateString('id-ID', options);
   }

   function formatTimeAllocation(allocation) {
    const timeParts = allocation.split(':');
    return `${timeParts[0]} Jam ${timeParts[1]} Menit`;
   }

   function convertFirstLetterToUppercase(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
   }

   function addClasses(element, classes) {
    element.addClass(classes);
   }

   function getStatusClass(status) {
    switch (status) {
     case 'Dalam Proses':
      return 'bg-warning';
     case 'Diterima':
      return 'bg-primary';
     case 'Ditolak':
      return 'bg-danger';
     case 'Dibatalkan':
      return 'bg-danger';
     default:
      return 'bg-info';
    }
   }

   function getSemesterClass(semester) {
    return semester.toLowerCase() === 'ganjil' ? 'bg-warning' : 'bg-success';
   }
  });
        </script>

</x-app-layout>