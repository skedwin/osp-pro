<div
    :class="sidebarToggle ? 'justify-center' : 'justify-between'"
    class="flex items-center gap-2 pt-8 sidebar-header pb-7"
  >
    <a href="{{ route('portal.dashboard') }}">
      <span class="logo" :class="sidebarToggle ? 'hidden' : ''">
        <img class="dark:hidden" src="{{ asset('tailadmin/src/images/logo/logo.svg') }}" alt="Logo" />
        <img
          class="hidden dark:block"
          src="{{ asset('tailadmin/src/images/logo/logo-dark.svg') }}"
          alt="Logo"
        />
      </span>

      <img
        class="logo-icon"
        :class="sidebarToggle ? 'lg:block' : 'hidden'"
        src="{{ asset('tailadmin/src/images/logo/logo-icon.svg') }}"
        alt="Logo"
      />
    </a>
  </div>

