<?php

/*
 * This file is part of the Ivory Google Map package.
 *
 * (c) Eric GELOEN <geloen.eric@gmail.com>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Ivory\Tests\GoogleMap\Helper\Functional;

use GuzzleHttp\Psr7\Request;
use Http\Adapter\Guzzle6\Client;
use Http\Client\Common\Plugin\CachePlugin;
use Http\Client\Common\PluginClient;
use Http\Message\StreamFactory\GuzzleStreamFactory;
use Ivory\GoogleMap\Base\Coordinate;
use Ivory\GoogleMap\Base\Point;
use Ivory\GoogleMap\Helper\Builder\StaticMapHelperBuilder;
use Ivory\GoogleMap\Helper\StaticMapHelper;
use Ivory\GoogleMap\Map;
use Ivory\GoogleMap\MapTypeId;
use Ivory\GoogleMap\Overlay\EncodedPolyline;
use Ivory\GoogleMap\Overlay\Icon;
use Ivory\GoogleMap\Overlay\Marker;
use Ivory\GoogleMap\Overlay\Polyline;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

/**
 * @author GeLo <geloen.eric@gmail.com>
 *
 * @group  functional
 */
class StaticMapFunctionalTest extends TestCase
{
    private StaticMapHelper $staticMapHelper;

    private PluginClient $client;

    protected FilesystemAdapter $pool;

    protected function setUp(): void
    {
        if (!isset($_SERVER['API_KEY'])) {
            $this->markTestSkipped();
        }

        if (!isset($_SERVER['CACHE_PATH'])) {
            $this->markTestSkipped();
        }

        $this->staticMapHelper = $this->createStaticMapHelper();

        $this->pool   = new FilesystemAdapter('', 0, $_SERVER['CACHE_PATH']);
        $this->client = new PluginClient(new Client(), [
            new CachePlugin(
                $this->pool,
                new GuzzleStreamFactory(),
                [
                    'cache_lifetime'                    => null,
                    'default_ttl'                       => null,
                    'respect_response_cache_directives' => [],
                ]
            ),
        ]);
    }

    public function testRender()
    {
        $this->renderMap(new Map());
    }

    public function testRenderWithFormat()
    {
        $map = new Map();
        $map->setStaticOption('format', 'jpg');

        $this->renderMap($map);
    }

    public function testRenderWithScale()
    {
        $map = new Map();
        $map->setStaticOption('scale', 10);

        $this->renderMap($map);
    }

    public function testRenderWithWidth()
    {
        $map = new Map();
        $map->setStaticOption('width', 600);

        $this->renderMap($map);
    }

    public function testRenderWithHeight()
    {
        $map = new Map();
        $map->setStaticOption('height', 100);

        $this->renderMap($map);
    }

    public function testRenderWithZoom()
    {
        $map = new Map();
        $map->setMapOption('zoom', 10);

        $this->renderMap($map);
    }

    public function testRenderWithCenterCoordinate()
    {
        $map = new Map();
        $map->setCenter(new Coordinate(1, 1));

        $this->renderMap($map);
    }

    public function testRenderWithCenterAddress()
    {
        $map = new Map();
        $map->setStaticOption('center', 'Lille, France');

        $this->renderMap($map);
    }

    public function testRenderWithStyles()
    {
        $map = new Map();
        $map->setStaticOption('styles', [
            [
                'feature' => 'road.highway',
                'element' => 'geometry',
                'rules'   => [
                    'color'      => '0xc280e9',
                    'visibility' => 'simplified',
                ],
            ],
            [
                'feature' => 'transit.line',
                'rules'   => [
                    'visibility' => 'simplified',
                    'color'      => '0xbababa',
                ],
            ],
        ]);

        $this->renderMap($map);
    }

    public function testRenderWithMapType()
    {
        $map = new Map();
        $map->setStaticOption('maptype', MapTypeId::SATELLITE);

        $this->renderMap($map);
    }

    public function testRenderWithMapTypeId()
    {
        $map = new Map();
        $map->setMapOption('mapTypeId', MapTypeId::SATELLITE);

        $this->renderMap($map);
    }

    public function testRenderWithVisibleCoordinate()
    {
        $map = new Map();
        $map->setAutoZoom(true);
        $map->setStaticOption('visible', new Coordinate(1, 1));

        $this->renderMap($map);
    }

    public function testRenderWithVisibleAddress()
    {
        $map = new Map();
        $map->setAutoZoom(true);
        $map->setStaticOption('visible', 'Lille, France');

        $this->renderMap($map);
    }

    public function testRenderWithVisibleArray()
    {
        $map = new Map();
        $map->setAutoZoom(true);
        $map->setStaticOption('visible', ['Lille, France', new Coordinate(1, 1)]);

        $this->renderMap($map);
    }

    public function testRenderWithMarker()
    {
        $map = new Map();
        $map->getOverlayManager()->addMarker(new Marker(new Coordinate()));

        $this->renderMap($map);
    }

    public function testRenderWithMarkerAddress()
    {
        $marker = new Marker(new Coordinate());
        $marker->setStaticOption('location', 'Lille, France');

        $map = new Map();
        $map->getOverlayManager()->addMarker($marker);

        $this->renderMap($map);
    }

