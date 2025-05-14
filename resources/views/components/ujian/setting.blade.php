@props(['ujian_id', 'exams', 'class_level'])

<form id="main-form" action="{{ route('teacher.sekolah.edit_ujian', ['ujian_id' => $ujian_id]) }}" method="POST"
  enctype="multipart/form-data" class="mt-3">
  @csrf
  @method('PUT')

  <div class="tab-content mt-3" id="myTabContent">
    <div class="tab-pane fade show active" id="pengaturan" role="tabpanel" aria-labelledby="pengaturan-tab">
      <div class="row mb-3">
        <div class="mb-3 mb-sm-0 col-12 col-sm-6">
          <label for="start_time" class="form-label">Waktu Mulai<span class="text-danger">*</span></label>
          <input type="datetime-local" name="start_time" id="start_time" class="form-control"
            value="{{ old('start_time', $exams->examSetting->start_time) }}">
        </div>
        <div class="col-12 col-sm-6">
          <label for="end_time" class="form-label">Waktu Berakhir<span class="text-danger">*</span></label>
          <input type="datetime-local" name="end_time" id="end_time" class="form-control"
            value="{{ old('end_time', $exams->examSetting->end_time) }}">
        </div>
      </div>

      <div class="row mb-3">
        <div class="mb-3 mb-sm-0 col-12 col-sm-6">
          <label for="repeat_chance" class="form-label">Total Kesempatan<span class="text-danger">*</span></label>
          <input type="number" name="repeat_chance" id="repeat_chance" class="form-control"
            value="{{ old('repeat_chance', $exams->examSetting->repeat_chance) }}">
        </div>

        <div class="col-12 col-sm-6">
          <label for="duration" class="form-label">Total Durasi<span class="text-danger">*</span></label>
          <input type="text" id="duration" name="duration" class="form-control"
            value="{{ old('duration', \Carbon\Carbon::parse($exams->examSetting->duration)->format('H:i:s')) }}"
            placeholder="00:00:00" maxlength="8">
        </div>

      </div>

      <div class="row mb-3">
        <div class="mb-3 mb-sm-0 col-12 col-sm-6">
          <label for="device" class="form-label">Perangkat<span class="text-danger">*</span></label>
          <select name="device" id="device" class="form-select">
            <option value="">- Pilih Perangkat -</option>
            <option value="Web" {{ $exams->examSetting->device == 'Web' ? 'selected' : '' }}>Web
            </option>
            <option value="Mobile" {{ $exams->examSetting->device == 'Mobile' ? 'selected' : '' }}>
              Mobile</option>
            <option value="All" {{ $exams->examSetting->device == 'All' ? 'selected' : '' }}>
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
          value="{{ old('maximum_user', $exams->examSetting->maximum_user) }}">
      </div>

      <div class="row mb-3">
        <div class="col">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="is_random_question" name="is_random_question"
              {{ $exams->examSetting->is_random_question ? 'checked' : '' }}>
            <label class="form-check-label" for="is_random_question">Acak soal</label>
          </div>
        </div>
        <div class="col">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="is_random_answer" name="is_random_answer"
              {{ $exams->examSetting->is_random_answer ? 'checked' : '' }}>
            <label class="form-check-label" for="is_random_answer">Acak jawaban</label>
          </div>
        </div>
        <div class="col">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="is_show_score" name="is_show_score"
              {{ $exams->examSetting->is_show_score ? 'checked' : '' }}>
            <label class="form-check-label" for="is_show_score">Tampilkan skor</label>
          </div>
        </div>
        <div class="col">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="is_show_result" name="is_show_result"
              {{ $exams->examSetting->is_show_result ? 'checked' : '' }}>
            <label class="form-check-label" for="is_show_result">Tampilkan
              pengerjaan</label>
          </div>
        </div>
      </div>

      <input type="hidden" name="class_level" id="class_level" value="{{ $class_level['id'] }}">

      <div class="d-flex justify-content-between">
        <a href="{{ route('teacher.sekolah.v_ujian') }}" class="btn btn-secondary">Kembali</a>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </div>
  </div>
</form>

<script>
  $(document).ready(function() {
    const maxTimeValue = [23, 59, 59];

    $('#duration').on('input', function() {
      this.value = this.value.replace(/[^0-9:]/g, '');
      let val = this.value.replace(/:/g, '');
      if (val.length >= 2) val = val.substring(0, 2) + ':' + val.substring(2);
      if (val.length >= 5) val = val.substring(0, 5) + ':' + val.substring(5);

      this.value = val.substring(0, 8);
    });

    $('#duration').on('blur', function() {
      let val = this.value.split(':').map(v => v.padStart(2, '0'));
      this.value = val.join(':');
    });

    $('#duration').on('keydown', function(e) {
      let pos = getCaretPosition(this);
      if (e.which == 38 || e.which == 40) {
        let timeParts = this.value.split(':');
        let partIndex = getTimePartIndex(pos);

        let currentValue = parseInt(timeParts[partIndex]) || 0;
        if (e.which == 38) {
          timeParts[partIndex] = (currentValue < maxTimeValue[partIndex] ? currentValue + 1 : 0).toString()
            .padStart(2, '0');
        } else if (e.which == 40) {
          timeParts[partIndex] = (currentValue > 0 ? currentValue - 1 : maxTimeValue[partIndex]).toString()
            .padStart(2, '0');
        }

        this.value = timeParts.join(':');
        setCaretPosition(this, getNewCaretPosition(pos));
        e.preventDefault();
      }
    });

    function getTimePartIndex(pos) {
      if (pos <= 2) return 0;
      else if (pos >= 3 && pos <= 5) return 1;
      else return 2;
    }

    function getCaretPosition(ctrl) {
      let position = 0;
      if (ctrl.selectionStart || ctrl.selectionStart === 0) {
        position = ctrl.selectionStart;
      }
      return position;
    }

    function setCaretPosition(ctrl, pos) {
      if (ctrl.setSelectionRange) {
        ctrl.focus();
        ctrl.setSelectionRange(pos, pos);
      }
    }

    function getNewCaretPosition(pos) {
      if (pos <= 2) return 2;
      else if (pos >= 3 && pos <= 5) return 5;
      else return 8;
    }
  });
</script>
