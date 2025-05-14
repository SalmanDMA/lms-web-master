@props(['ujian_id', 'exam', 'participants', 'participantOptions'])

<div>
  <div class="d-flex justify-content-end">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-participant">Tambah</button>
  </div>

  <div class="modal fade modal-borderless text-left" id="add-participant" tabindex="-1" aria-labelledby="myModalLabel1"
    style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Tambah Peserta</h5>
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
          <form action="{{ route('staff_curriculum.enroll_student') }}" method="POST" id="participant-form">
            @csrf

            <input type="hidden" name="exam_id" value="{{ $exam->id }}">
            
            @if(count($participantOptions) > 0)
              <div class="form-group">
                <label for="students" class="form-label">Siswa</label>
                @foreach($participantOptions as $index => $item)
                    <div class="form-check">
                      <input type="checkbox" class="form-check-input" name="students[{{ $index }}][id]"
                        value="{{ $item?->is_student?->id }}" id="student-{{ $index }}">
                      <label class="form-check-label" for="student-{{ $index }}">
                        {{ $item->fullname }}
                      </label>
                    </div>
                  @endforeach
              </div>
            @else
              <p class="!text-xs">Tidak ada siswa yang terdaftar di pelajaran {{ !empty($exam?->courses_name['name']) ? $exam?->courses_name['name'] : '' }} dan tingkat kelas {{ !empty($exam?->class_level['name']) ? $exam?->class_level['name'] : '' }}</p>
            @endif
          </form>
        </div>
        <div class="modal-footer">
          <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary" id="participant-form-button">Tambahkan</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <x-datatable title="Daftar Peserta">
    <table class="table" id="table-ujian-bank">
      <thead>
        <tr>
          <th>#</th>
          <th>NISN</th>
          <th>Nama</th>
          <th>Jenis Kelamin</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody id="ujian-body-table">
        @forelse ($participants as $participant)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{ $participant?->student?->is_student?->nisn }}</td>
          <td>{{ $participant?->student?->fullname }}</td>
          <td>{{ $participant?->student?->gender ?? '-' }}</td>
          <td>
            <form action="{{ route('staff_curriculum.unenroll_student') }}" method="POST" onsubmit="return confirm('Hapus data?')">
                @method('DELETE')
                @csrf
                <input type="hidden" name="exam_id" value="{{ $exam->id }}">
                <input type="hidden" name="students[0][id]" id="id" value="{{ $participant?->student?->is_student?->id }}" required />

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

<script>
  $('#participant-form-button').click(function() {
    $('#participant-form').submit()
  })
</script>