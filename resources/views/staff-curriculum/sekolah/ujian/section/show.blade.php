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
    Bagian Ujian
    </x-slot>

    <x-ujian.staff.question :exam="$section->exam" :section="$section" :questions="$questions"
      :questionTypes="$questionTypes" :questionCategories="$questionCategories" :bankQuestions="$bankQuestions"
      :classLevels="$classLevels" />

    <div class="card">
      <div class="card-body">
        <a href="{{ route('staff_curriculum.sekolah.v_ujian_detail', ['id' => $section->exam->id]) }}" class="btn btn-primary">Kembali</a>
      </div>
    </div>
</x-app-layout>