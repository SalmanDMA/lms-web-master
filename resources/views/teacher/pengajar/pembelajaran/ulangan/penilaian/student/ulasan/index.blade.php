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
    Ulasan
  </x-slot>

  @php
    $title =
        'Ulasan Pembelajaran ' .
        $subclasses->class->name .
        ' - ' .
        $subclasses->name .
        ' Pelajaran ' .
        $learning->course->courses_title;
    $student = $student_reports->first();
    $attempts_history = $student['attempts_history']->toArray();
    $latest_attempt = !empty($attempts_history) ? end($attempts_history) : null;
  @endphp

  <div class="card">
    <div class="card-header bg-primary text-white">
      <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-between">
        <span class="mb-0 fs-4 fw-bold text-center text-md-start">{{ $title }}</span>
      </div>
    </div>

    <div class="card-body mt-4">
      @if ($student)
        <div class="alert alert-light mb-4" role="alert">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h5 class="mb-0 fw-bold fs-2">{{ $student['name'] }}</h5>
              <p class="text-muted mb-0">
                @if ($analytics->answers->unanswered > 0)
                  {{ $analytics->answers->unanswered }} soal yang tidak diisi.
                @else
                  Semua soal sudah diisi.
                @endif
              </p>
            </div>

            @if (is_array($latest_attempt))
              <div>
                <h5 class="mb-0 fw-bold fs-3 text-end">
                  @if ($student['final_grade'] !== null)
                    {{ $student['final_grade'] }}
                  @else
                    @if ($latest_attempt['is_essay_graded'])
                      {{ $latest_attempt['total_points_with_essay'] }}
                    @else
                      {{ $latest_attempt['initial_points'] }}
                    @endif
                  @endif
                </h5>
                <p class="text-muted">Nilai Akhir</p>
              </div>
            @else
              <div>
                <h5 class="mb-0 fw-bold fs-3 text-end">Belum ada pengerjaan</h5>
                <p class="text-muted">Nilai Akhir</p>
              </div>
            @endif
          </div>
        </div>
      @else
        <div class="alert alert-light mb-4" role="alert">
          <p class="text-center mb-0">Data murid tidak ditemukan.</p>
        </div>
      @endif

      @if ($all_questions->count())
        @foreach ($all_questions as $index => $question)
          <div class="card mb-4" style="box-shadow: 0 0 10px 0 rgba(0, 0, 0, 0.15);">
            <div class="card-header bg-primary">
              <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-sm-between">
                <div class="mb-0">
                  <h3 class="mb-0 text-white">Soal {{ $index + 1 }}</h3>
                  <div class="d-flex flex-wrap align-items-center gap-2">
                    <p class="text-sm mb-0 text-white">
                      ({{ $question->point }})
                      Point
                    </p>
                    @if ($question->grade_method)
                      <p class="text-sm mb-0 text-white">
                        * {{ ucwords($question->grade_method) }}
                      </p>
                    @endif
                  </div>
                </div>
                <p class="text-sm mb-0 text-white">
                  ( {{ $question->question_type }} )
                </p>
              </div>
            </div>
            <div class="card-body border rounded pt-3">
              <h5 class="fw-bold mb-0">{!! $question->question_text !!}</h5>

              @if ($question->question_attachments)
                <div class="attachments mb-3">
                  @foreach ($question->question_attachments as $attachment)
                    @php
                      $linkUrl = str_replace('storage/public/', '', $attachment->file_url);
                      $fileType = pathinfo($attachment->file_url, PATHINFO_EXTENSION);
                    @endphp

                    @if ($attachment->file_type == 'url' || $attachment->file_type == 'youtube')
                      <p>Lampiran: <a href="{{ $linkUrl }}" target="_blank">{{ $attachment->file_name }}</a></p>
                    @elseif ($attachment->file_type == 'image')
                      <div class="attachment">
                        <img src="{{ Storage::url($linkUrl) }}" alt="{{ $attachment->file_name }}" class="img-fluid">
                      </div>
                    @elseif ($attachment->file_type == 'video')
                      <div class="attachment">
                        <video controls>
                          <source src="{{ Storage::url($linkUrl) }}" type="video/{{ $fileType }}">
                          Your browser does not support the video tag.
                        </video>
                      </div>
                    @elseif ($attachment->file_type == 'audio')
                      <div class="attachment">
                        <audio controls>
                          <source src="{{ Storage::url($linkUrl) }}" type="audio/{{ $fileType }}">
                          Your browser does not support the audio tag.
                        </audio>
                      </div>
                    @elseif ($attachment->file_type == 'document')
                      <p>Lampiran: <a href="{{ $linkUrl }}" target="_blank">{{ $attachment->file_name }}</a></p>
                    @elseif ($attachment->file_type == 'archive')
                      <p>Lampiran: <a href="{{ $linkUrl }}" target="_blank">{{ $attachment->file_name }}</a></p>
                    @else
                      <p>Lampiran: <a href="{{ $linkUrl }}" target="_blank">{{ $attachment->file_name }}</a></p>
                    @endif
                  @endforeach
                </div>
              @endif

              @php
                $student_answers_choices = $all_answers
                    ->where('question_id', $question->id)
                    ->pluck('choice_id')
                    ->toArray();

                $student_answer_text = $all_answers
                    ->where('question_id', $question->id)
                    ->pluck('answer_text')
                    ->first();

                $answer = $all_answers->where('question_id', $question->id)->first();
              @endphp

              @if ($question->question_type == 'Pilihan Ganda' || $question->question_type == 'True False')
                <div>
                  @foreach ($question->choices as $choice)
                    <div class="form-check position-relative">
                      <input class="form-check-input" type="radio" name="question_{{ $question->id }}"
                        value="{{ $choice->id }}"
                        {{ in_array($choice->id, $student_answers_choices) ? 'checked' : '' }} disabled>
                      <label
                        class="form-check-label w-100 rounded py-2 px-3 {{ $choice->is_true ? 'bg-success text-white fw-bold' : '' }}">
                        {!! $choice->choice_text !!}
                        @if ($choice->is_true)
                          <span
                            class="badge bg-info position-absolute end-0 top-50 translate-middle-y me-2">Kunci</span>
                        @endif
                      </label>
                    </div>
                  @endforeach
                </div>
              @elseif ($question->question_type == 'Pilihan Ganda Complex')
                <div>
                  @foreach ($question->choices as $choice)
                    @php
                      $isRequiredAllCorrect = $question->grade_method === 'wajib benar semua';
                    @endphp

                    <div class="form-check position-relative">
                      <input class="form-check-input" type="checkbox" name="question_{{ $question->id }}[]"
                        {{ in_array($choice->id, $student_answers_choices) ? 'checked' : '' }} disabled>
                      <label
                        class="form-check-label w-100 rounded py-2 px-3 {{ $isRequiredAllCorrect ? 'bg-success text-white fw-bold opacity-100' : ($choice->is_true ? 'bg-success text-white fw-bold opacity-100' : '') }}">
                        {!! $choice->choice_text !!}
                        @if ($isRequiredAllCorrect || $choice->is_true)
                          <span
                            class="badge bg-info position-absolute end-0 top-50 translate-middle-y me-2">Kunci</span>
                        @endif
                      </label>
                    </div>
                  @endforeach
                </div>
              @elseif ($question->question_type == 'Essay')
                <div class="mb-3">
                  <label for="answer_{{ $question->id }}" class="form-label">Jawaban Siswa :</label>
                  @php
                    $cleanedContent = preg_replace('/\s+|&nbsp;|\'/', '', strip_tags($student_answer_text));
                  @endphp

                  @if (!empty($cleanedContent))
                    <textarea class="form-control" rows="4" disabled>{{ strip_tags($student_answer_text) }}</textarea>
                  @else
                    <textarea class="form-control" rows="4" disabled></textarea>
                  @endif

                </div>
              @endif


              @if ($question->question_type == 'Essay' && $answer && $answer->question_id == $question->id && $answer->is_graded == 0)
                <div class="alert alert-danger d-flex align-items-center justify-content-between mb-0" role="alert">
                  <span class="me-2 mb-0">Soal ini belom dinilai.</span>
                  <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                    data-bs-target="#gradeModal{{ $question->id }}">Beri Nilai</button>
                </div>
                <div class="modal fade" id="gradeModal{{ $question->id }}" tabindex="-1"
                  aria-labelledby="gradeModalLabel{{ $question->id }}" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="gradeModalLabel{{ $question->id }}">
                          Konfirmasi
                          Penilaian</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <form method="POST" class="mb-3"
                          action="{{ route('teacher.pengajar.pembelajaran.penilaian_ulasan', ['learning_id' => $learning_id, 'ulangan_id' => $ulangan_id, 'student_id' => str_replace('/', '-', $student['student_id']), 'response_id' => $response_id]) }}">
                          @csrf
                          @method('PUT')
                          <label for="grade_{{ $question->id }}" class="form-label">Beri
                            Nilai :</label>
                          <input type="number" class="form-control" id="grade_{{ $question->id }}"
                            name="grade_{{ $question->id }}" min="0" max="{{ $question->point }}"
                            data-question-point="{{ $question->point }}">
                          <input type="hidden" name="answer_id" value="{{ $answer->id }}">
                          <div class="row mt-3">
                            <div class="col-12 col-sm-4">
                              <button class="btn btn-outline-warning w-100" type="button"
                                id="full-grade-button-{{ $question->id }}">
                                Point Penuh
                              </button>
                            </div>
                            <div class="col-12 col-sm-4 px-0">
                              <button class="btn btn-outline-danger w-100" type="button"
                                id="zero-grade-button-{{ $question->id }}">
                                Nilai Nol
                              </button>
                            </div>
                            <div class="col-12 col-sm-4">
                              <button class="btn btn-primary w-100" type="submit">
                                Simpan
                              </button>
                            </div>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
              @endif
            </div>
          </div>
        @endforeach

        <div class="d-flex justify-content-between">
          @if (!$all_questions->onFirstPage())
            <a href="{{ $all_questions->previousPageUrl() }}" class="btn btn-primary">Sebelumnya</a>
          @endif
          @if ($all_questions->hasMorePages())
            <a href="{{ $all_questions->nextPageUrl() }}" class="btn btn-primary ms-auto">Selanjutnya</a>
          @endif
        </div>
      @else
        <div class="alert alert-light mb-0" role="alert">
          <p class="text-center mb-0">Data soal tidak ditemukan.</p>
        </div>
      @endif
    </div>
  </div>

  <script>
    $(document).ready(function() {
      $('[id^=full-grade-button-]').on('click', function() {
        var idParts = $(this).attr('id').split('-');
        var questionId = idParts[3] + '-' + idParts[4];
        $('#grade_' + questionId).val('{{ $question->point }}');
      });

      $('[id^=zero-grade-button-]').on('click', function() {
        var idParts = $(this).attr('id').split('-');
        var questionId = idParts[3] + '-' + idParts[4];
        $('#grade_' + questionId).val(0);
      });

      $(document).on('input', 'input[id^=grade_]', function() {
        var input = $(this);
        var maxPoints = input.data('question-point');
        if (typeof maxPoints === 'undefined') {
          maxPoints = 0;
        } else {
          maxPoints = parseInt(maxPoints, 10);
        }

        if (!isNaN(maxPoints) && input.val() > maxPoints) {
          input.val(maxPoints);
        }
      });
    });
  </script>


</x-app-layout>
