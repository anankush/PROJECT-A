<?php
class FileHandler {
    private $allowedTypes = [
        'image/jpeg',
        'image/jpg',
        'image/pjpeg',
        'image/png',
        'application/pdf'
    ];
    private $maxFileSize = 5242880; // 5MB
    private $uploadBaseDir;
    private $uploadedFiles = [];

    public function __construct($baseDir) {
        $this->uploadBaseDir = $baseDir;
    }

    public function validateFile($file) {
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            throw new Exception('No file was uploaded');
        }

        if ($file['size'] > $this->maxFileSize) {
            throw new Exception('File size exceeds limit of 5MB');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $this->allowedTypes)) {
            throw new Exception('Invalid file type. Allowed types: JPG, PNG, PDF');
        }

        return true;
    }

    public function uploadFile($file, $subDir) {
        $targetDir = $this->uploadBaseDir . $subDir;
        
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $fileName = uniqid() . '_' . basename($file['name']);
        $targetPath = $targetDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $this->uploadedFiles[] = $targetPath;
            return $fileName;
        }
        throw new Exception("Failed to upload file");
    }

    public function cleanup() {
        foreach ($this->uploadedFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
}
