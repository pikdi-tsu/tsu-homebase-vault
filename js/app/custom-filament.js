// resources/js/custom-filament.js

window.addEventListener('copy-to-clipboard', event => {
    if (!event.detail.token) return;

    // Gunakan API browser untuk menyalin teks ke clipboard
    navigator.clipboard.writeText(event.detail.token);

    // Tampilkan notifikasi sukses bawaan Filament
    new FilamentNotification()
        .title('Token berhasil disalin ke clipboard!')
        .success()
        .send();
});
