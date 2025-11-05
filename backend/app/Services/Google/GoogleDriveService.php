<?php

namespace App\Services\Google;

use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Permission;
use Illuminate\Http\UploadedFile;

class GoogleDriveService
{
    private $drive;
    private $rootFolderId;

    public function __construct()
    {
        $client = $this->getGoogleClient();
        $this->drive = new Drive($client);
        $this->rootFolderId = config('services.google.drive_folder_id');
    }

    private function getGoogleClient()
    {
        $client = new \Google\Client();
        $client->setAuthConfig(storage_path('app/google-credentials.json'));
        $client->addScope(Drive::DRIVE);
        $client->setAccessType('offline');

        return $client;
    }

    public function createMaterialFolder(string $materialId, string $slug): string
    {
        $folderName = "{$materialId}_{$slug}";
        $folderMetadata = new DriveFile([
            'name' => $folderName,
            'parents' => [$this->rootFolderId],
            'mimeType' => 'application/vnd.google-apps.folder',
        ]);

        $folder = $this->drive->files->create($folderMetadata, [
            'fields' => 'id',
        ]);

        // Create subfolders
        $this->createSubFolders($folder->id);

        // Set folder permissions to public
        $this->setPublicPermission($folder->id);

        return $folder->id;
    }

    private function createSubFolders(string $parentFolderId): void
    {
        $subFolders = ['source', 'video', 'audio', 'flashcards', 'reports', 'forms'];

        foreach ($subFolders as $folderName) {
            $folderMetadata = new DriveFile([
                'name' => $folderName,
                'parents' => [$parentFolderId],
                'mimeType' => 'application/vnd.google-apps.folder',
            ]);

            $this->drive->files->create($folderMetadata);
        }
    }

    private function setPublicPermission(string $fileId): void
    {
        $permission = new Permission([
            'type' => 'anyone',
            'role' => 'reader',
        ]);

        $this->drive->permissions->create($fileId, $permission);
    }

    public function uploadFile(UploadedFile $file, string $folderId, string $fileName = null): array
    {
        $fileName = $fileName ?: $file->getClientOriginalName();

        $fileMetadata = new DriveFile([
            'name' => $fileName,
            'parents' => [$folderId],
        ]);

        $uploadedFile = $this->drive->files->create($fileMetadata, [
            'data' => file_get_contents($file->getPathname()),
            'mimeType' => $file->getMimeType(),
            'uploadType' => 'multipart',
            'fields' => 'id,name,webViewLink,webContentLink',
        ]);

        // Set file permissions to public
        $this->setPublicPermission($uploadedFile->id);

        return [
            'id' => $uploadedFile->id,
            'name' => $uploadedFile->name,
            'view_url' => $uploadedFile->webViewLink,
            'download_url' => $uploadedFile->webContentLink,
        ];
    }

    public function uploadJsonFile(array $data, string $folderId, string $fileName): array
    {
        $jsonContent = json_encode($data, JSON_PRETTY_PRINT);
        $tempFile = tempnam(sys_get_temp_dir(), 'json_');
        file_put_contents($tempFile, $jsonContent);

        $fileMetadata = new DriveFile([
            'name' => $fileName,
            'parents' => [$folderId],
            'mimeType' => 'application/json',
        ]);

        $uploadedFile = $this->drive->files->create($fileMetadata, [
            'data' => $jsonContent,
            'uploadType' => 'multipart',
            'fields' => 'id,name,webViewLink,webContentLink',
        ]);

        // Set file permissions to public
        $this->setPublicPermission($uploadedFile->id);

        unlink($tempFile);

        return [
            'id' => $uploadedFile->id,
            'name' => $uploadedFile->name,
            'view_url' => $uploadedFile->webViewLink,
            'download_url' => $uploadedFile->webContentLink,
        ];
    }

    public function uploadHtmlFile(string $htmlContent, string $folderId, string $fileName): array
    {
        $fileMetadata = new DriveFile([
            'name' => $fileName,
            'parents' => [$folderId],
            'mimeType' => 'text/html',
        ]);

        $uploadedFile = $this->drive->files->create($fileMetadata, [
            'data' => $htmlContent,
            'uploadType' => 'multipart',
            'fields' => 'id,name,webViewLink,webContentLink',
        ]);

        // Set file permissions to public
        $this->setPublicPermission($uploadedFile->id);

        return [
            'id' => $uploadedFile->id,
            'name' => $uploadedFile->name,
            'view_url' => $uploadedFile->webViewLink,
            'download_url' => $uploadedFile->webContentLink,
        ];
    }

    public function getFolderInfo(string $folderId): array
    {
        $folder = $this->drive->files->get($folderId, [
            'fields' => 'id,name,webViewLink',
        ]);

        return [
            'id' => $folder->id,
            'name' => $folder->name,
            'view_url' => $folder->webViewLink,
        ];
    }

    public function listFilesInFolder(string $folderId): array
    {
        $response = $this->drive->files->listFiles([
            'q' => "'{$folderId}' in parents and trashed=false",
            'fields' => 'files(id,name,mimeType,webViewLink,webContentLink,size,createdTime)',
        ]);

        $files = [];
        foreach ($response->getFiles() as $file) {
            $files[] = [
                'id' => $file->getId(),
                'name' => $file->getName(),
                'mimeType' => $file->getMimeType(),
                'view_url' => $file->getWebViewLink(),
                'download_url' => $file->getWebContentLink(),
                'size' => $file->getSize(),
                'created_time' => $file->getCreatedTime(),
            ];
        }

        return $files;
    }
}