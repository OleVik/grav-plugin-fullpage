<?php
namespace Grav\Plugin;

use Grav\Common\Grav;
use Grav\Common\Plugin;
use Grav\Common\Page\Page;
use Grav\Common\Page\Media;
use Grav\Common\Page\Collection;
use RocketTheme\Toolbox\Event\Event;

require 'Utilities.php';
use Fullpage\Utilities;

/**
 * Creates slides using fullPage.js
 *
 * Class FullPageJsPlugin
 * 
 * @package Grav\Plugin
 * @return  void
 * @license MIT License by Ole Vik
 */
class FullPagePlugin extends Plugin
{

    /**
     * Grav cache setting
     *
     * @var [type]
     */
    protected $cache;

    /**
     * Register intial event
     * 
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0]
        ];
    }

    /**
     * Declare config from plugin-config
     * 
     * @return array Plugin configuration
     */
    public function config()
    {
        $pluginsobject = (array) $this->config->get('plugins');
        if (isset($pluginsobject) && $pluginsobject['fullpage']['enabled']) {
            $config = $pluginsobject['fullpage'];
        } else {
            return;
        }
        return $config;
    }

    /**
     * Initialize the plugin and events
     *
     * @param Event $event RocketTheme events
     * 
     * @return void
     */
    public function onPluginsInitialized(Event $event)
    {
        if ($this->isAdmin()) {
            $this->enable(
                [
                    'onGetPageTemplates' => ['onGetPageTemplates', 0]
                ]
            );
        } else {
            $this->grav['config']->set('system.cache.enabled', false);
            $this->enable([
                'onPageContentProcessed' => ['pageIteration', 0],
                'onTwigTemplatePaths' => ['templates', 0],
                'onShutdown' => ['onShutdown', 0]
            ]);
        }
    }

    /**
     * Construct the page
     * 
     * @return void
     */
    public function pageIteration()
    {
        $page = $this->grav['page'];
        $config = $this->config();
        if ($config['enabled'] && $page->template() == 'fullpage') {
            $utility = new Utilities($config);
            $tree = $utility->buildTree($page->route());
            $slides = $utility->buildContent($tree);
            $page->setRawContent($slides);
            $menu = $utility->buildMenu($tree);
            $menu = $utility->flattenArray($menu, 1);
            $this->grav['twig']->twig_vars['fullpage_menu'] = $menu;

            $options = json_encode($config['options'], JSON_PRETTY_PRINT);
            $init = '$(document).ready(function() {';
            $init .= '$("#fullpage").fullpage(';
            if ($config['transition']) {
                $transition = ",
                afterRender: function() {
                    $('#page_transition').css({
                        'opacity': '0', 
                        'visibility': 'hidden'
                    });
                }
                ";
                $options = $utility->stringInsert($options, $transition, strlen($options)-1);
            }
            if ($config['change_titles']) {
                $changeTitles = ",
                afterLoad: function(anchorLink, index) {
                    document.title = $(this).data('name');
                }
                ";
                $options = $utility->stringInsert($options, $changeTitles, strlen($options)-1);
            }
            $init .= $options;
            $init .= ');';
            $init .= '});';
            $this->grav['twig']->twig_vars['fullpage_init'] = $init;
            if (!empty($config['header_font'])) {
                $header_font = $config['header_font'];
                $this->grav['assets']->addInlineCss("
                    #fullpage h1,
                    #fullpage h2,
                    #fullpage h3,
                    #fullpage h4,
                    #fullpage h5,
                    #fullpage h6 {
                        font-family: $header_font;
                    }
                ");
            }
            if (!empty($config['block_font'])) {
                $block_font = $config['block_font'];
                $this->grav['assets']->addInlineCss("
                    #fullpage,
                    #fullpage p,
                    #fullpage ul,
                    #fullpage ol,
                    #fullpage blockquote,
                    #fullpage figcaption {
                        font-family: $block_font;
                    }
                ");
            }
        }
    }

    /**
     * Register templates and blueprints
     *
     * @param RocketTheme\Toolbox\Event\Event $event Event handler
     * 
     * @return void
     */
    public function onGetPageTemplates(Event $event)
    {
        $types = $event->types;
        $res = $this->grav['locator'];
        $types->scanBlueprints(
            $res->findResource('plugin://' . $this->name . '/blueprints')
        );
        $types->scanTemplates(
            $res->findResource('plugin://' . $this->name . '/templates')
        );
    }

    /**
     * Add templates-directory to Twig paths
     * 
     * @return void
     */
    public function templates()
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }

    /**
     * Reset cache on shutdown
     * 
     * @return void
     */
    public function onShutdown()
    {
        $this->grav['config']->set('system.cache.enabled', $this->cache);
    }
}
