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
    Tambah Soal
  </x-slot>

  <div class="alert" role="alert" id="alertMessage" style="display: none;">
  </div>

  <div class="card mt-3" id="mainCard">
    <div class="card-header bg-primary">
      <h3 class="mb-0 text-white">Tambah Soal</h3>
    </div>
    <div class="card-body mt-3">
      <form id="main-form" action="{{ route('staff_curriculum.sekolah.add_soal', ['id' => $ujian_id, 'section_id' => $section_id]) }}" method="POST"
        enctype="multipart/form-data">
        @csrf

        <!-- Tab Navigation -->
        <ul class="nav nav-tabs" id="questionTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details"
              type="button" role="tab" aria-controls="details" aria-selected="true">Pertanyaan
              Umum</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="choices-tab" data-bs-toggle="tab" data-bs-target="#choices" type="button"
              role="tab" aria-controls="choices" aria-selected="false">Pilihan
              Jawaban</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="resources-tab" data-bs-toggle="tab" data-bs-target="#resources" type="button"
              role="tab" aria-controls="resources" aria-selected="false">Lampiran</button>
          </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content mt-3" id="questionTabsContent">
          <!-- Question Details Tab -->
          <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
            <div class="mb-3">
              <label for="question_text" class="form-label">Pertanyaan<span class="text-danger">*</span></label>
              <textarea class="form-control ckeditor" id="question_text" name="question_text"></textarea>
              <div class="invalid-feedback">
                Pertanyaan harus diisi.
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6 mb-3 mb-md-0">
                <label for="category_id" class="form-label">Kategori Soal<span class="text-danger">*</span></label>
                <div class="input-group">
                  <select class="form-select" id="category_id" name="category_id">
                    <option value="" selected>- Pilih Kategori -</option>
                    @foreach ($question_category as $item)
                      <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                  </select>
                  <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                    data-bs-target="#manageCategoryModal" title="Kelola Kategori">
                    <i class="bi bi-gear-wide"></i>
                  </button>
                </div>
              </div>

              <div class="col-md-6">
                <label for="question_type" class="form-label">Jenis Soal<span class="text-danger">*</span></label>
                <select class="form-select" id="question_type" name="question_type" onchange="filterDataChoices()">
                  <option value="" selected>- Pilih Jenis -</option>
                  @foreach ($question_type as $item)
                    <option value="{{ $item }}">{{ $item }}</option>
                  @endforeach
                </select>
              </div>
            </div>

            <div class="mb-3 row" id="pointGradeContainer">
              <div class="col-md-12 mb-3">
                <label for="point" class="form-label">Point<span class="text-danger">*</span></label>
                <input class="form-control" type="number" id="point" name="point" min="1">
                <div class="invalid-feedback">
                  Point harus diisi.
                </div>
              </div>
              {{-- Tempat untuk grade method yang akan muncul jika jenis soal adalah Pilihan Ganda Complex --}}
            </div>

            <div class="row mb-3">
              <div class="col-md-6 mb-3 mb-md-0">
                <label for="difficult" class="form-label">Tingkat Kesulitan<span class="text-danger">*</span></label>
                <select class="form-select" id="difficult" name="difficult">
                  <option value="" selected>- Pilih Tingkat Kesulitan -</option>
                  <option value="Sangat Mudah">Sangat Mudah</option>
                  <option value="Mudah">Mudah</option>
                  <option value="Sedang">Sedang</option>
                  <option value="Sulit">Sulit</option>
                  <option value="Sangat Sulit">Sangat Sulit</option>
                </select>
              </div>
            </div>

          </div>

          <!-- Choices Tab -->
          <div class="tab-pane fade" id="choices" role="tabpanel" aria-labelledby="choices-tab">
            <div id="choicesContainer">
              <!-- Choices will be added dynamically here -->
            </div>
            <div id="choicesAction" class="d-flex justify-content-end">

            </div>
          </div>

          <!-- Resources Tab -->
          <div class="tab-pane fade" id="resources" role="tabpanel" aria-labelledby="resources-tab">
            <div id="resources-container">
              <div class="resource mb-3" data-index="0">
                <div class="mb-3">
                  <label class="form-label">Lampiran Tipe</label>
                  <select name="resources[0][file_type]" class="form-select" onchange="updateResourceType(0, this)">
                    <option value="">- Pilih Tipe Lampiran -</option>
                    <option value="audio">Audio</option>
                    <option value="image">Image</option>
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

            <div class="d-flex justify-content-end gap-2 mt-2">
              <a href="{{ url()->previous() }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i>
                Kembali</a>
              <button type="button" class="btn btn-success" onclick="addResource()"> <i class="bi bi-plus-lg"></i>
                Tambah
                Lampiran</button>
              <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
          </div>
        </div>


      </form>
    </div>
  </div>

  <!-- Modal Kelola Kategori -->
  <div class="modal fade" id="manageCategoryModal" tabindex="-1" aria-labelledby="manageCategoryModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="manageCategoryModalLabel">Kelola Kategori Soal</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <ul class="nav nav-tabs" id="categoryTabs" role="tablist">
            <li class="nav-item" role="presentation" id="listCategoryContainer">
              <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#list"
                type="button" role="tab" aria-controls="list" aria-selected="true">List Kategori</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="add-tab" data-bs-toggle="tab" data-bs-target="#add" type="button"
                role="tab" aria-controls="add" aria-selected="false">Tambah
                Kategori</button>
            </li>
            <li class="nav-item" role="presentation" id="editCategoryContainer" style="display: none;">
              <button class="nav-link" id="edit-tab" data-bs-toggle="tab" data-bs-target="#edit" type="button"
                role="tab" aria-controls="edit" aria-selected="false">Edit
                Kategori</button>
            </li>
          </ul>
          <div class="tab-content" id="categoryTabContent">
            <div class="tab-pane fade show active" id="list" role="tabpanel" aria-labelledby="list-tab">
              <ul class="list-group mt-3" id="categoryList">
                @foreach ($question_category as $item)
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    {{ $item->name }}
                    <div>
                      <button type="button" class="btn btn-warning btn-sm"
                        onclick="editCategory('{{ $item->id }}', '{{ $item->name }}')">Ubah</button>
                      <button type="button" class="btn btn-danger btn-sm"
                        onclick="confirmDeleteCategory('{{ $item->id }}')">Hapus</button>
                    </div>
                  </li>
                @endforeach
              </ul>
            </div>
            <div class="tab-pane fade" id="add" role="tabpanel" aria-labelledby="add-tab">
              <form id="addCategoryForm" class="mt-3" method="POST"
                action="{{ route('staff_curriculum.add_soal_kategori') }}">
                @csrf
                <div class="mb-3">
                  <label for="newCategoryName" class="form-label">Nama Kategori</label>
                  <input type="text" class="form-control" id="newCategoryName" name="newCategoryName" required>
                </div>
                <button type="submit" class="btn btn-primary">Tambah</button>
              </form>
            </div>
            <div class="tab-pane fade" id="edit" role="tabpanel" aria-labelledby="edit-tab">
              <form id="editCategoryForm" class="mt-3" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                  <label for="editCategoryName" class="form-label">Nama Kategori</label>
                  <input type="text" class="form-control" id="editCategoryName" name="editCategoryName" required>
                </div>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Konfirmasi Hapus Kategori -->
  <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="confirmDeleteModalLabel">Konfirmasi Hapus</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Apakah Anda yakin ingin menghapus kategori ini?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <form id="deleteCategoryForm" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" id="deleteCategoryButton">Hapus</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <a href="{{ route('staff_curriculum.detail_section', ['id' => $section_id]) }}" class="btn btn-primary">Kembali</a>
    </div>
  </div>

  <script src="https://cdn.ckeditor.com/ckeditor5/34.2.0/classic/ckeditor.js"></script>
  <script>
    $(document).ready(function() {
      let resourceIndex = 1;
      let choiceIndex = 0;
      let inputType = 'radio';

      var editCategoryUrl = `{{ route('staff_curriculum.edit_soal_kategori', ['id' => ':id']) }}`;
      var deleteCategoryUrl = `{{ route('staff_curriculum.delete_soal_kategori', ['id' => ':id']) }}`;

      ClassicEditor.create($('#question_text')[0]).catch(console.error);


      window.editCategory = function(categoryId, categoryName) {
        var url = editCategoryUrl.replace(':id', categoryId);

        $('#editCategoryName').val(categoryName);
        $('#editCategoryForm').attr('action', url);

        var tab = new bootstrap.Tab($('#edit-tab')[0]);
        tab.show();
        $('#editCategoryContainer').show();
      }

      window.confirmDeleteCategory = function(categoryId) {
        var url = deleteCategoryUrl.replace(':id', categoryId);

        $('#deleteCategoryForm').attr('action', url);
        var modal = new bootstrap.Modal($('#confirmDeleteModal')[0]);
        modal.show();
      }

      const showAlert = (message, type) => {
        $('#alertMessage').text(message).show().addClass(type);
        $('#mainCard').removeClass('mt-3');
        setTimeout(() => {
          $('#alertMessage').hide().removeClass(type);
          $('#mainCard').addClass('mt-3');
        }, 3000);
      };

      const initializeCKEditors = () => {
        // Pilih textarea yang belum memiliki class 'ckeditor-initialized'
        $('[id^="choices"][id$="[choice_text]"]:not(.ckeditor-initialized)').each(function() {
          ClassicEditor.create(this).then(editor => {
            $(this).addClass(
              'ckeditor-initialized');
          }).catch(console.error);
        });
      };

      const updateInputType = (questionType) => {
        inputType = ['Pilihan Ganda', 'True False'].includes(questionType) ? 'radio' : 'checkbox';
      };

      const generateChoiceDiv = (index, type, gradeMethod, isVisibleButton) => `
    <div class="choice-item mb-3">
        <div class="mb-2">
            <label for="choices[${index}][choice_text]" class="form-label">Pilihan Jawaban ${index + 1}</label>
            <textarea class="form-control ckeditor-choice-${index}" data-index="${index}" name="choices[${index}][choice_text]" id="choices[${index}][choice_text]"></textarea>
        </div>
        ${type === 'radio' ? `
                                                                                                                                                                                            <div class="form-check mb-3">
                                                                                                                                                                                                <input class="form-check-input" type="radio" name="correct_choice" id="choice_${index}" value="${index}">
                                                                                                                                                                                                <label class="form-check-label" for="choice_${index}">Tandai Sebagai Jawaban Benar</label>
                                                                                                                                                                                            </div>
                                                                                                                                                                                        ` : ''}
        ${type === 'checkbox' && gradeMethod === 'beberapa jawaban benar' ? `
                                                                                                                                                                                            <div class="form-check mb-3">
                                                                                                                                                                                                <input class="form-check-input" type="checkbox" name="correct_choice[${index}]" id="choice_${index}" value="${index}">
                                                                                                                                                                                                <label class="form-check-label" for="choice_${index}">Tandai Sebagai Jawaban Benar</label>
                                                                                                                                                                                            </div>
                                                                                                                                                                                        ` : ''}
        ${isVisibleButton ? '<button type="button" class="btn btn-danger" onclick="removeChoice(this)">Hapus Pilihan</button>' : ''}
    </div>
`;

      window.addChoice = () => {
        const $container = $('#choicesContainer');
        const questionType = $('#question_type').val();

        if (!questionType || !['Pilihan Ganda', 'Pilihan Ganda Complex', 'True False'].includes(
            questionType)) {
          showAlert('Pilih jenis soal terlebih dahulu', 'alert-warning');
          return;
        }

        if (questionType === 'True False') {
          showAlert('Tidak dapat menambah pilihan untuk tipe soal True False.', 'alert-warning');
          return;
        }

        // Use the current length of choices as the new choiceIndex
        const newChoiceIndex = $container.children().length;
        const newChoiceDiv = generateChoiceDiv(newChoiceIndex, inputType, $('#grade_method').val(),
          true);
        $container.append(newChoiceDiv);

        initializeCKEditors();

        // Increment choiceIndex after adding the new choice
        choiceIndex = newChoiceIndex + 1;

        if (['Pilihan Ganda', 'Pilihan Ganda Complex'].includes(questionType)) {
          generateButtonAddChoice();
        }
      };

      window.generateChoices = () => {
        const $container = $('#choicesContainer');
        const questionType = $('#question_type').val();
        const gradeMethod = $('#grade_method').val();

        updateInputType(questionType);

        // Clear existing choices
        $container.empty();
        choiceIndex = 0;

        for (let i = 0; i < 2; i++) {
          $container.append(generateChoiceDiv(choiceIndex, inputType, gradeMethod, false));
          choiceIndex++;
        }

        initializeCKEditors();

        if (['Pilihan Ganda', 'Pilihan Ganda Complex'].includes(questionType)) {
          generateButtonAddChoice();
        }
      };

      window.generateButtonAddChoice = () => {
        $('#choicesAction').html(
            '<button type="button" class="btn btn-primary">Tambah Pilihan</button>')
          .find('button').on('click', addChoice);
      };


      window.removeChoice = (button) => {
        if ($('.choice-item').length <= 2) {
          showAlert('Minimal 2 pilihan harus ada.', 'alert-warning');
          return;
        }
        $(button).parent().remove();
        choiceIndex--;
        generateButtonAddChoice();
      };

      window.filterDataChoices = () => {
        const questionType = $('#question_type').val();
        const gradeMethod = $('#grade_method').val();
        const $choicesTab = $('#choices-tab');
        const $resourcesTab = $('#resources-tab');
        const $choicesContainer = $('#choicesContainer');
        const $buttonChoiceContainer = $('#choicesAction');
        const $pointGradeContainer = $('#pointGradeContainer');

        $choicesContainer.empty();
        $buttonChoiceContainer.empty();
        choiceIndex = 0;

        updateInputType(questionType);

        if (['Pilihan Ganda', 'Pilihan Ganda Complex', 'True False', 'Essay'].includes(questionType)) {
          if (questionType === 'Essay') {
            $choicesTab.hide();
            $resourcesTab.show();
          } else if (questionType === 'Pilihan Ganda Complex') {
            $choicesTab.hide();
            $resourcesTab.hide();
          } else {
            $choicesTab.show();
            $resourcesTab.show();
            generateChoices();
          }

          $pointGradeContainer.html(questionType === 'Pilihan Ganda Complex' ? `
                <div class="col-md-6 mb-3">
                    <label for="point" class="form-label">Point <span class="text-danger">*</span></label>
                    <input class="form-control" type="number" id="point" name="point" min="1">
                    <div class="invalid-feedback">Point harus diisi.</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="grade_method" class="form-label">Metode Penilaian<span
                                        class="text-danger">*</span></label>
                    <select class="form-select" id="grade_method" name="grade_method" onchange="handleGradMethod()">
                        <option value="" selected>- Pilih Metode Penilaian -</option>
                        <option value="wajib benar semua">Wajib Benar Semua</option>
                        <option value="beberapa jawaban benar">Beberapa Jawaban Benar</option>
                    </select>
                </div>
            ` : `
                <div class="col-md-12 mb-3">
                    <label for="point" class="form-label">Point <span class="text-danger">*</span></label>
                    <input class="form-control" type="number" id="point" name="point" min="1">
                    <div class="invalid-feedback">Point harus diisi.</div>
                </div>
            `);
        } else {
          $choicesTab.hide();
          $resourcesTab.hide();
          $pointGradeContainer.html(`
                <div class="col-md-12 mb-3">
                    <label for="point" class="form-label">Point <span class="text-danger">*</span></label>
                    <input class="form-control" type="number" id="point" name="point" min="1">
                    <div class="invalid-feedback">Point harus diisi.</div>
                </div>
            `);
        }
      };

      window.handleGradMethod = () => {
        generateButtonAddChoice();
        generateChoices();
        $('#choices-tab').show();
        $('#resources-tab').show();
      };

      window.addResource = () => {
        const resourceContainer = $('#resources-container');
        const newResource = `
            <div class="resource mb-3" data-index="${resourceIndex}">
                <div class="mb-3">
                    <label class="form-label">Lampiran Tipe</label>
                    <select name="resources[${resourceIndex}][file_type]" class="form-select" onchange="updateResourceType(${resourceIndex}, this)">
                        <option value="">- Pilih Tipe Lampiran -</option>
                        <option value="audio">Audio</option>
                        <option value="image">Image</option>
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
            <button type="button" class="btn btn-danger" onclick="removeResource(${resourceIndex})"><i class="bi bi-trash"></i> Remove</button>
        </div>
    `;
        resourceContainer.append(newResource);
        resourceIndex++;
      };

      window.removeResource = (index) => {
        $(`.resource[data-index="${index}"]`).remove();
      };

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
      };

      $('#question_type').on('change', filterDataChoices);
      filterDataChoices();
    });
  </script>

</x-app-layout>
