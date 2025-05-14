<x-app-layout>
  <x-slot:title>
      {{ $title }}
  </x-slot>
  <div class="page-heading">
      <div class="page-title">
          <div class="row">
              <div class="col-12 col-md-6 order-md-1 order-last">
                  <h3>Form Siswa</h3>
              </div>
              <div class="col-12 col-md-6 order-md-2 order-first">
                  <nav aria-label="breadcrumb" class="breadcrumb-header float-lg-end float-start">
                      <ol class="breadcrumb">
                          <li class="breadcrumb-item"><a href="{{ route('staff_curriculum.siswa') }}">Daftar Siswa</a></li>
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

              <form action="/staff-curriculum/user" method="POST">
                @method('PUT')
                @csrf
                <input type="hidden" name="id" id="id" value="{{ $user->id }}" required />

                <input type="hidden" name="role" value="student">

                <div class="form-group">
                    <label for="nisn">NISN</label>
                    <input type="text" class="form-control" name="nisn" id="nisn" placeholder="NISN" value="{{ $user->is_student?->nisn }}" required />
                </div>

                <div class="form-group">
                    <label for="fullname">Nama Lengkap</label>
                    <input type="text" class="form-control" name="fullname" id="fullname" placeholder="Nama Lengkap" value="{{ $user->fullname }}" required />
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="text" class="form-control" name="email" id="email" placeholder="Email" value="{{ $user->email }}" required />
                </div>

                <div class="form-group">
                    <label for="phone">No. Telepon</label>
                    <input type="text" class="form-control" name="phone" id="phone" placeholder="No. Telepon" value="{{ $user->phone }}" required />
                </div>

                <div class="form-group">
                    <label for="religion">Agama</label>
                    <select class="choices form-select" name="religion" id="religion" value="{{ $user->religion }}" required>
                        <option value="" selected disabled>Pilih Agama</option>
                        <option value="Islam" {{ $user->religion == 'Islam' ? 'selected' : '' }}>Islam</option>
                        <option value="Kristen" {{ $user->religion == 'Kristen' ? 'selected' : '' }}>Kristen</option>
                        <option value="Hindu" {{ $user->religion == 'Hindu' ? 'selected' : '' }}>Hindu</option>
                        <option value="Budha" {{ $user->religion == 'Budha' ? 'selected' : '' }}>Budha</option>
                        <option value="Konghucu" {{ $user->religion == 'Konghucu' ? 'selected' : '' }}>Konghucu</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="year">Tahun</label>
                    <input type="text" class="form-control" name="year" id="year" placeholder="Tahun" value="{{ $user->is_student?->year }}" required />
                </div>

                <div class="form-group">
                    <label for="gender">Jenis Kelamin</label>
                    <select class="choices form-select" name="gender" id="gender" value="{{ $user->gender }}" required>
                        <option value="" selected disabled>Pilih Jenis Kelamin</option>
                        <option value="laki" {{ $user->gender == 'laki' ? 'selected' : '' }}>Pria</option>
                        <option value="perempuan" {{ $user->gender == 'perempuan' ? 'selected' : '' }}>Wanita</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="sub_class_id">Sub Kelas</label>
                    <select class="choices form-select" name="sub_class_id" id="sub_class_id"
                        value="{{ $user->is_student?->sub_class_id }}" required>
                        <option value="" selected disabled>Pilih Sub Kelas</option>
                        @foreach ($subClassData as $subClass)
                            <option value="{{ $subClass->id }}" {{ $user->is_student?->sub_class_id == $subClass->id ? 'selected' : '' }}>{{ $subClass->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="address">Alamat</label>
                    <textarea type="text" class="form-control" name="address" id="address" placeholder="Alamat" required rows="3">{{ $user->address }}</textarea>
                </div>

                <div class="form-group">
                    <label for="password">Kata Sandi</label>
                    <input type="password" class="form-control" name="password" id="password" placeholder="Kata Sandi" value="{{ old('password') }}" required />
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Konfirmasi Kata Sandi</label>
                    <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" placeholder="Konfirmasi Kata Sandi" value="{{ old('password_confirmation') }}" required />
                </div>

                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-primary text-end" data-bs-dismiss="modal">
                        Simpan
                    </button>
                </div>
            </form>
          </div>
      </div>
  </section>
</x-app-layout>
