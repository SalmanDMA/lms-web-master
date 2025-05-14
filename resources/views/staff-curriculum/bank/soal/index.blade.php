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
        Bank Soal
    </x-slot>

    <x-datatable title="Bank Soal">
        <div class="mb-4">
            <div class="row mb-4 align-items-center">
                <div class="col-12 d-flex justify-content-start">
                    <div class="mb-3 me-2">
                        <a href="{{ route('staff_curriculum.bank.v_add_soal') }}" class="btn btn-primary">
                            Buat Baru
                        </a>
                    </div>
                    {{-- <div class="mb-3 me-2">
                        <button type="button" class="btn btn-warning" id="btnBagikan" data-bs-toggle="modal"
                            data-bs-target="#modalBagikanSoal" disabled>
                            <i class="bi bi-upload"></i> Bagikan
                        </button>
                    </div> --}}
                    <div class="mb-3">
                        <button type="button" class="btn btn-danger" id="btnHapus" data-bs-toggle="modal"
                            data-bs-target="#modalDeleteBanyakSoal" disabled>
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    </div>
                </div>
            </div>

            <div class="row mb-4 align-items-center">
                <div class="col-12">
                    <div class="row g-3 w-100">
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Filter Pelajaran</label>
                                <select class="form-select" id="courseFilter" onchange="filterData()">
                                    <option value="" selected>- Pilih Mata Pelajaran -</option>
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}">{{ $course->courses_title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Filter Tingkat</label>
                                <select class="form-select" id="levelFilter" onchange="filterData()">
                                    <option value="" selected>- Pilih Tingkat -</option>
                                    @foreach ($levels as $level)
                                        <option value="{{ $level->id }}">{{ $level->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Filter Jenis Soal</label>
                                <select class="form-select" id="questionTypeFilter" onchange="filterData()">
                                    <option value="" selected>- Pilih Jenis -</option>
                                    @foreach ($question_type as $item)
                                        <option value="{{ $item }}">{{ $item }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Filter Kategori Soal</label>
                                <select class="form-select" id="questionCategoryFilter" onchange="filterData()">
                                    <option value="" selected>- Pilih Kategori -</option>
                                    @foreach ($question_category as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <table class="table" id="table-soal-bank">
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll"></th>
                    <th>Jenis Soal</th>
                    <th>Pertanyaan</th>
                    <th>Mata Pelajaran</th>
                    <th>Kategori</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="soal-body-table">
                @forelse ($question_bank_data as $item)
                    <tr>
                        <td><input type="checkbox" class="selectItem" value="{{ $item->id }}"></td>
                        <td data-question-type="{{ $item->question_type }}" data-question-category="{{ $item->category_id }}" data-course-id="{{ $item?->course?->id }}">{{ $item->question_type }}</td>
                        <td>{!! $item->question_text !!}</td>
                        <td data-question-type="{{ $item->question_type }}" data-question-category="{{ $item->category_id }}" data-course-id="{{ $item?->course?->id }}">{{ $item?->course?->courses_title }}</td>
                        <td data-question-type="{{ $item->question_type }}" data-question-category="{{ $item->category_id }}" data-course-id="{{ $item?->course?->id }}">{{ $item?->category?->name }}</td>
                        <td class="actions">
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('staff_curriculum.bank.v_edit_soal', $item->id) }}"
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
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Apakah Anda yakin ingin menghapus soal ini?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Batal</button>
                                    <form action="{{ route('staff_curriculum.bank.delete_soal', $item->id) }}" method="POST">
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

        <!-- Modal Hapus Banyak Soal -->
        <div class="modal fade" id="modalDeleteBanyakSoal" tabindex="-1"
            aria-labelledby="modalDeleteBanyakSoalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalDeleteBanyakSoalLabel">Hapus Banyak Soal</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p id="deleteConfirmationMessage">Anda yakin ingin menghapus <span id="item-count">0</span>
                            soal yang dipilih?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <form id="formDelete" action="{{ route('staff_curriculum.bank.delete_multiple_soal') }}"
                            method="POST">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" id="deleteIds" name="deleteIds">
                            <button type="submit" class="btn btn-danger">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Bagikan Soal -->
        {{-- <div class="modal fade" id="modalBagikanSoal" tabindex="-1" aria-labelledby="modalBagikanSoalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalBagikanSoalLabel">Bagikan Banyak Soal</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <form id="formBagikan" action="{{ route('staff_curriculum.bank.share_soal') }}" method="POST">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" id="selectedItems" name="selectedItems">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Jenis</th>
                                            <th>Pertanyaan</th>
                                            <th>Total Dibagikan</th>
                                            <th>Poin</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="previewBody">
                                        <!-- Preview akan diisi oleh JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Bagikan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div> --}}

    </x-datatable>

    <script>
        const questionBankData = @json($question_bank_data);

        $(document).ready(function() {
            const questionBankData = @json($question_bank_data);

            if (questionBankData.length > 0) {
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
                $('#btnBagikan').prop('disabled', selectedCount === 0);
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

            $('#btnBagikan').on('click', function() {
                var selectedItems = $('.selectItem:checked').map(function() {
                    return $(this).val();
                }).get();

                if (selectedItems.length === 0) {
                    alert('Silakan pilih soal yang ingin dibagikan.');
                    return;
                }

                var previewBody = $('#previewBody');
                previewBody.empty();

                var selectedItemData = selectedItems.map(function(itemId) {
                    var item = questionBankData.find(function(q) {
                        return q.id == itemId;
                    });

                    previewBody.append(`
            <tr data-id="${itemId}">
                <td>${item.question_type}</td>
                <td>${item.question_text}</td>
                <td>
                    ${item.shared_count > 0 ?
                        `<p class="m-0 text-primary font-bold"> ${item.shared_count} x ke kurikulum</p>
                                 <small class="m-0 text-muted">Pada : ${item.shared_at} </small>` :
                        `<small class="m-0 text-muted"> ${item.shared_at} </small>`}
                </td>
                <td><input type="number" class="form-control" name="points[${itemId}]" value="${item.point}" min="0"></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-item" data-id="${itemId}">Hapus</button></td>
            </tr>
        `);

                    return {
                        id: itemId,
                        question_type: item.question_type,
                        question_text: item.question_text,
                        shared_count: item.shared_count,
                        shared_at: item.shared_at,
                        point: item.point
                    };
                });

                $('#selectedItems').val(JSON.stringify(selectedItemData));

                $('.remove-item').on('click', function() {
                    var itemId = $(this).data('id');
                    $(this).closest('tr').remove();
                    $('.selectItem[value="' + itemId + '"]').prop('checked', false).trigger(
                        'change');
                    selectedItemData = selectedItemData.filter(function(item) {
                        return item.id !== itemId;
                    });
                    $('#selectedItems').val(JSON.stringify(selectedItemData));
                    toggleButtons();
                });

                $('#modalBagikanSoal').modal('show');
            });

            window.filterData = function() {
                var courseFilter = $('#courseFilter').val();
                var levelFilter = $('#levelFilter').val();
                var questionTypeFilter = $('#questionTypeFilter').val();
                var questionCategoryFilter = $('#questionCategoryFilter').val();
                var emptyRow = $('#empty-row');

                var hasVisibleRows = false;

                $('#soal-body-table tr').each(function() {
                    var row = $(this);
                    var courseCell = row.find('td[data-course-id]');
                    var levelCell = row.find('td[data-level-id]');
                    var questionTypeCell = row.find('td[data-question-type]');
                    var questionCategoryCell = row.find('td[data-question-category]');

                    if (courseCell.length && levelCell.length && questionTypeCell.length &&
                        questionCategoryCell.length) {
                        var courseText = courseCell.attr('data-course-id');
                        var levelText = levelCell.attr('data-level-id');
                        var questionTypeText = questionTypeCell.attr('data-question-type');
                        var questionCategoryText = questionCategoryCell.attr('data-question-category');

                        var courseMatch = courseFilter === "" || courseText.indexOf(courseFilter) !== -
                            1;
                        var levelMatch = levelFilter === "" || levelText.indexOf(levelFilter) !== -1;
                        var questionTypeMatch = questionTypeFilter === "" || questionTypeText.indexOf(
                            questionTypeFilter) !== -1;
                        var questionCategoryMatch = questionCategoryFilter === "" ||
                            questionCategoryText.indexOf(questionCategoryFilter) !== -1;

                        if (courseMatch && levelMatch && questionTypeMatch && questionCategoryMatch) {
                            row.show();
                            hasVisibleRows = true;
                        } else {
                            row.hide();
                        }
                    }
                });

                if (!hasVisibleRows) {
                    if (emptyRow.length === 0) {
                        $('#soal-body-table').append(
                            '<tr id="empty-row"><td colspan="5" class="text-center">Data tidak ditemukan. Silakan tambah tugas.</td></tr>'
                        );
                    }
                } else {
                    if (emptyRow.length > 0) {
                        emptyRow.remove();
                    }
                }
            }

            function initializeDataTable() {
                $('#table-soal-bank').DataTable({
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
        });
    </script>

    <style>
        .table .selected-tr {
            background-color: #e9ecef !important;
        }

        .table td.actions-hidden .btn {
            display: none;
        }
    </style>
</x-app-layout>
