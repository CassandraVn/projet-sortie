<?php

namespace App\Form\model;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImportCsvFormModel extends UploadedFile
{
   private string $csv;

    public function __construct()
    {
    }

    /**
     * @return string
     */
    public function getCsv(): string
    {
        return $this->csv;
    }

    /**
     * @param string $csv
     */
    public function setCsv(string $csv): void
    {
        $this->csv = $csv;
    }
}