<x-app-layout>
  <x-slot:title>
      {{ $title }}
  </x-slot>
  <div class="page-heading">
      <div class="page-title">
          <div class="row">
              <div class="col-12 col-md-6 order-md-1 order-last">
                  <h3>Form Staff</h3>
              </div>
              <div class="col-12 col-md-6 order-md-2 order-first">
                  <nav aria-label="breadcrumb" class="breadcrumb-header float-lg-end float-start">
                      <ol class="breadcrumb">
                          <li class="breadcrumb-item"><a href="{{ route('admin.staff') }}">Daftar Staff</a></li>
                          <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
                      </ol>
                  </nav>
              </div>
          </div>
      </div>
  </div>

  <section class="section">
      <div class="card">
          <div class="card-header">
              <h4 class="card-title">{{ $title }}</h4>
          </div>
          <div class="card-body">
              @if ($errors->any())
                  <div class="alert alert-danger">
                      <ul>
                          @foreach ($errors->all() as $error)
                              <li>{{ $error }}</li>
                          @endforeach
                      </ul>
                  </div>
              @endif

              <form action="/admin/user" method="POST">
                @csrf

                <input type="hidden" name="role" value="staff">

                <div class="form-group">
                    <label for="nip">NIP<span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nip" id="nip" placeholder="NIP" value="{{ old('nip') }}" required />
                </div>

                <div class="form-group">
                    <label for="fullname">Nama Lengkap<span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="fullname" id="fullname" placeholder="Nama Lengkap" value="{{ old('fullname') }}" required />
                </div>

                <div class="form-group">
                    <label for="email">Email<span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="email" id="email" placeholder="Email" value="{{ old('email') }}" required />
                </div>

                <div class="form-group">
                    <label for="phone">No. Telepon<span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="phone" id="phone" placeholder="No. Telepon" value="{{ old('phone') }}" required />
                </div>

                <div class="form-group">
                    <label for="religion">Agama<span class="text-danger">*</span></label>
                    <select class="choices form-select" name="religion" id="religion"
                        value="{{ old('religion') }}" required>
                        <option value="" selected disabled>Pilih Agama</option>
                        <option value="Islam">Islam</option>
                        <option value="Kristen">Kristen</option>
                        <option value="Hindu">Hindu</option>
                        <option value="Budha">Budha</option>
                        <option value="Konghucu">Konghucu</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="gender">Jenis Kelamin<span class="text-danger">*</span></label>
                    <select class="choices form-select" name="gender" id="gender" value="{{ old('gender') }}" required>
                        <option value="" selected disabled>Pilih Jenis Kelamin</option>
                        <option value="laki">Pria</option>
                        <option value="perempuan">Wanita</option>
                    </select>
                </div>

                <div class="form-group">
                  <label for="placement">Penempatan<span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="placement" id="placement" placeholder="Penempatan" value="{{ old('placement') }}" required />
                </div>

                <div class="form-group">
                  <label for="authority">Authority<span class="text-danger">*</span></label>
                  <select class="choices form-select" name="authority" id="authority" value="{{ old('authority') }}" required>
                      <option value="" selected disabled>Pilih Authority</option>
                      <option value="KURIKULUM">Kurikulum</option>
                      <option value="ADMIN">Admin</option>
                  </select>
                </div>

                <div class="form-group">
                    <label for="password">Kata Sandi<span class="text-danger">*</span></label>
                    <input type="password" class="form-control" name="password" id="password" placeholder="Kata Sandi" value="{{ old('password') }}" required />
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Konfirmasi Kata Sandi<span class="text-danger">*</span></label>
                    <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" placeholder="Konfirmasi Kata Sandi" value="{{ old('password_confirmation') }}" required />
                </div>

                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-primary text-end" data-bs-dismiss="modal">
                        Tambah
                    </button>
                </div>
            </form>
          </div>
      </div>
  </section>
</x-app-layout>
