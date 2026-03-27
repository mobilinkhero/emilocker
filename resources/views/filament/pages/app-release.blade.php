<x-filament-panels::page>

    {{-- Current release info --}}
    @php $current = $this->getCurrentRelease(); @endphp
    @if($current)
    <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-green-800">Current Live Release</p>
                <p class="text-sm text-green-700 mt-1">
                    v{{ $current['version_name'] }} (code {{ $current['version_code'] }})
                    — uploaded {{ \Carbon\Carbon::parse($current['uploaded_at'])->diffForHumans() }}
                </p>
                <a href="{{ $current['latest_url'] ?? $current['download_url'] }}" class="text-xs text-green-600 underline mt-1 inline-block" target="_blank">
                    Download APK
                </a>
            </div>
            <form action="{{ route('admin.app-release.delete-latest') }}" method="POST" onsubmit="return confirm('Delete the current APK? This will break QR provisioning until you upload a new one!');">
                @csrf
                <button type="submit" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs rounded-lg transition">
                    Delete APK
                </button>
            </form>
        </div>
    </div>
    @else
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6">
        <p class="text-sm text-yellow-800">No release uploaded yet.</p>
    </div>
    @endif

    {{-- Simple HTML upload form (bypasses Livewire) --}}
    <x-filament::section heading="Upload New Release">
        <form action="{{ route('admin.app-release.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            
            <div>
                <label class="block text-sm font-medium mb-2">Version Name</label>
                <input type="text" name="version_name" placeholder="1.0.0" required 
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Version Code</label>
                <input type="number" name="version_code" placeholder="1" required 
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Release Notes (Optional)</label>
                <textarea name="release_notes" rows="3" placeholder="Bug fixes and improvements..." 
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">APK File</label>
                <input type="file" name="apk" accept=".apk,application/vnd.android.package-archive" required 
                    class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                <p class="text-xs text-gray-500 mt-1">Maximum file size: 200MB</p>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    Upload Release
                </button>
            </div>
        </form>
    </x-filament::section>

</x-filament-panels::page>
