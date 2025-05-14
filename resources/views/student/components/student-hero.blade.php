<div class="rounded-bl-[3rem] bg-primary p-8 md:rounded-none">
    <div class="mx-auto grid max-w-[600px] grid-cols-3 gap-6">
        <h1 class="col-span-3 text-center text-2xl font-bold text-primary-foreground">
            Hello, {{ session('user')->fullname }}
        </h1>

        {{-- Card --}}
        <a href="/student/material">
            <div
                class="rounded-lg border bg-card text-card-foreground shadow-sm cursor-pointer border-none transition-all hover:bg-blue-900">
                <div class=" pt-0 flex flex-col items-center gap-3 p-4 md:p-6 group">
                    <svg class="h-10 w-10 md:h-20 md:w-20 text-foreground group-hover:text-primary-foreground"
                        viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <path d="M12 21V10C12 7.23858 9.76142 5 7 5H3V18.7143" stroke="currentcolor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path d="M3 19H7.5C10.5 19 11 20 12 21" stroke="currentcolor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"></path>
                            <path d="M12 21V10C12 7.23858 14.2386 5 17 5H21V18.7143" stroke="currentcolor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path d="M21 19H16.5C13.5 19 13 20 12 21" stroke="currentcolor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"></path>
                        </g>
                    </svg>

                    <div class="text-sm font-bold md:text-lg text-foreground group-hover:text-primary-foreground">
                        Materi
                    </div>

                </div>
            </div>
        </a>
        {{--  --}}

        {{-- Card --}}
        <a href="#">
            <div
                class="rounded-lg border bg-card text-card-foreground shadow-sm cursor-pointer border-none transition-all hover:bg-blue-900">
                <div class=" pt-0 flex flex-col items-center gap-3 p-4 md:p-6 group">
                    <svg class="h-10 w-10 md:h-20 md:w-20 text-foreground group-hover:text-primary-foreground"
                        viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <path
                                d="M3 8C3 5.17157 3 3.75736 3.87868 2.87868C4.75736 2 6.17157 2 9 2H15C17.8284 2 19.2426 2 20.1213 2.87868C21 3.75736 21 5.17157 21 8V16C21 18.8284 21 20.2426 20.1213 21.1213C19.2426 22 17.8284 22 15 22H9C6.17157 22 4.75736 22 3.87868 21.1213C3 20.2426 3 18.8284 3 16V8Z"
                                stroke="currentcolor" stroke-width="1.5"></path>
                            <path d="M8 2.5V22" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round"></path>
                            <path d="M2 12H4" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round"></path>
                            <path d="M2 16H4" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round"></path>
                            <path d="M2 8H4" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round"></path>
                            <path d="M11.5 6.5H16.5" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round">
                            </path>
                            <path d="M11.5 10H16.5" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round">
                            </path>
                        </g>
                    </svg>

                    <div class="text-sm font-bold md:text-lg text-foreground group-hover:text-primary-foreground">
                        Tugas
                    </div>

                </div>
            </div>
        </a>
        {{--  --}}

        {{-- Card --}}
        <a href="/student/school-exam">
            <div
                class="rounded-lg border {{ $menu === 'ujian' ? 'bg-blue-900' : 'bg-card' }} text-card-foreground shadow-sm cursor-pointer border-none transition-all hover:bg-blue-900">
                <div class=" pt-0 flex flex-col items-center gap-3 p-4 md:p-6 group">
                    <svg class="h-10 w-10 md:h-20 md:w-20 {{ $menu === 'ujian' ? 'text-primary-foreground' : 'text-foreground' }} group-hover:text-primary-foreground"
                        viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <path
                                d="M4 8C4 5.17157 4 3.75736 4.87868 2.87868C5.75736 2 7.17157 2 10 2H14C16.8284 2 18.2426 2 19.1213 2.87868C20 3.75736 20 5.17157 20 8V16C20 18.8284 20 20.2426 19.1213 21.1213C18.2426 22 16.8284 22 14 22H10C7.17157 22 5.75736 22 4.87868 21.1213C4 20.2426 4 18.8284 4 16V8Z"
                                stroke="currentcolor" stroke-width="1.5"></path>
                            <path
                                d="M19.8978 16H7.89778C6.96781 16 6.50282 16 6.12132 16.1022C5.08604 16.3796 4.2774 17.1883 4 18.2235"
                                stroke="currentcolor" stroke-width="1.5"></path>
                            <path d="M8 7H16" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round"></path>
                            <path d="M8 10.5H13" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round"></path>
                            <path
                                d="M13 16V19.5309C13 19.8065 13 19.9443 12.9051 20C12.8103 20.0557 12.6806 19.9941 12.4211 19.8708L11.1789 19.2808C11.0911 19.2391 11.0472 19.2182 11 19.2182C10.9528 19.2182 10.9089 19.2391 10.8211 19.2808L9.57889 19.8708C9.31943 19.9941 9.18971 20.0557 9.09485 20C9 19.9443 9 19.8065 9 19.5309V16.45"
                                stroke="currentcolor" stroke-width="1.5" stroke-linecap="round"></path>
                        </g>
                    </svg>

                    <div
                        class="text-sm font-bold md:text-lg {{ $menu === 'ujian' ? 'text-primary-foreground' : 'text-foreground' }} group-hover:text-primary-foreground">
                        Ujian
                    </div>

                </div>
            </div>
        </a>
        {{--  --}}

    </div>
</div>
