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
    Draft RPP
  </x-slot>

  <x-breadcrumb :title="$customTheme->title ?? 'Nama Sekolah'" :subtitle="'TMB Learning Management System'" :breadcrumbs="[['label' => 'Draft RPP', 'url' => null]]" :show-notifications="true" :unread-notifications="$unreadNotifications"
    :customTheme="$customTheme" />

  <x-datatable title="Draft RPP" :customTheme="$customTheme">
    <div class="row mb-4">
      <div class="col-md-6">
        <div class="mb-3">
          <button type="button" class="btn btn-primary-custom" data-bs-toggle="modal"
            data-bs-target="#addDraftRppModal">
            Tambah Draft RPP
          </button>
        </div>
      </div>
      <div class="col-md-6">
        <div class="mb-3">
          <label class="form-label font-custom">Filter</label>
          <select class="form-select form-select-custom" id="courseFilter" onchange="filterCourse()">
            <option value="" selected>Pilih Mata Pelajaran</option>
            @foreach ($courses as $course)
              <option value="{{ $course->id }}">{{ $course->courses_title }}</option>
            @endforeach
          </select>
        </div>
      </div>
    </div>
    <table class="table" id="table-rpp-bank">
      <thead>
        <tr>
          <th class="font-custom">#</th>
          <th class="font-custom">Judul</th>
          <th class="font-custom">Aksi</th>
        </tr>
      </thead>
      <tbody id="rppTableBodyData" style="display: none">
      </tbody>
      <tbody id="rppTableBodyNoData">
        <tr id="noCourseSelectedMessage">
          <td colspan="3" class="text-center font-custom">Silakan pilih mata pelajaran</td>
        </tr>
        <tr id="noDataRow" style="display: none">
          <td colspan="3" class="text-center font-custom">Tidak ada data yang ditemukan</td>
        </tr>
        <tr id="loadingIndicator" style="display: none">
          <td colspan="3" class="text-center my-3">
            <div class="spinner-border spinner-custom" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <div class="font-custom">Loading...</div>
          </td>
        </tr>
      </tbody>
    </table>
  </x-datatable>

  <!-- Modal for adding Draft RPP -->
  <div class="modal fade" id="addDraftRppModal" tabindex="-1" aria-labelledby="addDraftRppModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content modal-content-custom">
        <div class="modal-header">
          <h5 class="modal-title font-custom" id="addDraftRppModalLabel">Tambah Draft RPP</h5>
          <button type="button" class="btn btn-close-custom" data-bs-dismiss="modal">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
        <form id="addDraftRppForm" action="/teacher/bank/rpp" method="POST">
          <div class="modal-body">
            @csrf
            <div class="mb-3">
              <label for="course" class="form-label font-custom">Mata Pelajaran <span
                  class="text-danger">*</span></label>
              <select class="form-select form-select-custom" id="course" name="course">
                <option value="" selected>- Pilih Pelajaran -</option>
                @foreach ($courses as $course)
                  <option value="{{ $course->id }}">{{ $course->courses_title }}</option>
                @endforeach
              </select>
              <div class="invalid-feedback">
                Mata pelajaran harus dipilih.
              </div>
            </div>
            <div class="mb-3">
              <label for="level" class="form-label font-custom">Tingkat <span class="text-danger">*</span></label>
              <select class="form-select form-select-custom" id="level" name="level">
                <option value="" selected>- Pilih Tingkatan -</option>
                @foreach ($levels as $level)
                  <option value="{{ $level->id }}">{{ $level->name }}</option>
                @endforeach
              </select>
              <div class="invalid-feedback">
                Tingkat harus dipilih.
              </div>
            </div>

            <div class="mb-3">
              <label for="academic_year" class="form-label font-custom">Filter Tahun Ajaran</label>
              <select class="form-select form-select-custom" id="academic_year" name="academic_year">
                <option value="" selected>Pilih Tahun Ajaran</option>
                @foreach ($academic_years as $item)
                  <option value="{{ $item->id }}">{{ $item->year }}</option>
                @endforeach
              </select>
            </div>

            <div class="mb-3">
              <label for="draftName" class="form-label font-custom">Nama Draft <span
                  class="text-danger">*</span></label>
              <input type="text" class="form-control form-input-custom" id="draftName" name="draftName"
                value="">
              <div class="invalid-feedback">
                Nama draft harus diisi.
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label font-custom">Semester<span class="text-danger">*</span></label>
              <select class="form-select form-select-custom" id="semester" name="semester">
                <option value="" selected disabled>- Pilih Semester -</option>
                <option value="ganjil">Ganjil</option>
                <option value="genap">Genap</option>
              </select>
              <div class="invalid-feedback">
                Tahun Ajaran harus dipilih.
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary-custom">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    $(document).ready(function() {
      const draftNameInput = document.getElementById('draftName');
      const now = new Date();
      const formattedDate = now.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
      });
      const formattedTime = now.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit'
      });
      draftNameInput.value = `Draft RPP ${formattedDate} <${formattedTime}>`;

      generateCustomTheme();
    })

    function initializeDataTable() {
      $('#table-rpp-bank').DataTable({
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
        },
        initComplete: function() {
          applyCustomStyles();
        },
        drawCallback: function() {
          applyCustomStyles();
        }
      });
    }

    function generateAcademicYears() {
      const academicYears = @json($academic_years);
      return academicYears
    }

    const academicYearsOptions = generateAcademicYears().map(year => `<option value="${year.id}">${year.year}</option>`)
      .join(
        '');

    const filterCourse = async () => {
      const selectedCourseId = $('#courseFilter').val();
      const tableBody = $('#rppTableBodyData');
      const noCourseSelectedMessage = $('#noCourseSelectedMessage');
      const noDataRow = $('#noDataRow');
      const token = $('meta[name="api-token"]').attr('content');
      const loadingIndicator = $('#loadingIndicator');
      const courses = @json($courses);
      const levels = @json($levels);

      if (!selectedCourseId) {
        tableBody.hide();
        noCourseSelectedMessage.show();
        noDataRow.hide();
        return;
      }

      try {
        loadingIndicator.show();
        tableBody.hide();
        noCourseSelectedMessage.hide();
        noDataRow.hide();

        const response = await fetch(`/api/v1/cms/teacher/rpp-bank`, {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + token
          },
        });

        if (!response.ok) {
          throw new Error('Error fetching data');
        }

        const data = await response.json();
        const filteredData = data.data.filter(item => item.courses === selectedCourseId);

        $('#table-rpp-bank').DataTable().destroy();

        if (filteredData.length > 0) {
          tableBody.empty();
          filteredData.forEach((item, index) => {
            const row = `
                    <tr>
                        <td class="font-custom">${index + 1}</td>
                        <td class="font-custom">${item.draft_name}</td>
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                <button type="button" data-bs-toggle="modal" data-bs-target="#editDraftRppModal${index}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-pencil-square"></i> Ubah
                                </button>
                                <button type="button" data-bs-toggle="modal" data-bs-target="#deleteDraftRppModal${index}" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                                <a href="/teacher/bank/rpp/${item.id}/subject" class="btn btn-success btn-sm">
                                    <i class="bi bi-eye"></i> Lihat
                                </a>
                                <button type="button" data-bs-toggle="modal" data-bs-target="#submitDraftRppModal${index}" class="btn btn-warning btn-sm">
                                    <i class="bi bi-upload"></i> Ajukan
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            tableBody.append(row);

            // Modal Ubah (Edit)
            const editModalContent = `
                    <div class="modal fade" id="editDraftRppModal${index}" tabindex="-1" aria-labelledby="editDraftRppModal${index}Label" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content modal-content-custom">
                                <div class="modal-header">
                                    <h5 class="modal-title font-custom" id="editDraftRppModal${index}Label">Ubah Draft RPP</h5>
                                    <button type="button" class="btn btn-close-custom" data-bs-dismiss="modal">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                                <form action="/teacher/bank/rpp/${item.id}" method="POST">
                                    <div class="modal-body">
                                        @csrf
                                        @method('PUT')
                                        <div class="mb-3">
                                            <label for="editCourse${index}" class="form-label font-custom">Mata Pelajaran <span class="text-danger">*</span></label>
                                            <select class="form-select form-select-custom" id="editCourse${index}" name="course">
                                                <option value="">- Pilih Pelajaran -</option>
                                                ${courses.map(course => `<option value="${course.id}" ${course.id === item.courses ? 'selected' : ''}>${course.courses_title}</option>`).join('')}
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="editLevel${index}" class="form-label font-custom">Tingkat <span class="text-danger">*</span></label>
                                            <select class="form-select form-select-custom" id="editLevel${index}" name="level">
                                                <option value="">- Pilih Tingkatan -</option>
                                                ${levels.map(level => `<option value="${level.id}" ${level.id === item.class_level ? 'selected' : ''}>${level.name}</option>`).join('')}
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="editDraftName${index}" class="form-label font-custom">Nama Draft <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control form-input-custom" id="editDraftName${index}" name="draftName" value="${item.draft_name}">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary-custom">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                `;
            $('body').append(editModalContent);

            // Modal Hapus
            const deleteModalContent = `
                    <div class="modal fade" id="deleteDraftRppModal${index}" tabindex="-1" aria-labelledby="deleteDraftRppModal${index}Label" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content modal-content-custom">
                                <div class="modal-header">
                                    <h5 class="modal-title font-custom" id="deleteDraftRppModal${index}Label">Konfirmasi Hapus Draft RPP</h5>
                                    <button type="button" class="btn btn-close-custom" data-bs-dismiss="modal">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                                <div class="modal-body font-custom">
                                    Apakah Anda yakin ingin menghapus draft RPP ini?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                                    <form action="/teacher/bank/rpp/${item.id}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-primary-custom">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            $('body').append(deleteModalContent);

            // Modal Ajukan
            const ajukanModalContent = `
                    <div class="modal fade" id="submitDraftRppModal${index}" tabindex="-1" aria-labelledby="submitDraftRppModal${index}Label" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content modal-content-custom">
                                <div class="modal-header">
                                    <h5 class="modal-title font-custom" id="submitDraftRppModal${index}Label">Ajukan RPP</h5>
                                    <button type="button" class="btn btn-close-custom" data-bs-dismiss="modal">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                                <form action="/teacher/bank/rpp/ajukan" method="POST">
                                    <div class="modal-body">
                                        @csrf
                                        <input type="hidden" id="hiddenId${index}" name="id" value="${item.id}">
                                        <div class="mb-3">
                                            <label for="ajukanAcademicYear${index}" class="form-label font-custom">Tahun Pelajaran <span class="text-danger">*</span></label>
                                            <select class="form-select form-select-custom" id="ajukanAcademicYear${index}" name="academicYear">
                                                <option value="">- Pilih Tahun Pelajaran -</option>
                                                ${academicYearsOptions}
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="ajukanSemester${index}" class="form-label font-custom">Semester <span class="text-danger">*</span></label>
                                            <select class="form-select form-select-custom" id="ajukanSemester${index}" name="semester">
                                                <option value="">- Pilih Semester -</option>
                                                <option value="ganjil">Ganjil</option>
                                                <option value="genap">Genap</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary-custom">Ajukan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                `;
            $('body').append(ajukanModalContent);
          });

          tableBody.show();
          noCourseSelectedMessage.hide();
          noDataRow.hide();
          initializeDataTable();
        } else {
          tableBody.hide();
          noCourseSelectedMessage.hide();
          noDataRow.show();
        }

        generateCustomTheme();
      } catch (error) {
        console.error('Error fetching data:', error);
      } finally {
        loadingIndicator.hide();
      }
    };
  </script>
</x-app-layout>
