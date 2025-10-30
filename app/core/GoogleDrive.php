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

    public function __construct()
    {
        return static::init();
    }
    public static function init()
    {
        try {
            $_credentials = Settings::get('gdrive');
            foreach($_credentials as $key => $value){
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
    public static function create(string $filePath)
    {
        $filename = basename($filePath);

        $driveFile = new Google_Drive_File();
        $driveFile->setName($filename);
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
            $uploadedFile = static::$service->files->delete($fileId);
        } catch (\Throwable $error) {
            error_log('Error uploading file: ' . $error->getMessage());
            return false;
        }
        return true;
    }
    public static function listFiles(string $pageToken = '')
    {
        $result = [];
        try {
            $results = static::$service->files->listFiles([
                'pageSize' => 5,
                'pageToken' => $pageToken,
                'fields' => 'nextPageToken, files(id, name, size, thumbnailLink)',
            ]);

            $_SESSION['nextPageToken'] = $results->nextPageToken;

            if (count($results->files) === 0)
                return $result;

            foreach ($results->files as $file) {
                $_result = get_object_vars($file);
                $_result['realLink'] = static::getLink($file->id);
                $result[] = $_result;
            }
        } catch (\Throwable $error) {
            error_log('Error uploading file: ' . $error->getMessage());
        }
        return $result;
    }
    public static function getLink(string $fileId): string
    {
        return "https://lh3.googleusercontent.com/d/$fileId";
    }
}
