// resources/js/custom-filament.js

if (!navigator.clipboard) {
    Object.defineProperty(navigator, 'clipboard', {
        value: {
            writeText: function (text) {
                return new Promise(function (resolve, reject) {
                    var textArea = document.createElement("textarea");
                    textArea.value = text;
                    textArea.style.position = "fixed";
                    textArea.style.left = "-999999px";
                    textArea.style.top = "-999999px";
                    document.body.appendChild(textArea);
                    textArea.focus();
                    textArea.select();
                    try {
                        document.execCommand('copy');
                        resolve();
                    } catch (err) {
                        reject(err);
                    }
                    document.body.removeChild(textArea);
                });
            }
        }
    });
}

window.addEventListener('copy-to-clipboard', event => {
    const token = event.detail.token;
    if (!token) return;

    // Fungsi kecil untuk memanggil notifikasi sukses Filament
    const showNotification = () => {
        new FilamentNotification()
            .title('Token berhasil disalin ke clipboard!')
            .success()
            .send();
    };

    // Cek jalur aman (HTTPS / Localhost)
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(token)
            .then(() => {
                showNotification();
            })
            .catch(err => {
                console.error('Gagal menyalin:', err);
            });
    } else {
        // HTTP / IP Address Hosting
        let textArea = document.createElement("textarea");
        textArea.value = token;

        textArea.style.position = "fixed";
        textArea.style.left = "-999999px";
        textArea.style.top = "-999999px";
        document.body.appendChild(textArea);

        textArea.focus();
        textArea.select();

        try {
            // Gunakan perintah eksekusi klasik browser
            document.execCommand('copy');
            showNotification();
        } catch (err) {
            console.error('Taktik Cadangan Gagal:', err);
        }

        // Bersihkan sisa elemen
        document.body.removeChild(textArea);
    }
});