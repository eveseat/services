<?php

namespace Seat\Services\ReportParser\Exceptions;

use Throwable;

/**
 * Class EmptyReportException.
 *
 * @package Seat\Services\ReportParser\Exceptions
 */
class EmptyReportException extends InvalidReportException
{
    /**
     * EmptyReportException constructor.
     *
     * @param \Throwable|null $previous
     */
    public function __construct(Throwable $previous = null)
    {
        parent::__construct('No report has been found or report is invalid', 0, $previous);
    }
}
