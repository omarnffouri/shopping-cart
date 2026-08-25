<?php

namespace App\Models\Helper;

use Exception;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class FileHelper
{
    public static function getUploadPath(): string
    {
        return storage_path('app/public/uploads');
    }

    public static function uploadPath($image, $deleting = false): string
    {
        if ($image === Config::get('constants.media.DEFAULT_IMAGE') && $deleting) {
            $image = time() . '-' . mt_rand(1, 9);
        }

        return self::getUploadPath() . $image;
    }

    public static function imgSrcUrl()
    {
        if (config('env.media.STORAGE') == config('env.media.GCS')) {
            return config('env.media.CDN_URL') . 'uploads/';
        } else if (config('env.media.STORAGE') == config('env.media.URL')) {
            return config('env.media.CDN_URL');
        }

        return config('env.url.APP_URL') . '/storage/uploads/';
    }

    public static function imageLink($image): string
    {
        if ($image == 'null' || $image == '') {
            $image = Config::get('constants.media.DEFAULT_IMAGE');
        }

        return self::imgSrcUrl() . $image;
    }

    public static function imageFullUrl($image): false|string
    {
        if ($image == 'null' || $image == '') {
            $image = Config::get('constants.media.DEFAULT_IMAGE');
        }
        if (config('env.media.STORAGE') == config('env.media.LOCAL')) {
            return config('app.url', config('env.url.APP_URL')) . '/storage/uploads/' . $image;

        } else if (config('env.media.STORAGE') == config('env.media.GCS')) {
            return config('env.media.CDN_URL') . 'uploads/' . $image;
        }

        return false;
    }

    public static function imageToBase64($image, $default = true): JsonResponse|string
    {
        try {
            if ($default && ($image == 'null' || $image == '')) {
                $image = Config::get('constants.media.DEFAULT_IMAGE');
            }
            $content = '';
            if ($image) {
                if (config('env.media.STORAGE') == config('env.media.LOCAL')) {
                    //$path = Storage::disk('public')->getAdapter()->applyPathPrefix($image);
                    $path = storage_path('app/public/uploads' . $image);
                    if (file_exists($path)) {
                        $content = base64_encode(file_get_contents($path));
                    }
                } else if (config('env.media.STORAGE') == config('env.media.GCS')) {
                    $path = config('env.media.CDN_URL') . 'uploads/' . $image;
                    $content = base64_encode(file_get_contents($path));
                }
            }
            return $content;

        } catch (Exception $ex) {
            return response()->json(Validation::error(null, $ex->getMessage()));
        }
    }

    /**
     * @throws Exception
     */
    public static function deleteFile($image): bool
    {
        try {
            if (config('env.media.STORAGE') == config('env.media.LOCAL')) {
                return self::deleteFileLocal($image);
            } else if (config('env.media.STORAGE') == config('env.media.GCS')) {
                return self::deleteFileGcs($image);
            }
        } catch (Exception $e) {
            report($e);
        }
        return false;
    }

    public static function readAllFileGcs(): string|array
    {
        try {
            $storage = new StorageClient([
                'keyFilePath' => base_path() . DIRECTORY_SEPARATOR . config('googlecloud.gc_key_file'),
            ]);
            $storageBucketName = config('googlecloud.storage_bucket');
            $bucket = $storage->bucket($storageBucketName);
            $images = [];
            foreach ($bucket->objects() as $object) {
                if (Utils::startsWith($object->name(), 'uploads/')) {
                    if ($object->name() !== 'uploads/') {
                        $images[] = str_replace('uploads/', '', $object->name());
                    }
                }
            }
            return $images;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function deleteFileGcs($image): true
    {
        try {
            $storage = new StorageClient([
                'keyFilePath' => base_path() . DIRECTORY_SEPARATOR . config('googlecloud.gc_key_file'),
            ]);

            $storageBucketName = config('googlecloud.storage_bucket');
            $bucket = $storage->bucket($storageBucketName);
            $object = $bucket->object(config('googlecloud.path_prefix') . $image);
            if ($object->exists()) {
                $object->delete();
                $thumbObject = $bucket->object(config('googlecloud.path_prefix') .config('constants.media.THUMB_PREFIX') . $image);
                if ($thumbObject->exists()) {
                    $thumbObject->delete();
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        return true;
    }

    public static function deleteFileLocal($image): bool
    {
        try {
            $file_path = $image ? FileHelper::uploadPath($image, true) : null;
            if (file_exists($file_path)) {
                unlink($file_path);
                $thumb_file_path = $image ?
                    FileHelper::uploadPath(config("constants.media.THUMB_PREFIX") . $image, true) : null;

                if (file_exists($thumb_file_path)) {
                    unlink($thumb_file_path);
                }
            }
            return true;
        } catch (Exception $e) {
            report($e);
            return false;
        }
    }

    public static function uploadImage($file, $prefix, $thumb = true)
    {
        try {
            if (config('env.media.STORAGE') == config('env.media.LOCAL')) {
                return self::uploadToLocal($file, $prefix, $thumb);
            } else if (config('env.media.STORAGE') == config('env.media.GCS')) {
                return self::uploadToGcs($file, $prefix, $thumb);
            } else if (config('env.media.STORAGE') == config('env.media.URL')) {
                $data['name'] = $file;
                return $data;
            }
        } catch (Exception $e) {
            report($e);
        }
    }

    public static function uploadToGcs($file, $prefix, $thumb = true): array
    {
        $storage = new StorageClient([
            'keyFilePath' => base_path() . DIRECTORY_SEPARATOR . config('googlecloud.gc_key_file'),
        ]);

        $storageBucketName = config('googlecloud.storage_bucket');
        $bucket = $storage->bucket($storageBucketName);

        $image_path = $file->getRealPath();
        $extension = $file->getClientOriginalExtension();
        $filename = $prefix . '-' . time() . '-' . mt_rand(1, 9) . '.' . $extension;

        $fileSource = fopen($image_path, 'r');
        $googleCloudStoragePath = config('googlecloud.path_prefix') . $filename;

        /* Upload a file to the bucket.
        Using Predefined ACLs to manage object permissions, you may
        upload a file and give read access to anyone with the URL.*/
        $bucket->upload($fileSource, [
            // 'predefinedAcl' => 'publicRead',
            'name' => $googleCloudStoragePath
        ]);

        if ($thumb) {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file);
            $thumbImg = $image->scale(320, 320);
            $googleCloudStorageThumbPath = config('googlecloud.path_prefix') . config("constants.media.THUMB_PREFIX") . $filename;

            $bucket->upload($thumbImg->stream(), [
                'name' => $googleCloudStorageThumbPath
            ]);
        }

        $data['name'] = $filename;

        return $data;
    }

    /**
     * @throws FileNotFoundException
     * @throws Exception
     */
    public static function uploadToLocal($file, $prefix, $thumb = true): array
    {
        $extension = $file->getClientOriginalExtension();
        $filename = $prefix . '-' . time() . '-' . mt_rand(1, 9) . '.' . $extension;

        if ($thumb) {
            self::generateThumbLocal($file, $filename);
        }

        Storage::disk('public')->put('uploads/'.$filename, File::get($file));

        $data['name'] = $filename;

        return $data;
    }

    public static function generateThumbLocal($file, $filename): void
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file)->scale(320, 320);
        $image->save(storage_path('app/public/uploads/' . config('constants.media.THUMB_PREFIX') . $filename));
    }

}
