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

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title mb-0">Detail Materi Pokok</h3>
        </div>
        <div class="card-body pt-4">
            <div class="row mb-3">
                <div class="col-md-6">
                    <h5 class="font-weight-bold">Judul Materi:</h5>
                    <p>{{ $dataSubject->title }}</p>
                </div>
                <div class="col-md-6">
                    <h5 class="font-weight-bold">Alokasi Waktu:</h5>
                    <p id="timeAllocation">{{ $dataSubject->time_allocation }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-12">
                    <h5 class="font-weight-bold">Tujuan Pembelajaran:</h5>
                    <div class="border p-3 rounded">
                        {!! $dataSubject->learning_goals !!}
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-12">
                    <h5 class="font-weight-bold">Aktivitas Pembelajaran:</h5>
                    <div class="border p-3 rounded">
                        {!! $dataSubject->learning_activity !!}
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-12">
                    <h5 class="font-weight-bold">Penilaian:</h5>
                    <div class="border p-3 rounded">
                        {!! $dataSubject->grading !!}
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ route('teacher.bank.v_bank_rpp_detail', ['rpp_id' => $rpp_id]) }}"
                    class="btn btn-secondary">Kembali</a>
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
        });
    </script>

</x-app-layout>
