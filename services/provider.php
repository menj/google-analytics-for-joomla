<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.google_analytics_4
 *
 * @copyright   Copyright (C) 2023-2026 MENJ
 * @license     GNU General Public License version 2 or later
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use MENJ\Plugin\System\GoogleAnalytics4\Extension\GoogleAnalytics4;

return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   2.0.0
     */
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $dispatcher = $container->get(DispatcherInterface::class);
                $plugin     = new GoogleAnalytics4(
                    $dispatcher,
                    (array) PluginHelper::getPlugin('system', 'google_analytics_4')
                );

                $plugin->setApplication(Factory::getApplication());

                return $plugin;
            }
        );
    }
};
