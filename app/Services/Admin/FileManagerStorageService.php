<?php

namespace App\Services\Admin;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class FileManagerStorageService
{
    private const AVIF_QUALITY = 72;

    private const IMAGE_META_ARTIST = 'www.kalands.ir';

    private const IMAGE_META_COPYRIGHT = 'Copyright (c) www.kalands.ir';

    private const IMAGE_META_DESCRIPTION = 'Uploaded for www.kalands.ir';

    public function __construct(private readonly array $settings) {}

    public function workingRoot(): string
    {
        return public_path('uploads');
    }

    public function cacheRoot(): string
    {
        return $this->workingRoot().'/.cache';
    }

    public function ensureWorkingRoot(): void
    {
        File::ensureDirectoryExists($this->workingRoot());
        File::ensureDirectoryExists($this->cacheRoot());
    }

    public function browse(string $path = ''): array
    {
        $this->ensureWorkingRoot();

        $path = trim(str_replace(['..', '\\'], ['', '/'], $path), '/');
        $base = rtrim($this->workingRoot(), '/');
        $current = $path === '' ? $base : $base.'/'.$path;

        if (! is_dir($current)) {
            $current = $base;
            $path = '';
        }

        $directories = collect(File::directories($current))
            ->map(function (string $directory) use ($base) {
                $relative = Str::after($directory, $base.DIRECTORY_SEPARATOR);

                return [
                    'name' => basename($directory),
                    'path' => str_replace(DIRECTORY_SEPARATOR, '/', $relative),
                ];
            })
            ->sortBy('name')
            ->values()
            ->all();

        $files = collect(File::files($current))
            ->reject(fn ($file) => $file->getFilename() === '.gitignore')
            ->map(function ($file) use ($base) {
                $relative = Str::after($file->getPathname(), $base.DIRECTORY_SEPARATOR);
                $relativeUrl = str_replace(DIRECTORY_SEPARATOR, '/', $relative);

                return [
                    'name' => $file->getFilename(),
                    'path' => $relativeUrl,
                    'size' => $file->getSize(),
                    'modified_at' => $file->getMTime(),
                    'is_image' => (bool) preg_match('/\.(png|jpe?g|gif|webp|svg|bmp|avif)$/i', $file->getFilename()),
                ];
            })
            ->sortByDesc('modified_at')
            ->values()
            ->all();

        $breadcrumbs = [];
        $segments = $path === '' ? [] : explode('/', $path);
        $accumulated = '';
        foreach ($segments as $segment) {
            $accumulated = $accumulated === '' ? $segment : $accumulated.'/'.$segment;
            $breadcrumbs[] = [
                'name' => $segment,
                'path' => $accumulated,
            ];
        }

        return [
            'path' => $path,
            'breadcrumbs' => $breadcrumbs,
            'directories' => $directories,
            'files' => $files,
        ];
    }

    public function sanitizePath(string $path): string
    {
        return trim(str_replace(['..', '\\'], ['', '/'], $path), '/');
    }

    public function absolutePath(string $path = ''): string
    {
        $clean = $this->sanitizePath($path);
        $base = rtrim($this->workingRoot(), '/');

        return $clean === '' ? $base : ($base.'/'.$clean);
    }

    public function createDirectory(string $parentPath, string $name): void
    {
        $parent = $this->absolutePath($parentPath);
        File::ensureDirectoryExists($parent);
        $safeName = trim(str_replace(['..', '/', '\\'], '', $name));
        abort_if($safeName === '', 422);
        File::ensureDirectoryExists($parent.'/'.$safeName);
    }

    public function deletePath(string $path): void
    {
        $absolute = $this->absolutePath($path);
        if (is_dir($absolute)) {
            File::deleteDirectory($absolute);

            return;
        }
        if (is_file($absolute)) {
            File::delete($absolute);
        }
    }

    public function renamePath(string $path, string $newName): string
    {
        $absolute = $this->absolutePath($path);
        abort_unless(file_exists($absolute), 404);
        abort_if(is_file($absolute), 422, 'نام فایل قابل تغییر نیست.');
        $safeName = trim(str_replace(['..', '/', '\\'], '', $newName));
        abort_if($safeName === '', 422);
        $newAbsolute = dirname($absolute).'/'.$safeName;
        File::move($absolute, $newAbsolute);

        $base = rtrim($this->workingRoot(), '/');
        $relative = Str::after($newAbsolute, $base.DIRECTORY_SEPARATOR);

        return str_replace(DIRECTORY_SEPARATOR, '/', $relative);
    }

    public function movePath(string $path, string $targetDirectory): string
    {
        $source = $this->absolutePath($path);
        abort_unless(file_exists($source), 404);
        $targetDir = $this->absolutePath($targetDirectory);
        File::ensureDirectoryExists($targetDir);
        $target = rtrim($targetDir, '/').'/'.basename($source);
        File::move($source, $target);

        $base = rtrim($this->workingRoot(), '/');
        $relative = Str::after($target, $base.DIRECTORY_SEPARATOR);

        return str_replace(DIRECTORY_SEPARATOR, '/', $relative);
    }

    public function copyPath(string $path, string $targetDirectory): string
    {
        $source = $this->absolutePath($path);
        abort_unless(file_exists($source), 404);
        $targetDir = $this->absolutePath($targetDirectory);
        File::ensureDirectoryExists($targetDir);
        $target = rtrim($targetDir, '/').'/'.basename($source);

        if (is_dir($source)) {
            File::copyDirectory($source, $target);
        } else {
            File::copy($source, $target);
        }

        $base = rtrim($this->workingRoot(), '/');
        $relative = Str::after($target, $base.DIRECTORY_SEPARATOR);

        return str_replace(DIRECTORY_SEPARATOR, '/', $relative);
    }

