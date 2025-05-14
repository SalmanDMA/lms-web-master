@php
    $isPremiumSchool = session('is_premium_school') ?? false;
@endphp

@if (session('role') === 'TEACHER')
    <x-mazer-sidebar :href="route('teacher.dashboard')" logo="{{ asset('assets/static/images/logo/logo_digy.png') }}">
        <x-mazer-sidebar-item icon="bi bi-grid-fill" :link="route('teacher.dashboard')" name="Dashboard" :active="request()->routeIs('teacher.dashboard')" />
        <x-mazer-sidebar-item icon="bi bi-archive-fill" name="Bank" :active="request()->routeIs('teacher.bank.rpp') ||
            request()->routeIs('teacher.bank.v_bank_rpp_detail') ||
            request()->routeIs('teacher.v_bank.materi') ||
            request()->routeIs('teacher.v_bank.tugas') ||
            request()->routeIs('teacher.v_bank.soal')">
            <x-mazer-sidebar-subitem :link="route('teacher.bank.rpp')" name="RPP" :active="request()->routeIs('teacher.bank.rpp')" />
            <x-mazer-sidebar-subitem :link="route('teacher.v_bank.materi')" name="Materi" :active="request()->routeIs('teacher.v_bank.materi')" />
            <x-mazer-sidebar-subitem :link="route('teacher.v_bank.tugas')" name="Tugas" :active="request()->routeIs('teacher.v_bank.tugas')" />
            <x-mazer-sidebar-subitem :link="route('teacher.v_bank.soal')" name="Soal" :active="request()->routeIs('teacher.v_bank.soal')" />
        </x-mazer-sidebar-item>
        <x-mazer-sidebar-item icon="bi bi-mortarboard-fill" name="Pengajar" :active="request()->routeIs('teacher.v_pengajar.rpp') ||
            request()->routeIs(
                'teacher.pengajar.v_rpp_detail' ||
                    request()->routeIs('teacher.v_pengajar.kelas_ajar') ||
                    request()->routeIs('teacher.v_pengajar.pembelajaran') ||
                    request()->routeIs('teacher.pengajar.pembelajaran.v_materi') ||
                    request()->routeIs('teacher.pengajar.pembelajaran.v_tugas') ||
                    request()->routeIs('teacher.pengajar.pembelajaran.v_ulangan'),
            )">
            <x-mazer-sidebar-subitem :link="route('teacher.v_pengajar.kelas_ajar')" name="Kelas Mengajar" :active="request()->routeIs('teacher.v_pengajar.kelas_ajar')" />
            <x-mazer-sidebar-subitem :link="route('teacher.v_pengajar.rpp')" name="Pengajuan RPP" :active="request()->routeIs('teacher.v_pengajar.rpp')" />
            <x-mazer-sidebar-subitem :link="route('teacher.v_pengajar.pembelajaran')" name="Pembelajaran" :active="request()->routeIs('teacher.v_pengajar.pembelajaran')" />
            <x-mazer-sidebar-subitem :link="route('teacher.pengajar.v_rekap_nilai')" name="Rekap Nilai" :active="request()->routeIs('teacher.pengajar.v_rekap_nilai')" />
        </x-mazer-sidebar-item>
        <x-mazer-sidebar-item icon="bi bi-building" name="Sekolah" :is_premium="$isPremiumSchool" :active="request()->routeIs('teacher.sekolah.v_ujian')">
            <x-mazer-sidebar-subitem :link="route('teacher.sekolah.v_ujian')" name="Ujian" :active="request()->routeIs('v_sekolah_ujian')" :is_premium="$isPremiumSchool" />
        </x-mazer-sidebar-item>
        <x-mazer-sidebar-item icon="bi bi-gear-fill" name="Pengaturan" :active="request()->routeIs('teacher.v_pengaturan.profile')">
            <x-mazer-sidebar-subitem :link="route('teacher.v_pengaturan.profile')" name="Profile" :active="request()->routeIs('teacher.v_pengaturan.profile')" />
            <x-mazer-sidebar-subitem :link="route('teacher.logout')" name="Logout" :active="request()->routeIs('teacher.logout')" />
        </x-mazer-sidebar-item>
    </x-mazer-sidebar>
