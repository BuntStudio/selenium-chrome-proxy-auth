<?php

namespace SeleniumChromeProxyAuth;

use RuntimeException;
use ZipArchive;

class ExtensionGenerator
{
    const HELPER_PATH = __DIR__ . '/helper';
    /**
     * @var string
     */
    protected $extensionZipPath;

    public function __construct($extensionZipPath = null)
    {
        if ($extensionZipPath === null) {
            $extensionZipPath = $this->generateTempPath();
        }

        if (pathinfo($extensionZipPath, PATHINFO_EXTENSION) !== 'zip') {
            throw new RuntimeException('File extension needs to be .zip');
        }

        $this->extensionZipPath = $extensionZipPath;
    }

    /**
     * @param string $proxyIp
     * @param int $proxyPort
     * @param string $proxyUser
     * @param string $proxyPass
     * @return string
     * @throws RuntimeException if can't create the zip file
     */
    public function generate(string $proxyIp, int $proxyPort, string $proxyUser, string $proxyPass): string
    {
        $zip = new ZipArchive();
        if ($zip->open($this->extensionZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Could not create ZIP file at ' . $this->extensionZipPath);
        }

        $zip->addFile(self::HELPER_PATH . '/manifest.json', 'manifest.json');

        $background = file_get_contents(self::HELPER_PATH . '/background.js');
        $background = str_replace(['%proxy_host', '%proxy_port', '%username', '%password'], [$proxyIp, $proxyPort, $proxyUser, $proxyPass], $background);

        $zip->addFromString('background.js', $background);
        $zip->close();

        return $this->extensionZipPath;
    }

    protected function generateTempPath()
    {
        return '/tmp/a'.uniqid().'.zip';
    }
}