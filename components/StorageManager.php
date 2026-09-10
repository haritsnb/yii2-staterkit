<?php

namespace app\components;

use Yii;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;

class StorageManager
{
    /**
     * Ekstensi yang diizinkan (Strict)
     */
    public const ALLOWED_EXTENSIONS = ['jpeg', 'jpg', 'png', 'webp', 'svg', 'gif'];

    /**
     * MIME Types yang diizinkan
     */
    public const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/svg+xml',
        'image/gif',
    ];

    /**
     * Maksimal ukuran file: 5 MB (dalam bytes)
     */
    public const MAX_FILE_SIZE = 5 * 1024 * 1024;

    public static function getAbsolutePath(?string $relativePath): ?string
    {
        if (empty($relativePath)) return null;
        $cleanPath = ltrim(str_replace('\\', '/', $relativePath), '/');
        return Yii::getAlias('@storageRoot/' . $cleanPath);
    }

    public static function getUrl(?string $relativePath): string
    {
        if (empty($relativePath)) return '';
        if (preg_match('/^https?:\/\//i', $relativePath)) return $relativePath;
        
        $cleanPath = ltrim(str_replace('\\', '/', $relativePath), '/');
        return Yii::getAlias('@web/storages/' . $cleanPath);
    }

    public static function exists(?string $relativePath): bool
    {
        if (empty($relativePath)) return false;
        $abs = self::getAbsolutePath($relativePath);
        return $abs && file_exists($abs) && is_file($abs);
    }

    public static function delete(?string $relativePath): bool
    {
        if (empty($relativePath)) return false;
        $abs = self::getAbsolutePath($relativePath);
        if ($abs && file_exists($abs) && is_file($abs)) {
            return @unlink($abs);
        }
        return false;
    }

    /**
     * Eksekusi Upload Atomik & Validasi Ketat
     */
    public static function executeAtomicUpload(
        UploadedFile $file,
        string $folder,
        ?string $oldRelativePath,
        callable $dbOperation
    ): mixed {
        // 1. VALIDASI EKSTENSI FILE
        $extension = strtolower($file->extension ?: '');
        if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new \Exception('Format file tidak didukung! Format yang diperbolehkan hanya: JPEG, JPG, PNG, WebP, SVG, GIF.');
        }

        // 2. VALIDASI UKURAN FILE (Maks. 5MB)
        if ($file->size > self::MAX_FILE_SIZE) {
            throw new \Exception('Ukuran file terlalu besar! Maksimal ukuran file adalah 5 MB.');
        }

        // 3. VALIDASI MIME TYPE ASLI
        $mimeType = FileHelper::getMimeType($file->tempName);
        if ($mimeType && !in_array(strtolower($mimeType), self::ALLOWED_MIME_TYPES, true)) {
            // Khusus SVG beberapa server membaca text/plain atau image/svg
            if ($extension !== 'svg' && !str_contains($mimeType, 'svg')) {
                throw new \Exception('Tipe konten file tidak valid. Pastikan file adalah gambar asli.');
            }
        }

        // 4. Proses Simpan File ke project/storages/{folder}/
        $folderClean = trim($folder, '/');
        $dir = Yii::getAlias('@storageRoot/' . $folderClean);
        FileHelper::createDirectory($dir, 0775, true);

        $fileName   = $folderClean . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $absPath    = $dir . DIRECTORY_SEPARATOR . $fileName;
        $newRelPath = $folderClean . '/' . $fileName;

        if (!$file->saveAs($absPath)) {
            throw new \Exception('Gagal menyimpan file ke direktori storage.');
        }

        // 5. Jalankan Transaksi Database
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $result = call_user_func($dbOperation, $newRelPath);
            $transaction->commit();

            if (!empty($oldRelativePath) && $oldRelativePath !== $newRelPath) {
                self::delete($oldRelativePath);
            }

            return $result;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            // Rollback fisik jika query database gagal
            if (file_exists($absPath)) {
                @unlink($absPath);
            }
            throw $e;
        }
    }

    public static function executeAtomicDelete(?string $relativePath, callable $dbOperation): mixed
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $result = call_user_func($dbOperation);
            $transaction->commit();
            self::delete($relativePath);
            return $result;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}