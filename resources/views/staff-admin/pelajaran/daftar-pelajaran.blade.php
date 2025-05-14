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
                    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
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
                                <form action="/staff-administrator/course" method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="col-12 col-sm-6">
                                            <div class="form-group">
                                                <label for="type">
                                                    Tipe Pelajaran <span class="text-danger">*</span>
                                                </label>
                                                <select class="choices form-select" name="type" id="type"
                                                    value="{{ old('type') }}"
                                                    onchange="curriculumSelector(this.value, 'curriculum-div')"
                                                    required>
                                                    <option value="" selected disabled>
                                                        Pilih Tipe Pelajaran
                                                    </option>
                                                    <option value="Kurikulum">Kurikulum</option>
                                                    <option value="Lainnya">Lainnya</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-12 col-sm-6" id="curriculum-div"></div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12 col-sm-8">
                                            <div class="form-group">
                                                <label for="courses_title">
                                                    Nama Pelajaran <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" class="form-control" name="courses_title"
                                                    id="courses_title" placeholder="Nama Pelajaran"
                                                    value="{{ old('courses_title') }}" required />
                                            </div>
                                        </div>

                                        <div class="col-12 col-sm-4">
                                            <div class="form-group">
                                                <label for="course_code">
                                                    Kode Pelajaran <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" class="form-control" name="course_code"
                                                    id="course_code" placeholder="Kode Pelajaran"
                                                    value="{{ old('course_code') }}" required />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="courses_description" class="form-label">
                                            Deskripsi Pelajaran <span class="text-danger">*</span>
                                        </label>
                                        <textarea name="courses_description" id="courses_description">
                                            {{ old('courses_description') }}
                                        </textarea>
                                    </div>

                                    <div class="mt-3 text-end">
                                        <button type="submit" class="btn btn-primary text-end"
                                            data-bs-dismiss="modal">
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
                            <th>Nama Pelajaran</th>
                            <th>Deskripsi Pelajaran</th>
                            <th>Tipe Pelajaran</th>
                            <th>Keterangan</th>
                            <th>Kode Pelajaran</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($courseData as $pelajaran)
                            <tr>
                                <td class="text-center"></td>
                                <td>{{ $pelajaran->courses_title }}</td>
                                <td>{!! $pelajaran->courses_description !!}</td>
                                <td>{{ $pelajaran->type }}</td>
                                <td>
                                    {{ $pelajaran->type === 'Kurikulum' ? "Kurikulum $pelajaran->curriculum" : $pelajaran->curriculum }}
                                </td>
                                <td>{{ $pelajaran->course_code }}</td>
                                <td class="d-flex justify-content-center flex-nowrap">
                                    <button type="button" class="btn btn-primary me-1" data-bs-toggle="modal"
                                        data-bs-target="#update-{{ $pelajaran->id }}">
                                        Ubah
                                    </button>

                                    <form action="/staff-administrator/course" method="POST"
                                        onsubmit="return confirm('Hapus data?')">
                                        @method('DELETE')
                                        @csrf
                                        <input type="hidden" name="id" id="id"
                                            value="{{ $pelajaran->id }}" required />

                                        <button type="submit" class="btn btn-danger">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <div class="modal fade modal-borderless text-left" id="update-{{ $pelajaran->id }}"
                                tabindex="-1" aria-labelledby="myModalLabel1" style="display: none;"
                                aria-hidden="true">
                                <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
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
                                            <form action="/staff-administrator/course" method="POST">
                                                @method('PUT')
                                                @csrf
                                                <input type="hidden" name="id" id="id"
                                                    value="{{ $pelajaran->id }}" required />

                                                <div class="row">
                                                    <div class="col-12 col-sm-6">
                                                        <div class="form-group">
                                                            <label for="type">
                                                                Tipe Pelajaran <span class="text-danger">*</span>
                                                            </label>
                                                            <select class="choices form-select" name="type"
                                                                id="type-{{ $pelajaran->id }}"
                                                                value="{{ $pelajaran->type }}"
                                                                onchange="curriculumSelector(this.value, 'curriculum-div-{{ $pelajaran->id }}')"
                                                                required>
                                                                <option value="" selected disabled>
                                                                    Pilih Tipe Pelajaran
                                                                </option>
                                                                <option value="Kurikulum"
                                                                    {{ $pelajaran->type === 'Kurikulum' ? 'selected' : '' }}>
                                                                    Kurikulum
                                                                </option>
                                                                <option value="Lainnya"
                                                                    {{ $pelajaran->type === 'Lainnya' ? 'selected' : '' }}>
                                                                    Lainnya
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-12 col-sm-6"
                                                        id="curriculum-div-{{ $pelajaran->id }}">
                                                        @if (isset($pelajaran->curriculum))
                                                            <div class="form-group">
                                                                <label for="curriculum">
                                                                    Kurikulum <span class="text-danger">*</span>
                                                                </label>
                                                                <select class="choices form-select" name="curriculum"
                                                                    id="curriculum"
                                                                    value="{{ $pelajaran->curriculum }}" required>
                                                                    <option value="" selected disabled>
                                                                        Pilih Kurikulum
                                                                    </option>
                                                                    <option value="K13"
                                                                        {{ $pelajaran->curriculum === 'K13' ? 'selected' : '' }}>
                                                                        K13
                                                                    </option>
                                                                    <option value="Merdeka"
                                                                        {{ $pelajaran->curriculum === 'Merdeka' ? 'selected' : '' }}>
                                                                        Merdeka
                                                                    </option>
                                                                </select>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-12 col-sm-8">
                                                        <div class="form-group">
                                                            <label for="courses_title">
                                                                Nama Pelajaran <span class="text-danger">*</span>
                                                            </label>
                                                            <input type="text" class="form-control"
                                                                name="courses_title" id="courses_title"
                                                                placeholder="Nama Pelajaran"
                                                                value="{{ $pelajaran->courses_title }}" required />
                                                        </div>
                                                    </div>

                                                    <div class="col-12 col-sm-4">
                                                        <div class="form-group">
                                                            <label for="course_code">
                                                                Kode Pelajaran <span class="text-danger">*</span>
                                                            </label>
                                                            <input type="text" class="form-control"
                                                                name="course_code" id="course_code"
                                                                placeholder="Kode Pelajaran"
                                                                value="{{ $pelajaran->course_code }}" required />
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label for="courses_description" class="form-label">
                                                        Deskripsi Pelajaran <span class="text-danger">*</span>
                                                    </label>
                                                    <textarea name="courses_description" id="courses_description-{{ $pelajaran->id }}">
                                                        {{ $pelajaran->courses_description }}
                                                    </textarea>
                                                </div>

                                                <div class="mt-3 text-end">
                                                    <button type="submit" class="btn btn-primary text-end"
                                                        data-bs-dismiss="modal">
                                                        Ubah
                                                    </button>
                                                </div>
                                            </form>

                                            <script>
                                                ClassicEditor.create(document.getElementById("courses_description-{{ $pelajaran->id }}")).catch((error) => {
                                                    console.error(error);
                                                });
                                            </script>
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
            const curriculumSelector = (selectedType, div) => {
                const curriculumDiv = document.getElementById(div);

                while (curriculumDiv.firstChild) {
                    curriculumDiv.removeChild(curriculumDiv.firstChild);
                }

                if (selectedType === 'Kurikulum') {
                    curriculumDiv.innerHTML =
                        `<div class="form-group">
                            <label for="curriculum">
                                Kurikulum <span class="text-danger">*</span>
                            </label>
                            <select class="choices form-select" name="curriculum" id="curriculum"
                                value="{{ old('curriculum') }}" required>
                                <option value="" selected disabled>Pilih Kurikulum</option>
                                <option value="K13">K13</option>
                                <option value="Merdeka">Merdeka</option>
                            </select>
                        </div>`;
                }
            }

            ClassicEditor.create(document.querySelector("#courses_description")).catch((error) => {
                console.error(error);
            });

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
