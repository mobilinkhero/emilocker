<x-filament-panels::page>
<div class="space-y-6">

    {{-- Status Banner --}}
    @if($this->isSecretSet())
    <div class="flex items-center gap-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl px-5 py-4">
        <div class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></div>
        <p class="text-sm text-emerald-400 font-medium">Webhook secret is configured. Auto-deploy is ready.</p>
    </div>
    @else
    <div class="flex items-center gap-3 bg-amber-500/10 border border-amber-500/20 rounded-xl px-5 py-4">
        <div class="w-2.5 h-2.5 rounded-full bg-amber-400"></div>
        <p class="text-sm text-amber-400 font-medium">DEPLOY_WEBHOOK_SECRET is not set in .env — webhook signature verification is disabled.</p>
    </div>
    @endif

    {{-- Step by step setup --}}
    <x-filament::section heading="Step-by-Step GitHub Webhook Setup">
        <div class="space-y-5 text-sm">

            {{-- Step 1 --}}
            <div class="flex gap-4">
                <div class="w-7 h-7 rounded-full bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-emerald-400 font-bold text-xs flex-shrink-0 mt-0.5">1</div>
                <div class="flex-1">
                    <p class="text-gray-200 font-medium mb-2">Go to your GitHub repository settings</p>
                    <a href="{{ $this->getRepoUrl() }}/settings/hooks/new"
                       target="_blank"
                       class="inline-flex items-center gap-2 bg-gray-800 hover:bg-gray-700 text-gray-300 text-xs px-3 py-1.5 rounded-lg transition">
                        <x-heroicon-o-arrow-top-right-on-square class="w-3.5 h-3.5"/>
                        Open GitHub → Settings → Webhooks → Add webhook
                    </a>
                </div>
            </div>

            {{-- Step 2 - Payload URL --}}
            <div class="flex gap-4">
                <div class="w-7 h-7 rounded-full bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-emerald-400 font-bold text-xs flex-shrink-0 mt-0.5">2</div>
                <div class="flex-1">
                    <p class="text-gray-200 font-medium mb-2">Set <strong>Payload URL</strong></p>
                    <div class="flex items-center gap-2">
                        <code class="flex-1 bg-gray-900 border border-gray-700 text-emerald-400 px-4 py-2.5 rounded-lg text-xs font-mono">{{ $this->getWebhookUrl() }}</code>
                        <button
                            onclick="navigator.clipboard.writeText('{{ $this->getWebhookUrl() }}').then(() => { this.textContent = '✓ Copied'; setTimeout(() => this.textContent = 'Copy', 2000); })"
                            class="bg-gray-700 hover:bg-gray-600 text-gray-300 text-xs px-3 py-2.5 rounded-lg transition whitespace-nowrap">
                            Copy
                        </button>
                    </div>
                </div>
            </div>

            {{-- Step 3 - Content Type --}}
            <div class="flex gap-4">
                <div class="w-7 h-7 rounded-full bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-emerald-400 font-bold text-xs flex-shrink-0 mt-0.5">3</div>
                <div class="flex-1">
                    <p class="text-gray-200 font-medium mb-2">Set <strong>Content type</strong></p>
                    <code class="bg-gray-900 border border-gray-700 text-sky-400 px-4 py-2 rounded-lg text-xs font-mono">application/json</code>
                </div>
            </div>

            {{-- Step 4 - Secret --}}
            <div class="flex gap-4">
                <div class="w-7 h-7 rounded-full bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-emerald-400 font-bold text-xs flex-shrink-0 mt-0.5">4</div>
                <div class="flex-1">
                    <p class="text-gray-200 font-medium mb-2">Set <strong>Secret</strong> (must match your .env)</p>
                    @if($this->isSecretSet())
                    <div class="flex items-center gap-2">
                        <code class="flex-1 bg-gray-900 border border-gray-700 text-amber-400 px-4 py-2.5 rounded-lg text-xs font-mono">{{ $this->getWebhookSecret() }}</code>
                        <button
                            onclick="navigator.clipboard.writeText('{{ $this->getWebhookSecret() }}').then(() => { this.textContent = '✓ Copied'; setTimeout(() => this.textContent = 'Copy', 2000); })"
                            class="bg-gray-700 hover:bg-gray-600 text-gray-300 text-xs px-3 py-2.5 rounded-lg transition whitespace-nowrap">
                            Copy
                        </button>
                    </div>
                    @else
                    <div class="bg-amber-500/10 border border-amber-500/20 rounded-lg px-4 py-3">
                        <p class="text-amber-400 text-xs">Add this to your server's <code>.env</code> file first:</p>
                        <code class="text-amber-300 text-xs font-mono">DEPLOY_WEBHOOK_SECRET=your_strong_random_secret</code>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Step 5 --}}
            <div class="flex gap-4">
                <div class="w-7 h-7 rounded-full bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-emerald-400 font-bold text-xs flex-shrink-0 mt-0.5">5</div>
                <div class="flex-1">
                    <p class="text-gray-200 font-medium mb-1">Select <strong>Just the push event</strong></p>
                    <p class="text-gray-500 text-xs">Only triggers on git push — not on PRs, issues, etc.</p>
                </div>
            </div>

            {{-- Step 6 --}}
            <div class="flex gap-4">
                <div class="w-7 h-7 rounded-full bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-emerald-400 font-bold text-xs flex-shrink-0 mt-0.5">6</div>
                <div class="flex-1">
                    <p class="text-gray-200 font-medium">Click <strong>Add webhook</strong> — done.</p>
                    <p class="text-gray-500 text-xs mt-1">Every push to <code class="text-emerald-400">main</code> will now auto-deploy to this server.</p>
                </div>
            </div>
        </div>
    </x-filament::section>

    {{-- What the deploy does --}}
    <x-filament::section heading="What Happens on Each Deploy">
        <ol class="text-sm text-gray-400 space-y-1.5 list-decimal list-inside">
            <li>GitHub sends a POST to <code class="text-emerald-400">{{ $this->getWebhookUrl() }}</code></li>
            <li>Signature is verified using your secret key</li>
            <li><code>git pull origin main</code> — pulls latest code</li>
            <li><code>composer install --no-dev</code> — updates PHP dependencies</li>
            <li><code>php artisan migrate --force</code> — runs new migrations</li>
            <li><code>php artisan optimize:clear</code> + cache rebuild</li>
            <li><code>php artisan queue:restart</code> — restarts workers</li>
        </ol>
    </x-filament::section>

    {{-- Manual deploy button --}}
    <x-filament::section heading="Manual Deploy">
        <div class="flex items-center gap-4">
            <form wire:submit.prevent="triggerManualDeploy">
                <x-filament::button type="submit" icon="heroicon-o-arrow-path" color="warning">
                    Pull Latest & Deploy Now
                </x-filament::button>
            </form>
            <p class="text-xs text-gray-500">Runs git pull + cache clear without needing a GitHub push.</p>
        </div>
    </x-filament::section>

    {{-- Deploy log --}}
    <x-filament::section heading="Deploy Log">
        <pre class="text-xs text-emerald-400 font-mono bg-gray-950 border border-gray-800 p-4 rounded-xl overflow-x-auto max-h-80 overflow-y-auto whitespace-pre-wrap">{{ $this->getLastDeployLog() }}</pre>
    </x-filament::section>

</div>
</x-filament-panels::page>
