<?php

namespace Modules\Core\Support\PDF\Drivers;

use Modules\Core\Support\PDF\PDFAbstract;

use Modules\Core\Support\PDF\Drivers\wkhtmltopdf;

use Knp\Snappy\Pdf;
use Modules\Core\Support\PDF\PDFAbstract;

class wkhtmltopdf extends PDFAbstract
{
    protected $paperSize;

    protected $paperOrientation;

    public function download($html, $filename): void
    {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo $this->getOutput($html);
    }

    public function getOutput($html)
    {
        $pdf = $this->getPdf();

        return $pdf->getOutputFromHtml($html);
    }

    public function save($html, $filename): void
    {
        $pdf = $this->getPdf();
        $pdf->generateFromHtml($html, $filename);
    }

    private function getPdf()
    {
        $pdf = new Pdf(config('ip.pdfBinaryPath'));
        $pdf->setOption('orientation', $this->paperOrientation);
        $pdf->setOption('page-size', $this->paperSize);
        $pdf->setOption('viewport-size', '1024x768');

        return $pdf;
    }
}
