<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2021 Leon Jacobs
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace Seat\Services\ReportParser\Parsers;

use Seat\Services\ReportParser\Exceptions\EmptyReportException;
use Seat\Services\ReportParser\Exceptions\InvalidReportElementException;
use Seat\Services\ReportParser\Exceptions\InvalidReportException;
use Seat\Services\ReportParser\Exceptions\InvalidReportGroupException;
use Seat\Services\ReportParser\Exceptions\MissingReportGroupException;
use Seat\Services\ReportParser\Exceptions\MissingReportHeaderException;
use Seat\Services\ReportParser\ReportParser;

/**
 * Class MoonReportParser.
 *
 * @package Seat\Services\ReportParser\Parsers
 * @example
 *
 * Moon	Moon Product	Quantity	Ore TypeID	SolarSystemID	PlanetID	MoonID
 * Mesybier II - Moon 1
 * 	Glossy Scordite	0.300030559301	46687	30004975	40315069	40315070
 * 	Immaculate Jaspet	0.328855156898	46682	30004975	40315069	40315070
 * 	Pellucid Crokite	0.287893354893	46677	30004975	40315069	40315070
 * 	Sylvite	0.083220936358	45491	30004975	40315069	40315070
 * Mesybier V - Moon 1
 * 	Dazzling Spodumain	0.397311687469	46688	30004975	40315073	40315074
 * 	Immaculate Jaspet	0.412641495466	46682	30004975	40315073	40315074
 * 	Sylvite	0.190046817064	45491	30004975	40315073	40315074
 */
class MoonReport extends ReportParser
{
    /**
     * @var string
     */
    protected $line_delimiter = "\r\n";

    /**
     * @var string
     */
    protected $field_delimiter = "\t";

    /**
     * @var string
     */
    protected $header_regex = "/^(Moon)\t(Moon Product)\t(Quantity)\t(Ore TypeID)\t(SolarSystemID)\t(PlanetID)\t(MoonID)$/";

    /**
     * @var string
     */
    protected $group_regex = '/^([a-zA-Z0-9-]+ [IVX]{1,4} - Moon [0-9]{1,2}+)$/';

    /**
     * @var string
     */
    protected $component_regex = "/^\t([a-zA-Z ]+)\t([0-9]+.[0-9]+)\t([0-9]+)\t([0-9]+)\t([0-9]+)\t([0-9]+)$/";

    /**
     * @throws \Seat\Services\ReportParser\Exceptions\EmptyReportException
     * @throws \Seat\Services\ReportParser\Exceptions\InvalidReportElementException
     * @throws \Seat\Services\ReportParser\Exceptions\InvalidReportException
     * @throws \Seat\Services\ReportParser\Exceptions\InvalidReportGroupException
     * @throws \Seat\Services\ReportParser\Exceptions\MissingReportGroupException
     * @throws \Seat\Services\ReportParser\Exceptions\MissingReportHeaderException
     */
    public function validate()
    {
        if ($this->isEmpty())
            throw new EmptyReportException();

        if (! $this->hasHeader())
            throw new MissingReportHeaderException();

        if (! $this->hasGroups())
            throw new MissingReportGroupException();

        foreach ($this->groups as $group) {
            if ($group->isEmpty())
                throw new InvalidReportGroupException();

            foreach ($group->getElements() as $element)
                if ($element->isEmpty())
                    throw new InvalidReportElementException();
        }

        if ($this->hasElements())
            throw new InvalidReportException('A Moon Report cannot have root elements');
    }
}
