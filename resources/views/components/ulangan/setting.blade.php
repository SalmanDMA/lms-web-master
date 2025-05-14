@props(['learning_id', 'class_exams', 'subclasses'])

<form id="main-form"
  action="{{ route('teacher.pengajar.pembelajaran.update_ulangan', ['learning_id' => $learning_id, 'ulangan_id' => $class_exams->id]) }}"
  method="POST" enctype="multipart/form-data" class="mt-3">
  @csrf
  @method('PUT')

  <!-- Tabs -->
  <ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item" role="presentation">
      <a class="nav-link active" id="umum-tab" data-bs-toggle="tab" href="#umum" role="tab" aria-controls="umum"
        aria-selected="true">Umum</a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link" id="pengaturan-tab" data-bs-toggle="tab" href="#pengaturan" role="tab"
        aria-controls="pengaturan" aria-selected="false">Pengaturan</a>
    </li>
  </ul>
  <div class="tab-content mt-3" id="myTabContent">
    <!-- Tab Umum -->
    <div class="tab-pane fade show active" id="umum" role="tabpanel" aria-labelledby="umum-tab">
      <div class="row mb-3">
        <div class="mb-3 mb-sm-0 col-12 col-sm-6">
          <label for="title" class="form-label">Judul Ulangan<span class="text-danger">*</span></label>
          <input type="text" name="title" id="title" class="form-control"
            value="{{ old('title', $class_exams->title) }}">
        </div>

        <div class="col-12 col-sm-6">
          <label for="type" class="form-label">Jenis Ulangan<span class="text-danger">*</span></label>
          <select name="type" id="type" class="form-select">
            <option value="">- Pilih Opsi -</option>
            <option value="Ulangan Harian" {{ $class_exams->type == 'Ulangan Harian' ? 'selected' : '' }}>
              Ulangan Harian
            </option>
            <option value="Quiz Prakerja" {{ $class_exams->type == 'Quiz Prakerja' ? 'selected' : '' }}>Quiz
              Prakerja
            </option>
            <option value="Post Test Prakerja" {{ $class_exams->type == 'Post Test Prakerja' ? 'selected' : '' }}>Post
              Test
              Prakerja</option>
          </select>
        </div>
      </div>

      <div class="mb-3">
        <label for="description" class="form-label">Deskripsi<span class="text-danger">*</span></label>
        <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $class_exams->description) }}</textarea>
      </div>

      <div class="mb-3">
        <label for="instruction" class="form-label">Intruksi<span class="text-danger">*</span></label>
        <textarea name="instruction" id="instruction" class="form-control" rows="3">{{ old('instruction', $class_exams->instruction) }}</textarea>
      </div>

    </div>

    <!-- Tab Pengaturan -->
    <div class="tab-pane fade" id="pengaturan" role="tabpanel" aria-labelledby="pengaturan-tab">
      <div class="row mb-3">
        <div class="mb-3 mb-sm-0 col-12 col-sm-6">
          <label for="start_time" class="form-label">Waktu Mulai<span class="text-danger">*</span></label>
          <input type="datetime-local" name="start_time" id="start_time" class="form-control"
            value="{{ old('start_time', $class_exams->exam_setting->start_time) }}">
        </div>
        <div class="col-12 col-sm-6">
          <label for="end_time" class="form-label">Waktu Berakhir<span class="text-danger">*</span></label>
          <input type="datetime-local" name="end_time" id="end_time" class="form-control"
            value="{{ old('end_time', $class_exams->exam_setting->end_time) }}">
        </div>
      </div>

      <div class="row mb-3">
        <div class="mb-3 mb-sm-0 col-12 col-sm-6">
          <label for="repeat_chance" class="form-label">Total Kesempatan<span class="text-danger">*</span></label>
          <input type="number" name="repeat_chance" id="repeat_chance" class="form-control"
            value="{{ old('repeat_chance', $class_exams->exam_setting->repeat_chance) }}">
        </div>

        <div class="col-12 col-sm-6">
          <label for="duration" class="form-label">Total Durasi<span class="text-danger">*</span></label>
          <input type="text" name="duration" id="duration" class="form-control"
            value="{{ old('duration', \Carbon\Carbon::parse($class_exams->exam_setting->duration)->format('H:i')) }}"
            placeholder="00:00:00" maxlength="8">
        </div>
      </div>

      <div class="row mb-3">
        <div class="mb-3 mb-sm-0 col-12 col-sm-6">
          <label for="device" class="form-label">Perangkat<span class="text-danger">*</span></label>
          <select name="device" id="device" class="form-select">
            <option value="">- Pilih Perangkat -</option>
            <option value="Web" {{ $class_exams->exam_setting->device == 'Web' ? 'selected' : '' }}>Web
            </option>
            <option value="Mobile" {{ $class_exams->exam_setting->device == 'Mobile' ? 'selected' : '' }}>
              Mobile</option>
            <option value="All" {{ $class_exams->exam_setting->device == 'All' ? 'selected' : '' }}>
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
          value="{{ old('maximum_user', $class_exams->exam_setting->maximum_user) }}">
      </div>

      <div class="row mb-3">
        <div class="col">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="is_random_question" name="is_random_question"
              {{ $class_exams->exam_setting->is_random_question ? 'checked' : '' }}>
            <label class="form-check-label" for="is_random_question">Acak soal</label>
          </div>
        </div>
        <div class="col">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="is_random_answer" name="is_random_answer"
              {{ $class_exams->exam_setting->is_random_answer ? 'checked' : '' }}>
            <label class="form-check-label" for="is_random_answer">Acak jawaban</label>
          </div>
        </div>
        <div class="col">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="is_show_score" name="is_show_score"
              {{ $class_exams->exam_setting->is_show_score ? 'checked' : '' }}>
            <label class="form-check-label" for="is_show_score">Tampilkan skor</label>
          </div>
        </div>
        <div class="col">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="is_show_result" name="is_show_result"
              {{ $class_exams->exam_setting->is_show_result ? 'checked' : '' }}>
            <label class="form-check-label" for="is_show_result">Tampilkan
              pengerjaan</label>
          </div>
        </div>
      </div>

      <input type="hidden" name="class_level" id="class_level" value="{{ $subclasses->class->id }}">

      <div class="d-flex justify-content-between">
        <a href="{{ route('teacher.pengajar.pembelajaran.v_ulangan', ['learning_id' => $learning_id]) }}"
          class="btn btn-secondary">Kembali</a>
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

      this.value = val.substring(0, 5);
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

<script>
  ClassicEditor.create($('#description')[0]).catch(console.error);
  ClassicEditor.create($('#instruction')[0]).catch(console.error);
</script>
