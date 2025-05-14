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

                <a href="{{ route('staff_curriculum.siswa_create') }}" class="btn btn-primary">
                    Tambah
                </a>
            </div>
            <div class="card-body">
                <table class="table" id="table">
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th>NISN</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>No. Telepon</th>
                            <th>Agama</th>
                            <th>Status</th>
                            {{-- <th class="text-center">Aksi</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($userStudentData as $user)
                            <tr>
                                <td class="text-center"></td>
                                <td>{{ $user->is_student?->nisn }}</td>
                                <td>{{ $user->fullname }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->phone }}</td>
                                <td>{{ $user->religion }}</td>
                                <td>{{ $user->status }}</td>
                                {{-- <td class="d-flex justify-content-center flex-nowrap">
                                    <a href="{{ route('staff_curriculum.siswa_update', ['id' => $user->id]) }}"
                                        class="btn btn-primary me-1">
                                        Ubah
                                    </a>

                                    <form action="/staff-curriculum/user" method="POST"
                                        onsubmit="return confirm('Hapus data?')">
                                        @method('DELETE')
                                        @csrf
                                        <input type="hidden" name="id" id="id" value="{{ $user->id }}"
                                            required />

                                        <button type="submit" class="btn btn-danger">
                                            Hapus
                                        </button>
                                    </form>
                                </td> --}}
                            </tr>
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
