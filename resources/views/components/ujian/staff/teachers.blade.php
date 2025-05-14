@props(['ujian_id', 'exam', 'teachers', 'teacherOptions'])

<div>
  <div class="d-flex justify-content-end">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-teachers">Tambah</button>
  </div>

  <div class="modal fade modal-borderless text-left" id="add-teachers" tabindex="-1" aria-labelledby="myModalLabel1"
    style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Tambah Guru</h5>
          <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
              class="feather feather-x">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>
        <div class="modal-body">
          <form action="{{ route('staff_curriculum.enroll_teacher') }}" method="POST">
            @csrf

            <input type="hidden" name="exam_id" value="{{ $exam->id }}">

            <div class="form-group">
              <label for="teacher_id">Guru</label>
              <select class="choices form-select" name="teacher_id" id="teacher_id" required>
                <option value="" selected disabled>Pilih Guru</option>
                @foreach($teacherOptions as $item)
                  <option value="{{ $item?->is_teacher?->id }}">{{ $item->fullname }}</option>
                @endforeach
              </select>
            </div>

            <div class="form-group">
              <label for="role">Role</label>
              <select class="choices form-select" name="role" id="role" required>
                <option value="" selected disabled>Pilih role</option>
                <option value="PENILAI">Penilai</option>
                <option value="PENGELOLA">Pengelola</option>
              </select>
            </div>

            <div class="d-flex justify-content-end">
              <button type="submit" class="btn btn-primary">Tambahkan</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <x-datatable title="Daftar Guru">
    <table class="table" id="table-ujian-bank">
      <thead>
        <tr>
          <th>#</th>
          <th>NIP</th>
          <th>Nama</th>
          <th>Role</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody id="ujian-body-table">
        @forelse ($teachers as $item)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{ $item?->teacher?->is_teacher?->nip }}</td>
          <td>{{ $item?->teacher?->fullname }}</td>
          <td>{{ $item?->role }}</td>
          <td>
            <form action="{{ route('staff_curriculum.unenroll_teacher') }}" method="POST" onsubmit="return confirm('Hapus data?')">
                @method('DELETE')
                @csrf
                <input type="hidden" name="exam_id" value="{{ $exam->id }}">
                <input type="hidden" name="teacher_id" id="id" value="{{ $item?->teacher?->is_teacher?->id }}" required />

                <button type="submit" class="btn btn-danger">
                    Hapus
                </button>
            </form>
          </td>
        </tr>
        @empty
        <tr id="empty-row">
          <td colspan="5" class="text-center">Data tidak ditemukan. Silakan tambah ujian.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </x-datatable>
</div>
