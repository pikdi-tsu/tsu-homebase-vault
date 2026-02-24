<script>
    // Logika yang disempurnakan untuk menangani 3 kasus
    let isDarkMode;

    if (localStorage.theme === 'dark') {
        isDarkMode = true;
    } else if (localStorage.theme === 'light') {
        isDarkMode = false;
    } else {
        // Kondisi ini mencakup jika theme adalah 'system' atau belum ada sama sekali
        isDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
    }

    if (isDarkMode) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
</script>
