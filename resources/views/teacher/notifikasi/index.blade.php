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
        Notifikasi
    </x-slot>

    <x-datatable title="Notifikasi">
        <div class="row mb-4 align-items-center">
            <div class="col-12 col-md-4 mb-2 mb-md-0">
                <form id="readAllForm" action="{{ route('notifications.markAllRead') }}" method="POST">
                    @csrf
                    <input type="hidden" name="ids" id="allSelectedIds">
                    <button type="submit" id="readAllButton" class="btn btn-success" disabled>Baca Semua Pesan</button>
                </form>
            </div>
            <div class="col-12 col-md-8">
                <div class="row">
                    <div class="col-12 col-sm-6 mb-2 mb-sm-0">
                        <div class="mb-3">
                            <label class="form-label">Filter Status</label>
                            <select class="form-select" id="statusFilter" onchange="filterData()">
                                <option value="">- Pilih Status -</option>
                                <option value="0">Belum Dibaca</option>
                                <option value="1">Sudah Dibaca</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="mb-3">
                            <label class="form-label">Urutkan Berdasarkan</label>
                            <select class="form-select" id="sortFilter" onchange="filterData()">
                                <option value="">- Pilih Opsi -</option>
                                <option value="latest">Terbaru</option>
                                <option value="oldest">Terlama</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table" id="table-notification">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll" onclick="selectAll(this)"></th>
                        <th>#</th>
                        <th>Judul</th>
                        <th>Pesan</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="notification-body-table">
                    <!-- Data akan dimuat di sini oleh JavaScript -->
                </tbody>
            </table>
        </div>
    </x-datatable>

    <script>
        const notifications = @json($notifications);

        function filterData() {
            const status = $('#statusFilter').val();
            const sort = $('#sortFilter').val();

            let filteredNotifications = notifications;

            if (status) {
                filteredNotifications = filteredNotifications.filter(notification => notification.is_read == status);
            }

            if (sort) {
                filteredNotifications.sort((a, b) => {
                    const dateA = new Date(a.created_at);
                    const dateB = new Date(b.created_at);
                    return sort === 'latest' ? dateB - dateA : dateA - dateB;
                });
            }

            updateTable(filteredNotifications);
        }

        function updateTable(data) {
            if ($.fn.DataTable.isDataTable('#table-notification')) {
                $('#table-notification').DataTable().clear().destroy();
                $('#notification-body-table').empty();
            }

            const tableBody = $('#notification-body-table');
            tableBody.empty();

            if (data.length === 0) {
                tableBody.append('<tr><td colspan="7" class="text-center">Tidak ada data</td></tr>');
                return;
            }

            data.forEach((item, index) => {
                const statusText = item.is_read == '1' ? 'Sudah Dibaca' : 'Belum Dibaca';
                const statusClass = item.is_read == '1' ? 'bg-success' : 'bg-danger';
                const formattedDate = formatDate(item.created_at);

                tableBody.append(`
                    <tr>
                        <td><input type="checkbox" class="notification-checkbox" data-id="${item.id}" ${item.is_read == '1' ? 'checked disabled' : ''}></td>
                        <td>${index + 1}</td>
                        <td>${item.title}</td>
                        <td>${item.message}</td>
                        <td><span class="badge ${statusClass} text-sm">${statusText}</span></td>
                        <td>${formattedDate}</td>
                        <td>
                            ${item.is_read == '1' ? '' : `<form action="{{ route('notifications.markAllRead') }}" method="POST">
                                @csrf
                                <input type="hidden" name="ids" value="${item.id}">
                                <button type="submit" class="btn btn-primary btn-sm">Tandai Sebagai Dibaca</button>
                            </form>`}
                        </td>
                    </tr>
                `);
            });

            initializeDataTable();
            updateSelectAllCheckbox();
        }

        function formatDate(dateString) {
            const days = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
            const months = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September",
                "Oktober", "November", "Desember"
            ];

            const date = new Date(dateString);
            const dayName = days[date.getDay()];
            const day = date.getDate();
            const monthName = months[date.getMonth()];
            const year = date.getFullYear();

            return `${dayName}, ${day} ${monthName} ${year}`;
        }

        function initializeDataTable() {
            $('#table-notification').DataTable({
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

        function updateSelectAllCheckbox() {
            const $checkBox = $('#selectAll');
            const $notificationCheckboxes = $('.notification-checkbox');

            if ($notificationCheckboxes.length === 0) {
                $checkBox.prop('disabled', true).prop('checked', false);
                return;
            }

            $checkBox.prop('disabled', false);

            const allChecked = $notificationCheckboxes.length === $notificationCheckboxes.filter(':checked').length;
            $checkBox.prop('checked', allChecked);
            $checkBox.prop('disabled', allChecked);
        }

        function selectAll(selectAllCheckbox) {
            const isChecked = $(selectAllCheckbox).is(':checked');
            $('.notification-checkbox').each(function() {
                $(this).prop('checked', isChecked);
            });
            toggleReadAllButton();
        }

        function toggleReadAllButton() {
            const hasSelected = $('.notification-checkbox:checked').length > 0;
            $('#readAllButton').prop('disabled', !hasSelected);

            if (hasSelected) {
                const selectedIds = $('.notification-checkbox:checked').map(function() {
                    return $(this).data('id');
                }).get();
                $('#allSelectedIds').val(JSON.stringify(selectedIds));
            } else {
                $('#allSelectedIds').val('');
            }
        }

        $(document).ready(function() {
            if (notifications.length > 0) {
                filterData();
            }

            $('#notification-body-table').on('change', '.notification-checkbox', function() {
                updateSelectAllCheckbox();
                toggleReadAllButton();
            });

            $('#selectAll').on('change', function() {
                selectAll(this);
            });

            updateTable(notifications);
        });
    </script>


</x-app-layout>
