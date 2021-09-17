<?php

/*
 * This file is part of the PDF Version Converter.
 *
 * (c) Thiago Rodrigues <xthiago@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace App\Utilidades\PDF\GhostScript;

use Symfony\Component\Filesystem\Filesystem;
use App\Utilidades\PDF\GhostScript\ConverterCommand;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Exception\ProcessSignaledException;
use Symfony\Component\Process\Exception\LogicException;
use RuntimeException as GlobalRuntimeException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Throwable;

/**
 * Soporte para cambiar la version a un archivo PDF
 * @package App\Utilidades\PDF\GhostScript
 */
class Converter
{
    protected $command;
    protected $fs;

    public function __construct()
    {
        $this->command = new ConverterCommand;
        $this->fs = new Filesystem;
    }

    /**
     * convierte de version
     * agregar un uuid al nombre
     * @param mixed $file 
     * @param mixed $newVersion 
     * @param mixed $tmp 
     * @return void 
     * @throws RuntimeException 
     * @throws ProcessTimedOutException 
     * @throws ProcessSignaledException 
     * @throws LogicException 
     * @throws GlobalRuntimeException 
     * @throws IOException 
     * @throws FileNotFoundException 
     * @throws Throwable 
     */
    public function convert($file, $newVersion, $tmp)
    {
        $tmpFile = $tmp.'/'.uniqid('pdf_version_changer_') . '.pdf';

        $this->command->run($file, $tmpFile, $newVersion);

        if (!$this->fs->exists($tmpFile))
            throw new \RuntimeException("The generated file '{$tmpFile}' was not found.");

        $this->fs->copy($tmpFile, $file, true);
    }
}
