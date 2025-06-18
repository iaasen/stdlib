<?php
/**
 * User: ingvar.aasen
 * Date: 2025-06-18
 */

namespace Iaasen\Cli;

use Monolog\Formatter\LineFormatter;

class NoMicrosecondsFormatter extends LineFormatter
{
    protected function formatDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d\TH:i:sP');
    }
}
