<?php


namespace Seat\Services\ReportParser\Exceptions;

use Throwable;

/**
 * Class InvalidReportElementException.
 *
 * @package Seat\Services\ReportParser\Exceptions
 */
class InvalidReportElementException extends InvalidReportException
{
    /**
     * InvalidReportElementException constructor.
     *
     * @param \Throwable|null $previous
     */
    public function __construct(Throwable $previous = null)
    {
        parent::__construct('Report is malformed - at least one provided element is empty', 0, $previous);
    }
}
