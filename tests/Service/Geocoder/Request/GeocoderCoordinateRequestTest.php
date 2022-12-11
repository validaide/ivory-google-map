<?php

/*
 * This file is part of the Ivory Google Map package.
 *
 * (c) Eric GELOEN <geloen.eric@gmail.com>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Ivory\Tests\GoogleMap\Service\Geocoder\Request;

use PHPUnit\Framework\MockObject\MockObject;
use Ivory\GoogleMap\Base\Coordinate;
use Ivory\GoogleMap\Service\Geocoder\Request\AbstractGeocoderReverseRequest;
use Ivory\GoogleMap\Service\Geocoder\Request\GeocoderCoordinateRequest;
use PHPUnit\Framework\TestCase;

/**
 * @author GeLo <geloen.eric@gmail.com>
 */
class GeocoderCoordinateRequestTest extends TestCase
{
    private GeocoderCoordinateRequest $request;

    /**
     * @var Coordinate|MockObject|null
     */
    private Coordinate|MockObject|null $coordinate = null;

    protected function setUp(): void
    {
        $this->request = new GeocoderCoordinateRequest($this->coordinate = $this->createCoordinateMock());
    }

    public function testInheritance()
    {
        $this->assertInstanceOf(AbstractGeocoderReverseRequest::class, $this->request);
    }

    public function testDefaultState()
    {
        $this->assertSame($this->coordinate, $this->request->getCoordinate());
    }

    public function testCoordinate()
    {
        $this->request->setCoordinate($coordinate = $this->createCoordinateMock());

        $this->assertSame($coordinate, $this->request->getCoordinate());
    }

    public function testBuildQuery()
    {
        $this->coordinate
            ->expects($this->once())
            ->method('getLatitude')
            ->will($this->returnValue($latitude = 1.2));

        $this->coordinate
            ->expects($this->once())
            ->method('getLongitude')
            ->will($this->returnValue($longitude = 2.3));

        $this->assertSame(['latlng' => implode(',', [$latitude, $longitude])], $this->request->buildQuery());
    }

    private function createCoordinateMock(): MockObject|Coordinate
    {
        return $this->createMock(Coordinate::class);
    }
}
