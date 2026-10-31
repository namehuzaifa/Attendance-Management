<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Serves the Android build to employees straight from the portal, so nobody
 * has to be emailed an APK.
 */
class AppDownloadController extends Controller
{
    /** Where a release is dropped, relative to public/. */
    public const PATH = 'downloads/hrms-app.apk';

    public static function file(): ?string
    {
        $path = public_path(self::PATH);

        return is_file($path) ? $path : null;
    }

    public static function isAvailable(): bool
    {
        return self::file() !== null;
    }

    /** Human-readable size of the published build, e.g. "5.4 MB". */
    public static function size(): ?string
    {
        $path = self::file();

        if (!$path) {
            return null;
        }

        return round(filesize($path) / 1048576, 1) . ' MB';
    }

    public function download(): BinaryFileResponse
    {
        $path = self::file();

        abort_unless($path, 404, 'The Android app has not been published yet.');

        // The Android media type matters: with a generic one some browsers
        // open the file instead of handing it to the package installer.
        return response()->download($path, 'HRMS.apk', [
            'Content-Type' => 'application/vnd.android.package-archive',
        ]);
    }
}
