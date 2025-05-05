<?php

namespace App\Support\PDF;

interface PDFInterface
{
    public function save($html, $filename);

    public function download($html, $filename);

    public function setPaperSize($paperSize);

    public function setPaperOrientation($paperOrientation);
}
