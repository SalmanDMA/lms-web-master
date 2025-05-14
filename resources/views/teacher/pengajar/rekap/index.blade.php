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
    Rekap Nilai
  </x-slot>

  <div class="card mt-3">
    <div class="card-header bg-primary text-white">
      <div class="d-flex align-items-center justify-content-between">
        <span class="mb-0 fs-2 fw-bold">Rekap Nilai</span>
        <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#selectSubjectModal">Pilih
          Mata Pelajaran</button>
      </div>
    </div>
    <div class="card-body">
      <div id="alertMessage" class="alert alert-warning mt-4">
        Silakan pilih mata pelajaran terlebih dahulu.
      </div>
      <div id="classesContainer" class="row mt-4">
        <!-- Kartu-kartu kelas akan dimuat di sini secara dinamis -->
      </div>
    </div>
  </div>

  <!-- Modal untuk memilih mata pelajaran -->
  <div class="modal fade" id="selectSubjectModal" tabindex="-1" aria-labelledby="selectSubjectModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="selectSubjectModalLabel">Pilih Mata Pelajaran</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="selectSubjectForm">
          <div class="modal-body">
            <div class="mb-3">
              <label for="subjectSelect" class="form-label">Mata Pelajaran</label>
              <select class="form-select" id="subjectSelect" required>
                <option value="" selected disabled>Pilih Mata Pelajaran</option>
                @foreach ($courses as $course)
                  <option value="{{ $course['course']['id'] }}">{{ $course['course']['name'] }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Pilih</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    $(document).ready(function() {
      const filteredTeacherSubclassesByLearning = @json($filteredTeacherSubclassesByLearning);

      const randomColors = [
        'rgba(255, 99, 132, 0.2)',
        'rgba(54, 162, 235, 0.2)',
        'rgba(255, 206, 86, 0.2)',
        'rgba(75, 192, 192, 0.2)',
        'rgba(153, 102, 255, 0.2)',
        'rgba(255, 159, 64, 0.2)'
      ];

      function getRandomColor() {
        return randomColors[Math.floor(Math.random() * randomColors.length)];
      }

      loadClasses();

      $('#selectSubjectForm').on('submit', function(e) {
        e.preventDefault();
        var selectedSubject = $('#subjectSelect').val();
        if (selectedSubject) {
          loadClasses(selectedSubject);
          $('#selectSubjectModal').modal('hide');
        } else {
          alert('Silakan pilih mata pelajaran.');
        }
      });

      function loadClasses(subjectId = null) {
        const filteredClasses = subjectId ?
          filteredTeacherSubclassesByLearning.filter(cls => cls.course_id === subjectId) :
          filteredTeacherSubclassesByLearning;

        if (filteredClasses.length > 0) {
          $('#alertMessage').hide();
          $('#classesContainer').html('');

          filteredClasses.forEach(cls => {
            const detailAssignmentRoute =
              "{{ route('teacher.pengajar.rekap.v_tugas', ':learning_id') }}";
            const detailAssignmentUrl = detailAssignmentRoute.replace(':learning_id', cls.learning_id);

            const detailClassExamRoute =
              "{{ route('teacher.pengajar.rekap.v_ulangan', ':learning_id') }}";
            const detailClassExamUrl = detailClassExamRoute.replace(':learning_id', cls.learning_id);

            const subclassesHTML = `
            <div class="col-12 col-md-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex gap-3 align-items-center bg-primary text-white">
                        ${cls.teacher_profile ?
                            `<img src="${cls.teacher_profile}" alt="Avatar" class="rounded-circle" style="width: 100px; height: 100px">`
                            :
                        `<div class="bg-secondary rounded-circle">
                              <i class="bi bi-person-fill text-white d-flex justify-content-center align-items-center" style="width: 100px; height: 100px; font-size: 50px"></i>
                          </div>`
                        }
                        <div>
                            <h5 class="card-title mb-0">${cls.teacher || 'No Teacher'}</h5>
                            <small>${cls.class_name || 'No Class Name'} - ${cls.subclassName || 'No Subclass Name'} ( ${cls.course_name || 'No Course Name'} )</small>
                        </div>
                    </div>
                    <div class="card-body border rounded">
                        <a href="${detailAssignmentUrl || '#'}" class="info-box d-flex align-items-center my-3">
                            <div class="info-icon bg-success text-white d-flex align-items-center justify-content-center rounded-circle me-3">
                                <i class="bi bi-list-task d-flex justify-content-center align-items-center fs-2"></i>
                            </div>
                            <div class="info-text">
                                <h6 class="mb-0">Tugas</h6>
                                <p class="mb-0">${cls.assignments.length}</p>
                            </div>
                        </a>
                        <a href="${detailClassExamUrl || '#'}" class="info-box d-flex align-items-center">
                            <div class="info-icon bg-warning text-white d-flex align-items-center justify-content-center rounded-circle me-3">
                                <i class="bi bi-pencil d-flex justify-content-center align-items-center fs-2"></i>
                            </div>
                            <div class="info-text">
                                <h6 class="mb-0">Ulangan</h6>
                                <p class="mb-0">${cls.class_exams.length}</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>`;

            $('#classesContainer').append(subclassesHTML);
          });
        } else {
          $('#alertMessage').text(
              'Data tidak ditemukan untuk mata pelajaran yang dipilih. Silakan pilih mata pelajaran lain.'
            )
            .removeClass('alert-warning')
            .addClass('alert-danger')
            .show();
          $('#classesContainer').html('');
        }
      }
    });
  </script>

  <style>
    .info-box {
      border: 1px solid #ddd;
      padding: 10px;
      border-radius: 5px;
    }

    .info-icon {
      width: 50px;
      height: 50px;
    }
  </style>
</x-app-layout>
