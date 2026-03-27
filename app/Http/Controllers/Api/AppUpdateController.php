<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AppUpdateController extends Controller
{
    /**
     * Device polls this endpoint to check if a newer APK is available.
     * GET /api/app/update-check?current_version_code=5
     */
    public function check(Request $request)
    {
        $currentVersionCode = (int) $request->query('current_version_code', 0);
        $latest             = $this->getLatestRelease();

        if (! $latest || $latest['version_code'] <= $currentVersionCode) {
            return response()->json(['update_available' => false]);
        }

        return response()->json([
            'update_available' => true,
            'version_code'     => $latest['version_code'],
            'version_name'     => $latest['version_name'],
            'download_url'     => $latest['download_url'],
            'release_notes'    => $latest['release_notes'] ?? null,
        ]);
    }

    /**
     * Admin uploads a new APK release.
     * POST /api/app/release  (admin only)
     */
    public function upload(Request $request)
    {
        $request->validate([
            'apk'          => 'required|file|mimes:apk|max:102400', // 100MB max
            'version_code' => 'required|integer',
            'version_name' => 'required|string',
            'release_notes'=> 'nullable|string',
        ]);

        $path = $request->file('apk')->storeAs(
            'releases',
            "noxlock-v{$request->version_name}.apk",
            'public'
        );

        // Store release metadata
        $releases   = $this->loadReleases();
        $releases[] = [
            'version_code'  => $request->version_code,
            'version_name'  => $request->version_name,
            'download_url'  => url(Storage::url($path)),
            'release_notes' => $request->release_notes,
            'uploaded_at'   => now()->toISOString(),
        ];

        Storage::put('releases/manifest.json', json_encode($releases, JSON_PRETTY_PRINT));

        return response()->json(['message' => 'Release uploaded.', 'download_url' => url(Storage::url($path))]);
    }

    private function getLatestRelease(): ?array
    {
        $releases = $this->loadReleases();
        if (empty($releases)) return null;

        usort($releases, fn($a, $b) => $b['version_code'] <=> $a['version_code']);
        return $releases[0];
    }

    private function loadReleases(): array
    {
        if (! Storage::exists('releases/manifest.json')) return [];
        return json_decode(Storage::get('releases/manifest.json'), true) ?? [];
    }
}
