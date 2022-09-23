<?php

namespace App\Http\Controllers\Base;

use App\Http\Controllers\Base\BaseController;
use App\Models\Base\FileModel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

/**
 * @author Mrinal <mrinalmallik1@gmail.com>
 */

class FileBaseController extends BaseController
{
    public function __construct(FileModel $file)
    {
        $this->entityInstance = $file;
    }
      
    public function getImage(Request $request)
    {
        $filepath = $request->url;

        try {
            if (File::exists($filepath)) {
                $fileExt =  File::extension($filepath);
                if ($fileExt == 'jpg' || $fileExt == 'jpeg') {
                    $im = imagecreatefromjpeg($filepath);
                    if ($im !== false) {
                        header('Content-Type: image/jpeg');
                        return imagejpeg($im);
                    }
                }
                if ($fileExt == 'png') {
                    $im = imagecreatefrompng($filepath);
                    if ($im !== false) {
                        header('Content-Type: image/png');
                        return imagepng($im);
                    }
                }
            }
        } catch (Exception $ex) {
            return null;
        }
    }

    public function getFile(Request $request)
    {
        $filepath = $request->url;
        $from = $request->from;
        try {
            $path =  str_replace("\\", '/', public_path($filepath));

            if ($from == 1) {
                $path =  str_replace("\\", '/', storage_path($filepath));
            }

            if (file_exists($path)) {
                $headers = array(
                              'Content-Disposition' => 'inline',
                        );
                $file = explode('/', $filepath);
                $fileName = $file[count($file) - 1];
                return response()->download($path, $fileName, $headers);
            }
        } catch (Exception $ex) {
            return response()->json(['error' => 'file not found'], 404);
        }
    }

    public function manifest(Request $request)
    {
        $filepath = 'appstore/manifest.plist';
        $from = $request->form;
        try {
            $path =  str_replace("\\", '/', public_path($filepath));

            if ($from == 1) {
                $path =  str_replace("\\", '/', storage_path($filepath));
            }
            if (file_exists($path)) {
                $headers = array(
                              'Content-Disposition' => 'inline',
                        );
                $file = explode('/', $filepath);
                $fileName = $file[count($file) - 1];
                return response()->download($path, $fileName, $headers);
            }
        } catch (Exception $ex) {
            return response()->json(['error' => 'file not found'], 404);
        }
    }

    public function hrip(Request $request)
    {
        $filepath = 'appstore/hr4u.ipa';
        $from = $request->form;
        try {
            $path =  str_replace("\\", '/', public_path($filepath));

            if ($from == 1) {
                $path =  str_replace("\\", '/', storage_path($filepath));
            }
            if (file_exists($path)) {
                $headers = array(
                              'Content-Disposition' => 'inline',
                        );
                $file = explode('/', $filepath);
                $fileName = $file[count($file) - 1];
                return response()->download($path, $fileName, $headers);
            }
        } catch (Exception $ex) {
            return response()->json(['error' => 'file not found'], 404);
        }
    }

    public function imageFirst(Request $request)
    {
        $filepath = 'appstore/image.57x57.png';
        $from = $request->form;
        try {
            $path =  str_replace("\\", '/', public_path($filepath));

            if ($from == 1) {
                $path =  str_replace("\\", '/', storage_path($filepath));
            }
            if (file_exists($path)) {
                $headers = array(
                              'Content-Disposition' => 'inline',
                        );
                $file = explode('/', $filepath);
                $fileName = $file[count($file) - 1];
                return response()->download($path, $fileName, $headers);
            }
        } catch (Exception $ex) {
            return response()->json(['error' => 'file not found'], 404);
        }
    }

    public function imageSecond(Request $request)
    {
        $filepath = 'appstore/image.512x512.png';
        $from = $request->form;
        try {
            $path =  str_replace("\\", '/', public_path($filepath));

            if ($from == 1) {
                $path =  str_replace("\\", '/', storage_path($filepath));
            }
            if (file_exists($path)) {
                $headers = array(
                              'Content-Disposition' => 'inline',
                        );
                $file = explode('/', $filepath);
                $fileName = $file[count($file) - 1];
                return response()->download($path, $fileName, $headers);
            }
        } catch (Exception $ex) {
            return response()->json(['error' => 'file not found'], 404);
        }
    }
}
