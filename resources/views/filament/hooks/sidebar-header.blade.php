<div 
    class="fi-sidebar-custom-header flex transition-all duration-300 ease-in-out" 
    :class="{ 
        'flex-col-reverse items-center gap-y-4 py-4': ! $store.sidebar.isOpen, 
        'items-center gap-x-3 px-4 mb-4': $store.sidebar.isOpen 
    }" 
    x-data
    x-init="$watch('$store.sidebar.isOpen', value => {
        if (value) { document.body.classList.add('is-sidebar-open'); document.body.classList.remove('is-sidebar-closed'); }
        else { document.body.classList.add('is-sidebar-closed'); document.body.classList.remove('is-sidebar-open'); }
    }); 
    if ($store.sidebar.isOpen) { document.body.classList.add('is-sidebar-open'); } else { document.body.classList.add('is-sidebar-closed'); }"
>
    <!-- Custom Search Trigger -->
    <button 
        @click="$dispatch('open-global-search')"
        type="button"
        class="flex items-center rounded-lg border border-gray-200 bg-white text-sm text-gray-950 shadow-sm transition-all duration-200 hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:bg-white/10 dark:focus-visible:outline-primary-500"
        :class="{
            'w-full justify-between px-3 py-2': $store.sidebar.isOpen,
            'h-9 w-9 justify-center p-0 border-transparent bg-transparent dark:bg-transparent shadow-none hover:bg-gray-100 dark:hover:bg-white/5': ! $store.sidebar.isOpen
        }"
        x-tooltip="{
            content: 'Search',
            theme: $store.theme,
            placement: 'right',
        }"
    >
        <svg class="h-5 w-5 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
        
        <span 
            x-show="$store.sidebar.isOpen" 
            x-transition
            class="ml-2 flex-1 text-left text-gray-400 dark:text-gray-500 truncate"
        >
            Search
        </span>

        <span 
            x-show="$store.sidebar.isOpen"
            x-transition
            class="hidden text-xs text-gray-400 dark:text-gray-500 sm:block border border-gray-200 dark:border-white/10 px-1.5 rounded ml-2"
        >
            ⌘K
        </span>
    </button>

    <!-- Sidebar Collapse Trigger -->
    <button
        x-on:click="$store.sidebar.isOpen = ! $store.sidebar.isOpen"
        type="button"
        class="flex shrink-0 items-center justify-center rounded-lg outline-none transition duration-75 hover:bg-gray-100 focus-visible:bg-gray-100 dark:text-white dark:hover:bg-white/5 dark:focus-visible:bg-white/10"
        :class="{
            'h-9 w-9 bg-gray-50 dark:bg-white/5': $store.sidebar.isOpen,
            'h-9 w-9 bg-transparent hover:bg-gray-100 dark:hover:bg-white/5': ! $store.sidebar.isOpen
        }"
        x-tooltip="{
            content: $store.sidebar.isOpen ? 'Collapse sidebar' : 'Expand sidebar',
            theme: $store.theme,
            placement: 'right',
        }"
    >
        <svg 
            class="h-5 w-5 transition-transform duration-300" 
            :class="{ 'rotate-180': ! $store.sidebar.isOpen }"
            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
        </svg>
    </button>
</div>
