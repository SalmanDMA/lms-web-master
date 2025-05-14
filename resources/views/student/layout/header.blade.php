<header class="flex h-14 items-center gap-4 bg-primary px-4 text-primary-foreground lg:h-[60px] lg:px-6 z-20">
    <div class="w-full flex-1 text-white">
        <h1 class="text-sm font-semibold sm:text-lg">@yield('nav_title')</h1>
        <h2 class="text-xs titleNav">TMB Learning Management System</h2>
    </div>
    <div class="items-center hidden md:flex">
        <div class="relative inline-block text-left">
            <button id="nav-account" type="button" class="flex items-center px-4 py-2 focus:outline-none">
                <svg class="h-10 w-10 svg-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                    stroke="#000000" stroke-width="0.00024000000000000003">
                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                    <g id="SVGRepo_iconCarrier">
                        <path
                            d="M6.02958 19.4012C5.97501 19.9508 6.3763 20.4405 6.92589 20.4951C7.47547 20.5497 7.96523 20.1484 8.01979 19.5988L6.02958 19.4012ZM15.9802 19.5988C16.0348 20.1484 16.5245 20.5497 17.0741 20.4951C17.6237 20.4405 18.025 19.9508 17.9704 19.4012L15.9802 19.5988ZM20 12C20 16.4183 16.4183 20 12 20V22C17.5228 22 22 17.5228 22 12H20ZM12 20C7.58172 20 4 16.4183 4 12H2C2 17.5228 6.47715 22 12 22V20ZM4 12C4 7.58172 7.58172 4 12 4V2C6.47715 2 2 6.47715 2 12H4ZM12 4C16.4183 4 20 7.58172 20 12H22C22 6.47715 17.5228 2 12 2V4ZM13 10C13 10.5523 12.5523 11 12 11V13C13.6569 13 15 11.6569 15 10H13ZM12 11C11.4477 11 11 10.5523 11 10H9C9 11.6569 10.3431 13 12 13V11ZM11 10C11 9.44772 11.4477 9 12 9V7C10.3431 7 9 8.34315 9 10H11ZM12 9C12.5523 9 13 9.44772 13 10H15C15 8.34315 13.6569 7 12 7V9ZM8.01979 19.5988C8.22038 17.5785 9.92646 16 12 16V14C8.88819 14 6.33072 16.3681 6.02958 19.4012L8.01979 19.5988ZM12 16C14.0735 16 15.7796 17.5785 15.9802 19.5988L17.9704 19.4012C17.6693 16.3681 15.1118 14 12 14V16Z"
                            fill="#000000"></path>
                    </g>
                </svg>
            </button>

            <!-- Dropdown Menu -->
            <div id="nav-account-menu"
                class="z-30 origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none hidden">
                <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                    <a href="#" class="block px-4 py-2 text-sm font-semibold text-black"
                        role="menuitem">{{ session('user')->fullname }}</a>
                    <a href="#" class="block px-4 py-1 text-xs font-semibold text-black"
                        role="menuitem">{{ session('user')->email }}</a>

                    <hr class="my-1" />

                    <a href="{{ route('student.profile') }}" class="w-full text-left block px-2 py-1 text-black"
                        role="menuitem">Profile</a>
                    <form method="GET" action="/logout">
                        @csrf
                        <button type="submit" class="w-full text-left block px-2 text-black" role="menuitem">Sign
                            out</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
