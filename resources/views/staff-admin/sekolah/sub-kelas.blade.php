<x-app-layout>
    <x-slot:title>
        {{ $title }}
    </x-slot>
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Nama Sekolah</h3>
                    <p class="text-subtitle text-muted">TMB Learning Management System</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-lg-end float-start">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="m-0 p-0">
                @foreach ($errors->all() as $error)
                    <li style="list-style: none">{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session()->has('success'))
        <div class="alert alert-primary alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <section class="section">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="card-title">{{ $title }}</h5>

                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#create">
                    Tambah
                </button>
                <div class="modal fade modal-borderless text-left" id="create" tabindex="-1"
                    aria-labelledby="myModalLabel1" style="display: none;" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-scrollable" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Tambah {{ $title }}</h5>
                                <button type="button" class="close rounded-pill" data-bs-dismiss="modal"
                                    aria-label="Close">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="feather feather-x">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form action="/staff-administrator/sub-class" method="POST">
                                    @csrf
                                    <div class="form-group">
                                        <label for="class_id">Kelas<span class="text-danger">*</span></label>
                                        <select class="choices form-select" name="class_id" id="class_id"
                                            value="{{ old('class_id') }}" required>
                                            <option value="" selected disabled>Pilih Kelas</option>
                                            @foreach ($classData as $kelas)
                                                <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="name">Nama Sub Kelas<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="name" id="name"
                                            placeholder="Sub Kelas" value="{{ old('name') }}" required />
                                    </div>

                                    <div class="form-group">
                                        <label for="guardian">Wali Kelas<span class="text-danger">*</span></label>
                                        <select class="choices form-select" name="guardian" id="guardian"
                                            value="{{ old('guardian') }}" required>
                                            <option value="" selected disabled>Pilih Wali Kelas</option>
                                            @foreach ($teachers as $teacher)
                                                <option value="{{ $teacher->fullname }}">{{ $teacher->fullname }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mt-3 text-end">
                                        <button type="submit" class="btn btn-primary text-end" data-bs-dismiss="modal">
                                            Tambah
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table class="table" id="table">
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th>Kelas</th>
                            <th>Sub Kelas</th>
                            <th>Guardian</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($subClassData as $subKelas)
                            <tr>
                                <td class="text-center"></td>
                                <td>
                                    @foreach ($classData as $kelas)
                                        @if ($kelas->id == $subKelas->class_id)
                                            {{ $kelas->name }}
                                        @endif
                                    @endforeach
                                </td>
                                <td>{{ $subKelas->name }}</td>
                                <td>{{ $subKelas->guardian }}</td>
                                <td class="d-flex justify-content-center flex-nowrap">
                                    <button type="button" class="btn btn-primary me-1" data-bs-toggle="modal"
                                        data-bs-target="#update-{{ $subKelas->id }}">
                                        Ubah
                                    </button>

                                    <form action="/staff-administrator/sub-class" method="POST"
                                        onsubmit="return confirm('Hapus data?')">
                                        @method('DELETE')
                                        @csrf
                                        <input type="hidden" name="id" id="id"
                                            value="{{ $subKelas->id }}" required />

                                        <button type="submit" class="btn btn-danger">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <div class="modal fade modal-borderless text-left" id="update-{{ $subKelas->id }}"
                                tabindex="-1" aria-labelledby="myModalLabel1" style="display: none;"
                                aria-hidden="true">
                                <div class="modal-dialog modal-dialog-scrollable" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Ubah {{ $title }}</h5>
                                            <button type="button" class="close rounded-pill" data-bs-dismiss="modal"
                                                aria-label="Close">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="feather feather-x">
                                                    <line x1="18" y1="6" x2="6"
                                                        y2="18"></line>
                                                    <line x1="6" y1="6" x2="18"
                                                        y2="18"></line>
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="/staff-administrator/sub-class" method="POST">
                                                @method('PUT')
                                                @csrf
                                                <input type="hidden" name="id" id="id"
                                                    value="{{ $subKelas->id }}" required />

                                                <div class="form-group">
                                                    <label for="class_id">Kelas<span class="text-danger">*</span></label>
                                                    <select class="choices form-select" name="class_id"
                                                        id="class_id" required>
                                                        <option value="" selected disabled>Pilih Kelas</option>
                                                        @foreach ($classData as $kelas)
                                                            @if ($kelas->id == $subKelas->class_id)
                                                                <option value="{{ $kelas->id }}" selected>
                                                                    {{ $kelas->name }}
                                                                </option>
                                                            @else
                                                                <option value="{{ $kelas->id }}">
                                                                    {{ $kelas->name }}
                                                                </option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="form-group">
                                                    <label for="name">Nama Sub Kelas<span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="name"
                                                        id="name" placeholder="Sub Kelas"
                                                        value="{{ $subKelas->name }}" required />
                                                </div>

                                                <div class="form-group">
                                                    <label for="guardian">Wali Kelas<span class="text-danger">*</span></label>
                                                    <select class="choices form-select" name="guardian" id="guardian"
                                                        value="{{ old('guardian', $subKelas->guardian) }}" required>
                                                        <option value="" selected disabled>Pilih Wali Kelas</option>
                                                        @foreach ($teachers as $teacher)
                                                            <option value="{{ $teacher->fullname }}" {{ old('guardian', $subKelas->guardian) == $teacher->fullname ? 'selected' : '' }}>{{ $teacher->fullname }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="mt-3 text-end">
                                                    <button type="submit" class="btn btn-primary text-end"
                                                        data-bs-dismiss="modal">
                                                        Ubah
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <script>
            const table = $("#table").DataTable({
                columnDefs: [{
                    orderable: false,
                    searchable: false,
                    targets: [0, -1],
                }],
                order: [1, 'asc'],
                responsive: true,
                dom: "<'row'<'col-3'l><'col-9'f>>" +
                    "<'row dt-row'<'col-sm-12'tr>>" +
                    "<'row'<'col-4'i><'col-8'p>>",
                language: {
                    "info": "Halaman _PAGE_ dari _PAGES_",
                    "infoEmpty": "Tidak ada data",
                    "infoFiltered": "(difilter dari total _MAX_ data)",
                    "lengthMenu": "_MENU_",
                    "search": "",
                    "searchPlaceholder": "Cari data...",
                    "zeroRecords": "Tidak ada data yang cocok",
                    "paginate": {
                        "first": "Pertama",
                        "last": "Terakhir",
                        "next": "Selanjutnya",
                        "previous": "Sebelumnya"
                    }
                }
            })

            table.on('order.dt search.dt', () => {
                let i = 1

                table.cells(null, 0, {
                    order: 'applied',
                    search: 'applied',
                }).every(function(cell) {
                    this.data(i++)
                })
            }).draw()

            const setTableColor = () => {
                document.querySelectorAll('.dataTables_paginate .pagination').forEach(dt => {
                    dt.classList.add('pagination-primary')
                })
            }
            setTableColor()
            jquery_datatable.on('draw', setTableColor)
        </script>
    </section>
</x-app-layout>
