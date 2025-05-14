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
    Edit Tugas
  </x-slot>

  @php
    $title =
        'Ubah Tugas kelas ' .
        $subclasses->class->name .
        ' - ' .
        $subclasses->name .
        ' Pelajaran ' .
        $learning->course->courses_title;
  @endphp

  <div class="card">
    <div class="card-header bg-primary text-white">
      <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-between">
        <span class="mb-0 fs-4 fw-bold text-center text-md-start">{{ $title }}</span>
        <span id="title-panel" class="mb-0 fs-4 fw-bold">( Umum )</span>
      </div>
    </div>
    <div class="card-body">
      <form id="main-form"
        action="{{ route('teacher.pengajar.pembelajaran.update_tugas', ['learning_id' => $learning_id, 'id' => $assignments->id]) }}"
        method="POST" enctype="multipart/form-data" class="mt-3">
        @csrf
        @method('PUT')

        <!-- Tabs -->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
          <li class="nav-item" role="presentation">
            <a class="nav-link active" id="umum-tab" data-bs-toggle="tab" href="#umum" role="tab"
              aria-controls="umum" aria-selected="true">Umum</a>
          </li>
          <li class="nav-item" role="presentation">
            <a class="nav-link" id="pengaturan-tab" data-bs-toggle="tab" href="#pengaturan" role="tab"
              aria-controls="pengaturan" aria-selected="false">Pengaturan</a>
          </li>
          <li class="nav-item" role="presentation">
            <a class="nav-link" id="resources-tab" data-bs-toggle="tab" href="#resources" role="tab"
              aria-controls="resources" aria-selected="false">Lampiran ( Opsional )</a>
          </li>
          <li class="nav-item" role="presentation">
            <a class="nav-link" id="pengerjaan-tab" data-bs-toggle="tab" href="#pengerjaan" role="tab"
              aria-controls="pengerjaan" aria-selected="false">Pengerjaan dan Nilai Siswa</a>
          </li>
          <li class="nav-item" role="presentation">
            <a class="nav-link" id="statistik-tab" data-bs-toggle="tab" href="#statistik" role="tab"
              aria-controls="statistik" aria-selected="false">Statistik</a>
          </li>
        </ul>
        <div class="tab-content mt-3" id="myTabContent">
          <!-- Tab Umum -->
          <div class="tab-pane fade show active" id="umum" role="tabpanel" aria-labelledby="umum-tab">
            <div class="mb-3">
              <label for="assignment_title" class="form-label">Judul Tugas<span class="text-danger">*</span></label>
              <input type="text" name="assignment_title" id="assignment_title" class="form-control"
                value="{{ old('assignment_title', $assignments->assignment_title) }}">
            </div>

            <div class="mb-3">
              <label for="assignment_description" class="form-label">Deskripsi<span class="text-danger">*</span></label>
              <textarea name="assignment_description" id="assignment_description" class="form-control" rows="3">{{ old('assignment_description', $assignments->assignment_description) }}</textarea>
            </div>

            <div class="mb-3">
              <label for="instruction" class="form-label">Instruksi<span class="text-danger">*</span></label>
              <textarea name="instruction" id="instruction" class="form-control" rows="3">{{ old('instruction', $assignments->instruction) }}</textarea>
            </div>

            <div class="mb-3">
              <label for="publication_status" class="form-label">Status Publikasi<span
                  class="text-danger">*</span></label>
              <select name="publication_status" id="publication_status" class="form-select"
                onchange="handlePublicationStatusChange()">
                <option value="">- Select Status -</option>
                <option value="Publikasikan Sekarang"
                  {{ $assignments->publication_status == 'Publikasikan Sekarang' ? 'selected' : '' }}>
                  Publikasikan Sekarang</option>
                <option value="Jadwalkan" {{ $assignments->publication_status == 'Jadwalkan' ? 'selected' : '' }}>
                  Jadwalkan
                </option>
                <option value="Tidak Publikasikan"
                  {{ $assignments->publication_status == 'Tidak Publikasikan' ? 'selected' : '' }}>
                  Tidak Publikasikan</option>
              </select>
            </div>

            <div class="mb-3" id="schedule-date-time" style="display: none;">
              <label for="shared_at" class="form-label">Jadwal Publikasi<span class="text-danger">*</span></label>
              <input type="datetime-local" name="shared_at" id="shared_at" class="form-control"
                value="{{ old('shared_at', $assignments->shared_at ?? '') }}">
            </div>
          </div>

          <!-- Tab Pengaturan -->
          <div class="tab-pane fade" id="pengaturan" role="tabpanel" aria-labelledby="pengaturan-tab">
            <div class="mb-3">
              <label class="form-label">Bentuk Pengumpulan<span class="text-danger">*</span></label>
              <div class="form-check">
                <input type="checkbox" class="form-check-input" name="collection_type[]"
                  id="collection_type_catatan" value="Catatan"
                  {{ (is_array(old('collection_type', explode(',', $assignments->collection_type))) && in_array('Catatan', old('collection_type', explode(',', $assignments->collection_type)))) || $assignments->collection_type == 'All' ? 'checked' : '' }}>
                <label for="collection_type_catatan" class="form-check-label">Catatan</label>
              </div>
              <div class="form-check">
                <input type="checkbox" class="form-check-input" name="collection_type[]"
                  id="collection_type_lampiran" value="Lampiran"
                  {{ (is_array(old('collection_type', explode(',', $assignments->collection_type))) && in_array('Lampiran', old('collection_type', explode(',', $assignments->collection_type)))) || $assignments->collection_type == 'All' ? 'checked' : '' }}>
                <label for="collection_type_lampiran" class="form-check-label">Lampiran</label>
              </div>
            </div>

            <div class="mb-3">
              <label for="limit_submit" class="form-label">Batas Kirim Tugas<span
                  class="text-danger">*</span></label>
              <input type="number" name="limit_submit" id="limit_submit" class="form-control"
                value="{{ old('limit_submit', $assignments->limit_submit) }}">
            </div>

            <div class="row mb-3">
              <div class="mb-3 mb-sm-0 col-12 col-sm-6">
                <label for="due_date" class="form-label">Batas Tanggal Tugas<span
                    class="text-danger">*</span></label>
                <input type="date" name="due_date" id="due_date" class="form-control"
                  value="{{ old('due_date', $assignments->due_date) }}">
              </div>
              <div class="col-12 col-sm-6">
                <label for="end_time" class="form-label">Batas Waktu Tugas<span class="text-danger">*</span></label>
                <input type="time" name="end_time" id="end_time" class="form-control"
                  value="{{ old('end_time', $assignments->end_time) }}">
              </div>
            </div>

            <div class="mb-3">
              <label for="keanggotaan" class="form-label">Keanggotaan<span class="text-danger">*</span></label>
              <select name="keanggotaan" id="keanggotaan" class="form-select">
                <option value="individual" selected>Individu</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="max_attach" class="form-label">Batas Kirim File<span class="text-danger">*</span></label>
              <input type="number" name="max_attach" id="max_attach" max="10" min="1"
                class="form-control" value="{{ old('max_attach', $assignments->max_attach) }}"
                oninput="handleMaxFileLimit()">
              <small class="form-text text-muted">Min: 1, Mak: 10</small>
            </div>

            <div class="mb-3">
              <label for="is_visibleGrade" class="form-label">Tampilkan Nilai<span
                  class="text-danger">*</span></label>
              <select name="is_visibleGrade" id="is_visibleGrade" class="form-select">
                <option value="" selected>- Pilih Opsi -</option>
                <option value="1" {{ $assignments->is_visibleGrade == 1 ? 'selected' : '' }}>Ya
                </option>
                <option value="0" {{ $assignments->is_visibleGrade == 0 ? 'selected' : '' }}>
                  Tidak
                </option>
              </select>
            </div>

          </div>

          <!-- Tab Resource -->
          <div class="tab-pane fade" id="resources" role="tabpanel" aria-labelledby="resources-tab">
            <!-- Panel Tambah Lampiran -->
            <div id="add-resource-panel" style="display: block;">
              <div id="resources-container">
                {{-- Panel Tambah Resource --}}
                <div class="resource mb-3" data-index="0">
                  <div class="mb-3">
                    <label class="form-label">Lampiran Tipe</label>
                    <select name="resources[0][file_type]" class="form-select"
                      onchange="updateResourceType(0, this)">
                      <option value="">- Select Resource Type -</option>
                      <option value="audio">Audio</option>
                      <option value="video">Video</option>
                      <option value="image">Image</option>
                      <option value="archive">Archive</option>
                      <option value="document">Document</option>
                      <option value="url">URL</option>
                      <option value="youtube">YouTube</option>
                    </select>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Lampiran</label>
                    <input type="file" name="resources[0][file_url]" class="form-control" id="resource_input_0">
                    <small id="file-info_0" class="form-text text-muted"></small>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Nama Lampiran<span class="text-danger">*</span></label>
                    <input type="text" name="resources[0][file_name]" class="form-control">
                  </div>
                </div>
              </div>
            </div>

            <!-- Panel Lihat Lampiran Sebelumnya -->
            <div id="previous-resources-panel" style="display: none;">
              <div id="resources-container-previous">
                {{-- Panel Daftar Lampiran Sebelumnya --}}
                <table class="table table-bordered table-responsive">
                  <thead>
                    <tr>
                      <th>Tipe File</th>
                      <th>Nama File</th>
                      <th>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse ($assignments->assignment_attachments as $index => $resource)
                      <tr id="resource-row-{{ $index }}" data-id="{{ $resource->id }}">
                        <td>{{ ucfirst($resource->file_type) }}</td>
                        <td>
                          @if ($resource->file_type === 'url' || $resource->file_type === 'youtube')
                            {{ $resource->file_name }}
                          @else
                            {{ $resource->file_name . '.' . $resource->file_extension }}
                          @endif
                        </td>
                        <td>
                          @if ($resource->file_type === 'url' || $resource->file_type === 'youtube')
                            <a href="{{ $resource->file_url }}" target="_blank" class="btn btn-primary">Lihat
                              Link</a>
                          @elseif ($resource->file_type === 'archive')
                            <a href="{{ route('teacher.pengajar.pembelajaran.download_tugas', ['learning_id' => $learning_id, 'id' => $resource->id]) }}"
                              class="btn btn-success">Unduh</a>
                          @else
                            @php
                              $linkUrl = str_replace('storage/public/', '', $resource->file_url);
                            @endphp

                            <a href="{{ Storage::url($linkUrl) }}" target="_blank" class="btn btn-primary">Lihat
                              File</a>
                            <a href="{{ route('teacher.pengajar.pembelajaran.download_tugas', ['learning_id' => $learning_id, 'id' => $resource->id]) }}"
                              class="btn btn-success">Unduh</a>
                          @endif
                          <button type="button" class="btn btn-danger"
                            onclick="removeExistingResource({{ $index }})">Hapus</button>
                        </td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="3">Tidak ada lampiran</td>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>

            <button type="button" class="btn btn-success" onclick="addResource()" id="btn-add-resource"> <i
                class="bi bi-plus-lg"></i> Tambah Lampiran</button>
            <button type="button" class="btn btn-info" onclick="toggleResources()" id="btn-toggle-resources"> Lihat
              Lampiran
              Sebelumnya</button>
            <button type="submit" class="btn btn-primary">Simpan</button>
          </div>

          <!-- Tab Pengerjaan dan Nilai Siswa -->
          <div class="tab-pane fade show" id="pengerjaan" role="tabpanel" aria-labelledby="pengerjaan-tab">
            <x-datatable :title="'Daftar Pengerjaan'">
              <table class="table table-responsive w-100" id="table-tugas">
                <thead>
                  <tr>
                    <th rowspan="2">#</th>
                    <th rowspan="2">NIS</th>
                    <th rowspan="2">Siswa</th>
                    <th rowspan="2">Status</th>
                    <th colspan="2" class="text-center">Nilai</th>
                    <th rowspan="2">Aksi</th>
                  </tr>
                  <tr>
                    <th>Pengetahuan</th>
                    <th>Keterampilan</th>
                  </tr>
                </thead>
                <tbody id="tugasTableBodyData">
                  @forelse ($student_submissions as $student)
                    <tr>
                      <td>{{ $loop->iteration }}</td>
                      <td>{{ $student->student->nisn }}</td>
                      <td>{{ $student->student->fullname }}</td>
                      <td>
                        @if ($student->status == 'sudah mengerjakan')
                          <span class="badge bg-success">Sudah Mengerjakan</span>
                        @else
                          <span class="badge bg-danger">Belum Mengerjakan</span>
                        @endif
                      </td>
                      <td>
                        @if ($student->is_main)
                          {{ $student->knowledge }}
                        @else
                          -
                        @endif
                      </td>
                      <td>
                        @if ($student->is_main)
                          {{ $student->skills }}
                        @else
                          -
                        @endif
                      </td>
                      <td>
                        @if ($student->status == 'sudah mengerjakan')
                          <div class="d-flex justify-content-center align-items-center gap-2">
                            <a href="{{ route('teacher.pengajar.pembelajaran.v_detail_tugas_student', ['learning_id' => $learning_id, 'id' => $student->assignment_id, 'student_id' => str_replace('/', '-', $student->student->id)]) }}"
                              class="btn btn-primary"><i class="bi bi-eye"></i>
                              Lihat</a>
                          </div>
                        @endif
                      </td>
                    </tr>
                  @empty
                    <tr id="emptyMessageRow">
                      <td colspan="7" class="text-center">Data tidak ditemukan. Silakan
                        tambah tugas.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </x-datatable>
          </div>

          <!-- Tab Statistik -->
          <div class="tab-pane fade" id="statistik" role="tabpanel" aria-labelledby="statistik-tab">
            <x-datatable :title="'Statistik'">
              <table class="table table-responsive w-100" id="table-statistik">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>NIS</th>
                    <th>Siswa</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody id="statistikTableBodyData">
                  @forelse ($student_submissions as  $student)
                    <tr>
                      <td>{{ $loop->iteration }}</td>
                      <td>{{ $student->student->nisn }}</td>
                      <td>{{ $student->student->fullname }}</td>
                      <td>
                        @if ($student->time)
                          <span class="badge bg-success">Sudah Mengerjakan
                            {{ \Carbon\Carbon::parse($student->time)->translatedFormat('l, d-M-Y H:i') }}</span>
                        @else
                          <span class="badge bg-danger">Belum Mengerjakan</span>
                        @endif
                      </td>
                    </tr>
                  @empty
                    <tr id="emptyMessageRow">
                      <td colspan="4" class="text-center">Data tidak ditemukan.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </x-datatable>
          </div>
        </div>
        <input type="hidden" name="deleted_resources" id="deleted_resources" value="">
        <input type="hidden" name="existing_resources" id="existing_resources" value="">
        <input type="hidden" name="class_level" id="class_level" value="{{ $subclasses->class->id }}">
      </form>
    </div>
  </div>

  <script src="https://cdn.ckeditor.com/ckeditor5/34.2.0/classic/ckeditor.js"></script>
  <script>
    ClassicEditor.create($('#assignment_description')[0]).catch(console.error);
    ClassicEditor.create($('#instruction')[0]).catch(console.error);
  </script>

  <script>
    $(document).ready(function() {
      let resourceIndex = 1;
      let deletedResources = [];
      let existingResources = @json(array_column($assignments->assignment_attachments, 'id'));
      const assignments = @json($assignments);
      const student_submissions = @json($student_submissions);

      $('#myTab a').on('shown.bs.tab', function(event) {
        var activeTab = $(event.target).text();
        if (activeTab === 'Pengerjaan dan Nilai Siswa') {
          activeTab = 'Pengerjaan';
        } else if (activeTab === 'Lampiran ( Opsional )') {
          activeTab = 'Lampiran';
        }
        $('#title-panel').text('( ' + activeTab + ' )');
      });

      window.handlePublicationStatusChange = function() {
        const publicationStatus = document.getElementById('publication_status').value;
        const scheduleDateTime = document.getElementById('schedule-date-time');

        if (publicationStatus === 'Jadwalkan' && assignments.publication_status === 'Jadwalkan') {
          scheduleDateTime.style.display = 'block';
        } else {
          scheduleDateTime.style.display = 'none';
        }
      }

      handlePublicationStatusChange();

      window.toggleResources = function() {
        const previousResourcesPanel = $('#previous-resources-panel');
        const addResourcePanel = $('#add-resource-panel');
        const btnToggleResources = $('#btn-toggle-resources');
        const btnAddResource = $('#btn-add-resource');

        if (previousResourcesPanel.css('display') === 'none') {
          previousResourcesPanel.css('display', 'block');
          addResourcePanel.css('display', 'none');
          btnAddResource.css('display', 'none');
          btnToggleResources.text('Tambah Lampiran');
        } else {
          previousResourcesPanel.css('display', 'none');
          btnAddResource.css('display', '');
          addResourcePanel.css('display', 'block');
          btnToggleResources.text('Lihat Lampiran Sebelumnya');
        }
      };

      window.addResource = function() {
        const resourceContainer = $('#resources-container');
        const newResource = `
                    <div class="resource mb-3" data-index="${resourceIndex}">
                        <div class="mb-3">
                            <label class="form-label">Lampiran Tipe</label>
                            <select name="resources[${resourceIndex}][file_type]" class="form-select" onchange="updateResourceType(${resourceIndex}, this)">
                                <option value="">- Pilih Tipe Lampiran -</option>
                                <option value="audio">Audio</option>
                                <option value="video">Video</option>
                                <option value="image">Image</option>
                                <option value="archive">Archive</option>
                                <option value="document">Document</option>
                                <option value="url">URL</option>
                                <option value="youtube">YouTube</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Lampiran</label>
                            <input type="file" name="resources[${resourceIndex}][file_url]" class="form-control" id="resource_input_${resourceIndex}">
                            <small id="file-info_${resourceIndex}" class="form-text text-muted"></small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Lampiran<span class="text-danger">*</span></label>
                            <input type="text" name="resources[${resourceIndex}][file_name]" class="form-control">
                        </div>
                        <button type="button" class="btn btn-danger" onclick="removeResource(${resourceIndex})"> <i class="bi bi-trash"></i> Remove</button>
                    </div>
                `;
        resourceContainer.append(newResource);
        resourceIndex++;
      }

      window.updateResourceType = function(index, element) {
        const fileType = $(element).val();
        const fileInput = $(`#resource_input_${index}`);
        const fileInfo = $(`#file-info_${index}`);

        if (fileType === 'url') {
          fileInput.attr('type', 'url');
          fileInfo.text('Link must be valid URL, e.g. https://www.google.com');
        } else if (fileType === 'youtube') {
          fileInput.attr('type', 'url');
          fileInfo.text('Enter YouTube URL');
        } else {
          fileInput.attr('type', 'file');
          fileInfo.text(
            'Link must be valid YouTube URL, e.g. https://www.youtube.com/watch?v=abc123');
          switch (fileType) {
            case 'audio':
              fileInput.attr('accept', 'audio/mpeg,audio/wav,audio/mp3');
              fileInfo.text('Accepted file types: .mp3, .wav, .mpeg');
              break;
            case 'video':
              fileInput.attr('accept', 'video/mp4,video/mkv,video/mpeg');
              fileInfo.text('Accepted file types: .mp4, .mkv, .mpeg');
              break;
            case 'image':
              fileInput.attr('accept', 'image/png,image/jpg,image/jpeg');
              fileInfo.text('Accepted file types: .jpg, .jpeg, .png');
              break;
            case 'archive':
              fileInput.attr('accept',
                'application/zip,application/rar,application/x-zip-compressed,application/x-rar-compressed'
              );
              fileInfo.text('Accepted file types: .zip, .rar');
              break;
            case 'document':
              fileInput.attr('accept', 'application/pdf,application/msword');
              fileInfo.text('Accepted file types: .pdf, .doc, .docx');
              break;
            default:
              fileInput.attr('accept', '');
              fileInfo.text('');
              break;
          }
        }
      }

      window.removeExistingResource = function(index) {
        const row = $(`#resource-row-${index}`);
        if (row.length) {
          const resourceId = row.data('id');
          deletedResources.push(String(resourceId));
          updateDeletedResourcesInput();
          row.remove();
        }
      };

      function updateDeletedResourcesInput() {
        $('#deleted_resources').val(JSON.stringify(deletedResources));
      }

      $('#main-form').on('submit', function(event) {
        updateDeletedResourcesInput();
        $('#existing_resources').val(JSON.stringify(existingResources));
      });

      window.removeResource = function(index) {
        $(`.resource[data-index="${index}"]`).remove();
      };

      window.handleMaxFileLimit = function() {
        const maxAttach = parseInt($('#max_attach').val());
        if (maxAttach > 10) {
          $('#max_attach').val(10);
        } else if (maxAttach < 1) {
          $('#max_attach').val(1);
        }
      };

      function initializeDataTableStatistik() {
        $('#table-statistik').DataTable({
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

      function initializeDataTableTugas() {
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

      if (student_submissions.length > 0) {
        initializeDataTableStatistik();
        initializeDataTableTugas();
      }
    });
  </script>

</x-app-layout>
