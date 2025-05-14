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
    Tambah Materi
  </x-slot>

  <x-breadcrumb :title="$customTheme->title ?? 'Nama Sekolah'" :subtitle="'TMB Learning Management System'" :breadcrumbs="[
      ['label' => 'Bank Materi', 'url' => route('teacher.v_bank.materi')],
      ['label' => 'Add Materi', 'url' => null],
  ]" :show-notifications="true" :unread-notifications="$unreadNotifications"
    :customTheme="$customTheme" />

  <div class="card">
    <div class="card-header card-header-custom">
      <div class="d-flex align-items-center justify-content-between">
        <span class="mb-0 fs-2 fw-bold font-card-custom">Tambah Materi</span>
        <span id="title-panel" class="mb-0 fs-4 fw-bold font-card-custom">( Umum )</span>
      </div>
    </div>
    <div class="card-body card-body-custom">
      <form id="main-form" action="{{ route('teacher.bank.add_materi') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div id="form-content">
          <!-- Panel untuk Menambah Materi -->
          <div id="materi-panel">
            <div class="mb-3">
              <label for="material_title" class="form-label font-custom">Judul Materi<span
                  class="text-danger">*</span></label>
              <input type="text" name="material_title" id="material_title" class="form-control form-input-custom">
            </div>

            <div class="row mb-3">
              <div class="col-12 col-sm-6 mb-3 mb-sm-0">
                <label for="course_title" class="form-label font-custom">Mata Pelajaran<span
                    class="text-danger">*</span></label>
                <select name="course_title" id="course_title" class="form-select form-select-custom">
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

            <div class="row mb-3">
              <div class="col-12 col-sm-6 mb-3 mb-sm-0">
                <label for="max_attach" class="form-label font-custom">Maksimum File<span
                    class="text-danger">*</span></label>
                <input type="number" name="max_attach" id="max_attach" max="10" min="1"
                  class="form-control form-input-custom" oninput="handleMaxFileLimit()">
                <small class="form-text font-custom">Min: 1, Mak: 10</small>
              </div>

              <div class="col-12 col-sm-6">
                <label for="shared_at" class="form-label font-custom">Dibagikan Tanggal<span
                    class="text-danger">*</span></label>
                <input type="date" name="shared_at" id="shared_at" class="form-control form-input-custom">
              </div>
            </div>

            <div class="mb-3">
              <label for="material_description" class="form-label font-custom">Deskripsi Materi</label>
              <textarea name="material_description" id="material_description" class="form-control" rows="3"></textarea>
            </div>

            <button type="button" class="btn btn-primary-custom" onclick="showResourcesPanel()">Tambah
              Resource</button>
            <button type="submit" class="btn btn-primary-custom">Submit</button>
          </div>

          <!-- Panel untuk Menambah Resource -->
          <div id="resources-panel" style="display: none;">
            <button type="button" class="btn btn-primary-custom mb-3" onclick="showMateriPanel()"><i
                class="bi bi-arrow-left"></i>
              Back to Materi</button>
            <div id="resources-container">
              <!-- Resource template awal -->
              <div class="resource mb-3" data-index="0">
                <div class="mb-3">
                  <label class="form-label font-custom">Lampiran Tipe</label>
                  <select name="resources[0][resource_type]" class="form-select form-select-custom"
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
                  <label class="form-label font-custom">Lampiran</label>
                  <input type="file" name="resources[0][resource_url]" class="form-control form-input-custom"
                    id="resource_input_0">
                  <small id="file-info_0" class="form-text font-custom"></small>
                </div>
                <div class="mb-3">
                  <label class="form-label font-custom">Nama Lampiran<span class="text-danger">*</span></label>
                  <input type="text" name="resources[0][resource_name]" class="form-control form-input-custom">
                </div>
              </div>
            </div>
            <button type="button" class="btn btn-primary-custom" onclick="addResource()"> <i
                class="bi bi-plus-lg"></i>
              Add
              Resource</button>
            <button type="submit" class="btn btn-primary-custom">Submit</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</x-app-layout>

<script src="https://cdn.ckeditor.com/ckeditor5/34.2.0/classic/ckeditor.js"></script>
<script>
  ClassicEditor.create($('#material_description')[0]).catch(console.error);
</script>

