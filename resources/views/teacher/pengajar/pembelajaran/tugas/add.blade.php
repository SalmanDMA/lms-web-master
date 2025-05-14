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
    Tambah Tugas
  </x-slot>

  @php
    $title =
        'Tambah Tugas kelas ' .
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
        action="{{ route('teacher.pengajar.pembelajaran.add_tugas', ['learning_id' => $learning_id]) }}" method="POST"
        enctype="multipart/form-data" class="mt-3">
        @csrf

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
        </ul>
        <div class="tab-content mt-3" id="myTabContent">
          <!-- Tab Umum -->
          <div class="tab-pane fade show active" id="umum" role="tabpanel" aria-labelledby="umum-tab">
            <div class="mb-3">
              <label for="assignment_title" class="form-label">Judul Tugas<span class="text-danger">*</span></label>
              <input type="text" name="assignment_title" id="assignment_title" class="form-control">
            </div>

            <div class="mb-3">
              <label for="assignment_description" class="form-label">Deskripsi<span class="text-danger">*</span></label>
              <textarea name="assignment_description" id="assignment_description" class="form-control" rows="3"></textarea>
            </div>

            <div class="mb-3">
              <label for="instruction" class="form-label">Intruksi<span class="text-danger">*</span></label>
              <textarea name="instruction" id="instruction" class="form-control" rows="3"></textarea>
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

          <!-- Tab Pengaturan -->
          <div class="tab-pane fade" id="pengaturan" role="tabpanel" aria-labelledby="pengaturan-tab">
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
              <label for="limit_submit" class="form-label">Batas Kirim Tugas<span
                  class="text-danger">*</span></label>
              <input type="number" name="limit_submit" id="limit_submit" class="form-control">
            </div>

            <div class="row mb-3">
              <div class="mb-3 mb-sm-0 col-12 col-sm-6">
                <label for="due_date" class="form-label">Batas Tanggal Tugas<span
                    class="text-danger">*</span></label>
                <input type="date" name="due_date" id="due_date" class="form-control">
              </div>
              <div class="col-12 col-sm-6">
                <label for="end_time" class="form-label">Batas Waktu Tugas<span class="text-danger">*</span></label>
                <input type="time" name="end_time" id="end_time" class="form-control">
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
                class="form-control" oninput="handleMaxFileLimit()">
              <small class="form-text text-muted">Min: 1, Mak: 10</small>
            </div>

            <div class="mb-3">
              <label for="is_visibleGrade" class="form-label">Tampilkan Nilai<span
                  class="text-danger">*</span></label>
              <select name="is_visibleGrade" id="is_visibleGrade" class="form-select">
                <option value="" selected>- Pilih Opsi -</option>
                <option value="1">Ya</option>
                <option value="0">Tidak</option>
              </select>
            </div>

          </div>

          <!-- Tab Resource -->
          <div class="tab-pane fade" id="resources" role="tabpanel" aria-labelledby="resources-tab">
            <div id="resources-container">
              <!-- Resource template awal -->
              <div class="resource mb-3" data-index="0">
                <div class="mb-3">
                  <label class="form-label">Lampiran Tipe</label>
                  <select name="resources[0][file_type]" class="form-select" onchange="updateResourceType(0, this)">
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
                  <input type="file" name="resources[0][file_url]" class="form-control" id="resource_input_0">
                  <small id="file-info_0" class="form-text text-muted"></small>
                </div>
                <div class="mb-3">
                  <label class="form-label">Nama Lampiran<span class="text-danger">*</span></label>
                  <input type="text" name="resources[0][file_name]" class="form-control">
                </div>
              </div>
            </div>
            <button type="button" class="btn btn-success" onclick="addResource()"> <i class="bi bi-plus-lg"></i>
              Tambah
              Lampiran</button>
            <button type="submit" class="btn btn-primary">Simpan</button>
          </div>
        </div>

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

      $('#myTab a').on('shown.bs.tab', function(event) {
        var activeTab = $(event.target).text();
        $('#title-panel').text('( ' + activeTab + ' )');
      });

      window.handlePublicationStatusChange = function() {
        const publicationStatus = document.getElementById('publication_status').value;
        const scheduleDateTime = document.getElementById('schedule-date-time');

        if (publicationStatus === 'Jadwalkan') {
          scheduleDateTime.style.display = 'block';
        } else {
          scheduleDateTime.style.display = 'none';
        }
      }

      // Fungsi untuk menambah resource
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

      // Fungsi untuk menghapus resource
      window.removeResource = function(index) {
        $(`.resource[data-index="${index}"]`).remove();
      }

      // Fungsi untuk memperbarui tipe resource
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

      window.handleMaxFileLimit = function() {
        const maxAttach = $('#max_attach').val();

        if (maxAttach > 10) {
          $('#max_attach').val(10);
        } else if (maxAttach < 1) {
          $('#max_attach').val(1);
        }
      }
    });
  </script>
</x-app-layout>
