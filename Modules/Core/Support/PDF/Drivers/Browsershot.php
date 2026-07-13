<?php

namespace Modules\Core\Support\PDF\Drivers;

use Modules\Core\Support\PDF\PDFAbstract;
use Spatie\Browsershot\Browsershot as BrowsershotEngine;

/**
 * Headless-Chromium driver via spatie/browsershot. Opt-in: requires Node
 * and a Puppeteer-managed (or system) Chromium on the host — select it
 * with IP_PDF_DRIVER=Browsershot. Renders modern CSS the pure-PHP dompdf
 * driver cannot.
 */
class Browsershot extends PDFAbstract
{
    public function save($html, $filename): void
    {
        file_put_contents($filename, $this->getOutput($html));
    }

    public function getOutput($html)
    {
        return $this->getEngine($html)->pdf();
    }

    public function download($html, $filename)
    {
        $response = response($this->getOutput($html));

        $response->header('Content-Type', 'application/pdf');
        $response->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response->send();
    }

    protected function getEngine($html): BrowsershotEngine
    {
        $engine = BrowsershotEngine::html($html)
            ->format($this->paperSize)
            ->showBackground()
            /*
             * Report HTML is app-generated only (never user-authored) and
             * references images by local path, which Chromium blocks from
             * file:// pages unless file access is allowed.
             */
            ->addChromiumArguments(['allow-file-access-from-files']);

        if ($this->paperOrientation === 'landscape') {
            $engine->landscape();
        }

        if (config('ip.browsershot.node_binary')) {
            $engine->setNodeBinary(config('ip.browsershot.node_binary'));
        }

        if (config('ip.browsershot.npm_binary')) {
            $engine->setNpmBinary(config('ip.browsershot.npm_binary'));
        }

        if (config('ip.browsershot.chrome_path')) {
            $engine->setChromePath(config('ip.browsershot.chrome_path'));
        }

        if (config('ip.browsershot.no_sandbox')) {
            $engine->noSandbox();
        }

        return $engine;
    }
}
