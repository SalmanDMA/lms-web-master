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
  Kelas Ajar
 </x-slot>

 <x-datatable title="Kelas Ajar">
  <div class="row mb-4 align-items-center">
   <div class="col-12 col-sm-4 col-md-3 d-flex justify-content-start">
    <div class="mb-3">
     <button type="button" data-bs-toggle="modal" data-bs-target="#addKelasAjarModal" class="btn btn-primary">
      Tambah Kelas Ajar
     </button>
    </div>
   </div>

   <div class="col-12 col-sm-8 col-md-9 d-sm-flex justify-content-sm-end">
    <div class="col-12 col-sm-8 row g-3">
     <div class="col-12 col-sm-6">
      <div class="mb-3">
       <label class="form-label">Filter Pelajaran</label>
       <select class="form-select" id="courseFilter" onchange="filterCourse()">
        <option value="" selected>Pilih Mata Pelajaran</option>
        @foreach ($courses as $course)
         <option value="{{ $course->id }}">{{ $course->courses_title }}</option>
        @endforeach
       </select>
      </div>
     </div>
     <div class="col-12 col-sm-6">
      <div class="mb-3">
       <label class="form-label">Filter Tingkat</label>
       <select class="form-select" id="levelFilter" onchange="filterLevel()">
        <option value="" selected>Pilih Tingkat</option>
        @foreach ($levels as $level)
         <option value="{{ $level->id }}">{{ $level->name }}</option>
        @endforeach
       </select>
      </div>
     </div>
    </div>
   </div>
  </div>

  <table class="table" id="table-kelas-ajar">
   <thead>
    <tr>
     <th>#</th>
     <th>Tingkat</th>
     <th>Kelas</th>
     <th>Mata Pelajaran</th>
     <th>Aksi</th>
    </tr>
   </thead>
   <tbody id="table-body">
    @forelse ($enrollmentData as $item)
     <tr data-course-id="{{ $item->course_id }}" data-level-name="{{ $item->class_id }}">
      <td>{{ $loop->iteration }}</td>
      <td>{{ $item->class_name }}</td>
      <td>{{ $item->sub_class_name }}</td>
      <td>{{ $item->course_name }}</td>
      <td>
       <div class="d-flex justify-content-center gap-2">
        <a
         href="{{ route('teacher.pengajar.v_kelas_ajar_student', ['course_id' => $item->course_id, 'sub_class_id' => str_replace('/', '-', $item->sub_class_id)]) }}"
         class="btn btn-primary btn-sm">
         <i class="bi bi-people-fill"></i> Siswa
        </a>
       </div>
      </td>
     </tr>
    @empty
     <tr id="emptyMessageRow">
      <td colspan="5" class="text-center">Data tidak ditemukan. Silakan tambah materi.</td>
     </tr>
    @endforelse
   </tbody>
  </table>
 </x-datatable>

 <!-- Modal for adding Draft RPP -->
 <div class="modal fade" id="addKelasAjarModal" tabindex="-1" aria-labelledby="addKelasAjarModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
   <div class="modal-content">
    <div class="modal-header">
     <h5 class="modal-title" id="addKelasAjarModalLabel">Tambah Kelas Ajar</h5>
     <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <form id="addKelasAjarForm" action="{{ route('teacher.pengajar.add_kelas_ajar') }}" method="POST">
     <div class="modal-body">
      @csrf
      <div class="mb-3">
       <label for="course" class="form-label">Mata Pelajaran <span class="text-danger">*</span></label>
       <select class="form-select" id="modalCourse" name="course" onchange="filterSubClass()">
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
       <label for="level" class="form-label">Tingkat <span class="text-danger">*</span></label>
       <select class="form-select" id="modalLevel" name="class_id" onchange="filterSubClass()">
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
       <label for="subClass" class="form-label">Sub Kelas <span class="text-danger">*</span></label>
       <select class="form-select" id="subClass" name="sub_class_id">
        <option value="" selected>- Pilih Sub Kelas -</option>
        <!-- Options will be populated by JavaScript -->
       </select>
       <div class="invalid-feedback">
        Sub Kelas harus dipilih.
       </div>
      </div>
      <div class="alert alert-danger" id="noSubClassMessage" style="display:none;">
       Tidak ada kelas baru untuk diajar berdasarkan tingkat dan pelajaran yang diminta.
      </div>
     </div>
     <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
      <button type="submit" class="btn btn-primary">Simpan</button>
     </div>
    </form>
   </div>
  </div>
 </div>

 <script>
  const enrollmentData = @json($enrollmentData);
  const subClasses = @json($allSubClass->data ?? []);

  $(document).ready(function() {
   if (enrollmentData.length > 0) {
    initializeDataTable();
   }
  });

  function initializeDataTable() {
   $('#table-kelas-ajar').DataTable({
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

  function filterCourse() {
   const courseId = document.getElementById('courseFilter').value;
   const level = document.getElementById('levelFilter').value;
   filterData(courseId, level);
  }

  function filterLevel() {
   const courseId = document.getElementById('courseFilter').value;
   const level = document.getElementById('levelFilter').value;
   filterData(courseId, level);
  }

  function filterData(courseId, level) {
   let rows = document.querySelectorAll('#table-body tr');
   let found = false;
   let index = 1;

   rows.forEach(row => {
    if (!row.dataset.courseId && !row.dataset.levelName) {
     return;
    }

    let courseColumn = row.dataset.courseId;
    let levelColumn = row.dataset.levelName;

    let showRow = (!courseId || courseColumn == courseId) && (!level || levelColumn == level);
    row.style.display = showRow ? '' : 'none';
    if (showRow) {
     row.querySelector('td:first-child').innerText = index++;
     found = true;
    }
   });

   let emptyMessageRow = document.getElementById('emptyMessageRow');
   if (!found) {
    if (!emptyMessageRow) {
     emptyMessageRow = document.createElement('tr');
     emptyMessageRow.id = 'emptyMessageRow';
     emptyMessageRow.innerHTML = `
                    <td colspan="5" class="text-center">Data yang Anda cari tidak ada. Silakan tambah data terlebih dahulu.</td>
                `;
     document.getElementById('table-body').appendChild(emptyMessageRow);
    } else {
     emptyMessageRow.style.display = '';
    }
   } else if (emptyMessageRow) {
    emptyMessageRow.style.display = 'none';
   }
  }

  function filterSubClass() {
   const courseId = document.getElementById('modalCourse').value;
   const levelId = document.getElementById('modalLevel').value;
   const subClassSelect = document.getElementById('subClass');
   const noSubClassMessage = document.getElementById('noSubClassMessage');

   // Clear the existing options
   subClassSelect.style.display = 'block';
   subClassSelect.innerHTML = '<option value="" selected>- Pilih Sub Kelas -</option>';

   if (!courseId || !levelId) {
    noSubClassMessage.style.display = 'none';
    return;
   }

   const existingEnrollments = enrollmentData.filter(item => item.course_id == courseId && item.class_id ==
     levelId)
    .map(item => item.sub_class_id);

   const filteredSubClasses = subClasses.filter(subClass => subClass.class.id == levelId && !existingEnrollments
    .includes(subClass.id));

   if (filteredSubClasses.length === 0) {
    subClassSelect.style.display = 'none';
    noSubClassMessage.style.display = 'block';
    return;
   }

   noSubClassMessage.style.display = 'none';

   filteredSubClasses.forEach(subClass => {
    const option = document.createElement('option');
    option.value = subClass.id;
    option.textContent = subClass.name;
    subClassSelect.appendChild(option);
   });
  }
 </script>
</x-app-layout>
