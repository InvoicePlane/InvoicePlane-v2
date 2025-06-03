<?php

namespace Modules\Core\Support\PDF\Drivers;

use Modules\Core\Support\PDF\PDFAbstract;

class domPDF extends PDFAbstract
{
    public function save($html, $filename): void
    {
        file_put_contents($filename, $this->getOutput($html));
    }

    public function getOutput($html)
    {
        $pdf = $this->getPdf($html);

        return $pdf->output();
    }

    public function download($html, $filename)
    {
        $response = response($this->getOutput($html));

        $response->header('Content-Type', 'application/pdf');
        $response->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response->send();
    }

    private function getPdf($html)
    {
        $options = new Options();

        $options->setTempDir(storage_path('/'));
        $options->setFontDir(storage_path('/'));
        $options->setFontCache(storage_path('/'));
        $options->setLogOutputFile(storage_path('dompdf_log'));
        $options->setIsRemoteEnabled(true);
        $options->setIsHtml5ParserEnabled(true);
        $options->setIsFontSubsettingEnabled(true);

        $pdf = new PDF($options);

        $pdf->setPaper($this->paperSize, $this->paperOrientation);
        $pdf->loadHtml($html);

        $pdf->render();

        return $pdf;
    }
}
