<?php

declare(strict_types=1);

class UploadService
{
    private string $basePath;
    private string $publicBase;

    public function __construct()
    {
        $this->basePath = UPLOAD_BASE_PATH;
        $this->publicBase = UPLOAD_PUBLIC_PATH;
    }

    public function validate(array $file): void
    {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name']) && !file_exists($file['tmp_name'])) {
            error_response('No file was uploaded.', 400);
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            error_response('Upload failed with error code ' . $file['error'], 400);
        }

        $size = $file['size'] ?? filesize($file['tmp_name']);
        if ($size > UPLOAD_MAX_SIZE) {
            error_response('File exceeds maximum size of ' . (UPLOAD_MAX_SIZE / 1024 / 1024) . ' MB.', 400);
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!in_array($mime, UPLOAD_ALLOWED_MIME, true)) {
            error_response('Unsupported file type. Allowed: JPG, PNG, WebP.', 400);
        }

        $extension = $this->getExtension($file['name']);
        if (!in_array($extension, UPLOAD_ALLOWED_EXTENSIONS, true)) {
            error_response('Unsupported file extension.', 400);
        }
    }

    public function store(array $file, string $folder): array
    {
        $this->validate($file);

        $extension = $this->getExtension($file['name']);
        $filename = $this->generateFilename($extension);
        $relativePath = $folder . '/' . $filename;
        $absolutePath = $this->basePath . '/' . $relativePath;

        if (!is_dir(dirname($absolutePath))) {
            mkdir(dirname($absolutePath), 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $absolutePath) && !rename($file['tmp_name'], $absolutePath)) {
            error_response('Failed to store uploaded file.', 500);
        }

        $dimensions = $this->getImageDimensions($absolutePath);
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($absolutePath);

        return [
            'filename' => $relativePath,
            'absolute_path' => $absolutePath,
            'original_filename' => $file['name'],
            'mime_type' => $mime,
            'extension' => $extension,
            'width' => $dimensions['width'],
            'height' => $dimensions['height'],
            'size_bytes' => filesize($absolutePath),
        ];
    }

    public function delete(string $relativePath): void
    {
        if ($relativePath === '') {
            return;
        }

        $absolute = $this->basePath . '/' . $relativePath;
        if (file_exists($absolute) && is_file($absolute)) {
            unlink($absolute);
        }
    }

    public function publicUrl(string $relativePath): string
    {
        return $this->publicBase . '/' . $relativePath;
    }

    private function getExtension(string $filename): string
    {
        $parts = explode('.', $filename);
        return count($parts) > 1 ? strtolower(end($parts)) : '';
    }

    private function generateFilename(string $extension): string
    {
        $date = date('Ymd');
        $hash = bin2hex(random_bytes(4));
        return $date . '_' . $hash . '.' . $extension;
    }

    private function getImageDimensions(string $path): array
    {
        $info = @getimagesize($path);
        if ($info === false) {
            return ['width' => null, 'height' => null];
        }
        return [
            'width' => (int) $info[0],
            'height' => (int) $info[1],
        ];
    }
}