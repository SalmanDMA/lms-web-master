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

  <div class="alert alert-danger alert-dismissible fade show" role="alert" id="alertContainer" style="display: none;">
    <span id="messageAlert"></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>

  <x-slot:title>
    Detail Tugas
  </x-slot>

  @php
    $title =
        'Detail Tugas kelas ' .
        $subclasses->class->name .
        ' - ' .
        $subclasses->name .
        ' Pelajaran ' .
        $learning->course->courses_title;
  @endphp

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header bg-primary text-white">
          <h4 class="card-title">{{ $title }}</h4>
        </div>
        <div class="card-body mt-4">
          <div class="table-responsive">
            <table class="table table-bordered">
              <tr>
                <th>Nama Tugas</th>
                <th>:</th>
                <td>{{ $assignments->assignment_title }}</td>
              </tr>
              <tr>
                <th>Waktu Pembagian Tugas</th>
                <th>:</th>
                <td>{{ \Carbon\Carbon::parse($assignments->created_at)->translatedFormat('l, d F Y H:i') }}
                  WIB</td>
              </tr>
              <tr>
                <th>Waktu Batas Pengumpulan</th>
                <th>:</th>
                <td>
                  {{ \Carbon\Carbon::parse($assignments->due_date . ' ' . $assignments->end_time)->translatedFormat('l, d F Y H:i') }}
                  WIB</td>
              </tr>
              <tr>
                <th>Bentuk Pengumpulan</th>
                <th>:</th>
                <td>
                  @if ($assignments->collection_type == 'All')
                    <span class="badge bg-warning">Catatan</span>
                    <span class="badge bg-warning me-3">Lampiran</span>
                  @else
                    <span class="badge bg-warning">
                      {{ $assignments->collection_type }}
                    </span>
                  @endif
                </td>
              </tr>
              <tr>
                <th>Deskripsi</th>
                <th>:</th>
                <td>{{ $assignments->assignment_description }}</td>
              </tr>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>

  <x-datatable title="Daftar Siswa">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div style="min-width: 200px;">
        <label for="global-publication-status" class="form-label me-2">Status Publikasi :</label>
        <select id="global-publication-status" class="form-select">
          <option value="">Pilih Status</option>
          <option value="Tidak Dibagikan">Tidak Dibagikan</option>
          <option value="Dibagikan">Dibagikan</option>
        </select>
      </div>
      <div class="d-flex gap-2">
        <form id="importForm"
          action="{{ route('teacher.pengajar.rekap.import_tugas_submission', ['learning_id' => $learning_id, 'tugas_id' => $assignments->id]) }}"
          method="POST" enctype="multipart/form-data">
          @csrf
          <input type="file" name="file" accept=".xlsx, .xls" id="fileInput" style="display: none;" />
          <button type="button" class="btn btn-primary" id="importButton">Import</button>
        </form>
        <form
          action="{{ route('teacher.pengajar.rekap.export_tugas_submission', ['learning_id' => $learning_id, 'tugas_id' => $assignments->id]) }}">
          @csrf
          <button type="submit" class="btn btn-outline-primary">Export</button>
        </form>
      </div>
    </div>
    <form
      action="{{ route('teacher.pengajar.rekap.simpan_nilai_tugas', ['learning_id' => $learning_id, 'tugas_id' => $assignments->id]) }}"
      method="POST">
      @csrf
      @method('PUT')
      <table class="table" id="table-submission">
        <thead>
          <tr>
            <th>NISN</th>
            <th>Nama</th>
            <th>Nilai Pengetahuan <br>
              <input type="checkbox" id="clear-knowledge">
              <label for="clear-knowledge" class="text-muted">Kosongkan Nilai</label>
            </th>
            <th>Nilai Keterampilan <br>
              <input type="checkbox" id="clear-skills">
              <label for="clear-skills" class="text-muted">Kosongkan Nilai</label>
            </th>
            <th>Status</th>
            <th>Status Publikasi</th>
          </tr>
        </thead>
        <tbody id="submission-body-table">
          @forelse ($submissions as $item)
            <tr>
              <td>{{ $item->student->nisn }}</td>
              <td>{{ $item->student->name }}</td>
              <td>
                @if (isset($item->grades[0]) && $item->grades[0]->is_main)
                  <input type="number" value="{{ $item->grades[0]->knowledge ?? '' }}"
                    class="form-control knowledge-input" name="knowledge[{{ $item->grades[0]->id }}]"
                    data-original-value="{{ $item->grades[0]->knowledge ?? '' }}">
                @else
                  <span class="badge bg-secondary">Nilai asli kosong</span>
                @endif
              </td>
              <td>
                @if (isset($item->grades[0]) && $item->grades[0]->is_main)
                  <input type="number" value="{{ $item->grades[0]->skills ?? '' }}" class="form-control skills-input"
                    name="skills[{{ $item->grades[0]->id }}]"
                    data-original-value="{{ $item->grades[0]->skills ?? '' }}">
                @else
                  <span class="badge bg-secondary">Nilai asli kosong</span>
                @endif
              </td>
              <td>
                @if ($item->grades[0]->status == null || $item->grades[0]->status == 'Belum Tersimpan')
                  <span class="badge bg-warning">Belum Tersimpan</span>
                @else
                  <span class="badge bg-success">Tersimpan</span>
                @endif
              </td>
              <td>
                <select class="form-select publication-status" name="publication_status[{{ $item->grades[0]->id }}]"
                  data-original-value="{{ $item->grades[0]->publication_status ?? '' }}">
                  <option value="Tidak Dibagikan" @if ($item->grades[0]->publication_status == null || $item->grades[0]->publication_status == 'Tidak Dibagikan') selected @endif>
                    Tidak Dibagikan</option>
                  <option value="Dibagikan" @if ($item->grades[0]->publication_status == 'Dibagikan') selected @endif>
                    Dibagikan</option>
                </select>
              </td>
            </tr>
            <input type="hidden" name="ids[]" value="{{ $item->grades[0]->id }}">
          @empty
            <tr>
              <td colspan="6" class="text-center">Tidak ada data</td>
            </tr>
          @endforelse
        </tbody>
      </table>
      <button type="submit" id="save-grades" class="btn btn-primary mt-3 w-100">
        <i class="bi bi-floppy"></i> Simpan
      </button>
    </form>
  </x-datatable>

  <script>
    const submissions = @json($submissions);
    $(document).ready(function() {
      $('#clear-knowledge').on('change', function() {
        if ($(this).is(':checked')) {
          $('.knowledge-input').each(function() {
            $(this).data('original-value', $(this).val());
            $(this).val('');
            $(this).prop('disabled', true);
          });
        } else {
          $('.knowledge-input').each(function() {
            $(this).val($(this).data('original-value'));
            $(this).prop('disabled', false);
          });
        }
      });

      $('#clear-skills').on('change', function() {
        if ($(this).is(':checked')) {
          $('.skills-input').each(function() {
            $(this).data('original-value', $(this).val());
            $(this).val('');
            $(this).prop('disabled', true);
          });
        } else {
          $('.skills-input').each(function() {
            $(this).val($(this).data('original-value'));
            $(this).prop('disabled', false);
          });
        }
      });

      $('#global-publication-status').on('change', function() {
        var selectedStatus = $(this).val();
        if (selectedStatus) {
          $('.publication-status').val(selectedStatus);
        } else {
          $('.publication-status').each(function() {
            if ($(this).data('original-value')) {
              $(this).val($(this).data('original-value'));
            } else {
              $(this).val('Tidak Dibagikan');
            }
          })
        }
      });

      $('#importButton').on('click', function() {
        $('#fileInput').trigger('click');
      });

      $('#fileInput').on('change', function() {

        const file = this.files[0];
        const fileName = file.name;
        const fileExtension = fileName.split('.').pop().toLowerCase();

        if (fileExtension === 'xlsx' || fileExtension === 'xls') {
          $('#alertContainer').css('display', 'none');
          $('#messageAlert').text('');
          $('#importForm').submit();
        } else {
          $('#alertContainer').css('display', '');
          $('#messageAlert').text('File harus berekstensi .xlsx/.xls');
        }
      });

      if (submissions.length > 0) {
        initializeDataTable();
      }
    });

    function initializeDataTable() {
      $('#table-submission').DataTable({
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
