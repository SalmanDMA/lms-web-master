<x-auth-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <div class="row h-100">
        <div class="col-lg-5 col-12">
            <div id="auth-left">
                <div class="auth-logo m-0 mb-5">
                    <a href="index.html"><img id="auth-logo-img" src="{{ asset('assets/static/images/logo/logo.svg') }}" alt="Logo"></a>
                </div>
                <h1 class="auth-title" style="font-size: 3rem">Selamat Datang</h1>
                <p class="auth-subtitle mb-5">Masuk ke akun Anda terlebih dahulu</p>

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

                <form action="/login/{{ $role }}" method="POST">
                    @csrf
                    <div class="form-group position-relative has-icon-left mb-4">
                        <input type="text" class="form-control form-control-xl"
                            placeholder="Masukkan {{ $role === 'teacher' ? 'NIP' : 'NISN' }} atau email Anda"
                            name="email">
                        <div class="form-control-icon">
                            <i class="bi bi-person"></i>
                        </div>
                    </div>
                    <div class="form-group position-relative has-icon-left mb-4">
                        <input type="password" class="form-control form-control-xl"
                            placeholder="Masukkan kata sandi Anda" name="password">
                        <div class="form-control-icon">
                            <i class="bi bi-shield-lock"></i>
                        </div>
                    </div>
                    {{-- <div class="form-check form-check-lg d-flex align-items-end">
                        <input class="form-check-input me-2" type="checkbox" value="" id="flexCheckDefault"
                            name="remember">
                        <label class="form-check-label text-gray-600" for="flexCheckDefault">
                            Keep me logged in
                        </label>
                    </div> --}}
                    <button type="submit" class="btn btn-primary btn-block btn-lg mt-5 shadow-lg">Masuk</button>
                </form>
            </div>
        </div>
        <div class="col-lg-7 d-none d-lg-block">
            <img id="bg-auth" src="{{ $role === 'teacher'
                ? asset('assets/static/images/bg/auth-guru.png')
                : asset('assets/static/images/bg/auth-siswa.png') }}"
                alt="background" width="100%">
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const customTheme = @json($customTheme);
            const role = @json($role);
            const authTitle = document.querySelector('.auth-title');
            const authLogoImg = document.getElementById('auth-logo-img');
            const bgAuth = document.getElementById('bg-auth');
            const btnPrimary = document.querySelector('.btn-primary');

            if (customTheme !== null) {
                authTitle.innerHTML = customTheme['splash_title'];
                authLogoImg.src = "{{ loadAsset($customTheme->logo) }}";

                if (role === 'teacher') {
                    bgAuth.src = "{{ loadAsset($customTheme->login_image_teacher) }}";
                } else {
                    bgAuth.src = "{{ loadAsset($customTheme->login_image_student) }}";
                }

                btnPrimary.style.backgroundColor = customTheme['primary_color'];
            }
        });
    </script>
</x-auth-layout>
