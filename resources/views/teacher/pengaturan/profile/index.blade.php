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
    Kelas Mengajar
  </x-slot>

  @php
    $userImage = str_replace('storage/public/', 'public/', $user->image_path);
  @endphp

  <div class="card">
    <div class="card-header bg-primary text-white">
      <span class="mb-0 fs-2 fw-bold">Profile</span>
    </div>
    <div class="card-body py-4">
      <div class="row">
        <div class="col-12 col-lg-4">
          <div class="card">
            <div class="card-body border rounded">
              <div class="d-flex justify-content-center align-items-center flex-column">
                <div class="avatar avatar-2xl">
                  @if ($user->image_path && Storage::exists($userImage))
                    <img src="{{ Storage::url($userImage) }}" alt="Avatar" class="rounded-circle"
                      style="width: 100px; height: 100px">
                  @else
                    <div class="bg-secondary rounded-circle">
                      <i class="bi bi-person-fill text-white d-flex justify-content-center align-items-center"
                        style="width: 100px; height: 100px; font-size: 50px"></i>
                    </div>
                  @endif
                </div>
                <h3 class="mt-2 mb-0 fw-bold">{{ $user->fullname }}</h3>
                <div class="d-flex gap-2">
                  <span class="badge text-bg-primary">{{ $user->role }}</span>
                  @if ($user->is_teacher->is_wali)
                    <span class="badge text-bg-primary">Wali Kelas</span>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-12 col-lg-8">
          <div class="card">
            <div class="card-body border rounded">
              <!-- Nav tabs -->
              <ul class="nav nav-tabs" id="profileTabs" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general"
                    type="button" role="tab" aria-controls="general" aria-selected="true">Umum</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password"
                    type="button" role="tab" aria-controls="password" aria-selected="false">Keamanan</button>
                </li>
              </ul>

              <!-- Tab content -->
              <div class="tab-content mt-3" id="profileTabsContent">
                <!-- General Information Tab -->
                <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                  <form action="{{ route('teacher.pengaturan.update_profile_general') }}" enctype="multipart/form-data"
                    method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                      <label for="fullname" class="form-label">Nama</label>
                      <input type="text" name="fullname" id="fullname" class="form-control" placeholder="Your Name"
                        disabled value="{{ $user->fullname ?? '' }}">
                    </div>
                    <div class="form-group">
                      <label for="nip" class="form-label">NIP</label>
                      <input type="text" name="nip" id="nip" class="form-control" placeholder="Your Nip"
                        disabled value="{{ $user->is_teacher->nip ?? '' }}">
                    </div>
                    <div class="form-group">
                      <label for="email" class="form-label">Email</label>
                      <input type="email" name="email" id="email" class="form-control" placeholder="Your Email"
                        value="{{ $user->email ?? '' }}">
                    </div>
                    <div class="form-group">
                      <label for="phone" class="form-label">No Handphone</label>
                      <input type="text" name="phone" id="phone" class="form-control"
                        placeholder="Your Phone" value="{{ $user->phone ?? '' }}">
                    </div>
                    <div class="form-group">
                      <label for="religion" class="form-label">Agama</label>
                      <select name="religion" id="religion" class="form-control">
                        <option value="" disabled @if ($user->religion == '' || $user->religion == null) selected @endif>- Pilih Agama
                          -
                        </option>
                        @foreach ($religions as $religion)
                          <option value="{{ $religion }}" @if ($user->religion == $religion) selected @endif>
                            {{ $religion }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="form-group">
                      <label for="gender" class="form-label">Jenis Kelamin</label>
                      <div>
                        <input type="radio" name="gender" id="gender_male" value="male" class="mr-1"
                          @if ($user->gender == 'male') checked @endif>
                        <label for="gender_male">Laki Laki</label>
                        <input type="radio" name="gender" id="gender_female" value="female" class="ml-3 mr-1"
                          @if ($user->gender == 'female') checked @endif>
                        <label for="gender_female">Perempuan</label>
                      </div>
                    </div>
                    <div class="form-group">
                      <label for="address" class="form-label">Alamat</label>
                      <textarea class="form-control" name="address" id="address" rows="3">{!! $user->address ?? '' !!}
                                            </textarea>
                    </div>
                    <div class="form-group">
                      <label for="image_path" class="form-label">Foto</label>
                      <input type="file" name="image_path" id="image_path" class="form-control"
                        accept="image/png, image/jpeg, image/jpg">
                    </div>
                    <div class="form-group mt-3">
                      <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                  </form>
                </div>

                <!-- Password Tab -->
                <div class="tab-pane fade" id="password" role="tabpanel" aria-labelledby="password-tab">
                  <div class="alert alert-danger" role="alert">
                    <strong>Peringatan!</strong> Area ini berbahaya karena menyangkut keamanan Anda.
                    Tolong pikirkan dengan cermat sebelum melanjutkan.
                  </div>
                  <form action="{{ route('teacher.pengaturan.update_profile_password') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                      <label for="old_password" class="form-label">Password Lama</label>
                      <input type="password" name="old_password" id="old_password" class="form-control"
                        placeholder="Password Lama">
                    </div>
                    <div class="form-group">
                      <label for="new_password" class="form-label">Password Baru</label>
                      <input type="password" name="new_password" id="new_password" class="form-control"
                        placeholder="Password Baru">
                    </div>
                    <div class="form-group">
                      <label for="new_password_confirmation" class="form-label">Konfirmasi
                        Password Baru</label>
                      <input type="password" name="new_password_confirmation" id="new_password_confirmation"
                        class="form-control" placeholder="Konfirmasi Password Baru">
                    </div>
                    <div class="form-group mt-3">
                      <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.ckeditor.com/ckeditor5/34.2.0/classic/ckeditor.js"></script>
  <script>
    ClassicEditor.create($('#address')[0]).catch(console.error);
  </script>


</x-app-layout>
