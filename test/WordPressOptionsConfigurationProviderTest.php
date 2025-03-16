<?php

/*
 * Copyright (c) 2025 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N7e\WordPress;

use N7e\Configuration\ConfigurationInterface;
use N7e\ConfigurationManagerInterface;
use N7e\DependencyInjection\ContainerBuilderInterface;
use N7e\DependencyInjection\ContainerInterface;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(WordPressOptionsConfigurationProvider::class)]
final class WordPressOptionsConfigurationProviderTest extends TestCase
{
    use PHPMock;

    private WordPressOptionsConfigurationProvider $provider;
    private MockObject $containerBuilderMock;
    private MockObject $containerMock;
    private MockObject $configurationMock;
    private MockObject $configurationManagerMock;

    #[Before]
    public function setUp(): void
    {
        $this->containerBuilderMock = $this->getMockBuilder(ContainerBuilderInterface::class)->getMock();
        $this->containerMock = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $this->configurationMock = $this->getMockBuilder(ConfigurationInterface::class)->getMock();
        $this->configurationManagerMock = $this->getMockBuilder(ConfigurationManagerInterface::class)->getMock();
        $this->provider = new WordPressOptionsConfigurationProvider();

        $this->containerBuilderMock->method('build')
            ->willReturn($this->containerMock);
        $this->containerMock->method('get')
            ->willReturnOnConsecutiveCalls($this->configurationMock, $this->configurationManagerMock);
        $this->configurationMock->method('all')
            ->willReturn([]);
    }

    private function capture(&$destination): Callback
    {
        return $this->callback(static function ($source) use (&$destination) {
            $destination = $source;

            return true;
        });
    }

    #[Test]
    public function shouldNotAugmentConfigurationIfWordPressOptionNotConfigured(): void
    {
        $this->configurationMock
            ->expects($this->exactly(2))
            ->method('get')
            ->with($this->stringStartsWith('wordpressOptionsConfiguration.'))
            ->willReturn(null);
        $this->configurationManagerMock
            ->expects($this->never())
            ->method('replaceWith');

        $this->provider->configure($this->containerBuilderMock);
        $this->provider->load($this->containerMock);
    }

    #[Test]
    public function shouldAugmentConfigurationWithWordPressOption(): void
    {
        $option = 'option';
        $keyPath = 'keyPath';

        $this->configurationMock
            ->method('get')
            ->willReturnOnConsecutiveCalls($option, $keyPath);
        $this->configurationManagerMock
            ->expects($this->once())
            ->method('replaceWith')
            ->with($this->capture($configuration));
        $this->getFunctionMock('N7e\\Configuration', 'get_option')
            ->expects($this->once())
            ->with($option, [])
            ->willReturn(['key' => 'value']);

        $this->provider->configure($this->containerBuilderMock);
        $this->provider->load($this->containerMock);

        $this->assertEquals([$keyPath => ['key' => 'value']], $configuration->all());
    }
}
