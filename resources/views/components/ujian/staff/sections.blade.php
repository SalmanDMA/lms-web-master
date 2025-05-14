
@props(['ujian_id', 'exam', 'sections'])

<div>
  <div class="d-flex justify-content-end">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-section">Tambah</button>
  </div>

  <div class="modal fade modal-borderless text-left" id="add-section" tabindex="-1" aria-labelledby="myModalLabel1"
    style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Tambah Bagian Ujian</h5>
          <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
              class="feather feather-x">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>
        <div class="modal-body">
          <form action="{{ route('staff_curriculum.add_section') }}" method="POST">
            @csrf

            <input type="hidden" name="exam_id" value="{{ $exam->id }}">

            <div class="form-group">
              <label for="name">Nama Bagian</label>
              <input type="text" class="form-control" name="name" id="name" value="{{ old('name') }}" required>
            </div>

            <div class="form-group">
              <label for="description">Deskripsi</label>
              <textarea class="form-control ckeditor" name="description" id="description">{{ old('description') }}</textarea>
            </div>

            <div class="d-flex justify-content-end">
              <button type="submit" class="btn btn-primary">Tambahkan</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <x-datatable title="Bagian Ujian">
    <table class="table" id="table-ujian-bank">
      <thead>
        <tr>
          <th>#</th>
          <th>Nama</th>
          <th>Deskripsi</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody id="ujian-body-table">
        @forelse ($sections as $item)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{ $item->name }}</td>
          <td>{!! $item->description !!}</td>
          <td>
            <div class="d-flex gap-2">
              <a href="{{ route('staff_curriculum.detail_section', ['id' => $item->id]) }}" class="btn btn-primary">Lihat</a>

              <form action="{{ route('staff_curriculum.delete_section', ['id' => $item->id]) }}" method="POST" onsubmit="return confirm('Hapus data?')">
                @method('DELETE')
                @csrf
                <button type="submit" class="btn btn-danger">
                  Hapus
                </button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr id="empty-row">
          <td colspan="5" class="text-center">Data tidak ditemukan. Silakan tambah ujian.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </x-datatable>

  <script>
    const initializeCKEditors = () => {
        $('[id^="choices"][id$="[choice_text]"]:not(.ckeditor-initialized)').each(function() {
        ClassicEditor.create(this).then(editor => {
        $(this).addClass('ckeditor-initialized');
        }).catch(console.error);
        });
    };

    initializeCKEditors();

    ClassicEditor.create($('#description')[0]).catch(console.error);
    ClassicEditor.create($('#instruction')[0]).catch(console.error);
  </script>
</div>
