<?php

namespace App\Service;

use Exception;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

use function PHPUnit\Framework\directoryExists;

class FileUploader
{
    private string $targetDirectory;

    public function __construct(private SluggerInterface $slugger)
    {
        $this->targetDirectory = 'uploads';
    }

    public function upload(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        $targetDirectory = $this->getTargetDirectory();
        if (!directoryExists($targetDirectory)) {
            mkdir($targetDirectory, '0775');
        }
        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            throw new Exception('Erro ao salvar imagem : '. $e->getMessage(), $e->getCode());
            // ... handle exception if something happens during file upload
        }

        return $fileName;
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}