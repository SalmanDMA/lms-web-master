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
    Edit Materi
  </x-slot>

  @php
    $title =
        'Edit Materi kelas ' .
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
        action="{{ route('teacher.pengajar.pembelajaran.update_materi', ['learning_id' => $learning_id, 'id' => $materials->id]) }}"
        method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div id="form-content">
          <!-- Panel untuk Menambah Materi -->
          <div id="materi-panel">
            <div class="mb-3">
              <label for="material_title" class="form-label">Judul Materi<span class="text-danger">*</span></label>
              <input type="text" name="material_title" id="material_title" class="form-control"
                value="{{ old('material_title', $materials->material_title) }}">
            </div>

            <div class="row mb-3">
              <div class="col-12 col-sm-6 mb-3 mb-sm-0">
                <label for="max_file" class="form-label">Maksimum File<span class="text-danger">*</span></label>
                <input type="number" name="max_file" id="max_file" min="1" max="10" class="form-control"
                  value="{{ old('max_file', $materials->max_file) }}" oninput="handleMaxFileLimit()">
                <small class="form-text text-muted">Min: 1, Mak: 10</small>
              </div>

              <div class="col-12 col-sm-6">
                <label for="publication_status" class="form-label">Status Publikasi<span
                    class="text-danger">*</span></label>
                <select name="publication_status" id="publication_status" class="form-select"
                  onchange="handlePublicationStatusChange()">
                  <option value="">- Select Status -</option>
                  <option value="Publikasikan Sekarang"
                    {{ $materials->publication_status == 'Publikasikan Sekarang' ? 'selected' : '' }}>
                    Publikasikan Sekarang</option>
                  <option value="Jadwalkan" {{ $materials->publication_status == 'Jadwalkan' ? 'selected' : '' }}>
                    Jadwalkan
                  </option>
                  <option value="Tidak Publikasikan"
                    {{ $materials->publication_status == 'Tidak Publikasikan' ? 'selected' : '' }}>
                    Tidak Publikasikan</option>
                </select>
              </div>
            </div>

            <div class="mb-3" id="schedule-date-time" style="display: none;">
              <label for="shared_at" class="form-label">Jadwal Publikasi<span class="text-danger">*</span></label>
              <input type="datetime-local" name="shared_at" id="shared_at" class="form-control"
                value="{{ old('shared_at', $materials->shared_at) }}">
            </div>

            <div class="mb-3">
              <label for="material_description" class="form-label">Deskripsi Materi</label>
              <textarea name="material_description" id="material_description" class="form-control" rows="3">{{ old('material_description', $materials->material_description) }}</textarea>
            </div>

            <input type="hidden" name="class_level" id="class_level" value="{{ $subclasses->class->id }}">

            <button type="button" class="btn btn-secondary" onclick="showAddResourcePanel()">Tambah
              Resource</button>
            <button type="submit" class="btn btn-primary">Submit</button>
          </div>

          <!-- Panel untuk Menambah Resource -->
          <div id="resources-panel" style="display: none;">
            <!-- Buttons to switch between panels -->
            <button type="button" class="btn btn-secondary mb-3" onclick="showMateriPanel()"><i
                class="bi bi-arrow-left"></i>
              Back to Materi</button>
            <button type="button" class="btn btn-secondary mb-3" onclick="showAddResourcePanel()" id="btn-add-resource"
              style="display: none;">Tambah Lampiran</button>
            <button type="button" class="btn btn-secondary mb-3" onclick="showPreviousResourcesPanel()"
              id="btn-previous-resources" style="display: none;">Lihat Lampiran Sebelumnya</button>

            <!-- Panel Tambah Lampiran -->
            <div id="add-resource-panel" style="display: block;">
              <div id="resources-container">
                {{-- Panel Tambah Resource --}}
                <div class="resource mb-3" data-index="0">
                  <div class="mb-3">
                    <label class="form-label">Lampiran Tipe</label>
                    <select name="resources[0][resource_type]" class="form-select"
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
                    <input type="file" name="resources[0][resource_url]" class="form-control"
                      id="resource_input_0">
                    <small id="file-info_0" class="form-text text-muted"></small>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Nama Lampiran<span class="text-danger">*</span></label>
                    <input type="text" name="resources[0][resource_name]" class="form-control">
                  </div>
                </div>
              </div>

              <button type="button" class="btn btn-success" onclick="addResource()"><i class="bi bi-plus-lg"></i>
                Add
                Resource</button>
              <button type="submit" class="btn btn-primary">Submit</button>
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
                    @foreach ($materials->material_resources as $index => $resource)
                      <tr id="resource-row-{{ $index }}" data-id="{{ $resource->id }}">
                        <td>{{ ucfirst($resource->resource_type) }}</td>
                        <td>
                          @if ($resource->resource_type === 'url' || $resource->resource_type === 'youtube')
                            {{ $resource->resource_name }}
                          @else
                            {{ $resource->resource_name . '.' . $resource->resource_extension }}
                          @endif
                        </td>
                        <td>
                          @if ($resource->resource_type === 'url' || $resource->resource_type === 'youtube')
                            <a href="{{ $resource->resource_url }}" target="_blank" class="btn btn-primary">Lihat
                              Link</a>
                          @elseif ($resource->resource_type === 'archive')
                            <a href="{{ route('teacher.bank.download_materi', $resource->id) }}"
                              class="btn btn-success">Unduh</a>
                          @else
                            @php
                              $linkUrl = str_replace('storage/public/', '', $resource->resource_url);
                            @endphp

                            <a href="{{ Storage::url($linkUrl) }}" target="_blank" class="btn btn-primary">Lihat
                              File</a>
                            <a href="{{ route('teacher.bank.download_materi', $resource->id) }}"
                              class="btn btn-success">Unduh</a>
                          @endif
                          <button type="button" class="btn btn-danger"
                            onclick="removeExistingResource({{ $index }})">Hapus</button>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <input type="hidden" name="deleted_resources" id="deleted_resources" value="">
        <input type="hidden" name="existing_resources" id="existing_resources" value="">
      </form>
    </div>
  </div>

  <script src="https://cdn.ckeditor.com/ckeditor5/34.2.0/classic/ckeditor.js"></script>
  <script>
    ClassicEditor.create($('#material_description')[0]).catch(console.error);
  </script>

  <script>
    let deletedResources = [];
    let existingResources = @json(array_column($materials->material_resources, 'id'));

    function handlePublicationStatusChange() {
      const publicationStatus = document.getElementById('publication_status').value;
      const scheduleDateTime = document.getElementById('schedule-date-time');

      if (publicationStatus === 'Jadwalkan') {
        scheduleDateTime.style.display = 'block';
      } else {
        scheduleDateTime.style.display = 'none';
      }
    }

    function handleMaxFileLimit() {
      const maxAttachInput = document.getElementById('max_attach');
      const maxAttachValue = parseInt(maxAttachInput.value);

      if (maxAttachValue < 1 || maxAttachValue > 10) {
        maxAttachInput.value = Math.max(1, Math.min(maxAttachValue, 10));
      }
    }

    function showAddResourcePanel() {
      document.getElementById('add-resource-panel').style.display = 'block';
      document.getElementById('previous-resources-panel').style.display = 'none';
      document.getElementById('materi-panel').style.display = 'none';
      document.getElementById('resources-panel').style.display = 'block';
      document.getElementById('btn-add-resource').style.display = 'none';
      document.getElementById('btn-previous-resources').style.display = '';
      document.getElementById('form-content').scrollIntoView({
        behavior: "smooth"
      });
      document.getElementById('title-panel').innerHTML = '( Tambah Lampiran )';
    }

    function showPreviousResourcesPanel() {
      document.getElementById('add-resource-panel').style.display = 'none';
      document.getElementById('previous-resources-panel').style.display = 'block';
      document.getElementById('materi-panel').style.display = 'none';
      document.getElementById('resources-panel').style.display = 'block';
      document.getElementById('btn-add-resource').style.display = '';
      document.getElementById('btn-previous-resources').style.display = 'none';
      document.getElementById('form-content').scrollIntoView({
        behavior: "smooth"
      });
      document.getElementById('title-panel').innerHTML = '( Daftar Lampiran )';
    }

    function showMateriPanel() {
      document.getElementById('materi-panel').style.display = 'block';
      document.getElementById('resources-panel').style.display = 'none';
      document.getElementById('form-content').scrollIntoView({
        behavior: "smooth"
      });
      document.getElementById('title-panel').innerHTML = '( Umum )';
    }

    function addResource() {
      const resourcesContainer = document.getElementById('resources-container');
      const resourceIndex = resourcesContainer.querySelectorAll('.resource').length;

      const resourceHtml = `
                <div class="resource mb-3" data-index="${resourceIndex}">
                    <div class="mb-3">
                        <label class="form-label">Lampiran Tipe</label>
                        <select name="resources[${resourceIndex}][resource_type]" class="form-select" onchange="updateResourceType(${resourceIndex}, this)">
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
                        <input type="file" name="resources[${resourceIndex}][resource_url]" class="form-control" id="resource_input_${resourceIndex}">
                        <small id="file-info_${resourceIndex}" class="form-text text-muted"></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Lampiran<span class="text-danger">*</span></label>
                        <input type="text" name="resources[${resourceIndex}][resource_name]" class="form-control">
                    </div>
                    <button type="button" class="btn btn-danger" onclick="removeResource(${resourceIndex})">Hapus</button>
                </div>
            `;

      resourcesContainer.insertAdjacentHTML('beforeend', resourceHtml);
    }

    function updateResourceType(index, selectElement) {
      const resourceInput = document.getElementById(`resource_input_${index}`);
      const fileInfo = document.getElementById(`file-info_${index}`);
      const selectedType = selectElement.value;

      if (selectedType === 'url') {
        resourceInput.type = 'url';
        resourceInput.name = `resources[${index}][resource_url]`;
        resourceInput.accept = '';
      } else if (selectedType === 'youtube') {
        resourceInput.type = 'url';
        resourceInput.name = `resources[${index}][resource_url]`;
        resourceInput.accept = '';
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
          case 'url':
            resourceInput.accept = '';
            fileInfo.textContent = 'Link must be valid URL, e.g. https://www.google.com';
            break;
          case 'youtube':
            resourceInput.accept = '';
            fileInfo.textContent =
              'Link must be valid YouTube URL, e.g. https://www.youtube.com/watch?v=abc123';
            break;
          default:
            resourceInput.accept = '';
            fileInfo.textContent = '';
            break;
        }
      }
    }

    function removeExistingResource(index) {
      const row = document.getElementById(`resource-row-${index}`);
      if (row) {
        const resourceId = row.getAttribute('data-id');
        deletedResources.push(String(resourceId));
        updateDeletedResourcesInput();
        row.remove();
      }
    }

    function removeResource(index) {
      const resourceDiv = document.querySelector(`.resource[data-index="${index}"]`);
      resourceDiv.remove();
    }

    function updateDeletedResourcesInput() {
      document.getElementById('deleted_resources').value = JSON.stringify(deletedResources);
    }

    document.getElementById('main-form').addEventListener('submit', function(event) {
      updateDeletedResourcesInput();
      document.getElementById('existing_resources').value = JSON.stringify(existingResources);
    });
  </script>
</x-app-layout>

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
