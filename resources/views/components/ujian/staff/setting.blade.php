@props(['ujian_id', 'exam', 'class_level'])
{{-- @dd($exam) --}}

<form id="main-form" action="{{ route('staff_curriculum.sekolah.edit_ujian', ['id' => $ujian_id]) }}" method="POST"
  enctype="multipart/form-data" class="mt-3">
  @csrf
  @method('PUT')
  
  <div class="tab-content mt-3" id="myTabContent">
    <div class="tab-pane fade show active" id="pengaturan" role="tabpanel" aria-labelledby="pengaturan-tab">
      <div class="row mb-3">
        <div class="mb-3 mb-sm-0 col-12 col-sm-6">
          <label for="start_time" class="form-label">Waktu Mulai<span class="text-danger">*</span></label>
          <input type="datetime-local" name="start_time" id="start_time" class="form-control"
            value="{{ old('start_time', date('Y-m-d', strtotime($exam?->examSetting?->start_time))) }}">

        </div>
        <div class="col-12 col-sm-6">
          <label for="end_time" class="form-label">Waktu Berakhir<span class="text-danger">*</span></label>
          <input type="datetime-local" name="end_time" id="end_time" class="form-control"
            value="{{ old('end_time', date('Y-m-d', strtotime($exam?->examSetting?->end_time))) }}">

        </div>
      </div>

      <div class="row mb-3">
        <div class="mb-3 mb-sm-0 col-12 col-sm-6">
          <label for="repeat_chance" class="form-label">Total Kesempatan<span class="text-danger">*</span></label>
          <input type="number" name="repeat_chance" id="repeat_chance" class="form-control"
            value="{{ old('repeat_chance', $exam?->examSetting?->repeat_chance) }}">
        </div>

        <div class="col-12 col-sm-6">
          <label for="duration" class="form-label">Total Durasi<span class="text-danger">*</span></label>
          <input type="time" id="duration" name="duration" class="form-control"
            value="{{ old('duration', \Carbon\Carbon::parse($exam?->examSetting?->duration)->format('H:i')) }}"
            placeholder="00:00" maxlength="8">
        </div>

      </div>

      <div class="row mb-3">
        <div class="mb-3 mb-sm-0 col-12 col-sm-6">
          <label for="device" class="form-label">Perangkat<span class="text-danger">*</span></label>
          <select name="device" id="device" class="form-select">
            <option value="">- Pilih Perangkat -</option>
            <option value="Web" {{ $exam?->examSetting?->device == 'Web' ? 'selected' : '' }}>Web
            </option>
            <option value="Mobile" {{ $exam?->examSetting?->device == 'Mobile' ? 'selected' : '' }}>
              Mobile</option>
            <option value="All" {{ $exam?->examSetting?->device == 'All' ? 'selected' : '' }}>
              Semua</option>

          </select>
        </div>

        <div class="col-12 col-sm-6">
          <label for="token" class="form-label">Token</label>
          <input type="text" name="token" id="token" class="form-control">
        </div>
      </div>

      <div class="mb-3">
        <label for="maximum_user" class="form-label">Maksimal Peserta<span class="text-danger">*</span></label>
        <input type="number" name="maximum_user" id="maximum_user" min="1" class="form-control"
          value="{{ old('maximum_user', $exam?->examSetting?->maximum_user) }}">
      </div>

      <div class="row mb-3">
        <div class="col">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="is_random_question" name="is_random_question"
              {{ $exam?->examSetting?->is_random_question ? 'checked' : '' }}>
            <label class="form-check-label" for="is_random_question">Acak soal</label>
          </div>
        </div>
        <div class="col">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="is_random_answer" name="is_random_answer"
              {{ $exam?->examSetting?->is_random_answer ? 'checked' : '' }}>
            <label class="form-check-label" for="is_random_answer">Acak jawaban</label>
          </div>
        </div>
        <div class="col">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="is_show_score" name="is_show_score"
              {{ $exam?->examSetting?->is_show_score ? 'checked' : '' }}>
            <label class="form-check-label" for="is_show_score">Tampilkan skor</label>
          </div>
        </div>
        <div class="col">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="is_show_result" name="is_show_result"
              {{ $exam?->examSetting?->is_show_result ? 'checked' : '' }}>
            <label class="form-check-label" for="is_show_result">Tampilkan
              pengerjaan</label>
          </div>
        </div>
      </div>

      <input type="hidden" name="class_level" id="class_level" value="{{ !empty($class_level['id']) ? $class_level['id'] : '' }}">

      <div class="d-flex justify-content-between">
        <a href="{{ route('staff_curriculum.sekolah.v_ujian') }}" class="btn btn-secondary">Kembali</a>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </div>
  </div>
</form>
