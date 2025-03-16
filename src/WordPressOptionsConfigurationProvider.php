<?php

/*
 * Copyright (c) 2025 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N7e\WordPress;

use N7e\Configuration\ArrayConfigurationSource;
use N7e\Configuration\ConfigurationBuilder;
use N7e\Configuration\ConfigurationInterface;
use N7e\Configuration\WordPressOptionsConfigurationSource;
use N7e\ConfigurationManagerInterface;
use N7e\DependencyInjection\ContainerBuilderInterface;
use N7e\DependencyInjection\ContainerInterface;
use N7e\ServiceProviderInterface;
use Override;

/**
 * Provides flexible application configuration.
 */
final class WordPressOptionsConfigurationProvider implements ServiceProviderInterface
{
    #[Override]
    public function configure(ContainerBuilderInterface $containerBuilder): void
    {
        $container = $containerBuilder->build();

        /** @var \N7e\Configuration\ConfigurationInterface $configuration */
        $configuration = $container->get(ConfigurationInterface::class);

        /** @var string|null $option */
        $option = $configuration->get('wordpressOptionConfiguration.option');

        /** @var string|null $keyPath */
        $keyPath = $configuration->get('wordpressOptionConfiguration.keyPath');

        if (is_null($option)) {
            return;
        }

        $configurationBuilder = (new ConfigurationBuilder())
            ->addConfigurationSource(new ArrayConfigurationSource($configuration->all()))
            ->addConfigurationSource(new WordPressOptionsConfigurationSource($option), $keyPath);

        /** @var \N7e\ConfigurationManagerInterface $configurationManager */
        $configurationManager = $container->get(ConfigurationManagerInterface::class);

        $configurationManager->replaceWith($configurationBuilder->build());
    }

    /**
     * {@inheritDoc}
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    #[Override]
    public function load(ContainerInterface $container): void
    {
    }
}
