<div x-data="{ sidebarOpen: false }" class="min-h-screen lg:flex">
    @include('components.sidebar')

    <div class="flex-1 min-h-screen flex flex-col lg:ml-8">
        @include('components.header')

        <main class="flex-1 px-4 py-8 sm:px-6 lg:pl-0 lg:pr-10">
            <div class="space-y-6">
                @include('components.alerts')
                @yield('content')
            </div>
        </main>

        <x-footer/>
    </div>
</div>
