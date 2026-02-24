<div x-data="{
    theme: localStorage.getItem('theme') || 'system',

    cycle() {
        if (this.theme === 'system') {
            this.theme = 'light';
        } else if (this.theme === 'light') {
            this.theme = 'dark';
        } else {
            this.theme = 'system';
        }
        localStorage.setItem('theme', this.theme);
        this.applyTheme();
    },

    applyTheme() {
        if (this.theme === 'light') {
            document.documentElement.classList.remove('dark');
        } else if (this.theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            // Untuk 'system', hapus class agar skrip di <head> yang mengambil alih
            if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }
    }
}">
    <button @click="cycle()" class="p-2 rounded-full text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none">
        {{-- Ikon Bulan --}}
        <div x-show="theme === 'dark'" style="display: none;">
            <x-heroicon-o-moon class="h-6 w-6" />
        </div>
        {{-- Ikon Matahari --}}
        <div x-show="theme === 'light'" style="display: none;">
            <x-heroicon-o-sun class="h-6 w-6" />
        </div>
        {{-- Ikon Sistem/Komputer --}}
        <div x-show="theme === 'system'" style="display: none;">
            <x-heroicon-o-computer-desktop class="h-6 w-6" />
        </div>
    </button>
</div>
