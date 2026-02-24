<x-form-section submit="updateProfileInformation">
    <x-slot name="title">
        {{ __('Profile Information') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Update your account\'s profile information and email address.') }}
    </x-slot>

    <x-slot name="form">
        <!-- Profile Photo -->
        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
            <div x-data="{
                            photoName: null,
                            photoPreview: null,
                            showCropModal: false,
                            imageToCrop: null,
                            cropper: null,
                            wire: @this
                         }"
             class="col-span-6 sm:col-span-4">

                <input type="file"
                       class="hidden"
                       x-ref="photo"
                       id="photo"
                       accept="image/*"
                       x-on:change="
                        const file = $refs.photo.files[0];
                        if (file) {
                        photoName = file.name;
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            imageToCrop = e.target.result;
                            showCropModal = true;

                            // Init Cropper
                            setTimeout(() => {
                                if (cropper) cropper.destroy();
                                const image = document.getElementById('image-to-crop');
                                cropper = new Cropper(image, {
                                    aspectRatio: 1 / 1,
                                    viewMode: 1,
                                    dragMode: 'move',
                                    autoCropArea: 1,
                                    cropBoxMovable: false,
                                    cropBoxResizable: false,
                                    toggleDragModeOnDblclick: false,
                                });
                            }, 100);
                        };
                        reader.readAsDataURL(file);
                    }
               " />

                <x-label for="photo" value="{{ __('Photo') }}" />

                <div class="mt-2" x-show="! photoPreview">
                    <img src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" style="width: 80px; height: 80px; display: block;" class="rounded-full h-20 w-20 object-cover">
                </div>

                <div class="mt-2" x-show="photoPreview" style="display: none;">
                    {{-- Kita pakai tag IMG biasa biar lebih stabil baca datanya --}}
                    <img x-bind:src="photoPreview"
                         style="width: 80px; height: 80px; display: block;"
                         class="block rounded-full object-cover shadow-md border-4 border-white dark:border-gray-700" alt="Foto Profil {{ Auth::user()->name }}" src="">
                </div>

                <x-secondary-button class="mt-2 mr-2" type="button" x-on:click.prevent="$refs.photo.click()">
                    {{ __('Select A New Photo') }}
                </x-secondary-button>

                @if (Auth::user()->profile_photo_path)
                    <x-secondary-button type="button" class="mt-2" wire:click="deleteProfilePhoto">
                        {{ __('Remove Photo') }}
                    </x-secondary-button>
                @endif

                <x-input-error for="photo" class="mt-2" />

                {{-- 👇👇👇 MODAL CROPPER MANUAL (PURE ALPINE) 👇👇👇 --}}
                <div x-show="showCropModal"
                                     style="display: none;"
                                     class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0"
                                     {{-- 👇 TAMBAHKAN BARIS INI 👇 --}}
                                     x-on:crop-success.window="
                        showCropModal = false;
                        photoPreview = $event.detail.preview;
                        if(cropper) { cropper.destroy(); cropper = null; }
                        document.getElementById('photo').value = null;
                     ">

                    {{-- Backdrop (Klik luar = Batal) --}}
                    <div x-show="showCropModal" class="fixed inset-0 transform transition-all"
                         x-on:click="showCropModal = false; document.getElementById('photo').value = null">
                        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                    </div>

                    {{-- Konten Modal --}}
                    <div x-show="showCropModal"
                         class="mb-6 bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:max-w-lg sm:mx-auto relative z-50">
                        <div class="px-6 py-4">
                            <div class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                Sesuaikan Foto
                            </div>
                            <div class="mt-4">
                                {{-- Container Gambar --}}
                                <div class="img-container w-full h-80 bg-black flex items-center justify-center overflow-hidden rounded-lg relative">
                                    <img id="image-to-crop" :src="imageToCrop" class="block max-w-full">
                                </div>
                            </div>
                        </div>
                        {{-- Footer Tombol --}}
                        <div class="px-6 py-4 bg-gray-100 dark:bg-gray-700 flex flex-row justify-end items-center gap-3 relative z-50">

                            {{-- Tombol Batal --}}
                            <x-secondary-button type="button" style="position: relative; z-index: 100; cursor: pointer;"
                                    x-on:click="
                                        showCropModal = false;
                                        if (cropper) { cropper.destroy(); cropper = null; }
                                        document.getElementById('photo').value = null;
                            ">
                                Batal
                            </x-secondary-button>

                            {{-- TOMBOL TERAPKAN --}}
                            <x-button type="button" class="ml-0"
                                      style="position: relative; z-index: 100;"
                                      x-on:click="processCrop(cropper, wire, $el)">
                                Terapkan
                            </x-button>
                        </div>
                    </div>
                </div>

            </div>
        @endif

        <!-- Name -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="name" value="{{ __('Name') }}" />
            <x-input id="name" type="text" class="mt-1 block w-full" wire:model="state.name" required autocomplete="name" />
            <x-input-error for="name" class="mt-2" />
        </div>

        <!-- Email -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="email" value="{{ __('Email') }}" />
            <x-input id="email" type="email" class="mt-1 block w-full" wire:model="state.email" required autocomplete="username" />
            <x-input-error for="email" class="mt-2" />

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::emailVerification()) && ! $this->user->hasVerifiedEmail())
                <p class="text-sm mt-2 dark:text-white">
                    {{ __('Your email address is unverified.') }}

                    <button type="button" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" wire:click.prevent="sendEmailVerification">
                        {{ __('Click here to re-send the verification email.') }}
                    </button>
                </p>

                @if ($this->verificationLinkSent)
                    <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                        {{ __('A new verification link has been sent to your email address.') }}
                    </p>
                @endif
            @endif
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-action-message class="me-3" on="saved">
            {{ __('Saved.') }}
        </x-action-message>

        <x-button wire:loading.attr="disabled" wire:target="photo">
            {{ __('Save') }}
        </x-button>
    </x-slot>
