<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NoxLock — EMI Device Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-950 text-white">

    {{-- Nav --}}
    <nav class="flex items-center justify-between px-8 py-4 border-b border-gray-800">
        <span class="text-2xl font-bold text-emerald-400">NoxLock</span>
        <div class="flex gap-6 text-sm">
            <a href="{{ route('features') }}" class="hover:text-emerald-400">Features</a>
            <a href="{{ route('pricing') }}" class="hover:text-emerald-400">Pricing</a>
            <a href="{{ route('contact') }}" class="hover:text-emerald-400">Contact</a>
            <a href="{{ route('customer.login') }}" class="bg-emerald-500 px-4 py-1.5 rounded-lg hover:bg-emerald-600">Customer Login</a>
        </div>
    </nav>

    {{-- Hero --}}
    <section class="text-center py-32 px-4">
        <h1 class="text-5xl font-extrabold mb-6 leading-tight">
            Smart EMI Device Management<br>
            <span class="text-emerald-400">for Your Business</span>
        </h1>
        <p class="text-gray-400 text-xl max-w-2xl mx-auto mb-10">
            Lock, unlock, and manage smartphones sold on installments — automatically.
            No missed payments. No stolen devices. Full control.
        </p>
        <a href="{{ route('customer.login') }}"
           class="bg-emerald-500 hover:bg-emerald-600 text-white px-8 py-4 rounded-xl text-lg font-semibold">
            Customer Portal
        </a>
    </section>

    {{-- Features Grid --}}
    <section class="max-w-6xl mx-auto px-8 py-20 grid grid-cols-1 md:grid-cols-3 gap-8">
        @foreach([
            ['Auto Lock/Unlock', 'Device locks automatically on missed EMI. Unlocks instantly on payment.', '🔒'],
            ['QR Provisioning', 'Set up any Android device as managed in under 2 minutes via QR code.', '📱'],
            ['Payment Gateways', 'Accept JazzCash, Easypaisa, card, and cash payments in one place.', '💳'],
            ['Real-time Dashboard', 'See every device status, payment, and alert in real time.', '📊'],
            ['SIM Change Detection', 'Instantly detect and lock on SIM swap attempts.', '🛡️'],
            ['Centralized Management', 'Manage all devices and customers from one powerful admin panel.', '🏪'],
        ] as [$title, $desc, $icon])
        <div class="bg-gray-900 rounded-2xl p-6 border border-gray-800">
            <div class="text-4xl mb-4">{{ $icon }}</div>
            <h3 class="text-lg font-bold mb-2">{{ $title }}</h3>
            <p class="text-gray-400 text-sm">{{ $desc }}</p>
        </div>
        @endforeach
    </section>

    {{-- Footer --}}
    <footer class="text-center py-8 text-gray-600 text-sm border-t border-gray-800">
        © {{ date('Y') }} NoxLock. All rights reserved.
    </footer>

</body>
</html>
