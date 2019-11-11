<?php


namespace Seat\Services\ReportParser\Exceptions;

use Throwable;

/**
 * Class MissingReportGroupException.
 *
 * @package Seat\Services\ReportParser\Exceptions
 */
class MissingReportGroupException extends InvalidReportException
{
    /**
     * MissingReportGroupException constructor.
     *
     * @param \Throwable|null $previous
     */
    public function __construct(Throwable $previous = null)
    {
        parent::__construct('Report is malformed - no groups have been provided', 0, $previous);
    }
}
