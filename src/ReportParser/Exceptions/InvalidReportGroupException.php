<?php


namespace Seat\Services\ReportParser\Exceptions;

use Throwable;

/**
 * Class InvalidReportGroupException.
 *
 * @package Seat\Services\ReportParser\Exceptions
 */
class InvalidReportGroupException extends InvalidReportException
{
    /**
     * InvalidReportGroupException constructor.
     *
     * @param \Throwable|null $previous
     */
    public function __construct(Throwable $previous = null)
    {
        parent::__construct('Report is malformed - at least one provided group is empty', 0, $previous);
    }
}