    public function testRenderWithMarkerStyles()
    {
        $styles = [
            'anchor' => 'bottomright',
            'size'   => 'tiny',
            'color'  => 'blue',
        ];

        $marker = new Marker(new Coordinate());

        $styledMarker = new Marker(new Coordinate(1, 1));
        $styledMarker->setStaticOption('styles', $styles);

        $styledMarkerAddress = new Marker(new Coordinate());
        $styledMarkerAddress->setStaticOption('location', 'Lille, France');
        $styledMarkerAddress->setStaticOption('styles', $styles);

        $map = new Map();
        $map->setAutoZoom(true);

        $map->getOverlayManager()->addMarker($marker);
        $map->getOverlayManager()->addMarker($styledMarker);
        $map->getOverlayManager()->addMarker($styledMarkerAddress);

        $this->renderMap($map);
    }

    public function testRenderWithMarkerIcon()
    {
        $marker = new Marker(new Coordinate());
        $marker->setIcon(new Icon('http://maps.google.com/mapfiles/ms/icons/blue-pushpin.png', new Point()));

        $map = new Map();
        $map->getOverlayManager()->addMarker($marker);

        $this->renderMap($map);
    }

    public function testRenderWithPolyline()
    {
        $map = new Map();
        $map->getOverlayManager()->addPolyline($this->createPolyline());

        $this->renderMap($map);
    }

    public function testRenderWithPolylineAddress()
    {
        $map = new Map();
        $map->getOverlayManager()->addPolyline($this->createPolylineAddress());

        $this->renderMap($map);
    }

    public function testRenderWithEncodedPolyline()
    {
        $map = new Map();
        $map->getOverlayManager()->addEncodedPolyline($this->createEncodedPolyline());

        $this->renderMap($map);
    }

    public function testRenderWithPolylineStyles()
    {
        $styles = [
            'color'  => '0x0000ff80',
            'weight' => 1,
        ];

        $polyline = $this->createPolyline();

        $styledPolyline = $this->createPolyline();
        $styledPolyline->setStaticOption('styles', $styles);

        $styledPolylineAddress = $this->createPolylineAddress();
        $styledPolylineAddress->setStaticOption('styles', $styles);

        $styledEncodedPolyline = $this->createEncodedPolyline();
        $styledEncodedPolyline->setStaticOption('styles', $styles);

        $map = new Map();
        $map->setAutoZoom(true);

        $map->getOverlayManager()->addPolyline($polyline);
        $map->getOverlayManager()->addPolyline($styledPolyline);
        $map->getOverlayManager()->addPolyline($styledPolylineAddress);
        $map->getOverlayManager()->addEncodedPolyline($styledEncodedPolyline);

        $this->renderMap($map);
    }

    public function testRenderWithPolylineOptions()
    {
        $polyline = $this->createPolyline();
        $polyline->setOptions([
            'geodisc'      => true,
            'strokeColor'  => '0x0000ff80',
            'strokeWeight' => 1,
        ]);

        $map = new Map();
        $map->getOverlayManager()->addPolyline($polyline);

        $this->renderMap($map);
    }

    public function testRenderWithEncodedPolylineOptions()
    {
        $encodedPolyline = $this->createEncodedPolyline();
        $encodedPolyline->setOptions([
            'geodisc'      => true,
            'strokeColor'  => '0x0000ff80',
            'strokeWeight' => 1,
        ]);

        $map = new Map();
        $map->getOverlayManager()->addEncodedPolyline($encodedPolyline);

        $this->renderMap($map);
    }

    public function testRenderWithSecret()
    {
        if (!isset($_SERVER['API_SECRET'])) {
            $this->markTestSkipped();
        }

        $this->staticMapHelper = StaticMapHelperBuilder::create()
            ->setKey($_SERVER['API_KEY'])
            ->setSecret($_SERVER['API_SECRET'])
            ->build();

        $this->renderMap(new Map());
    }

    protected function createStaticMapHelper(): StaticMapHelper
    {
        return StaticMapHelperBuilder::create()
            ->setKey($_SERVER['API_KEY'])
            ->build();
    }

    private function createPolyline(): Polyline
    {
        return new Polyline([
            new Coordinate(40.737102, -73.990318),
            new Coordinate(40.749825, -73.987963),
            new Coordinate(40.752946, -73.987384),
            new Coordinate(40.755823, -73.986397),
        ]);
    }

    private function createPolylineAddress(): Polyline
    {
        $polyline = new Polyline();
        $polyline->setStaticOption('locations', [
            '8t Avenue & 34th St, New York, NY',
            '8th Avenue & 42nd St, New+York, NY',
            'Park Ave & 42nd St, New York, NY',
            'Park Ave & 34th St, New York, NY',
        ]);

        return $polyline;
    }

    private function createEncodedPolyline(): EncodedPolyline
    {
        return new EncodedPolyline('yv_tHizrQiGsR`HcP');
    }

    private function renderMap(Map $map)
    {
        $request  = new Request('GET', $this->staticMapHelper->render($map));
        $response = $this->client->sendRequest($request);

        $this->assertSame(200, $response->getStatusCode());
    }
}