@elseif (session('role') === 'STAFF')
    <x-mazer-sidebar :href="route('teacher.dashboard')" logo="{{ asset('assets/static/images/logo/logo_digy.png') }}">
        @if (session('user.authority') === 'KURIKULUM')
            <x-mazer-sidebar-item icon="bi bi-columns-gap" :link="route('staff_curriculum.dashboard')" name="Dashboard" :active="request()->routeIs('staff_curriculum.dashboard')" />
            <x-mazer-sidebar-item icon="bi bi-card-text" name="Pengajuan">
                <x-mazer-sidebar-subitem :link="route('staff_curriculum.pengajuan_rpp')" name="Pengajuan RPP" :active="request()->routeIs('staff_curriculum.pengajuan_rpp')" />
            </x-mazer-sidebar-item>
            <x-mazer-sidebar-item icon="bi bi-archive-fill" name="Kelas Mengajar">
                <x-mazer-sidebar-subitem :link="route('staff_curriculum.kelas_mengajar')" name="Kelas Mengajar" :active="request()->routeIs('staff_curriculum.kelas_mengajar')" />
            </x-mazer-sidebar-item>
            <x-mazer-sidebar-item icon="bi bi-building" name="Sekolah">
                <x-mazer-sidebar-subitem :link="route('staff_curriculum.tahun_ajaran')" name="Tahun Ajaran" :active="request()->routeIs('staff_curriculum.tahun_ajaran')" />
                <x-mazer-sidebar-subitem :link="route('staff_curriculum.jurusan')" name="Jurusan" :active="request()->routeIs('staff_curriculum.jurusan')" />
                <x-mazer-sidebar-subitem :link="route('staff_curriculum.kelas')" name="Kelas" :active="request()->routeIs('staff_curriculum.kelas')" />
                <x-mazer-sidebar-subitem :link="route('staff_curriculum.sub_kelas')" name="Sub Kelas" :active="request()->routeIs('staff_curriculum.sub_kelas')" />
            </x-mazer-sidebar-item>
            <x-mazer-sidebar-item icon="bi bi-journal-bookmark-fill" name="Pelajaran">
                <x-mazer-sidebar-subitem :link="route('staff_curriculum.daftar_pelajaran')" name="Daftar Pelajaran" :active="request()->routeIs('staff_curriculum.daftar_pelajaran')" />

            </x-mazer-sidebar-item>
            <x-mazer-sidebar-item icon="bi bi-list-check" name="Ujian">
                <x-mazer-sidebar-subitem :link="route('staff_curriculum.sekolah.v_ujian')" name="Ujian Sekolah" :active="request()->routeIs('staff_curriculum.sekolah.v_ujian')"
                    :is_premium="$isPremiumSchool" />
                <x-mazer-sidebar-subitem :link="route('staff_curriculum.bank.v_bank.soal')" name="Bank Soal" :active="request()->routeIs('staff_curriculum.bank.v_bank.soal')" />
            </x-mazer-sidebar-item>
            <x-mazer-sidebar-item icon="bi bi-people" name="User">
                <x-mazer-sidebar-subitem :link="route('staff_curriculum.guru')" name="Guru" :active="request()->routeIs('staff_curriculum.guru')" />
                <x-mazer-sidebar-subitem :link="route('staff_curriculum.siswa')" name="Siswa" :active="request()->routeIs('staff_curriculum.siswa')" />
                <x-mazer-sidebar-subitem :link="route('staff_curriculum.staff')" name="Staff" :active="request()->routeIs('staff_curriculum.staff')" />
            </x-mazer-sidebar-item>
            <x-mazer-sidebar-item icon="bi bi-box-arrow-right" :link="route('staff_curriculum.logout')" name="Keluar" :active="request()->routeIs('staff_curriculum.logout')" />
        @endif
        @if (session('user.authority') === 'ADMIN')
            <x-mazer-sidebar-item icon="bi bi-columns-gap" :link="route('staff_administrator.dashboard')" name="Dashboard" :active="request()->routeIs('staff_administrator.dashboard')" />
            <x-mazer-sidebar-item icon="bi bi-building" name="Sekolah">
                <x-mazer-sidebar-subitem :link="route('staff_administrator.tahun_ajaran')" name="Tahun Ajaran" :active="request()->routeIs('staff_administrator.tahun_ajaran')" />
                <x-mazer-sidebar-subitem :link="route('staff_administrator.jurusan')" name="Jurusan" :active="request()->routeIs('staff_administrator.jurusan')" />
                <x-mazer-sidebar-subitem :link="route('staff_administrator.kelas')" name="Kelas" :active="request()->routeIs('staff_administrator.kelas')" />
                <x-mazer-sidebar-subitem :link="route('staff_administrator.sub_kelas')" name="Sub Kelas" :active="request()->routeIs('staff_administrator.sub_kelas')" />
            </x-mazer-sidebar-item>
            <x-mazer-sidebar-item icon="bi bi-journal-bookmark-fill" name="Pelajaran">
                <x-mazer-sidebar-subitem :link="route('staff_administrator.daftar_pelajaran')" name="Daftar Pelajaran" :active="request()->routeIs('staff_administrator.daftar_pelajaran')" />
            </x-mazer-sidebar-item>
            <x-mazer-sidebar-item icon="bi bi-people" name="User">
                <x-mazer-sidebar-subitem :link="route('staff_administrator.guru')" name="Guru" :active="request()->routeIs('staff_administrator.guru')" />
                <x-mazer-sidebar-subitem :link="route('staff_administrator.siswa')" name="Siswa" :active="request()->routeIs('staff_administrator.siswa')" />
                <x-mazer-sidebar-subitem :link="route('staff_administrator.staff')" name="Staff" :active="request()->routeIs('staff_administrator.staff')" />
            </x-mazer-sidebar-item>
            <x-mazer-sidebar-item icon="bi bi-box-arrow-right" :link="route('staff_administrator.logout')" name="Keluar" :active="request()->routeIs('staff_administrator.logout')" />
        @endif
    </x-mazer-sidebar>
