<?php

namespace  app\core;

use app\models\Settings;
use Google\Client as Google_Client;
use Google\Service\Drive  as Google_Drive;
use Google\Service\Drive\DriveFile as Google_Drive_File;
use Google\Service\Drive\Permission as Google_Drive_Permission;

class GoogleDrive
{
    public static $credentials = [];
    public static $client = null;
    public static $service = null;
    public static $maxPerPage = 50;
    public static $folderIds = [];

    public function __construct()
    {
        return static::init();
    }
    public static function init()
    {
        try {
            $_credentials = Settings::get('gdrive');
            foreach ($_credentials as $key => $value) {
                static::$credentials[$key] = $value['value'];
            }
            static::$client = new Google_Client();
            static::$client->setAuthConfig(static::$credentials);
            static::$client->addScope(Google_Drive::DRIVE);

            static::$service = new Google_Drive(static::$client);
        } catch (\Throwable $error) {
            error_log($error->__toString());
            return false;
        }
    }
    public static function getFolderId(string $folder = '')
    {
        if (empty($folder)) return false;

        if (!empty(static::$folderIds[$folder]))
            return static::$folderIds[$folder];

        $response = static::$service->files->listFiles([
            'q' => "mimeType='application/vnd.google-apps.folder' and name='$folder' and trashed=false",
            'fields' => 'files(id, name)'
        ]);

        $fId = $response->files[0]->id ?? static::createFolder($folder);

        static::$folderIds = [
            $folder => $fId,
        ];

        return $fId;
    }
    public static function createFolder(string $folder = '')
    {
        if (empty($folder)) return false;

        $folderMetadata = new Google_Drive_File([
            'name' => $folder,
            'mimeType' => 'application/vnd.google-apps.folder'
        ]);

        $folder = static::$service->files->create($folderMetadata, [
            'fields' => 'id'
        ]);

        $permission = new Google_Drive_Permission();
        $permission->setType('anyone');
        $permission->setRole('reader');

        static::$service->permissions->create($folder->getId(), $permission);

        return $folder->id;
    }
    public static function create(string $filePath, string $folder = '')
    {
        $filename = basename($filePath);

        $driveFile = new Google_Drive_File();
        $driveFile->setName($filename);

        if (!empty($folder))
            $driveFile->setParents([static::getFolderId($folder)]);

        $content = file_get_contents($filePath);

        try {
            $uploadedFile = static::$service->files->create($driveFile, [
                'data' => $content,
                'mimeType' =>  mime_content_type($filePath),
                'uploadType' => 'multipart'
            ]);
            $permission = new Google_Drive_Permission();
            $permission->setType('anyone');
            $permission->setRole('reader');

            static::$service->permissions->create($uploadedFile->getId(), $permission);
        } catch (\Throwable $error) {
            error_log('Error uploading file: ' . $error->getMessage());
            return false;
        }
        return $uploadedFile['id'];
    }
    public static function delete(string $fileId): bool
    {
        try {
            static::$service->files->delete($fileId);
        } catch (\Throwable $error) {
            error_log('Error uploading file: ' . $error->getMessage());
            return false;
        }
        return true;
    }
    public static function isFolder(string $fileId = '')
    {
        if (empty($fileId)) return false;

        try {
            return static::$service->files->get($fileId, ['fields' => 'mimeType'])->mimeType === "application/vnd.google-apps.folder";
        } catch (\Throwable $error) {
            error_log('Error get file list: ' . $error->getMessage());
        }
        return false;
    }
    public static function listFiles(string $pageToken = '', &$nextPageToken = '', string $folderId = 'root')
    {
        $result = [];
        try {
            $results = static::$service->files->listFiles([
                'q' => "'$folderId' in parents and trashed = false",
                'pageSize' => static::$maxPerPage,
                'pageToken' => $pageToken,
                'fields' => 'nextPageToken, files(id, name, size, thumbnailLink, imageMediaMetadata)',
            ]);

            $nextPageToken = $results->nextPageToken;

            if (count($results->files) === 0)
                return $result;

            foreach ($results->files as $file) {
                $_result = get_object_vars($file);
                $_result['realLink'] = static::getLink($file->id);
                $result[] = $_result;
            }
        } catch (\Throwable $error) {
            error_log('Error get file list: ' . $error->getMessage());
        }
        return $result;
    }
    public static function getLink(string $fileId): string
    {
        return "https://lh3.googleusercontent.com/d/$fileId";
    }
}
