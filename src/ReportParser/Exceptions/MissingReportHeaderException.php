<?php


namespace Seat\Services\ReportParser\Exceptions;

use Throwable;

/**
 * Class MissingReportHeaderException.
 *
 * @package Seat\Services\ReportParser\Exceptions
 */
class MissingReportHeaderException extends InvalidReportException
{
    /**
     * MissingReportHeaderException constructor.
     *
     * @param \Throwable|null $previous
     */
    public function __construct(Throwable $previous = null)
    {
        parent::__construct('Report is malformed - header is missing', 0, $previous);
    }
}
