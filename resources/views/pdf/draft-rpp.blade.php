    <!DOCTYPE html>
    <html>

    <head>
     <title>Rencana Pelaksanaan Pembelajaran</title>
     <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
     <style>
      * {
       font-family: 'Times New Roman', Times, serif;
       box-sizing: border-box;
      }

      .signature-table {
       margin-top: 40px;
       width: 100%;
      }

      .signature-table td {
       border: none;
       text-align: center;
       vertical-align: bottom;
       padding: 40px;
      }
     </style>
    </head>

    <body>

     @php
      //   $logoPath = str_replace('public/storage/', '', $user->school->logo);

      function formatTimeAllocation($time)
      {
          $parts = explode(':', $time);
          $hours = (int) $parts[0];
          $minutes = (int) $parts[1];

          return "{$hours} jam {$minutes} menit";
      }
     @endphp

     <div class="p-2">
      <div class="d-flex gap-2 my-4">
       {{-- @if ($user->school->logo && Storage::exists($logoPath))
        <img src="{{ asset('app/' . $logoPath) }}" alt="Logo">
       @endif --}}
       <div class="d-flex flex-column gap-2">
        <h3 class="m-0">Rencana Pelaksanaan Pembelajaran</h3>
        <p class="m-0 text-muted">{{ $user->school->name }}</p>
       </div>
      </div>
      <div class="mb-4 d-flex flex-column gap-2">
       <h4 class="m-0">DRAFT RPP {{ \Carbon\Carbon::now()->format('d F Y H:i') }}</h3>
        <p class="m-0 text-muted">#LMS-{{ $draft->id }}</p>
      </div>

      <h4 class="my-4">Informasi RPP</h3>
       <table class="table table-bordered">
        <tbody>
         <tr>
          <th>Lembaga Pendidikan</th>
          <td>Madrasah Hayatul Muslihin</td>
         </tr>
         <tr>
          <th>Mata Pelajaran</th>
          <td>{{ $draft->courses['name'] }}</td>
         </tr>
         <tr>
          <th>Tahun Ajaran</th>
          <td>{{ $draft->academic_year['name'] }}</td>
         </tr>
         <tr>
          <th>Tingkat/Semester</th>
          <td>{{ $draft->class_level['name'] }}/{{ $draft->semester }}</td>
         </tr>
         <tr>
          <th>Tanggal Pengajuan</th>
          <td>{{ \Carbon\Carbon::parse($draft->created_at)->format('d/m/Y H:i:s') }}</td>
         </tr>
         <tr>
          <th>Status Pengajuan</th>
          <td>{{ $draft->status }}</td>
         </tr>
         <tr>
          <th>Diunduh oleh</th>
          <td>{{ $user->fullname }} <br> <span class="text-muted">{{ $user->email }}</span></td>
         </tr>
         <tr>
          <th>Tanggal unduh</th>
          <td>{{ \Carbon\Carbon::now()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s') }}</td>
         </tr>
        </tbody>
       </table>

       @if (count($draft->subject_matters) > 0)
        <h3 class="my-4">Materi Pokok</h3>
        @foreach ($draft->subject_matters as $subject)
         <div class="card mb-2">
          <div class="card-body">
           <table class="table table-borderless">
            <tr>
             <td class="font-weight-bold" style="width: 50%;">Judul Materi:</td>
             <td>{{ $subject->title }}</td>
            </tr>
            <tr>
             <td class="font-weight-bold" style="width: 50%;">Alokasi Waktu:</td>
             <td>{{ formatTimeAllocation($subject->time_allocation) }}</td>
            </tr>
           </table>

           <div class="row mb-3">
            <div class="col-md-12">
             <h5 class="font-weight-bold">Tujuan Pembelajaran:</h5>
             <div class="border p-3 rounded">
              {!! $subject->learning_goals !!}
             </div>
            </div>
           </div>

           <div class="row mb-3">
            <div class="col-md-12">
             <h5 class="font-weight-bold">Aktivitas Pembelajaran:</h5>
             <div class="border p-3 rounded">
              {!! $subject->learning_activity !!}
             </div>
            </div>
           </div>

           <div class="row mb-3">
            <div class="col-md-12">
             <h5 class="font-weight-bold">Penilaian:</h5>
             <div class="border p-3 rounded">
              {!! $subject->grading !!}
             </div>
            </div>
           </div>
          </div>
         </div>
        @endforeach
       @else
        <div class="p-3 border">
         <p class="text-muted">Tidak ada materi pokok</p>
        </div>
       @endif

       <table class="signature-table">
        <tr>
         <td>
          Mengetahui,<br>
          Kepala Sekolah<br><br><br>
          NENENG SUMINAR<br>
          NIP. ...................<br>

         </td>
         <td>
          ...................,
          {{ \Carbon\Carbon::now()->setTimezone('Asia/Jakarta')->format('d/m/Y') }}<br>
          Guru Pengajar,<br><br><br>
          Febdinal<br>
          NIP. {{ $user->is_teacher->nip }}
         </td>
        </tr>
       </table>
     </div>

    </body>

    </html>
