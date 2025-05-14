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
        Draft Materi
    </x-slot>

    <x-datatable :title="$data->draft_name">
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="mb-3">
                    <a href="{{ route('teacher.bank.v_add_subject_matter', ['rpp_id' => $data->id]) }}"
                        class="btn btn-primary">
                        Tambah Materi Pokok
                    </a>
                </div>
            </div>
        </div>
        <table class="table" id="table-rpp-bank-detail">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Materi Pokok</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="materiPokokTableBody">
                @forelse ($data->subject_matters as $index => $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->title }}</td>
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('teacher.bank.v_edit_subject_matter', ['rpp_id' => $data->id, 'id' => $item->id]) }}"
                                    class="btn btn-primary btn-sm">
                                    <i class="bi bi-pencil-square"></i>
                                    Edit
                                </a>
                                <a href="{{ route('teacher.bank.v_subject_matter_detail', ['rpp_id' => $data->id, 'id' => $item->id]) }}"
                                    class="btn btn-success btn-sm">
                                    <i class="bi bi-eye"></i> Lihat
                                </a>
                                <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#deleteModal{{ $item->id }}">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </div>
                        </td>
                    </tr>

                    {{-- Modal Delete --}}
                    <div class="modal fade" id="deleteModal{{ $item->id }}" tabindex="-1"
                        aria-labelledby="deleteModalLabel{{ $item->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="deleteModalLabel{{ $item->id }}">Hapus Materi Pokok
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Apakah anda yakin ingin menghapus materi pokok ini?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Tutup</button>
                                    <form
                                        action="{{ route('teacher.bank.delete_subject_matter', ['rpp_id' => $data->id, 'id' => $item->id]) }}"
                                        method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="3" class="text-center">Data tidak ditemukan. Silahkan tambah materi.</td>
                        <td class="d-none"></td>
                        <td class="d-none"></td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </x-datatable>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeDataTable();
        });

        function initializeDataTable() {
            let customized_datatable = $("#table-rpp-bank-detail").DataTable({
                responsive: true,
                pagingType: 'simple',
                dom: "<'row'<'col-3'l><'col-9'f>>" +
                    "<'row dt-row'<'col-sm-12'tr>>" +
                    "<'row'<'col-4'i><'col-8'p>>",
                "language": {
                    "info": "Page _PAGE_ of _PAGES_",
                    "lengthMenu": "_MENU_ ",
                    "search": "",
                    "searchPlaceholder": "Search.."
                }
            });

            const setTableColor = () => {
                document.querySelectorAll('.dataTables_paginate .pagination').forEach(dt => {
                    dt.classList.add('pagination-primary');
                });
            };
            setTableColor();
            customized_datatable.on('draw', setTableColor);
        }
    </script>
</x-app-layout>
