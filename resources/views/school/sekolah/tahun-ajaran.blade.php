<x-app-layout>
    <x-slot:title>
        {{ $title }}
    </x-slot>
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>{{ $cmsData->title }}</h3>
                    <p class="text-subtitle text-muted">Learning Management System</p>
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
                                <form action="/admin/academic-year" method="POST">
                                    @csrf
                                    <div class="form-group">
                                        <label for="year">Tahun Ajaran</label>
                                        <input type="text" class="form-control" name="year" id="year"
                                            placeholder="YYYY/YYYY" value="{{ old('year') }}" autofocus required />
                                    </div>

                                    <div class="form-group">
                                        <label for="status">Status</label>
                                        <select class="choices form-select" name="status" id="status"
                                            value="{{ old('status') }}" required>
                                            <option value="" selected disabled>Pilih Status</option>
                                            <option value="Aktif">Aktif</option>
                                            <option value="Tidak Aktif">Tidak Aktif</option>
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
                            <th>Tahun Ajaran</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($academicYearData as $tahun)
                            <tr>
                                <td class="text-center"></td>
                                <td>{{ $tahun->year }}</td>
                                <td>{{ $tahun->status }}</td>
                                <td class="d-flex justify-content-center flex-nowrap">
                                    <button type="button" class="btn btn-primary me-1" data-bs-toggle="modal"
                                        data-bs-target="#update-{{ $tahun->id }}">
                                        Ubah
                                    </button>

                                    <form action="/admin/academic-year" method="POST"
                                        onsubmit="return confirm('Hapus data?')">
                                        @method('DELETE')
                                        @csrf
                                        <input type="hidden" name="id" id="id"
                                            value="{{ $tahun->id }}" required />

                                        <button type="submit" class="btn btn-danger">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <div class="modal fade modal-borderless text-left" id="update-{{ $tahun->id }}"
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
                                            <form action="/admin/academic-year" method="POST">
                                                @method('PUT')
                                                @csrf
                                                <input type="hidden" name="id" id="id"
                                                    value="{{ $tahun->id }}" required />

                                                <div class="form-group">
                                                    <label for="year">Tahun Ajaran</label>
                                                    <input type="text" class="form-control" name="year"
                                                        id="year" placeholder="YYYY/YYYY"
                                                        value="{{ $tahun->year }}" required />
                                                </div>

                                                <div class="form-group">
                                                    <label for="status">Status</label>
                                                    <select class="choices form-select" name="status" id="status"
                                                        required>
                                                        <option value="" disabled>Pilih Status
                                                        </option>
                                                        <option value="Aktif"
                                                            @if ($tahun->status == 'Aktif') selected @endif>
                                                            Aktif
                                                        </option>
                                                        <option value="Tidak Aktif"
                                                            @if ($tahun->status == 'Tidak Aktif') selected @endif>
                                                            Tidak Aktif
                                                        </option>
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
