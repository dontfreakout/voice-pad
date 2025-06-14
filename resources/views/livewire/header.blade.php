<header x-data="{
            displayMode: localStorage.getItem('displayMode') || 'list',
            init() {
                this.$watch('displayMode', value => {
                    localStorage.setItem('displayMode', value);
                    window.dispatchEvent(new CustomEvent('display-mode-changed', { detail: { mode: value } }));
                });
                /* Dispatch an initial event in case components load after the header and need the current mode */
                /* Alternatively, components can read directly from localStorage on their init */
                /* window.dispatchEvent(new CustomEvent('display-mode-changed', { detail: { mode: this.displayMode } })); */
            }
         }"
         class="bg-white dark:bg-gray-800 shadow-md">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <!-- Logo -->
        <a href="{{ route('home') }}" class="text-xl font-semibold text-gray-800 dark:text-white">
            VoicePad
        </a>

        <div class="flex items-center space-x-4">
            <!-- Display Options -->
            <div class="flex items-center space-x-2">
                <button @click="displayMode = 'list'"
                        title="List view"
                        :class="{ 'bg-indigo-600 text-white': displayMode === 'list', 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300': displayMode !== 'list' }"
                        class="p-2 rounded-md hover:bg-indigo-500 hover:text-white focus:outline-none cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                </button>
                <button @click="displayMode = 'grid'"
                        title="Grid view"
                        :class="{ 'bg-indigo-600 text-white': displayMode === 'grid', 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300': displayMode !== 'grid' }"
                        class="p-2 rounded-md hover:bg-indigo-500 hover:text-white focus:outline-none cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 14a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 14a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                </button>
            </div>

            <!-- Config Button -->
            <button @click="configOpen = true" title="Configuration" class="p-2 rounded-md text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </button>
        </div>
    </div>
</header>

