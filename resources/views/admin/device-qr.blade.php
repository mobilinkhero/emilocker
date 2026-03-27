<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code — Device #{{ $device->id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { background: white; }
            .no-print { display: none; }
            .print-only { display: block !important; }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white min-h-screen flex items-center justify-center p-6">
<div class="max-w-2xl w-full space-y-6">

    {{-- Header --}}
    <div class="text-center space-y-2">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-emerald-500/20 rounded-2xl mb-3">
            <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
            </svg>
        </div>
        <p class="text-emerald-400 text-xs font-semibold uppercase tracking-widest">NoxLock EMI System</p>
        <h1 class="text-3xl font-bold bg-gradient-to-r from-white to-gray-400 bg-clip-text text-transparent">
            Device Provisioning QR
        </h1>
        <div class="flex items-center justify-center gap-3 text-sm text-gray-400">
            <span class="flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                Device #{{ $device->id }}
            </span>
            <span class="text-gray-600">•</span>
            <span>{{ $device->imei }}</span>
            @if($device->customer)
            <span class="text-gray-600">•</span>
            <span class="text-emerald-400">{{ $device->customer->name }}</span>
            @endif
        </div>
    </div>

    {{-- QR Code Card --}}
    <div class="bg-gradient-to-br from-white to-gray-100 rounded-3xl p-8 shadow-2xl">
        <div class="flex items-center justify-center">
            {!! $qrImage !!}
        </div>
        <div class="mt-4 text-center">
            <p class="text-gray-600 text-xs font-medium">Scan to provision device</p>
        </div>
    </div>

    {{-- Instructions Card --}}
    <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-6 space-y-4">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-sm font-semibold text-white">Provisioning Instructions</p>
        </div>
        <ol class="text-sm text-gray-300 space-y-2.5 ml-7">
            <li class="flex items-start gap-2">
                <span class="flex-shrink-0 w-6 h-6 bg-emerald-500/20 text-emerald-400 rounded-full flex items-center justify-center text-xs font-bold">1</span>
                <span>Factory reset the target Android device</span>
            </li>
            <li class="flex items-start gap-2">
                <span class="flex-shrink-0 w-6 h-6 bg-emerald-500/20 text-emerald-400 rounded-full flex items-center justify-center text-xs font-bold">2</span>
                <span>On the "Welcome" screen, tap 6 times to enter provisioning mode</span>
            </li>
            <li class="flex items-start gap-2">
                <span class="flex-shrink-0 w-6 h-6 bg-emerald-500/20 text-emerald-400 rounded-full flex items-center justify-center text-xs font-bold">3</span>
                <span>Scan this QR code with the device camera</span>
            </li>
            <li class="flex items-start gap-2">
                <span class="flex-shrink-0 w-6 h-6 bg-emerald-500/20 text-emerald-400 rounded-full flex items-center justify-center text-xs font-bold">4</span>
                <span>NoxLock app installs automatically as Device Owner</span>
            </li>
            <li class="flex items-start gap-2">
                <span class="flex-shrink-0 w-6 h-6 bg-emerald-500/20 text-emerald-400 rounded-full flex items-center justify-center text-xs font-bold">5</span>
                <span>Device registers with server and starts monitoring</span>
            </li>
        </ol>
    </div>

    {{-- Security Notice --}}
    <div class="bg-gradient-to-r from-amber-500/10 to-orange-500/10 border border-amber-500/30 rounded-2xl p-5">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <div>
                <p class="text-amber-400 text-sm font-semibold mb-1">Security Notice</p>
                <p class="text-amber-300/80 text-xs leading-relaxed">
                    This QR contains a unique device API key. Do not share or screenshot this page.
                    Each QR can only be used once per device. Keep this secure.
                </p>
            </div>
        </div>
    </div>

    {{-- Device Info --}}
    <div class="grid grid-cols-2 gap-4 no-print">
        <div class="bg-gray-800/30 border border-gray-700/50 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1">Status</p>
            <p class="text-sm font-semibold capitalize">
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full {{ $device->status === 'active' ? 'bg-green-400' : 'bg-red-400' }}"></span>
                    {{ $device->status }}
                </span>
            </p>
        </div>
        <div class="bg-gray-800/30 border border-gray-700/50 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1">Created</p>
            <p class="text-sm font-semibold">{{ $device->created_at->format('M d, Y') }}</p>
        </div>
    </div>

    {{-- Raw JSON (for debugging) --}}
    <details class="bg-gray-800/30 border border-gray-700/50 rounded-xl no-print">
        <summary class="px-4 py-3 text-xs text-gray-500 cursor-pointer hover:text-gray-300 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
            </svg>
            View raw provisioning JSON
        </summary>
        <pre class="px-4 pb-4 text-xs text-gray-400 overflow-x-auto font-mono">{{ $qrJson }}</pre>
    </details>

    {{-- Actions --}}
    <div class="flex items-center justify-center gap-4 no-print">
        <button onclick="window.print()"
            class="flex items-center gap-2 bg-emerald-500 hover:bg-emerald-400 text-white px-6 py-3 rounded-xl text-sm font-medium transition shadow-lg shadow-emerald-500/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            Print QR Code
        </button>
        <a href="javascript:history.back()" 
            class="flex items-center gap-2 bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-xl text-sm font-medium transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Devices
        </a>
    </div>

</div>
</body>
</html>
