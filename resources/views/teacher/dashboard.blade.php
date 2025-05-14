<x-app-layout>
  <x-slot:title>
    Dashboard
  </x-slot>

  <x-breadcrumb :title="$customTheme->title ?? 'Nama Sekolah'" :subtitle="'TMB Learning Management System'" :breadcrumbs="[['label' => 'Dashboard', 'url' => null]]" :show-notifications="true" :unread-notifications="$unreadNotifications"
    :customTheme="$customTheme" />

  <div class="row">
    <div class="col-12 col-lg-3 col-md-6">
      <div class="card" style="background-color: {{ $customTheme->primary_color ?? 'bg-white' }};">
        <div class="card-body px-4 py-4-5">
          <div class="row">
            <div class="col-6 col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
              <div class="stats-icon purple mb-2">
                <i class="bi bi-people d-flex justify-content-center align-items-center"></i>
              </div>
            </div>
            <div class="col-6 col-md-8 col-lg-12 col-xl-12 col-xxl-7">
              <h6 class="font-semibold" style="color: {{ $customTheme->accent_color ?? 'text-muted' }};">Bank Tugas
              </h6>
              <h6 class="font-extrabold mb-0" style="color: {{ $customTheme->accent_color ?? 'text-primary' }};">
                {{ $assignmentBankCount }}</h6>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-3 col-md-6">
      <div class="card" style="background-color: {{ $customTheme->primary_color ?? 'bg-white' }};">
        <div class="card-body px-4 py-4-5">
          <div class="row">
            <div class="col-6 col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
              <div class="stats-icon blue mb-2">
                <i class="bi bi-card-text d-flex justify-content-center align-items-center"></i>
              </div>
            </div>
            <div class="col-6 col-md-8 col-lg-12 col-xl-12 col-xxl-7">
              <h6 class="font-semibold" style="color: {{ $customTheme->accent_color ?? 'text-muted' }};">Bank Soal
              </h6>
              <h6 class="font-extrabold mb-0" style="color: {{ $customTheme->accent_color ?? 'text-primary' }};">
                {{ $questionBankCount }}</h6>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-3 col-md-6">
      <div class="card" style="background-color: {{ $customTheme->primary_color ?? 'bg-white' }};">
        <div class="card-body px-4 py-4-5">
          <div class="row">
            <div class="col-6 col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
              <div class="stats-icon green mb-2">
                <i class="bi bi-clipboard-check  d-flex justify-content-center align-items-center"></i>
              </div>
            </div>
            <div class="col-6 col-md-8 col-lg-12 col-xl-12 col-xxl-7">
              <h6 class="font-semibold" style="color: {{ $customTheme->accent_color ?? 'text-muted' }};">Tugas Aktif
              </h6>
              <h6 class="font-extrabold mb-0" style="color: {{ $customTheme->accent_color ?? 'text-primary' }};">
                {{ $assignmentsActiveCount }}</h6>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-3 col-md-6">
      <div class="card" style="background-color: {{ $customTheme->primary_color ?? 'bg-white' }};">
        <div class="card-body px-4 py-4-5">
          <div class="row">
            <div class="col-6 col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
              <div class="stats-icon red mb-2">
                <i class="bi bi-clock-history d-flex justify-content-center align-items-center"></i>
              </div>
            </div>
            <div class="col-6 col-md-8 col-lg-12 col-xl-12 col-xxl-7">
              <h6 class="font-semibold" style="color: {{ $customTheme->accent_color ?? 'text-muted' }};">Ulangan
                Berlangsung</h6>
              <h6 class="font-extrabold mb-0" style="color: {{ $customTheme->accent_color ?? 'text-primary' }};">
                {{ $classExamCount }}</h6>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
