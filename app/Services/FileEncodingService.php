<?php

namespace App\Services;

use App\Interfaces\Services\FileEncodingServiceInterface;
use Illuminate\Http\UploadedFile;

class FileEncodingService implements FileEncodingServiceInterface {

    public function __construct(){

    }

    public function utf8Encode(UploadedFile $file):string|false {
        return mb_convert_encoding(
            $file->get(),
            'UTF-8',
            mb_detect_encoding($file->get(), ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true) ?: 'UTF-8'
        );
    }
}
