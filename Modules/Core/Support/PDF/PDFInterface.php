<?php

namespace Modules\Core\Support\PDF;

use Modules\Core\Support\PDF\PDFInterface;

interface PDFInterface
{
    public function save($html, $filename);

    public function download($html, $filename);

    public function setPaperSize($paperSize);

    public function setPaperOrientation($paperOrientation);
}
