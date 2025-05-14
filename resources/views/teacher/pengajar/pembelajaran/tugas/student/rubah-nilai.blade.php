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

  @php
    $dateTime = \Carbon\Carbon::parse($submission->submitted_at)->translatedFormat('l, d-M-Y H:i');
    $title = 'Pengerjaan ' . $dateTime;
  @endphp

  <x-slot:title>
    {{ $title }}
  </x-slot>

  <div class="card">
    <div class="card-header bg-primary text-white">
      <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-between">
        <span class="mb-0 fs-4 fw-bold text-center text-md-start">{{ $title }}</span>
        <span id="title-panel" class="mb-0 fs-4 fw-bold">( {{ $student->student->fullname }} )</span>
      </div>
    </div>
    <div class="card-body mt-3">
      <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="pengerjaan-tab" data-bs-toggle="tab" data-bs-target="#pengerjaan"
            type="button" role="tab" aria-controls="pengerjaan" aria-selected="true">Pengerjaan</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="kirim-catatan-tab" data-bs-toggle="tab" data-bs-target="#kirim-catatan"
            type="button" role="tab" aria-controls="kirim-catatan" aria-selected="false">Kirim Catatan
            Koreksi</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="beri-nilai-tab" data-bs-toggle="tab" data-bs-target="#beri-nilai" type="button"
            role="tab" aria-controls="beri-nilai" aria-selected="false">Beri
            Nilai</button>
        </li>
      </ul>
      <div class="tab-content mt-3" id="myTabContent">
        <div class="tab-pane fade show active" id="pengerjaan" role="tabpanel" aria-labelledby="pengerjaan-tab">
          <div class="mt-4 row">
            <div class="card mb-3 mb-lg-0 col-12 col-lg-5">
              <div class="card-header bg-primary text-white">
                Pengerjaan dalam Bentuk Catatan
              </div>
              <div class="card-body border rounded pt-3">
                {{ $submission->submission_content }}
              </div>
            </div>
            <div class="card col-12 col-lg-7 mb-0">
              <div class="card-header bg-primary text-white">
                Pengerjaan dalam Bentuk Lampiran
              </div>
              <div class="card-body border rounded pt-3">
                @foreach ($submission->submission_attachments as $attachment)
                  <div class="p-3 border rounded d-flex align-items-center">
                    @switch($attachment->file_type)
                      @case('url')
                        <div>
                          <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                            <div>
                              <strong>URL:</strong> <a href="{{ $attachment->file_url }}"
                                target="_blank">{{ $attachment->file_name }}</a>
                            </div>
                            <i class="bi bi-link-45deg fs-3 d-flex align-items-center justify-content-center"></i>
                          </div>
                        </div>
                      @break

                      @case('youtube')
                        <div>
                          <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                            <div>
                              <strong>Youtube Video:</strong> <a href="{{ $attachment->file_url }}"
                                target="_blank">{{ $attachment->file_name }}</a>
                            </div>
                            <i class="bi bi-youtube fs-3 d-flex align-items-center justify-content-center"></i>
                          </div>
                        </div>
                      @break

                      @case('archive')
                        <div>
                          <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                            <div>
                              <strong>Archive:</strong> {{ $attachment->file_name }}
                              ({{ $attachment->file_extension }})
                              -
                              {{ number_format($attachment->file_size / 1024, 2) }} KB
                            </div>
                            <i class="bi bi-file-earmark-zip fs-3 d-flex align-items-center justify-content-center"></i>
                          </div>
                        </div>
                      @break

                      @case('document')
                        <div>
                          <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                            <div>
                              <strong>Document:</strong> {{ $attachment->file_name }}
                              ({{ $attachment->file_extension }}) -
                              {{ number_format($attachment->file_size / 1024, 2) }} KB
                            </div>
                            <i class="bi bi-file-earmark-text fs-3 d-flex align-items-center justify-content-center"></i>
                          </div>
                        </div>
                      @break

                      @case('audio')
                        <div>
                          <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                            <div>
                              <strong>Audio:</strong> {{ $attachment->file_name }}
                              ({{ $attachment->file_extension }}) -
                              {{ number_format($attachment->file_size / 1024, 2) }} KB
                            </div>
                            <i class="bi bi-file-earmark-music fs-3 d-flex align-items-center justify-content-center"></i>
                          </div>
                          <audio controls class="mt-2">
                            @php
                              $fileUrl = str_replace('storage/public/', '', $attachment->file_url);
                            @endphp
                            <source src="{{ Storage::url($fileUrl) }}" type="audio/{{ $attachment->file_extension }}">
                            Your browser does not support the audio element.
                          </audio>
                        </div>
                      @break

                      @case('video')
                        <div>
                          <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                            <div>
                              <strong>Video:</strong> {{ $attachment->file_name }}
                              ({{ $attachment->file_extension }}) -
                              {{ number_format($attachment->file_size / 1024, 2) }} KB
                            </div>
                            <i class="bi bi-file-earmark-play fs-3 d-flex align-items-center justify-content-center"></i>
                          </div>
                          <video controls class="mt-2" width="100%">
                            @php
                              $fileUrl = str_replace('storage/public/', '', $attachment->file_url);
                            @endphp
                            <source src="{{ Storage::url($fileUrl) }}" type="video/{{ $attachment->file_extension }}">
                            Your browser does not support the video element.
                          </video>
                        </div>
                      @break

                      @case('image')
                        <div>
                          <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                            <div>
                              <strong>Image:</strong> {{ $attachment->file_name }}
                              ({{ $attachment->file_extension }}) -
                              {{ number_format($attachment->file_size / 1024, 2) }} KB
                            </div>
                            <i
                              class="bi bi-file-earmark-image me-2 fs-3 d-flex align-items-center justify-content-center"></i>
                          </div>
                          <img src="{{ Storage::url(str_replace('storage/public/', '', $attachment->file_url)) }}"
                            alt="{{ $attachment->file_name }}" class="img-fluid mt-2">
                        </div>
                      @break

                      @default
                        <i class="bi bi-file-earmark me-2 fs-3"></i>
                        <div>
                          <strong>File:</strong> {{ $attachment->file_name }}
                          ({{ $attachment->file_extension }}) -
                          {{ number_format($attachment->file_size / 1024, 2) }} KB
                        </div>
                    @endswitch
                  </div>
                @endforeach
              </div>
            </div>
          </div>
        </div>
        <div class="tab-pane fade" id="kirim-catatan" role="tabpanel" aria-labelledby="kirim-catatan-tab">
          <div class="card">
            <div class="card-header bg-primary text-white">
              Kirim Catatan Koreksi
            </div>
            <div class="card-body border rounded pt-3">
              @if ($submission->grades[0]->knowledge !== null || $submission->grades[0]->skills !== null)
                <div class="alert alert-warning p-3 rounded border-warning">
                  Tidak bisa kirim catatan lagi. Sudah memiliki nilai.
                </div>
                <div class="alert alert-info p-3 rounded border-info">
                  <strong>Catatan Terakhir:</strong> {!! $submission->feedback !!}
                </div>
              @else
                @if ($submission->feedback !== null)
                  <div class="alert alert-info p-3 rounded border-info mb-0">
                    <strong>Catatan:</strong> {!! $submission->feedback !!}
                  </div>
                  <form id="unsubmitForm"
                    action="{{ route('teacher.pengajar.pembelajaran.feedback_send', ['learning_id' => $learning_id, 'id' => $assignment_id, 'student_id' => str_replace('/', '-', $student_id), 'submission_id' => $submission_id]) }}"
                    method="POST">
                    @csrf
                    @method('PUT')
                    <div id="correctionNoteEditorContainer" style="display: none" class="mt-3">
                      <textarea id="correctionNoteEditor" name="feedback"></textarea>
                    </div>
                    <button type="button" class="btn btn-primary mt-3" id="btnUnchangeFeedback"
                      onclick="toggleUnsendFeedback()">Ubah Catatan</button>
                    <button type="submit" class="btn btn-primary mt-3" id="btnSubmitFeedback"
                      style="display: none;">Kirim Catatan</button>
                  </form>
                @else
                  <form id="feedbackForm"
                    action="{{ route('teacher.pengajar.pembelajaran.feedback_send', ['learning_id' => $learning_id, 'id' => $assignment_id, 'student_id' => str_replace('/', '-', $student_id), 'submission_id' => $submission_id]) }}"
                    method="POST">
                    @csrf
                    @method('PUT')
                    <textarea id="correctionNoteEditor" name="feedback"></textarea>
                    <button type="submit" class="btn btn-primary mt-3">Kirim Catatan</button>
                  </form>
                @endif
              @endif
            </div>
          </div>
        </div>
        <div class="tab-pane fade" id="beri-nilai" role="tabpanel" aria-labelledby="beri-nilai-tab">
          <div class="card">
            <div class="card-header bg-primary text-white">
              Berikan Nilai
            </div>
            <div class="card-body border rounded pt-3">
              <div class="alert alert-warning p-3 rounded border-warning">
                <strong>Perhatian:</strong> <br> Boleh mengisi salah satu atau keduanya & Fitur kirim
                catatan
                koreksi akan ditutup setelah diberi nilai.
              </div>
              <form id="scoreForm"
                action="{{ route('teacher.pengajar.pembelajaran.rubah_nilai', ['learning_id' => $learning_id, 'id' => $assignment_id, 'student_id' => str_replace('/', '-', $student_id), 'submission_id' => $submission_id]) }}"
                method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="is_null" id="is_null"
                  value="{{ $submission->grades[0]->knowledge !== null || $submission->grades[0]->skills !== null ? '1' : '0' }}">

                <div class="mb-3">
                  <label for="knowledge" class="form-label">Pengetahuan</label>
                  <input type="number" class="form-control" id="knowledge" name="knowledge"
                    value="{{ $submission->grades[0]->knowledge }}">
                </div>
                <div class="mb-3">
                  <label for="skills" class="form-label">Keterampilan</label>
                  <input type="number" class="form-control" id="skills" name="skills"
                    value="{{ $submission->grades[0]->skills }}">
                </div>
                <div class="d-flex justify-content-end">
                  @if ($submission->grades[0]->knowledge !== null || $submission->grades[0]->skills !== null)
                    <button type="submit" class="btn btn-danger" id="resetButton">Hapus</button>
                  @else
                    <button type="submit" class="btn btn-primary" id="saveButton">Simpan</button>
                  @endif
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.ckeditor.com/ckeditor5/34.2.0/classic/ckeditor.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      ClassicEditor.create(document.querySelector("#correctionNoteEditor")).catch((error) => {
        console.error(error)
      });
    });

    function toggleUnsendFeedback() {
      if ($('#correctionNoteEditorContainer').is(':visible')) {
        $('#correctionNoteEditorContainer').hide();
        $('#btnSubmitFeedback').hide();
        $('#btnUnchangeFeedback').text('Ubah Catatan');
      } else {
        $('#correctionNoteEditorContainer').show();
        $('#btnSubmitFeedback').show();
        $('#btnUnchangeFeedback').hide();
      }
    }
  </script>

</x-app-layout>