@elseif (session('role') === 'ADMIN')
    <x-mazer-sidebar :href="route('admin.dashboard')" logo="{{ asset('assets/static/images/logo/logo_digy.png') }}">
        <x-mazer-sidebar-item icon="bi bi-columns-gap" :link="route('admin.dashboard')" name="Dashboard" :active="request()->routeIs('admin.dashboard')" />
        <x-mazer-sidebar-item icon="bi bi-building" name="Sekolah">
            <x-mazer-sidebar-subitem :link="route('admin.tahun_ajaran')" name="Tahun Ajaran" :active="request()->routeIs('admin.tahun_ajaran')" />
            <x-mazer-sidebar-subitem :link="route('admin.jurusan')" name="Jurusan" :active="request()->routeIs('admin.jurusan')" />
            <x-mazer-sidebar-subitem :link="route('admin.kelas')" name="Kelas" :active="request()->routeIs('admin.kelas')" />
            <x-mazer-sidebar-subitem :link="route('admin.sub_kelas')" name="Sub Kelas" :active="request()->routeIs('admin.sub_kelas')" />
        </x-mazer-sidebar-item>
        <x-mazer-sidebar-item icon="bi bi-journal-bookmark-fill" name="Pelajaran">
            <x-mazer-sidebar-subitem :link="route('admin.daftar_pelajaran')" name="Daftar Pelajaran" :active="request()->routeIs('admin.daftar_pelajaran')" />
        </x-mazer-sidebar-item>
        <x-mazer-sidebar-item icon="bi bi-people" name="User">
            <x-mazer-sidebar-subitem :link="route('admin.guru')" name="Guru" :active="request()->routeIs('admin.guru')" />
            <x-mazer-sidebar-subitem :link="route('admin.siswa')" name="Siswa" :active="request()->routeIs('admin.siswa')" />
            <x-mazer-sidebar-subitem :link="route('admin.staff')" name="Staff" :active="request()->routeIs('admin.staff')" />
        </x-mazer-sidebar-item>
        <x-mazer-sidebar-item icon="bi bi-columns" :link="route('admin.cms')" name="CMS" :active="request()->routeIs('admin.cms')" />
        <x-mazer-sidebar-item icon="bi bi-box-arrow-right" :link="route('admin.logout')" name="Keluar" :active="request()->routeIs('admin.logout')" />
    </x-mazer-sidebar>
@endif
