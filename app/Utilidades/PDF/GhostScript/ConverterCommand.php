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

use Symfony\Component\Process\Process;

/**
 * Encapsulates the knowledge about gs command.
 *
 * @author Thiago Rodrigues <xthiago@gmail.com>
 */
class ConverterCommand
{

    protected $baseCommand = 'gs -sDEVICE=pdfwrite -dCompatibilityLevel=%s -dPDFSETTINGS=/screen -dNOPAUSE -dQUIET -dBATCH -dColorConversionStrategy=/LeaveColorUnchanged -dEncodeColorImages=false -dEncodeGrayImages=false -dEncodeMonoImages=false -dDownsampleMonoImages=false -dDownsampleGrayImages=false -dDownsampleColorImages=false -dAutoFilterColorImages=false -dAutoFilterGrayImages=false -dColorImageFilter=/FlateEncode -dGrayImageFilter=/FlateEncode -sOutputFile=%s %s';

    public function __construct()
    {
    }

    public function run($originalFile, $newFile, $newVersion)
    {
        $command = sprintf($this->baseCommand, $newVersion, $newFile, $originalFile);
        // $command[18] = str_replace( '/', '\\', $command[18] );
        // $command[19] = str_replace( '/', '\\', $command[19] );

        $process = new Process( explode( ' ', $command ) );

        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }
    }
}
