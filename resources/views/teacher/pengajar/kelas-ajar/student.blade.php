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
        Daftar Siswa
    </x-slot>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <div class="d-flex flex-column gap-2">
                <span class="mb-0 fs-2 fw-bold">Daftar Siswa</span>
                <div class="d-flex gap-2">
                    <span class="mb-0 fs-6">Tingkat: {{ $filteredSubClass->class->name }}</span>
                    <span class="mb-0 fs-6">Kelas: {{ $filteredSubClass->name }}</span>
                    <span class="mb-0 fs-6">Pelajaran: {{ $filteredCourse->courses_title }}</span>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                {{-- Siswa yang belum terdaftar --}}
                <div class="col-12 col-md-6 my-4 border">
                    <x-datatable title="Siswa Tidak Terdaftar">
                        <form id="form-left"
                            action="{{ route('teacher.pengajar.enroll_student', ['sub_class_id' => $sub_class_id, 'course_id' => $course_id]) }}"
                            method="POST">
                            @csrf
                            <div class="mb-3 d-flex justify-content-end align-items-center">
                                <button type="submit" class="btn btn-primary mt-3">Daftarkan Siswa</button>
                            </div>
                            <table class="table table-bordered table-responsive" id="table-unenrolled-students">
                                <thead>
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th class="text-center">NISN</th>
                                        <th class="text-center">Nama</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($filteredStudent as $student)
                                        <tr>
                                            <td class="text-center">
                                                <input class="form-check-input" type="checkbox"
                                                    name="students[{{ $loop->index }}][id]"
                                                    value="{{ $student->is_student->id }}"
                                                    id="student{{ $student->is_student->id }}">
                                            </td>
                                            <td class="text-center">{{ $student->is_student->nisn }}</td>
                                            <td class="text-center">{{ $student->fullname }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center">Data siswa tidak ditemukan.</td>
                                            <td class="d-none"></td>
                                            <td class="d-none"></td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </form>
                    </x-datatable>
                </div>

                {{-- Siswa yang sudah terdaftar --}}
                <div class="col-12 col-md-6 my-0 my-md-4 border">
                    <x-datatable title="Siswa Terdaftar">
                        <form id="form-right"
                            action="{{ route('teacher.pengajar.unenroll_student', ['sub_class_id' => $sub_class_id, 'course_id' => $course_id]) }}"
                            method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3 d-flex justify-content-end align-items-center">
                                <button type="submit" class="btn btn-danger mt-3">Keluarkan Siswa</button>
                            </div>
                            <table class="table table-bordered table-responsive" id="table-enrolled-students">
                                <thead>
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th class="text-center">NISN</th>
                                        <th class="text-center">Nama</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($allStudentOnEnrollment as $student)
                                        <tr>
                                            <td>
                                                <input class="form-check-input" type="checkbox"
                                                    name="students[{{ $loop->index }}][id]"
                                                    value="{{ $student->is_student->id }}"
                                                    id="student{{ $student->is_student->id }}">
                                            </td>
                                            <td class="text-center">{{ $student->is_student->nisn }}</td>
                                            <td class="text-center">{{ $student->fullname }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center">Data tidak ada, silahkan tambah.</td>
                                            <td class="d-none"></td>
                                            <td class="d-none"></td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </form>
                    </x-datatable>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    const enrollmentStudents = @json($allStudentOnEnrollment);
    const unenrollmentStudents = @json($filteredStudent);

    $(document).ready(function() {
        initializeDataTable('#table-enrolled-students');
        initializeDataTable('#table-unenrolled-students');
    });

    function initializeDataTable(tableId) {
        $(tableId).DataTable({
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
    }
</script>
