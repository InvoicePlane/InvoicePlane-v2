<?php

namespace Modules\Core\Support;

use Exporter\Handler;
use Exporter\Source\ArraySourceIterator;

class Export
{
    protected $exportType;

    protected $fileName;

    protected $storagePath;

    protected $writerType;

    public function __construct($exportType, $writerType)
    {
        $this->exportType  = $exportType;
        $this->storagePath = storage_path('app');
        $this->writerType  = $writerType;
    }

    public function writeFile(): void
    {
        $resultsClass = 'Modules\Core\Support\Results\\' . $this->exportType;
        $writerClass  = 'Exporter\Writer\\' . $this->writerType;

        $fileExtension  = mb_strtolower(str_replace('Writer', '', $this->writerType));
        $this->fileName = $this->exportType . 'Export.' . $fileExtension;

        if (file_exists($this->storagePath . '/' . $this->fileName)) {
            unlink($this->storagePath . '/' . $this->fileName);
        }

        $results = new $resultsClass();

        $source = new ArraySourceIterator($results->getResults());

        $writer = new $writerClass($this->storagePath . '/' . $this->fileName);

        Handler::create($source, $writer)->export();
    }

    public function getDownloadPath()
    {
        return $this->storagePath . '/' . $this->fileName;
    }
}
