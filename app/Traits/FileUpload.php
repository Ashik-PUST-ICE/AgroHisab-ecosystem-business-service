<?php

namespace App\Traits;

/**
 * @author Asif Iqbal
 */

use Illuminate\Support\Facades\Artisan;
use Intervention\Image\Facades\Image;

trait FileUpload
{
    /*
     * Define Directories
     */
    protected $logo_dir = 'storage/uploads/logo/';

    protected $proPic_dir = 'storage/uploads/profilePic/';

    protected $banner_dir = 'storage/uploads/banners/';

    protected $galary_dir = 'storage/uploads/galaries/';

    protected $images_dir = 'storage/uploads/images/';

    protected $client_images_dir = 'storage/uploads/client/images/';

    protected $file_dir = 'storage/uploads/files/';

    /*
     * ---------------------------------------------
     * Check the Derectory If exists or Not
     * ---------------------------------------------
     */
    protected function CheckDir($dir)
    {
        if (! is_dir('public/storage')) {
            Artisan::call('storage:link');
        }

        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        if (! file_exists($dir.'index.php')) {
            $file = fopen($dir.'index.php', 'w');
            fwrite($file, " <?php \n /* \n Unauthorize Access \n @Developer: Md Asif Iqbal \n Email: asif.ice.pust@gmail.com \n */ ");
            fclose($file);
        }
    }

    /*
     * ---------------------------------------------
     * Check the file If exists then Delete the file
     * ---------------------------------------------
     */
    protected function RemoveFile($filePath)
    {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    /*
     * ---------------------------------------------
     * Upload an Image
     * Change Image height and width
     * Send the null value in height or width to keep
     * the Image Orginal Ratio.
     * ---------------------------------------------
     */
    protected function UploadImage($request, $fileName, $dir = 'storage/uploads/', $width = null, $height = null, $oldFile = null)
    {
        if (! $request->hasFile($fileName)) {
            return $oldFile;
        }
        $dir = str_replace('//', '/', $dir);
        $this->CheckDir($dir);
        $this->RemoveFile($oldFile);
        ini_set('memory_limit', '1024M');
        $image = $request->file($fileName);
        $filename = $fileName.'_'.time().'.'.$image->getClientOriginalExtension();
        $path = $dir.$filename;
        if (empty($height) && empty($width)) {
            Image::make($image)->save($path);
        } elseif (empty($width)) {
            Image::make($image)->resize(null, $height, function ($constant) {
                $constant->aspectRatio();
            })->save($path);
        } elseif (empty($height)) {
            Image::make($image)->resize($width, null, function ($constant) {
                $constant->aspectRatio();
            })->save($path);
        } else {
            Image::make($image)->resize($width, $height)->save($path);
        }

        // imagejpeg(imagecreatefromstring(file_get_contents($path)), $path, 80);
        return $path;
    }

    /*
     * ---------------------------------------------
     * Upload any Kind of file
     * ---------------------------------------------
     */
    protected function uploadFile($request, $fileName, $dir, $oldFile = null)
    {
        if (! $request->hasFile($fileName)) {
            return $oldFile;
        }
        ini_set('memory_limit', '1024M');
        $this->CheckDir($dir);
        $this->RemoveFile($oldFile);
        $file = $request->file($fileName);
        $Newfilename = 'file_'.$fileName.'-'.time().'.'.$file->getClientOriginalExtension();
        $file->move($dir, $Newfilename);

        return $dir.$Newfilename;
    }

    /*
     * ---------------------------------------------
     * Upload any Kind of multiple file
     * ---------------------------------------------
     */
    protected function uploadMultipleFile($request, $fileName, $dir, $oldFile = null)
    {
        if (! $request->hasFile($fileName)) {
            return $oldFile;
        }
        if ($request->hasfile($fileName)) {
            $this->CheckDir($dir);
            ini_set('memory_limit', '1024M');
            $count = 0;
            $allfile = [];
            foreach ($request->file($fileName) as $file) {
                $newfilename = $fileName.$count.time().'.'.$file->getClientOriginalExtension();
                $path = $dir.$newfilename;

                $file->move($dir, $newfilename);

                $allfile[$count] = $dir.$newfilename;
                $count++;
            }

            return $allfile;
        }
    }

    /**
     * Rotate the Image
     */
    protected function rotateImage($image, $deg = 90)
    {
        $dir = explode('.', $image)[0].'1.'.explode('.', $image)[1];
        Image::make($image)->rotate($deg)->save($dir);
        $this->RemoveFile($image);

        return $dir;
    }

    /**
     * ------------------------------------------------------------
     * Upload Multiple Image
     * ------------------------------------------------------------
     */
    protected function UploadMultipleImage($request, $fileName, $dir, $width = null, $height = null)
    {
        if ($request->hasfile($fileName)) {
            $this->CheckDir($dir);
            ini_set('memory_limit', '1024M');
            $count = 0;
            $allImage = [];
            foreach ($request->file($fileName) as $image) {
                $filename = $fileName.$count.time().'.'.$image->getClientOriginalExtension();
                $path = $dir.$filename;
                if (empty($height) && empty($width)) {
                    Image::make($image)->save($path);
                } elseif (empty($width)) {
                    Image::make($image)->resize(null, $height, function ($constant) {
                        $constant->aspectRatio();
                    })->save($path);
                } elseif (empty($height)) {
                    Image::make($image)->resize($width, null, function ($constant) {
                        $constant->aspectRatio();
                    })->save($path);
                } else {
                    Image::make($image)->resize($width, $height)->save($path);
                }
                $allImage[$count] = $path;
                $count++;
            }

            return $allImage;
        }
    }

    protected function UploadMultipleAttachment($request, $fileName, $dir)
    {
        if ($request->hasfile($fileName)) {
            $this->CheckDir($dir);
            ini_set('memory_limit', '1024M');
            $count = 0;
            $allImage = [];
            foreach ($request->file($fileName) as $image) {
                $allImage[$count]['file_name'] = $image->getClientOriginalName();
                $allImage[$count]['file_type'] = $image->getMimeType();
                $allImage[$count]['file_size'] = $image->getSize();
                $filename = $fileName.$count.time().'.'.$image->getClientOriginalExtension();
                $path = $dir.$filename;
                $image->move($dir, $filename);
                $allImage[$count]['file_path'] = $path;
                $count++;
            }

            return $allImage;
        }
    }
}
