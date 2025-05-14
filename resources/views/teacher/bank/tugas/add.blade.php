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
    Tambah Tugas
  </x-slot>

  <x-breadcrumb :title="$customTheme->title ?? 'Nama Sekolah'" :subtitle="'TMB Learning Management System'" :breadcrumbs="[
      ['label' => 'Bank Tugas', 'url' => route('teacher.v_bank.tugas')],
      ['label' => 'Tambah Tugas', 'url' => null],
  ]" :show-notifications="true" :unread-notifications="$unreadNotifications"
    :customTheme="$customTheme" />

  <div class="card">
    <div class="card-header card-header-custom">
      <div class="d-flex align-items-center justify-content-between">
        <span class="mb-0 fs-2 fw-bold font-custom">Tambah Tugas</span>
        <span id="title-panel" class="mb-0 fs-4 fw-bold font-custom">( Umum )</span>
      </div>
    </div>
    <div class="card-body card-body-custom">
      <form id="main-form" action="{{ route('teacher.bank.add_tugas') }}" method="POST" enctype="multipart/form-data"
        class="mt-3">
        @csrf

        <!-- Tabs -->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
          <li class="nav-item" role="presentation">
            <a class="nav-link active nav-tabs-custom" id="umum-tab" data-bs-toggle="tab" href="#umum" role="tab"
              aria-controls="umum" aria-selected="true">Umum</a>
          </li>
          <li class="nav-item" role="presentation">
            <a class="nav-link nav-tabs-custom" id="pengaturan-tab" data-bs-toggle="tab" href="#pengaturan"
              role="tab" aria-controls="pengaturan" aria-selected="false">Pengaturan</a>
          </li>
          <li class="nav-item" role="presentation">
            <a class="nav-link nav-tabs-custom" id="resources-tab" data-bs-toggle="tab" href="#resources" role="tab"
              aria-controls="resources" aria-selected="false">Lampiran ( Opsional )</a>
          </li>
        </ul>
        <div class="tab-content mt-3" id="myTabContent">
          <!-- Tab Umum -->
          <div class="tab-pane fade show active" id="umum" role="tabpanel" aria-labelledby="umum-tab">
            <div class="mb-3">
              <label for="assignment_title" class="form-label font-custom">Judul Tugas<span
                  class="text-danger">*</span></label>
              <input type="text" name="assignment_title" id="assignment_title"
                class="form-control form-input-custom">
            </div>

            <div class="mb-3">
              <label for="assignment_description" class="form-label font-custom">Deskripsi<span
                  class="text-danger">*</span></label>
              <textarea name="assignment_description" id="assignment_description" class="form-control" rows="3"></textarea>
            </div>

            <div class="mb-3">
              <label for="instruction" class="form-label font-custom">Intruksi<span class="text-danger">*</span></label>
              <textarea name="instruction" id="instruction" class="form-control" rows="3"></textarea>
            </div>

            <div class="row mb-3">
              <div class="col-12 col-sm-6 mb-3 mb-sm-0">
                <label for="courses_name" class="form-label font-custom">Mata Pelajaran<span
                    class="text-danger">*</span></label>
                <select name="courses_name" id="courses_name" class="form-select form-select-custom">
                  <option value="" selected>- Pilih Pelajaran -</option>
                  @foreach ($courses as $course)
                    <option value="{{ $course->id }}">{{ $course->courses_title }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-12 col-sm-6">
                <label for="class_level" class="form-label font-custom">Tingkat<span
                    class="text-danger">*</span></label>
                <select name="class_level" id="class_level" class="form-select form-select-custom">
                  <option value="" selected>- Pilih Tingkat -</option>
                  @foreach ($levels as $level)
                    <option value="{{ $level->id }}">{{ $level->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>

          <!-- Tab Pengaturan -->
          <div class="tab-pane fade" id="pengaturan" role="tabpanel" aria-labelledby="pengaturan-tab">
            <div class="mb-3">
              <label for="limit_submit" class="form-label font-custom">Batas Kirim Tugas<span
                  class="text-danger">*</span></label>
              <input type="number" name="limit_submit" id="limit_submit" class="form-control form-input-custom">
            </div>

            <div class="mb-3">
              <label for="due_date" class="form-label font-custom">Batas Tanggal Tugas<span
                  class="text-danger">*</span></label>
              <input type="date" name="due_date" id="due_date" class="form-control form-input-custom">
            </div>

            <div class="mb-3">
              <label for="keanggotaan" class="form-label font-custom">Keanggotaan<span
                  class="text-danger">*</span></label>
              <select name="keanggotaan" id="keanggotaan" class="form-select form-select-custom">
                <option value="individual" selected>Individu</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="max_attach" class="form-label font-custom">Batas Kirim File<span
                  class="text-danger">*</span></label>
              <input type="number" name="max_attach" id="max_attach" max="10" min="1"
                class="form-control form-input-custom" oninput="handleMaxFileLimit()">
              <small class="form-text font-custom">Min: 1, Mak: 10</small>
            </div>

            <div class="mb-3">
              <label for="is_visibleGrade" class="form-label font-custom">Tampilkan Nilai<span
                  class="text-danger">*</span></label>
              <select name="is_visibleGrade" id="is_visibleGrade" class="form-select form-select-custom">
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
                  <label class="form-label font-custom">Lampiran Tipe</label>
                  <select name="resources[0][file_type]" class="form-select form-select-custom"
                    onchange="updateResourceType(0, this)">
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
                  <label class="form-label font-custom">Lampiran</label>
                  <input type="file" name="resources[0][file_url]" class="form-control form-input-custom"
                    id="resource_input_0">
                  <small id="file-info_0" class="form-text font-custom"></small>
                </div>
                <div class="mb-3">
                  <label class="form-label font-custom">Nama Lampiran<span class="text-danger">*</span></label>
                  <input type="text" name="resources[0][file_name]" class="form-control form-input-custom">
                </div>
              </div>
            </div>
            <button type="button" class="btn btn-primary-custom" onclick="addResource()"> <i
                class="bi bi-plus-lg"></i>
              Tambah
              Lampiran</button>
            <button type="submit" class="btn btn-primary-custom">Simpan</button>
          </div>
        </div>
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

      generateCustomTheme();

      $('#myTab a').on('shown.bs.tab', function(event) {
        var activeTab = $(event.target).text();
        $('#title-panel').text('( ' + activeTab + ' )');
      });

      // Fungsi untuk menambah resource
      window.addResource = function() {
        const resourceContainer = $('#resources-container');
        const newResource = `
                    <div class="resource mb-3" data-index="${resourceIndex}">
                        <div class="mb-3">
                            <label class="form-label font-custom">Lampiran Tipe</label>
                            <select name="resources[${resourceIndex}][file_type]" class="form-select form-select-custom" onchange="updateResourceType(${resourceIndex}, this)">
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
                            <label class="form-label font-custom">Lampiran</label>
                            <input type="file" name="resources[${resourceIndex}][file_url]" class="form-control form-input-custom" id="resource_input_${resourceIndex}">
                            <small id="file-info_${resourceIndex}" class="form-text font-custom"></small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label font-custom">Nama Lampiran<span class="text-danger">*</span></label>
                            <input type="text" name="resources[${resourceIndex}][file_name]" class="form-control form-input-custom">
                        </div>
                        <button type="button" class="btn btn-danger" onclick="removeResource(${resourceIndex})"> <i class="bi bi-trash"></i> Remove</button>
                    </div>
                `;
        resourceContainer.append(newResource);
        resourceIndex++;
        generateCustomTheme();
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