</x-form-section>

<script>
    function processCrop(cropper, component, button) {
        // 1. Cek Kesiapan
        if (!cropper) {
            alert('Cropper belum siap. Silakan coba lagi.');
            return;
        }

        // 2. Kunci Tombol
        button.innerText = 'Memproses...';
        button.disabled = true;

        // 3. Ambil Gambar dari Canvas (Ukuran 300x300px)
        // Kita pakai 'high' quality biar tajam di retina display
        const canvas = cropper.getCroppedCanvas({
            width: 300,
            height: 300,
            imageSmoothingQuality: 'high'
        });

        if (!canvas) {
            alert('Gagal membuat gambar.');
            resetButton(button);
            return;
        }

        // 4. Konversi Canvas ke File (Blob JPG kualitas 0.8)
        canvas.toBlob((blob) => {
            if (!blob) {
                alert('Gagal konversi file.');
                resetButton(button);
                return;
            }

            // Kita kasih nama "avatar.jpg" biar server tau ini gambar!
            const file = new File([blob], "avatar.jpg", { type: "image/jpeg" });

            // 5. Mulai Upload ke Livewire (Temporary)
            component.upload('photo', file,
                (uploadedFilename) => {
                    // === SUKSES UPLOAD ===

                    // A. Baca Blob jadi Data URL buat Preview (FIX BINGKAI HILANG)
                    const reader = new FileReader();
                    reader.onloadend = () => {
                        // B. Kirim sinyal sukses beserta data previewnya ke HTML
                        window.dispatchEvent(new CustomEvent('crop-success', {
                            detail: { preview: reader.result } // Kirim Data URL lengkap
                        }));

                        // C. Reset tombol
                        resetButton(button);
                    };
                    // Mulai baca file
                    reader.readAsDataURL(blob);
                },
                (error) => {
                    // === GAGAL UPLOAD ===
                    console.error(error);
                    alert('Gagal Upload ke Server. Cek koneksi internet.');
                    resetButton(button);
                },
                (event) => {
                    // === PROGRESS BERJALAN ===
                    // FIX TEKS ANEH: Kita ubah jadi teks simpel aja
                    button.innerText = 'Mengupload...';
                }
            );
        }, 'image/jpeg', 0.8);
    }

    // Fungsi pembantu untuk mereset tombol
    function resetButton(button) {
        button.disabled = false;
        button.innerText = 'Terapkan';
    }
</script>
