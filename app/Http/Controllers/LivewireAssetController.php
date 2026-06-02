<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LivewireAssetController extends Controller
{
    public function serveJs(Request $request)
    {
        // Determine if we should serve the minified or non-minified version
        $isMinified = true; // By default, serve minified version

        // Load the manifest to get the expected hash
        $manifestPath = public_path('vendor/livewire/manifest.json');
        $manifest = [];

        if (File::exists($manifestPath)) {
            $manifest = json_decode(File::get($manifestPath), true);
        }

        // Get the expected hash for the livewire.js file
        $expectedHash = $manifest['/livewire.js'] ?? null;

        // Check if there's a specific version requested via query parameter
        $requestedFile = $request->query('id');

        // If the requested file doesn't match the expected hash, return 404
        if ($requestedFile && $expectedHash && $requestedFile !== $expectedHash) {
            abort(404);
        }

        // Serve the renamed file (since we renamed livewire.js to app-core.js)
        $renamedFilePath = public_path('vendor/livewire/app-core.js');

        if (!File::exists($renamedFilePath)) {
            // Fallback to minified version
            $renamedFilePath = public_path('vendor/livewire/app-core.min.js');

            if (!File::exists($renamedFilePath)) {
                abort(404);
            }
        }

        $content = File::get($renamedFilePath);
        $mimeType = File::mimeType($renamedFilePath);

        $response = response($content, 200, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000', // 1 year cache
        ]);

        // Add ETag header for caching
        $response->setEtag(md5($content));
        $response->setLastModified(new \DateTime());

        return $response->isNotModified($request) ? $response : $response;
    }
}