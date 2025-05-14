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
    Riwayat Pengerjaan
  </x-slot>

  @php
    $title = 'Daftar Riwayat Pengerjaan ' . $class_level['name'] . ' Pelajaran ' . $courses_name['name'];

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
        <div class="alert alert-light mb-0" role="alert">
          <div class="d-flex justify-content-between">
            <div>
              <h5 class="mb-0 fw-bold fs-2">{{ $student['name'] }}</h5>
              <p class="text-muted mb-0">Melakukan {{ $student['total_attempts'] }} kali pengerjaan.</p>
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
                <h5><span class="badge bg-secondary">Belum ada pengerjaan</span></h5>
                <p class="text-muted">Nilai Akhir</p>
              </div>
            @endif
          </div>
        </div>
      @else
        <div class="alert alert-light mb-0" role="alert">
          <p class="text-center mb-0">Data murid tidak ditemukan.</p>
        </div>
      @endif

      <x-datatable title="Riwayat Pengerjaan">
        <div class="table-responsive pt-3">
          <table class="table" id="pengerjaan">
            <thead>
              <tr>
                <th>Urutan</th>
                <th>Waktu Mulai</th>
                <th>Waktu Selesai</th>
                <th>Nilai</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              {{-- @php
                dd($student['attempts_history']);
              @endphp --}}
              @forelse ($student['attempts_history'] ?? [] as $attempt)
                <tr>
                  <td>{{ $attempt['order'] }}</td>
                  <td>{{ $attempt['start_time'] }}</td>
                  <td>{{ $attempt['end_time'] ?? 'Tidak diketahui' }}</td>
                  <td>
                    @if ($attempt['is_essay_graded'])
                      <span class="badge bg-success">
                        {{ $attempt['total_points_with_essay'] }}
                      </span>
                    @else
                      <span class="text-muted">
                        {{ $attempt['initial_points'] }}
                      </span><br>
                      <span class="text-sm fw-bold">
                        {{ $attempt['score'] }}
                      </span><br>
                      <span class="text-muted">
                        {{ $attempt['status'] }}
                      </span>
                    @endif
                  </td>
                  <td>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                      data-bs-target="#modalNilaiAkhir{{ $attempt['response_id'] }}">Jadikan
                      Nilai Akhir</button>
                    <a href="{{ route('teacher.sekolah.v_penilaian_ulasan', ['ujian_id' => $ujian_id, 'student_id' => str_replace('/', '-', $student['student_id']), 'response_id' => $attempt['response_id']]) }}"
                      class="btn btn-warning btn-sm">Ulasan</a>
                  </td>
                </tr>

                <!-- Modal Nilai Akhir -->
                <div class="modal fade" id="modalNilaiAkhir{{ $attempt['response_id'] }}" tabindex="-1"
                  aria-labelledby="modalNilaiAkhirLabel{{ $attempt['response_id'] }}" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="modalNilaiAkhirLabel{{ $attempt['response_id'] }}">
                          Konfirmasi
                          Nilai Akhir</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        Apakah Anda yakin ingin menjadikan nilai akhir pengerjaan ini?
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <form
                          action="{{ route('teacher.sekolah.update_is_main', ['ujian_id' => $ujian_id, 'student_id' => str_replace('/', '-', $student['student_id']), 'response_id' => $attempt['response_id']]) }}"
                          method="POST">
                          @csrf
                          @method('PUT')
                          <input type="hidden" name="is_main" value="1">
                          <button type="submit" class="btn btn-primary">Ya, setuju</button>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>

              @empty
                <tr>
                  <td colspan="5" class="text-center">Tidak ada data.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </x-datatable>
    </div>
  </div>

  <script>
    const attempts = @json($student['attempts_history'] ?? []);
    $(document).ready(function() {
      let customized_datatable = $('#pengerjaan').DataTable({
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

      const setTableColor = () => {
        $('.dataTables_paginate .pagination').addClass('pagination-primary');
      };

      setTableColor();

      if (attempts.length > 0) {
        customized_datatable.on('draw', setTableColor);
      }
    });
  </script>
</x-app-layout>