<script>
  let resourceIndex = 1;

  $(document).ready(function() {
    generateCustomTheme();
  });

  function showResourcesPanel() {
    document.getElementById('materi-panel').style.display = 'none';
    document.getElementById('resources-panel').style.display = 'block';
    document.getElementById('form-content').scrollIntoView({
      behavior: "smooth"
    });
    document.getElementById('title-panel').innerHTML = '( Lampiran )';
  }

  function showMateriPanel() {
    document.getElementById('resources-panel').style.display = 'none';
    document.getElementById('materi-panel').style.display = 'block';
    document.getElementById('form-content').scrollIntoView({
      behavior: "smooth"
    });
    document.getElementById('title-panel').innerHTML = '( Umum )';
  }

  function addResource() {
    const index = resourceIndex++;
    const resourceTemplate = `
            <div class="resource mb-3" data-index="${index}">
                <div class="mb-3">
                    <label class="form-label font-custom">Resource Type</label>
                    <select name="resources[${index}][resource_type]" class="form-select form-select-custom" onchange="updateResourceType(${index}, this)">
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
                    <label class="form-label font-custom">Resource</label>
                    <input type="file" name="resources[${index}][resource_url]" class="form-control form-input-custom" id="resource_input_${index}">
                    <small id="file-info_${index}" class="form-text font-custom">Accepted file types: Any</small>
                </div>
                <div class="mb-3">
                    <label class="form-label font-custom">Resource Name</label>
                    <input type="text" name="resources[${index}][resource_name]" class="form-control form-input-custom">
                </div>
                <button type="button" class="btn btn-danger mb-3" onclick="removeResource(${index})"><i class="bi bi-trash"></i> Hapus</button>
            </div>
        `;
    document.getElementById('resources-container').insertAdjacentHTML('beforeend', resourceTemplate);
    generateCustomTheme();
  }

  function removeResource(index) {
    const resourceElement = document.querySelector(`.resource[data-index="${index}"]`);
    if (resourceElement) {
      resourceElement.remove();
    }
  }

  function updateResourceType(index, selectElement) {
    const resourceInput = document.getElementById(`resource_input_${index}`);
    const fileInfo = document.getElementById(`file-info_${index}`);
    const selectedType = selectElement.value;

    if (selectedType === 'url') {
      resourceInput.type = 'url';
      resourceInput.name = `resources[${index}][resource_url]`;
      resourceInput.accept = '';
      fileInfo.textContent = 'Link must be valid URL, e.g. https://www.google.com';
    } else if (selectedType === 'youtube') {
      resourceInput.type = 'url';
      resourceInput.name = `resources[${index}][resource_url]`;
      resourceInput.accept = '';
      fileInfo.textContent =
        'Link must be valid YouTube URL, e.g. https://www.youtube.com/watch?v=abc123';
    } else {
      resourceInput.type = 'file';
      resourceInput.name = `resources[${index}][resource_url]`;
      switch (selectedType) {
        case 'audio':
          resourceInput.accept = 'audio/mpeg,audio/wav,audio/mp3';
          fileInfo.textContent = 'Accepted file types: .mp3, .wav, .mpeg';
          break;
        case 'video':
          resourceInput.accept = 'video/mp4,video/mkv,video/mpeg';
          fileInfo.textContent = 'Accepted file types: .mp4, .mkv, .mpeg';
          break;
        case 'image':
          resourceInput.accept = 'image/png,image/jpg,image/jpeg';
          fileInfo.textContent = 'Accepted file types: .jpg, .jpeg, .png';
          break;
        case 'archive':
          resourceInput.accept =
            'application/zip,application/rar,application/x-zip-compressed,application/x-rar-compressed';
          fileInfo.textContent = 'Accepted file types: .zip, .rar';
          break;
        case 'document':
          resourceInput.accept = 'application/pdf,application/msword';
          fileInfo.textContent = 'Accepted file types: .pdf, .doc, .docx';
          break;
        default:
          resourceInput.accept = '';
          fileInfo.textContent = '';
          break;
      }
    }
  }

  function handleMaxFileLimit() {
    const maxAttach = document.getElementById('max_attach').value;

    if (maxAttach > 10) {
      document.getElementById('max_attach').value = 10;
    } else if (maxAttach < 1) {
      document.getElementById('max_attach').value = 1;
    }
  }
</script>

<style>
  #resources-container .resource {
    border: 1px solid #ddd;
    border-radius: .375rem;
    padding: .75rem;
    margin-bottom: .5rem;
  }

  #form-content {
    margin-top: 1rem;
  }
</style>
