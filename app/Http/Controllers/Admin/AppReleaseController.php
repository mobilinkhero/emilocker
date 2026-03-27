<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AppReleaseController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'version_name' => 'required|string|max:20',
            'version_code' => 'required|integer',
            'release_notes' => 'nullable|string',
            'apk' => 'required|file|mimes:apk|max:204800', // 200MB
        ]);

        // Delete old APK files first to force overwrite
        Storage::delete('public/releases/noxlock-latest.apk');
        Storage::delete("public/releases/noxlock-v{$request->version_name}.apk");

        // Store with version name for history
        $versionedPath = $request->file('apk')->storeAs('public/releases', "noxlock-v{$request->version_name}.apk");
        
        // Copy the versioned file to create the "latest" file
        Storage::copy($versionedPath, 'public/releases/noxlock-latest.apk');

        // Update manifest
        $manifestPath = 'public/releases/manifest.json';
        $releases = [];
        
        if (Storage::exists($manifestPath)) {
            $releases = json_decode(Storage::get($manifestPath), true) ?? [];
        }
        
        $releases[] = [
            'version_code' => $request->version_code,
            'version_name' => $request->version_name,
            'download_url' => url(Storage::url("releases/noxlock-v{$request->version_name}.apk")),
            'latest_url' => url(Storage::url('releases/noxlock-latest.apk')),
            'release_notes' => $request->release_notes,
            'uploaded_at' => now()->toISOString(),
        ];
        
        Storage::put($manifestPath, json_encode($releases, JSON_PRETTY_PRINT));

        return redirect()->back()->with('success', "v{$request->version_name} uploaded successfully!");
    }

    public function deleteLatest()
    {
        Storage::delete('public/releases/noxlock-latest.apk');
        return redirect()->back()->with('success', 'Latest APK deleted successfully!');
    }
}
