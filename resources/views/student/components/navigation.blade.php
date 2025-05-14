<div class="bg-primary p-8 rounded-bl-[3rem] md:rounded-none sticky top-0 z-10">
    <h1 class="col-span-3 text-center text-lg md:text-2xl font-bold text-primary-foreground">
        @if (request()->is('student/material*'))
            Selamat Datang Di Materi Pembelajaran, {{ session('user')->fullname }}
        @elseif (request()->is('student/assignment*'))
            Tugas Baru Menanti! Selamat Datang, {{ session('user')->fullname }}!
        @elseif (request()->is('student/school-exam*') || request()->is('student/exam-detail*'))
            Hello, Ujian terbaru menunggu kamu, {{ session('user')->fullname }}
        @elseif (request()->is('student/score*'))
            Selamat Datang di Nilai, {{ session('user')->fullname }}
        @else
            Hello, {{ session('user')->fullname }}
        @endif
    </h1>
    <div class="mx-auto mt-5 max-w-[600px] grid grid-cols-2 lg:grid-cols-4 gap-6">
        {{-- Card --}}
        <a href="/student/material">
            <div
                class="rounded-lg border {{ request()->is('student/material*') ? 'bg-blue-900 text-card-foreground activeCard' : 'bg-card nonActiveCard' }} shadow-sm cursor-pointer border-none transition-all hover:bg-blue-900">
                <div class="p-4 pt-3 flex flex-col items-center gap-3 md:p-6 group">
                    <img src="{{ asset('assets/static/images/svg-loaders/materi.svg') }}" alt="materi-icon" class="w-16 h-16 {{ request()->is('student/material*') ? 'text-primary-foreground activeText' : 'text-foreground nonActiveText' }}" />
                    <div
                        class="text-sm font-bold md:text-lg {{ request()->is('student/material*') ? 'text-primary-foreground activeText' : 'text-foreground nonActiveText' }} group-hover:text-primary-foreground">
                        Materi
                    </div>

                </div>
            </div>
        </a>
        {{--  --}}

        {{-- Card --}}
        <a href="/student/assignment">
            <div
                class="rounded-lg {{ request()->is('student/assignment*') ? 'bg-blue-900 text-card-foreground activeCard' : 'bg-card nonActiveCard' }} border shadow-sm cursor-pointer border-none transition-all hover:bg-blue-900">
                <div class="p-4 pt-3 flex flex-col items-center gap-3 md:p-6 group">
                    <img src="{{ asset('assets/static/images/svg-loaders/tugas.svg') }}" alt="tugas-icon" class="w-16 h-16 {{ request()->is('student/assignment*') ? 'text-primary-foreground activeText' : 'text-foreground nonActiveText' }}" />
                    <div
                        class="text-sm font-bold md:text-lg {{ request()->is('student/assignment*') ? 'text-primary-foreground activeText' : 'text-foreground nonActiveText' }} group-hover:text-primary-foreground">
                        Tugas
                    </div>

                </div>
            </div>
        </a>
        {{--  --}}

        {{-- Card --}}
        <a href="/student/school-exam">
            <div
                class="rounded-lg {{ request()->is('student/school-exam*') || request()->is('student/exam-detail*') ? 'bg-blue-900 text-card-foreground activeCard' : 'bg-card nonActiveCard' }} border shadow-sm cursor-pointer border-none transition-all hover:bg-blue-900">
                <div class="p-4 pt-3 flex flex-col items-center gap-3 md:p-6 group">
                    <img src="{{ asset('assets/static/images/svg-loaders/ujian.svg') }}" alt="ujian-icon" class="w-16 h-16 {{ request()->is('student/school-exam*') || request()->is('student/exam-detail*') ? 'text-primary-foreground activeText' : 'text-foreground nonActiveText' }}" />
                    <div class="text-sm font-bold md:text-lg {{ request()->is('student/school-exam*') || request()->is('student/exam-detail*') ? 'text-primary-foreground activeText' : 'text-foreground nonActiveText' }} group-hover:text-primary-foreground">
                        Ujian
                    </div>

                </div>
            </div>
        </a>
        {{--  --}}

        {{-- Card --}}
        <a href="/student/score">
            <div
                class="rounded-lg {{ request()->is('student/score*') ? 'bg-blue-900 text-card-foreground activeCard' : 'bg-card nonActiveCard' }} border shadow-sm cursor-pointer border-none transition-all hover:bg-blue-900">
                <div class="p-4 pt-3 flex flex-col items-center gap-3 md:p-6 group">
                    <img src="{{ asset('assets/static/images/svg-loaders/Scorecard.svg') }}" alt="Scorecard-icon" class="w-full h-16 {{ request()->is('student/score*') ? 'text-primary-foreground activeText' : 'text-foreground nonActiveText' }}" />
                    <div class="text-sm font-bold md:text-lg {{ request()->is('student/score*') ? 'text-primary-foreground activeText' : 'text-foreground nonActiveText' }} group-hover:text-primary-foreground">
                        Nilai
                    </div>

                </div>
            </div>
        </a>
        {{--  --}}

    </div>


</div>
