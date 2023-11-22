<?php
/**
 * @package     WT SEO Meta templates - Virtuemart
 * @version     2.0.0
 * @Author 		Sergey Tolkachyov, https://web-tolk.ru
 * @copyright   Copyright (C) 2023 Sergey Tolkachyov
 * @license     GNU/GPL 3
 * @since 		1.0.0
 */

defined('_JEXEC') || die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\System\Wt_seo_meta_templates_virtuemart\Extension\Wt_seo_meta_templates_virtuemart;

return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function register(Container $container)
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $subject = $container->get(DispatcherInterface::class);
                $config  = (array) PluginHelper::getPlugin('system', 'wt_seo_meta_templates_virtuemart');
                $plugin = new Wt_seo_meta_templates_virtuemart($subject, $config);
                $plugin->setApplication(\Joomla\CMS\Factory::getApplication());
                return $plugin;
            }
        );
    }
};