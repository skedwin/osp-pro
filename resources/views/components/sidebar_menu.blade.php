@php
    $selectedMenu = request()->routeIs('portal.profile') ? 'Profile' : 'Dashboard';
@endphp

<nav x-data="{selected: $persist('{{ $selectedMenu }}')}">
    <!-- Menu Group -->
    <div>
        <h3 class="mb-4 text-xs uppercase leading-[20px] text-gray-400">
            <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">
                MENU
            </span>
            <svg
                :class="sidebarToggle ? 'lg:block hidden' : 'hidden'"
                class="mx-auto fill-current menu-group-icon"
                width="24" height="24" viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    fill-rule="evenodd" clip-rule="evenodd"
                    d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z"
                    fill=""
                />
            </svg>
        </h3>

        <ul class="flex flex-col gap-4 mb-6">
            <!-- Menu Item Dashboard -->
            <li>
                <a
                    href="{{ route('portal.dashboard') }}"
                    @click="selected = 'Dashboard'"
                    class="menu-item group"
                    :class="(selected === 'Dashboard') || page === 'dashboard' ? 'menu-item-active' : 'menu-item-inactive'"
                >
                    <svg
                        :class="(selected === 'Dashboard') || page === 'dashboard' ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                        width="24" height="24" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            fill-rule="evenodd" clip-rule="evenodd"
                            d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V8.99998C3.25 10.2426 4.25736 11.25 5.5 11.25H9C10.2426 11.25 11.25 10.2426 11.25 8.99998V5.5C11.25 4.25736 10.2426 3.25 9 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H9C9.41421 4.75 9.75 5.08579 9.75 5.5V8.99998C9.75 9.41419 9.41421 9.74998 9 9.74998H5.5C5.08579 9.74998 4.75 9.41419 4.75 8.99998V5.5ZM5.5 12.75C4.25736 12.75 3.25 13.7574 3.25 15V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H9C10.2426 20.75 11.25 19.7427 11.25 18.5V15C11.25 13.7574 10.2426 12.75 9 12.75H5.5ZM4.75 15C4.75 14.5858 5.08579 14.25 5.5 14.25H9C9.41421 14.25 9.75 14.5858 9.75 15V18.5C9.75 18.9142 9.41421 19.25 9 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V15ZM12.75 5.5C12.75 4.25736 13.7574 3.25 15 3.25H18.5C19.7426 3.25 20.75 4.25736 20.75 5.5V8.99998C20.75 10.2426 19.7426 11.25 18.5 11.25H15C13.7574 11.25 12.75 10.2426 12.75 8.99998V5.5ZM15 4.75C14.5858 4.75 14.25 5.08579 14.25 5.5V8.99998C14.25 9.41419 14.5858 9.74998 15 9.74998H18.5C18.9142 9.74998 19.25 9.41419 19.25 8.99998V5.5C19.25 5.08579 18.9142 4.75 18.5 4.75H15ZM15 12.75C13.7574 12.75 12.75 13.7574 12.75 15V18.5C12.75 19.7426 13.7574 20.75 15 20.75H18.5C19.7426 20.75 20.75 19.7427 20.75 18.5V15C20.75 13.7574 19.7426 12.75 18.5 12.75H15ZM14.25 15C14.25 14.5858 14.5858 14.25 15 14.25H18.5C18.9142 14.25 19.25 14.5858 19.25 15V18.5C19.25 18.9142 18.9142 19.25 18.5 19.25H15C14.5858 19.25 14.25 18.9142 14.25 18.5V15Z"
                            fill=""
                        />
                    </svg>
                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                        Dashboard
                    </span>
                </a>
            </li>
            <!-- Menu Item Dashboard -->

            <!-- Menu Item Profile -->
            <li>
                <a
                    href="{{ route('portal.profile') }}"
                    @click="selected = 'Profile'"
                    class="menu-item group"
                    :class="(selected === 'Profile') || page === 'profile' ? 'menu-item-active' : 'menu-item-inactive'"
                >
                    <svg
                        :class="(selected === 'Profile') || page === 'profile' ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                        width="24" height="24" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            fill-rule="evenodd" clip-rule="evenodd"
                            d="M12 3.5C7.30558 3.5 3.5 7.30558 3.5 12C3.5 14.1526 4.3002 16.1184 5.61936 17.616C6.17279 15.3096 8.24852 13.5955 10.7246 13.5955H13.2746C15.7509 13.5955 17.8268 15.31 18.38 17.6167C19.6996 16.119 20.5 14.153 20.5 12C20.5 7.30558 16.6944 3.5 12 3.5ZM17.0246 18.8566V18.8455C17.0246 16.7744 15.3457 15.0955 13.2746 15.0955H10.7246C8.65354 15.0955 6.97461 16.7744 6.97461 18.8455V18.856C8.38223 19.8895 10.1198 20.5 12 20.5C13.8798 20.5 15.6171 19.8898 17.0246 18.8566ZM2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12ZM11.9991 7.25C10.8847 7.25 9.98126 8.15342 9.98126 9.26784C9.98126 10.3823 10.8847 11.2857 11.9991 11.2857C13.1135 11.2857 14.0169 10.3823 14.0169 9.26784C14.0169 8.15342 13.1135 7.25 11.9991 7.25ZM8.48126 9.26784C8.48126 7.32499 10.0563 5.75 11.9991 5.75C13.9419 5.75 15.5169 7.32499 15.5169 9.26784C15.5169 11.2107 13.9419 12.7857 11.9991 12.7857C10.0563 12.7857 8.48126 11.2107 8.48126 9.26784Z"
                            fill=""
                        />
                    </svg>
                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                        User Profile
                    </span>
                </a>
            </li>
            <!-- Menu Item Profile -->
        </ul>
    </div>

    <!-- Student Menu Group -->
    <div>
        <h3 class="mb-4 text-xs uppercase leading-[20px] text-gray-400">
            <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">
                STUDENT MENU
            </span>
            <svg
                :class="sidebarToggle ? 'lg:block hidden' : 'hidden'"
                class="mx-auto fill-current menu-group-icon"
                width="24" height="24" viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    fill-rule="evenodd" clip-rule="evenodd"
                    d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM21 9V7L15 4.5V7H9V4.5L3 7V9L9 11.5V14.5L3 17V19L9 21.5V22H15V21.5L21 19V17L15 14.5V11.5L21 9Z"
                    fill=""
                />
            </svg>
        </h3>

        <ul class="flex flex-col gap-4 mb-6">
            <!-- Menu Item Registration -->
            <li>
                <a href="{{ route('student.registration') }}" 
                   @click="selected = 'StudentRegistration'"
                   class="menu-item group"
                   :class="(selected === 'StudentRegistration') && (page === 'student-registration') ? 'menu-item-active' : 'menu-item-inactive'">
                    <svg
                        :class="(selected === 'StudentRegistration') && (page === 'student-registration') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                        width="24" height="24" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            fill-rule="evenodd" clip-rule="evenodd"
                            d="M19 3H14.82C14.4 1.84 13.3 1 12 1C10.7 1 9.6 1.84 9.18 3H5C3.9 3 3 3.9 3 5V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19V5C21 3.9 20.1 3 19 3ZM12 3C12.55 3 13 3.45 13 4C13 4.55 12.55 5 12 5C11.45 5 11 4.55 11 4C11 3.45 11.45 3 12 3ZM13 17H8C7.45 17 7 16.55 7 16C7 15.45 7.45 15 8 15H13C13.55 15 14 15.45 14 16C14 16.55 13.55 17 13 17ZM16 13H8C7.45 13 7 12.55 7 12C7 11.45 7.45 11 8 11H16C16.55 11 17 11.45 17 12C17 12.55 16.55 13 16 13ZM16 9H8C7.45 9 7 8.55 7 8C7 7.45 7.45 7 8 7H16C16.55 7 17 7.45 17 8C17 8.55 16.55 9 16 9Z"
                            fill=""
                        />
                    </svg>
                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                        Registration
                    </span>
                </a>
            </li>

            <!-- Menu Item Examination -->
            <li>
                <a href="{{ route('student.examination') }}" 
                   @click="selected = 'StudentExamination'"
                   class="menu-item group"
                   :class="(selected === 'StudentExamination') && (page === 'student-examination') ? 'menu-item-active' : 'menu-item-inactive'">
                    <svg
                        :class="(selected === 'StudentExamination') && (page === 'student-examination') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                        width="24" height="24" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            fill-rule="evenodd" clip-rule="evenodd"
                            d="M19 3H5C3.9 3 3 3.9 3 5V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19V5C21 3.9 20.1 3 19 3ZM5 5H19V7H5V5ZM5 9H19V11H5V9ZM5 13H13V15H5V13ZM5 17H13V19H5V17ZM15 17H19V19H15V17ZM15 13H19V15H15V13Z"
                            fill=""
                        />
                    </svg>
                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                        Examination
                    </span>
                </a>
            </li>

            <!-- Menu Item Internship -->
            <li>
                <a href="{{ route('student.internship') }}" 
                   @click="selected = 'StudentInternship'"
                   class="menu-item group"
                   :class="(selected === 'StudentInternship') && (page === 'student-internship') ? 'menu-item-active' : 'menu-item-inactive'">
                    <svg
                        :class="(selected === 'StudentInternship') && (page === 'student-internship') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                        width="24" height="24" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            fill-rule="evenodd" clip-rule="evenodd"
                            d="M20 6H16V4C16 2.9 15.1 2 14 2H10C8.9 2 8 2.9 8 4V6H4C2.9 6 2 6.9 2 8V19C2 20.1 2.9 21 4 21H20C21.1 21 22 20.1 22 19V8C22 6.9 21.1 6 20 6ZM10 4H14V6H10V4ZM20 19H4V8H20V19ZM7 10H9V12H7V10ZM7 14H9V16H7V14ZM11 10H13V12H11V10ZM11 14H13V16H11V14ZM15 10H17V12H15V10ZM15 14H17V16H15V14Z"
                            fill=""
                        />
                    </svg>
                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                        Internship
                    </span>
                </a>
            </li>

            <!-- Menu Item Indexing -->
            <li>
                <a href="{{ route('student.indexing') }}" 
                   @click="selected = 'StudentIndexing'"
                   class="menu-item group"
                   :class="(selected === 'StudentIndexing') && (page === 'student-indexing') ? 'menu-item-active' : 'menu-item-inactive'">
                    <svg
                        :class="(selected === 'StudentIndexing') && (page === 'student-indexing') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                        width="24" height="24" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            fill-rule="evenodd" clip-rule="evenodd"
                            d="M3 13H11V3H3V13ZM3 21H11V15H3V21ZM13 21H21V11H13V21ZM13 3V9H21V3H13Z"
                            fill=""
                        />
                    </svg>
                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                        Indexing
                    </span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Practitioner Menu Group -->
    <div>
        <h3 class="mb-4 text-xs uppercase leading-[20px] text-gray-400">
            <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">
                PRACTITIONER MENU
            </span>
            <svg
                :class="sidebarToggle ? 'lg:block hidden' : 'hidden'"
                class="mx-auto fill-current menu-group-icon"
                width="24" height="24" viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    fill-rule="evenodd" clip-rule="evenodd"
                    d="M16 2C16 2.82843 16.6716 3.5 17.5 3.5C18.3284 3.5 19 2.82843 19 2C19 1.17157 18.3284 0.5 17.5 0.5C16.6716 0.5 16 1.17157 16 2ZM21 9V7L15 4.5V7H9V4.5L3 7V9L9 11.5V14.5L3 17V19L9 21.5V22H15V21.5L21 19V17L15 14.5V11.5L21 9Z"
                    fill=""
                />
            </svg>
        </h3>

        <ul class="flex flex-col gap-4 mb-6">
            <!-- Menu Item Renewals -->
            <li>
                <a href="{{ route('practitioner.renewals') }}" 
                   @click="selected = 'PractitionerRenewals'"
                   class="menu-item group"
                   :class="(selected === 'PractitionerRenewals') && (page === 'practitioner-renewals') ? 'menu-item-active' : 'menu-item-inactive'">
                    <svg
                        :class="(selected === 'PractitionerRenewals') && (page === 'practitioner-renewals') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                        width="24" height="24" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            fill-rule="evenodd" clip-rule="evenodd"
                            d="M17.65 6.35C16.2 4.9 14.21 4 12 4C7.58 4 4.01 7.58 4.01 12C4.01 16.42 7.58 20 12 20C15.73 20 18.84 17.45 19.73 14H17.65C16.83 16.33 14.61 18 12 18C8.69 18 6 15.31 6 12C6 8.69 8.69 6 12 6C13.66 6 15.14 6.69 16.22 7.78L13 11H20V4L17.65 6.35Z"
                            fill=""
                        />
                    </svg>
                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                        Renewals
                    </span>
                </a>
            </li>

            <!-- Menu Item Outmigration -->
            <li>
                <a href="{{ route('practitioner.outmigration') }}" 
                   @click="selected = 'PractitionerOutmigration'"
                   class="menu-item group"
                   :class="(selected === 'PractitionerOutmigration') && (page === 'practitioner-outmigration') ? 'menu-item-active' : 'menu-item-inactive'">
                    <svg
                        :class="(selected === 'PractitionerOutmigration') && (page === 'practitioner-outmigration') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                        width="24" height="24" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            fill-rule="evenodd" clip-rule="evenodd"
                            d="M15 5L13.59 6.41L16.17 9H4V11H16.17L13.59 13.59L15 15L20 10L15 5ZM4 15V17H14V15H4Z"
                            fill=""
                        />
                    </svg>
                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                        Outmigration
                    </span>
                </a>
            </li>

            <!-- Menu Item Private Practice -->
            <li>
                <a href="{{ route('practitioner.private-practice') }}" 
                   @click="selected = 'PractitionerPrivatePractice'"
                   class="menu-item group"
                   :class="(selected === 'PractitionerPrivatePractice') && (page === 'practitioner-private-practice') ? 'menu-item-active' : 'menu-item-inactive'">
                    <svg
                        :class="(selected === 'PractitionerPrivatePractice') && (page === 'practitioner-private-practice') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                        width="24" height="24" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            fill-rule="evenodd" clip-rule="evenodd"
                            d="M19 3H5C3.9 3 3 3.9 3 5V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19V5C21 3.9 20.1 3 19 3ZM5 5H19V7H5V5ZM5 9H19V11H5V9ZM5 13H13V15H5V13ZM5 17H13V19H5V17ZM15 17H19V19H15V17ZM15 13H19V15H15V13Z"
                            fill=""
                        />
                    </svg>
                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                        Private Practice
                    </span>
                </a>
            </li>

            <!-- Menu Item CPD -->
            <li>
                <a href="{{ route('practitioner.cpd') }}" 
                   @click="selected = 'PractitionerCPD'"
                   class="menu-item group"
                   :class="(selected === 'PractitionerCPD') && (page === 'practitioner-cpd') ? 'menu-item-active' : 'menu-item-inactive'">
                    <svg
                        :class="(selected === 'PractitionerCPD') && (page === 'practitioner-cpd') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                        width="24" height="24" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            fill-rule="evenodd" clip-rule="evenodd"
                            d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM12 20C7.59 20 4 16.41 4 12C4 7.59 7.59 4 12 4C16.41 4 20 7.59 20 12C20 16.41 16.41 20 12 20ZM13 7H11V11H7V13H11V17H13V13H17V11H13V7Z"
                            fill=""
                        />
                    </svg>
                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                        CPD
                    </span>
                </a>
            </li>
        </ul>
    </div>
</nav>