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
          </div>
      </div>
  </section>
</x-app-layout>
