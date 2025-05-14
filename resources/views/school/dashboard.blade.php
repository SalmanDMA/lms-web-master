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

    <div class="row">
        <div class="col-12 col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body py-4-5 px-4">
                    <div class="row">
                        <div class="col-6 col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                            <div class="stats-icon purple mb-2">
                                <i class="bi bi-people d-flex justify-content-center align-items-center"></i>
                            </div>
                        </div>
                        <div class="col-6 col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                            <h6 class="font-semibold text-muted">Jumlah Siswa</h6>
                            <h6 class="mb-0 font-extrabold">{{ $dashboardData->jumlah_siswa }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body py-4-5 px-4">
                    <div class="row">
                        <div class="col-6 col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                            <div class="stats-icon blue mb-2">
                                <i class="bi bi-card-text d-flex justify-content-center align-items-center"></i>
                            </div>
                        </div>
                        <div class="col-6 col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                            <h6 class="font-semibold text-muted">Jumlah Guru</h6>
                            <h6 class="mb-0 font-extrabold">{{ $dashboardData->jumlah_guru }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body py-4-5 px-4">
                    <div class="row">
                        <div class="col-6 col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                            <div class="stats-icon green mb-2">
                                <i class="bi bi-clipboard-check d-flex justify-content-center align-items-center"></i>
                            </div>
                        </div>
                        <div class="col-6 col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                            <h6 class="font-semibold text-muted">Jumlah Pelajaran</h6>
                            <h6 class="mb-0 font-extrabold">{{ $dashboardData->jumlah_pelajaran }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body py-4-5 px-4">
                    <div class="row">
                        <div class="col-6 col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                            <div class="stats-icon red mb-2">
                                <i class="bi bi-clock-history d-flex justify-content-center align-items-center"></i>
                            </div>
                        </div>
                        <div class="col-6 col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                            <h6 class="font-semibold text-muted">Jumlah Kelas</h6>
                            <h6 class="mb-0 font-extrabold">{{ $dashboardData->jumlah_kelas }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body py-4-5 px-4">
                    <div class="row">
                        <div class="col-6 col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                            <div class="stats-icon green mb-2">
                                <i class="bi bi-clock-history d-flex justify-content-center align-items-center"></i>
                            </div>
                        </div>
                        <div class="col-6 col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                            <h6 class="font-semibold text-muted">Jumlah Jurusan</h6>
                            <h6 class="mb-0 font-extrabold">{{ $dashboardData->jumlah_jurusan }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
