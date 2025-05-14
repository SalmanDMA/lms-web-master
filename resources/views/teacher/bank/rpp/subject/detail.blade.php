@include('teacher.custom-theme', ['customTheme' => $customTheme])

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

  @if (isset($message))
    <div class="alert {{ $alertClass }} alert-dismissible fade show" role="alert">
      {{ $message }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <x-slot:title>
    Detail Materi
  </x-slot>

  <x-breadcrumb :title="$customTheme->title ?? 'Nama Sekolah'" :subtitle="'TMB Learning Management System'" :breadcrumbs="[
      ['label' => 'Draft RPP', 'url' => route('teacher.bank.rpp')],
      ['label' => 'Subject', 'url' => route('teacher.bank.v_bank_rpp_detail', ['rpp_id' => $rpp_id])],
      ['label' => 'Add', 'url' => null],
  ]" :show-notifications="true" :unread-notifications="$unreadNotifications"
    :customTheme="$customTheme" />

  <div class="card">
    <div class="card-header card-header-custom">
      <h3 class="card-title mb-0 font-card-custom">Detail Materi Pokok</h3>
    </div>
    <div class="card-body card-body-custom pt-4">
      <div class="row mb-3">
        <div class="col-md-6">
          <h5 class="font-weight-bold font-custom">Judul Materi:</h5>
          <p class="font-custom">{{ $dataSubject->title }}</p>
        </div>
        <div class="col-md-6">
          <h5 class="font-weight-bold font-custom">Alokasi Waktu:</h5>
          <p id="timeAllocation" class="font-custom">{{ $dataSubject->time_allocation }}</p>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-12">
          <h5 class="font-weight-bold font-custom">Tujuan Pembelajaran:</h5>
          <div class="border p-3 rounded font-custom">
            {!! $dataSubject->learning_goals !!}
          </div>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-12">
          <h5 class="font-weight-bold font-custom">Aktivitas Pembelajaran:</h5>
          <div class="border p-3 rounded font-custom">
            {!! $dataSubject->learning_activity !!}
          </div>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-12">
          <h5 class="font-weight-bold font-custom">Penilaian:</h5>
          <div class="border p-3 rounded font-custom">
            {!! $dataSubject->grading !!}
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-end">
        <a href="{{ route('teacher.bank.v_bank_rpp_detail', ['rpp_id' => $rpp_id]) }}"
          class="btn btn-primary-custom">Kembali</a>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const timeAllocationElement = document.querySelector('#timeAllocation');
      let timeAllocation = timeAllocationElement.textContent.trim();

      function formatTimeAllocation(time) {
        const [hours, minutes, seconds] = time.split(':').map(Number);
        let result = '';

        if (hours > 0) {
          result += hours + ' jam ';
        }
        if (minutes > 0) {
          result += minutes + ' menit';
        }
        return result || '0 menit';
      }

      timeAllocationElement.textContent = formatTimeAllocation(timeAllocation);
      generateCustomTheme();
    });
  </script>

</x-app-layout>
