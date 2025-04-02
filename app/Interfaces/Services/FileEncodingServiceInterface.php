<?php

namespace App\Interfaces\Services;

use Illuminate\Http\UploadedFile;

interface FileEncodingServiceInterface {
    public function __construct();
    public function utf8Encode(UploadedFile $file):string|false;
}
