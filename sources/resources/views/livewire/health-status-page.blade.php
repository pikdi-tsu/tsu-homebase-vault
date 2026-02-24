<div wire:poll.10s>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Detail Status Sistem
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8">
                    @if ($checkResults && $checkResults->isNotEmpty())
                        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                            {{-- PERHATIKAN PERUBAHAN DI DALAM LOOP INI --}}
                            @foreach ($checkResults as $result)
                                <li class="py-4 flex items-center justify-between">
                                    <div>
                                        <p class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $result->check_label }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $result->notification_message }}</p>
                                    </div>

                                    @if ($result->status === 'ok')
                                        <span class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                            Ok
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200">
                                            Failed
                                        </span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-center text-gray-500 dark:text-gray-400">Belum ada hasil pemeriksaan kesehatan yang tersimpan.</p>
                        <p class="text-center text-xs text-gray-400 dark:text-gray-500 mt-2">Pastikan scheduler Anda berjalan dengan benar (`php artisan schedule:run`).</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
