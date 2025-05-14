<x-app-layout>
    <x-slot:title>
        {{ $title }}
    </x-slot>

    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>{{ $cms->title }}</h3>
                    <p class="text-subtitle text-muted">Learning Management System</p>
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

    <section>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.update_cms', ['id' => $cms->id]) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="splash_logo" class="form-label">Splash Logo</label>
                        <input type="file" class="form-control" id="splash_logo" name="splash_logo" accept="image/*">
                        <img src="{{ loadAsset($cms->splash_logo) }}" alt="Current Splash Logo" width="100"
                            class="mt-2">
                    </div>

                    <div class="mb-3">
                        <label for="splash_title" class="form-label">Splash Title</label>
                        <input type="text" class="form-control" id="splash_title" name="splash_title"
                            value="{{ $cms->splash_title ?? '' }}" placeholder="Enter splash title">
                    </div>

                    <div class="mb-3">
                        <label for="login_image_student" class="form-label">Login Image (Student)</label>
                        <input type="file" class="form-control" id="login_image_student" name="login_image_student"
                            accept="image/*">
                        <img src="{{ loadAsset($cms->login_image_student) }}" alt="Current Login Image Student"
                            width="100" class="mt-2">
                    </div>

                    <div class="mb-3">
                        <label for="login_image_teacher" class="form-label">Login Image (Teacher)</label>
                        <input type="file" class="form-control" id="login_image_teacher" name="login_image_teacher"
                            accept="image/*">
                        <img src="{{ loadAsset($cms->login_image_teacher) }}" alt="Current Login Image Teacher"
                            width="100" class="mt-2">
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label">School Title</label>
                        <input type="text" class="form-control" id="title" name="title"
                            value="{{ $cms->title ?? '' }}" placeholder="Enter school title">
                    </div>

                    <div class="mb-3">
                        <label for="logo" class="form-label">Logo</label>
                        <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                        <img src="{{ loadAsset($cms->logo) }}" alt="Current Logo" width="100" class="mt-2">
                    </div>

                    <div class="mb-3">
                        <label for="logo_thumbnail" class="form-label">Logo Thumbnail</label>
                        <input type="file" class="form-control" id="logo_thumbnail" name="logo_thumbnail"
                            accept="image/*">
                        <img src="{{ loadAsset($cms->logo_thumbnail) }}" alt="Current Logo Thumbnail" width="100"
                            class="mt-2">
                    </div>

                    <div class="mb-3">
                        <label for="primary_color" class="form-label">Primary Color</label>
                        <input type="color" class="form-control form-control-color" id="primary_color"
                            name="primary_color" value="{{ $cms->primary_color ?? '#FF5733' }}">
                    </div>

                    <div class="mb-3">
                        <label for="secondary_color" class="form-label">Secondary Color</label>
                        <input type="color" class="form-control form-control-color" id="secondary_color"
                            name="secondary_color" value="{{ $cms->secondary_color ?? '#33FF57' }}">
                    </div>

                    <div class="mb-3">
                        <label for="accent_color" class="form-label">Accent Color</label>
                        <input type="color" class="form-control form-control-color" id="accent_color"
                            name="accent_color" value="{{ $cms->accent_color ?? '#3357FF' }}">
                    </div>

                    <div class="mb-3">
                        <label for="white_color" class="form-label">White Color</label>
                        <input type="color" class="form-control form-control-color" id="white_color"
                            name="white_color" value="{{ $cms->white_color ?? '#FFFFFF' }}">
                    </div>

                    <div class="mb-3">
                        <label for="black_color" class="form-label">Black Color</label>
                        <input type="color" class="form-control form-control-color" id="black_color"
                            name="black_color" value="{{ $cms->black_color ?? '#000000' }}">
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</x-app-layout>
