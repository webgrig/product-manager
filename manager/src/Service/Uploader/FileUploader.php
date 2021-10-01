<?php

declare(strict_types=1);

namespace App\Service\Uploader;

use League\Flysystem\FilesystemOperator;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    private $storage;
    private $basUrl;

    public function __construct(FilesystemOperator $storage, string $basUrl)
    {
        $this->storage = $storage;
        $this->basUrl = $basUrl;
    }

    public function upload(UploadedFile $file): File
    {
        $path = date('Y/m/d');
        $name = Uuid::uuid4()->toString() . '.' . $file->getClientOriginalExtension();

        $this->storage->createDirectory($path);
        $stream = fopen($file->getRealPath(), 'rb+');
        $this->storage->writeStream($path . '/' . $name, $stream);
        fclose($stream);

        return new File($path, $name, $file->getSize());
    }

    public function generateUrl(string $path): string
    {
        return $this->basUrl . '/' . $path;
    }
}