    public function storeUploadedImage(UploadedFile $file, string $path = ''): string
    {
        abort_unless($file->isValid(), 422, 'فایل نامعتبر است.');
        $targetDir = $this->absolutePath($path);
        File::ensureDirectoryExists($targetDir);

        $binary = @file_get_contents($file->getRealPath());
        if ($binary === false || $binary === '') {
            throw new RuntimeException('خواندن فایل آپلودی ممکن نیست.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = strtolower((string) $finfo->buffer($binary));
        abort_if(! str_starts_with($mime, 'image/'), 422, 'فرمت فایل مجاز نیست.');

        $imageInfo = @getimagesizefromstring($binary);
        abort_if($imageInfo === false, 422, 'فایل تصویر معتبر نیست.');
        abort_if(($imageInfo[0] ?? 0) < 1 || ($imageInfo[1] ?? 0) < 1, 422, 'ابعاد تصویر نامعتبر است.');
        abort_if(($imageInfo[0] ?? 0) > 12000 || ($imageInfo[1] ?? 0) > 12000, 422, 'ابعاد تصویر بیش از حد مجاز است.');

        // Optional malware scan when ClamAV is available on server.
        if ($this->isClamAvAvailable()) {
            $this->scanWithClamAv($file->getRealPath());
        }

        $filename = Str::lower(bin2hex(random_bytes(16))).'.avif';
        $absoluteTarget = rtrim($targetDir, '/').'/'.$filename;
        $this->convertToAvifWithMetadata($binary, $absoluteTarget);

        return ltrim(str_replace('\\', '/', trim($path, '/').'/'.$filename), '/');
    }

    private function convertToAvifWithMetadata(string $binary, string $absoluteTarget): void
    {
        if ($this->supportsImagickAvif()) {
            $this->convertWithImagick($binary, $absoluteTarget);

            return;
        }

        if ($this->supportsGdAvif()) {
            $this->convertWithGd($binary, $absoluteTarget);

            return;
        }

        abort(500, 'برای تبدیل AVIF باید Imagick یا GD با پشتیبانی AVIF روی سرور فعال باشد.');
    }

    private function supportsImagickAvif(): bool
    {
        if (! extension_loaded('imagick') || ! class_exists(\Imagick::class)) {
            return false;
        }

        try {
            $formats = \Imagick::queryFormats('AVIF');

            return ! empty($formats);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function supportsGdAvif(): bool
    {
        return function_exists('imagecreatefromstring') && function_exists('imageavif');
    }

    private function convertWithImagick(string $binary, string $absoluteTarget): void
    {
        $image = new \Imagick;
        try {
            $image->readImageBlob($binary);
            abort_if($image->getNumberImages() < 1, 422, 'تصویر معتبر نیست.');

            if ($image->getNumberImages() > 1) {
                $image = $image->coalesceImages();
            }
            $image->setFirstIterator();

            $frame = $image->getImage();
            $frame->setImageFormat('avif');
            $frame->setImageCompressionQuality(self::AVIF_QUALITY);
            $frame->stripImage();

            $frame->setImageProperty('comment', self::IMAGE_META_DESCRIPTION);
            $frame->setImageProperty('exif:Artist', self::IMAGE_META_ARTIST);
            $frame->setImageProperty('exif:Copyright', self::IMAGE_META_COPYRIGHT);
            $frame->setImageProperty('exif:ImageDescription', self::IMAGE_META_DESCRIPTION);
            $frame->setImageProperty('xmp:CreatorTool', self::IMAGE_META_ARTIST);
            $frame->setImageProperty('xmp:Rights', self::IMAGE_META_COPYRIGHT);
            $frame->setImageProperty('xmp:Description', self::IMAGE_META_DESCRIPTION);

            $written = $frame->writeImage($absoluteTarget);
            abort_if(! $written, 500, 'ذخیره فایل AVIF انجام نشد.');
            $frame->clear();
            $frame->destroy();
        } catch (\ImagickException $e) {
            throw new RuntimeException('تبدیل تصویر به AVIF ناموفق بود.');
        } finally {
            $image->clear();
            $image->destroy();
        }
    }

    private function convertWithGd(string $binary, string $absoluteTarget): void
    {
        $image = @imagecreatefromstring($binary);
        abort_if($image === false, 422, 'پردازش تصویر ممکن نیست.');

        if (function_exists('imagepalettetotruecolor')) {
            @imagepalettetotruecolor($image);
        }
        imagealphablending($image, false);
        imagesavealpha($image, true);

        $ok = @imageavif($image, $absoluteTarget, self::AVIF_QUALITY);
        imagedestroy($image);
        abort_if(! $ok, 500, 'ذخیره فایل AVIF انجام نشد.');
    }

    private function isClamAvAvailable(): bool
    {
        if (! function_exists('shell_exec')) {
            return false;
        }

        $result = @shell_exec('command -v clamscan 2>/dev/null');

        return is_string($result) && trim($result) !== '';
    }

    private function scanWithClamAv(string $filePath): void
    {
        $escaped = escapeshellarg($filePath);
        $output = [];
        $statusCode = 0;
        @exec("clamscan --no-summary --infected {$escaped} 2>&1", $output, $statusCode);
        if ($statusCode === 1) {
            abort(422, 'فایل آلوده تشخیص داده شد.');
        }
        if ($statusCode > 1) {
            throw new RuntimeException('اسکن امنیتی فایل انجام نشد.');
        }
    }
}
