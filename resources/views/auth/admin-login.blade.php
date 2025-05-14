'<x-auth-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <div class="d-flex align-items-center justify-content-center h-100">
        <div class="card" style="width: 500px">
            <div class="card-body">
                <img src="{{ asset('assets/static/images/logo/logo_digy.png') }}" alt="Logo" width="150px"
                    class="mb-4 mx-auto d-block" />
                <h1 class="auth-title" style="font-size: 2.5rem">Selamat Datang</h1>
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

                <form action="/admin/login" method="POST">
                    @csrf
                    <div class="form-group position-relative has-icon-left mb-4">
                        <input type="text" class="form-control form-control-xl" placeholder="Masukkan email Anda"
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
    </div>
</x-auth-layout>
'
