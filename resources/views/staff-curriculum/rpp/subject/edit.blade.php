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
        Rpp
    </x-slot>

    <section class="section">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Edit Materi Pokok</h4>
                    </div>
                    <div class="card-body">
                        <form id="rppForm"
                            action="{{ route('teacher.bank.edit_subject_matter', ['rpp_id' => $rpp_id, 'id' => $subject_matter_id]) }}"
                            method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3 row">
                                <div class="col-12 col-sm-6">
                                    <label class="form-label">Materi Pokok<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="title"
                                        value="{{ old('title', $dataSubject->title ?? '') }}" required>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="form-label">Alokasi Waktu<span class="text-danger">*</span>(format:
                                        HH:MM)</label>
                                    <input type="text" class="form-control" name="time_allocation"
                                        placeholder="Contoh: 02:30"
                                        value="{{ old('time_allocation', $dataSubject->time_allocation ?? '') }}"
                                        required pattern="\d{2}:\d{2}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tujuan Pembelajaran<span class="text-danger">*</span></label>
                                <textarea id="tujuan_pembelajaran" name="learning_goals">{{ old('learning_goals', $dataSubject->learning_goals ?? '') }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kegiatan Pembelajaran<span
                                        class="text-danger">*</span></label>
                                <textarea id="kegiatan_pembelajaran" name="learning_activity">{{ old('learning_activity', $dataSubject->learning_activity ?? '') }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Penilaian<span class="text-danger">*</span></label>
                                <textarea id="penilaian" name="grading">{{ old('grading', $dataSubject->grading ?? '') }}</textarea>
                            </div>
                            <div class="w-100 mt-4">
                                <button type="submit" class="btn btn-primary w-100">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.ckeditor.com/ckeditor5/34.2.0/classic/ckeditor.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            ClassicEditor.create(document.querySelector("#tujuan_pembelajaran")).catch((error) => {
                console.error(error)
            });
            ClassicEditor.create(document.querySelector("#kegiatan_pembelajaran")).catch((error) => {
                console.error(error)
            });
            ClassicEditor.create(document.querySelector("#penilaian")).catch((error) => {
                console.error(error)
            });


            var timeAllocationInput = document.querySelector('input[name="time_allocation"]');
            var timeAllocation = timeAllocationInput.value;

            if (timeAllocation.includes(':')) {
                var timeParts = timeAllocation.split(':');
                if (timeParts.length === 3) {
                    timeAllocationInput.value = timeParts[0] + ':' + timeParts[1];
                }
            }
        });
    </script>

</x-app-layout>
