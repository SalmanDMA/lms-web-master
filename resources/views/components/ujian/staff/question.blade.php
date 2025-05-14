@props(['exam', 'section', 'questions', 'bankQuestions', 'classLevels', 'questionTypes', 'questionCategories'])
<div id="question-main">

  @if ($questions->isEmpty())
    <div class="alert alert-info mb-4">
      <p>Tidak ada soal yang ditemukan. Anda dapat menambah soal dari bank soal atau membuat soal baru.</p>
    </div>

    <div class="row">
      <div class="col-md-6 mb-4">
        <div class="card border text-center">
          <div class="card-body">
            <h5 class="card-title text-primary">Tambah Soal Baru</h5>
            <p class="card-text">Tambahkan soal baru secara langsung ke dalam sistem.</p>
            <a href="{{ route('staff_curriculum.sekolah.v_add_soal', ['id' => $exam->id, 'section_id' => $section->id]) }}"
              class="btn btn-secondary">Tambah
              Soal Baru</a>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card border text-center">
          <div class="card-body">
            <h5 class="card-title text-primary">Tambah Dari Bank Soal</h5>
            <p class="card-text">Import soal dari bank soal yang sudah ada.</p>
            <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addFromBankModal">Tambah Dari Bank
              Soal</button>
          </div>
        </div>
      </div>
    </div>
  @else
    <x-datatable title="Daftar Soal">
      <div class="row mb-4 align-items-center">
        <div class="col-12 d-flex justify-content-start">
          <div class="mb-3 me-2">
            <div class="dropdown">
              <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton"
                data-bs-toggle="dropdown" aria-expanded="false">
                Buat Soal
              </button>
              <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                <li>
                  <a class="dropdown-item" href="{{ route('staff_curriculum.sekolah.v_add_soal', ['id' => $exam->id, 'section_id' => $section->id]) }}">
                    Buat Langsung
                  </a>
                </li>
                <li>
                  <button type="button" class="dropdown-item" data-bs-toggle="modal"
                    data-bs-target="#addFromBankModal">
                    Import dari Bank Soal
                  </button>
                </li>
              </ul>
            </div>
          </div>
          <div class="mb-3">
            <button type="button" class="btn btn-danger" id="btnHapus" data-bs-toggle="modal"
              data-bs-target="#modalDeleteBanyakSoal" disabled>
              <i class="bi bi-trash"></i> Hapus
            </button>
          </div>
        </div>
      </div>

      <table class="table" id="table-soal">
        <thead>
          <tr>
            <th><input type="checkbox" id="selectAll"></th>
            <th>Jenis Soal</th>
            <th>Pertanyaan</th>
            <th>Kategori</th>
            <th>Point</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody id="soal-body-table">
          @forelse ($questions as $item)
            <tr>
              <td><input type="checkbox" class="selectItem" value="{{ $item->id }}"></td>
              <td>{{ $item->question_type }}</td>
              <td>{!! $item->question_text !!}</td>
              <td>{{ $item->category_name }}</td>
              <td>{{ $item->point }}</td>
              <td class="actions">
                <div class="d-flex justify-content-center gap-2">
                  <a href="{{ route('staff_curriculum.sekolah.v_edit_soal', ['id' => $exam->id, 'section_id' => $section->id, 'soal_id' => $item->id]) }}"
                    class="btn btn-primary btn-sm">
                    <i class="bi bi-pencil-square"></i> Ubah
                  </a>
                  <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                    data-bs-target="#modalDeleteSoal{{ $item->id }}">
                    <i class="bi bi-trash"></i> Hapus
                  </button>
                </div>
              </td>
            </tr>

            {{-- Modal Hapus Soal --}}
            <div class="modal fade" id="modalDeleteSoal{{ $item->id }}" tabindex="-1"
              aria-labelledby="modalDeleteSoalLabel{{ $item->id }}" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="modalDeleteSoalLabel{{ $item->id }}">Konfirmasi
                      Hapus Soal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    Apakah Anda yakin ingin menghapus soal ini?
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form
                      action="{{ route('staff_curriculum.sekolah.delete_soal', ['id' => $exam->id, 'section_id' => $section->id, 'soal_id' => $item->id]) }}"
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
            <tr id="empty-row">
              <td colspan="5" class="text-center">Data tidak ditemukan. Silakan tambah tugas.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </x-datatable>
  @endif


  <!-- Modal Hapus Banyak Soal -->
  <div class="modal fade" id="modalDeleteBanyakSoal" tabindex="-1" aria-labelledby="modalDeleteBanyakSoalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalDeleteBanyakSoalLabel">Hapus Banyak Soal</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p id="deleteConfirmationMessage">Anda yakin ingin menghapus <span id="item-count">0</span>
            soal yang dipilih?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <form id="formDelete"
            action="{{ route('staff_curriculum.sekolah.delete_multiple_soal', ['id' => $exam->id, 'section_id' => $section->id]) }}" method="POST">
            @csrf
            @method('DELETE')
            <input type="hidden" id="deleteIds" name="deleteIds">
            <button type="submit" class="btn btn-danger">Hapus</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  {{-- Modal Import Dari Bank Soal --}}
  <div class="modal fade" id="addFromBankModal" tabindex="-1" aria-labelledby="addFromBankModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <form action="{{ route('staff_curriculum.sekolah.import_soal', ['id' => $exam->id, 'section_id' => $section->id]) }}" method="POST">
          @csrf
          <div class="modal-header">
            <h5 class="modal-title" id="addFromBankModalLabel">Tambah Dari Bank Soal</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row mb-3">
              <div class="mb-3 mb-md-0 col-12 col-md-4">
                <label for="question_type_filter" class="form-label">Jenis Soal</label>
                <select class="form-select" id="question_type_filter">
                  <option value="">- Semua Jenis Soal -</option>
                  @foreach ($questionTypes as $type)
                    <option value="{{ $type }}">{{ $type }}</option>
                  @endforeach
                </select>
              </div>
              <div class="mb-3 mb-md-0 col-12 col-md-4">
                <label for="class_level_filter" class="form-label">Tingkatan</label>
                <select class="form-select" id="class_level_filter">
                  <option value="">- Semua Tingkatan -</option>
                  @foreach ($classLevels as $level)
                    <option value="{{ $level->id }}">{{ $level->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-12 col-md-4">
                <label for="category_filter" class="form-label">Kategori</label>
                <select class="form-select" id="category_filter">
                  <option value="">- Semua Kategori -</option>
                  @foreach ($questionCategories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table table-striped" id="questionsTable">
                <thead>
                  <tr>
                    <th scope="col">Pilih</th>
                    <th scope="col">Pertanyaan</th>
                    <th scope="col">Jenis Soal</th>
                    <th scope="col">Kategori</th>
                    <th scope="col">Tingkat Kesulitan</th>
                  </tr>
                </thead>
                <tbody id="questionsContainer">
                  <!-- Konten tabel akan diisi oleh JavaScript -->
                </tbody>
              </table>
            </div>
            <div id="noDataMessage" class="alert alert-warning" style="display: none;">
              Data tidak ditemukan. Silakan coba filter dengan kriteria yang lain.
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Import Soal</button>
          </div>
        </form>
      </div>
    </div>
  </div>


</div>

<script>
  $(document).ready(function() {
    const question = @json($questions);
    const bankQuestions = @json($bankQuestions);

    if (question.length > 0) {
      initializeDataTable();
    }

    $('#selectAll').change(function() {
      $('.selectItem').prop('checked', $(this).prop('checked')).trigger('change');
    });

    $('.selectItem').change(function() {
      if ($('.selectItem:checked').length == $('.selectItem').length) {
        $('#selectAll').prop('checked', true);
      } else {
        $('#selectAll').prop('checked', false);
      }
      toggleButtons();
    });

    function toggleButtons() {
      var selectedCount = $('.selectItem:checked').length;
      $('#btnHapus').prop('disabled', selectedCount === 0);
    }

    $('.selectItem').on('change', function() {
      var row = $(this).closest('tr');
      if ($(this).is(':checked')) {
        row.addClass('selected-tr');
        row.find('td.actions').addClass('actions-hidden');
      } else {
        row.removeClass('selected-tr');
        row.find('td.actions').removeClass('actions-hidden');
      }
    });

    $('#btnHapus').on('click', function() {
      var selectedIds = $('.selectItem:checked').map(function() {
        return $(this).val();
      }).get();

      $('#item-count').text(selectedIds.length);
      $('#deleteIds').val(JSON.stringify(selectedIds));
      $('#modalDeleteBanyakSoal').modal('show');
    });

    window.filterQuestions = function() {
      const questionType = $('#question_type_filter').val();
      const classLevel = $('#class_level_filter').val();
      const category = $('#category_filter').val();

      const x = Array.isArray(bankQuestions) ? bankQuestions : Object.values(bankQuestions)
      const filteredQuestions = x.filter(question => {
        return (questionType === '' || question.question_type === questionType) &&
          (classLevel === '' || question.class_level == classLevel) &&
          (category === '' || question.category_id === category);
      });

      renderQuestions(filteredQuestions);
    }

    window.renderQuestions = function(questions) {
      questions = Array.isArray(questions) ? questions : Object.values(questions)
      
      const $container = $('#questionsContainer');
      $container.empty();

      if (questions.length === 0) {
        $('#noDataMessage').show();
      } else {
        $('#noDataMessage').hide();
      }

      questions.forEach(question => {
        const questionHtml = `
            <tr>
                <td>
                    <input class="form-check-input" type="checkbox" name="bank_question_ids[]" value="${question.id}" id="question_${question.id}">
                </td>
                <td>${question.question_text}</td>
                <td>${question.question_type}</td>
                <td>${question.category_name}</td>
                <td>
                    <select class="form-select" name="difficulty_levels[${question.id}]" id="difficulty_level_${question.id}" disabled>
                        <option value="" selected>- Pilih Tingkat Kesulitan -</option>
                        <option value="Sangat Mudah">Sangat Mudah</option>
                        <option value="Mudah">Mudah</option>
                        <option value="Sedang">Sedang</option>
                        <option value="Sulit">Sulit</option>
                        <option value="Sangat Sulit">Sangat Sulit</option>
                    </select>
                </td>
            </tr>
        `;
        $container.append(questionHtml);

        $(`#question_${question.id}`).on('change', function() {
          const $difficultySelect = $(`#difficulty_level_${question.id}`);
          if ($(this).is(':checked')) {
            $difficultySelect.prop('disabled', false);
          } else {
            $difficultySelect.prop('disabled', true);
            $difficultySelect.val('');
          }
        });
      });
    }

    $('#question_type_filter, #class_level_filter, #category_filter').on('change', filterQuestions);

    renderQuestions(bankQuestions);

    function initializeDataTable() {
      $('#table-soal').DataTable({
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
  })
</script>

<style>
  .card-body {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }
</style>
