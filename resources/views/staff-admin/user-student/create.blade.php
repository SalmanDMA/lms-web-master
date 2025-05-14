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
                            <li class="breadcrumb-item"><a href="{{ route('staff_administrator.siswa') }}">Daftar Siswa</a></li>
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
  
                <form action="/staff-administrator/user" method="POST">
                  @csrf
  
                <input type="hidden" name="role" value="student">

                <div class="form-group">
                    <label for="nisn">NISN<span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nisn" id="nisn" placeholder="NISN" value="{{ old('nisn') }}" required />
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
                    <label for="year">Tahun<span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="year" id="year" placeholder="Tahun" value="{{ old('year') }}" required />
                </div>

                <div class="form-group">
                    <label for="gender">Jenis Kelamin<span class="text-danger">*</span></label>
                    <select class="choices form-select" name="gender" id="gender"
                        value="{{ old('gender') }}" required>
                        <option value="" selected disabled>Pilih Jenis Kelamin</option>
                        <option value="laki">Pria</option>
                        <option value="perempuan">Wanita</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="sub_class_id">Sub Kelas<span class="text-danger">*</span></label>
                    <select class="choices form-select" name="sub_class_id" id="sub_class_id"
                        value="{{ old('sub_class_id') }}" required>
                        <option value="" selected disabled>Pilih Sub Kelas</option>
                        @foreach ($subClassData as $subClass)
                            <option value="{{ $subClass->id }}">{{ $subClass->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="address">Alamat<span class="text-danger">*</span></label>
                    <textarea type="text" class="form-control" name="address" id="address" placeholder="Alamat" required rows="3">{{ old('address') }}</textarea>
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

    <script>
        $(document).ready(function () {
            $('input[name="year"]').on('input', function (event) {
                if(event.target.value?.length > 4) {
                    event.target.value = event.target.value.substring(0, 4)
                }
            });
        });
    </script>
  </x-app-layout>
  