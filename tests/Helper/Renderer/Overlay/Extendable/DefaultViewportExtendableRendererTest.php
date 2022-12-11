<?php

/*
 * This file is part of the Ivory Google Map package.
 *
 * (c) Eric GELOEN <geloen.eric@gmail.com>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Ivory\Tests\GoogleMap\Helper\Renderer\Overlay\Extendable;

use PHPUnit\Framework\MockObject\MockObject;
use Ivory\GoogleMap\Base\Bound;
use Ivory\GoogleMap\Helper\Formatter\Formatter;
use Ivory\GoogleMap\Helper\Renderer\AbstractRenderer;
use Ivory\GoogleMap\Helper\Renderer\Overlay\Extendable\DefaultViewportExtendableRenderer;
use Ivory\GoogleMap\Helper\Renderer\Overlay\Extendable\ExtendableRendererInterface;
use Ivory\GoogleMap\Overlay\ExtendableInterface;
use PHPUnit\Framework\TestCase;

/**
 * @author GeLo <geloen.eric@gmail.com>
 */
class DefaultViewportExtendableRendererTest extends TestCase
{
    private DefaultViewportExtendableRenderer $defaultViewportExtendableRenderer;

    protected function setUp(): void
    {
        $this->defaultViewportExtendableRenderer = new DefaultViewportExtendableRenderer(new Formatter());
    }

    public function testInheritance()
    {
        $this->assertInstanceOf(AbstractRenderer::class, $this->defaultViewportExtendableRenderer);
        $this->assertInstanceOf(ExtendableRendererInterface::class, $this->defaultViewportExtendableRenderer);
    }

    public function testRender()
    {
        $extendable = $this->createExtendableMock();
        $extendable
            ->expects($this->once())
            ->method('getVariable')
            ->will($this->returnValue('extendable'));

        $bound = $this->createBoundMock();
        $bound
            ->expects($this->once())
            ->method('getVariable')
            ->will($this->returnValue('bound'));

        $this->assertSame(
            'bound.union(extendable.getDefaultViewport())',
            $this->defaultViewportExtendableRenderer->render($extendable, $bound)
        );
    }

    private function createExtendableMock(): MockObject|ExtendableInterface
    {
        return $this->createMock(ExtendableInterface::class);
    }

    private function createBoundMock(): MockObject|Bound
    {
        return $this->createMock(Bound::class);
    }
}
