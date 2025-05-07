<?php

namespace Modules\Core\Support\PDF;

use Modules\Core\Support\PDF\PDFAbstract;

use Modules\Core\Support\PDF\PDFInterface;

abstract class PDFAbstract implements PDFInterface
{
    protected $paperSize;

    protected $paperOrientation;

    public function __construct()
    {
        $this->paperSize        = config('ip.paperSize') ?: 'letter';
        $this->paperOrientation = config('ip.paperOrientation') ?: 'portrait';
    }

    public function setPaperSize($paperSize): void
    {
        $this->paperSize = $paperSize;
    }

    public function setPaperOrientation($paperOrientation): void
    {
        $this->paperOrientation = $paperOrientation;
    }
}
