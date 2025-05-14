<x-app-layout>
 @if ($errors->any())
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
   <ul class="mb-0">
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
  Edit Pengajuan RPP
 </x-slot>

 <div class="card mt-3">
  <div class="card-header bg-primary">
   <h3 class="mb-0 text-white">Edit Pengajuan RPP</h3>
  </div>
  <div class="card-body mt-3">
   <form id="main-form"
    action="{{ route('teacher.pengajar.edit_draft_rpp', ['rpp_id' => $rpp_id, 'rpp_draft_id' => $rpp_draft_id]) }}"
    method="POST">
    @csrf
    @method('PUT')

    <div class="mb-3">
     <label for="draftName" class="form-label">Nama Draft <span class="text-danger">*</span></label>
     <input type="text" class="form-control" id="draftName" name="draftName"
      value="{{ old('draftName', $draft->draft_name) }}">
     <div class="invalid-feedback">
      Nama draft harus diisi.
     </div>
    </div>

    <div class="row mb-3">
     <div class="col-md-6 mb-3 mb-md-0">
      <label for="course" class="form-label">Mata Pelajaran <span class="text-danger">*</span></label>
      <select name="course" id="course" class="form-select">
       <option value="" selected>- Pilih Pelajaran -</option>
       @foreach ($courses as $course)
        <option value="{{ $course->id }}" {{ old('course', $draft->courses) == $course->id ? 'selected' : '' }}>
         {{ $course->courses_title }}
        </option>
       @endforeach
      </select>
     </div>

     <div class="col-md-6">
      <label for="level" class="form-label">Tingkat <span class="text-danger">*</span></label>
      <select name="level" id="level" class="form-select">
       <option value="" selected>- Pilih Tingkat -</option>
       @foreach ($levels as $level)
        <option value="{{ $level->id }}" {{ old('level', $draft->class_level) == $level->id ? 'selected' : '' }}>
         {{ $level->name }}
        </option>
       @endforeach
      </select>
     </div>
    </div>

    <div class="row mb-3">
     <div class="col-md-6 mb-3 mb-md-0">
      <label for="academicYear" class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
      <select name="academicYear" id="academicYear" class="form-select">
       <option value="" selected>- Pilih Tahun Ajaran -</option>

       @foreach ($academic_years as $item)
        <option value="{{ $item->id }}"
         {{ old('academicYear', $draft->academic_year) == $item->id ? 'selected' : '' }}>
         {{ $item->year }}
        </option>
       @endforeach
      </select>
     </div>

     <div class="col-md-6">
      <label for="semester" class="form-label">Semester <span class="text-danger">*</span></label>
      <select name="semester" id="semester" class="form-select">
       <option value="" selected>- Pilih Semester -</option>
       <option value="ganjil" {{ old('semester', $draft->semester) == 'ganjil' ? 'selected' : '' }}>
        Ganjil</option>
       <option value="genap" {{ old('semester', $draft->semester) == 'genap' ? 'selected' : '' }}>
        Genap</option>
      </select>
     </div>
    </div>

    <div class="d-flex justify-content-between mt-4">
     <a href="{{ url()->previous() }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i>
      Kembali</a>
     <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </div>
   </form>
  </div>
 </div>
</x-app-layout>
