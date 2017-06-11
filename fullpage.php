<?php
namespace Grav\Plugin;

use Grav\Common\Grav;
use Grav\Common\Plugin;
use Grav\Common\Page\Page;
use Grav\Common\Page\Media;
use Grav\Common\Page\Collection;
use RocketTheme\Toolbox\Event\Event;

require('Utilities.php');
use Fullpage\Utilities;

/**
 * Creates slides using fullPage.js
 *
 * Class FullPageJsPlugin
 * @package Grav\Plugin
 * @return void
 * @license MIT License by Ole Vik
 */
class FullPagePlugin extends Plugin
{
    /**
     * [$options description]
     * @var [type]
     */
    protected $options;

    /**
     * Register intial event
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
     */
    public function onPluginsInitialized(Event $event)
    {
        if ($this->isAdmin()) {
            return;
        }
        $this->enable([
            'onPageContentProcessed' => ['pageIteration', 0],
            'onTwigTemplatePaths' => ['templates', 0]
        ]);
    }

    /**
     * Construct the page
     * @return void
     */
    public function pageIteration()
    {
        $page = $this->grav['page'];
        if ($page->template() == 'fullpage') {
            $config = $this->config();
            if (isset($page->header()->fullpage['options'])) {
                $config['options'] = array_merge($config['options'], $page->header()->fullpage['options']);
            }
            $utility = new Utilities($config);
            $tree = $utility->buildTree($page->route());
            $slides = $utility->buildContent($tree);
            $page->setRawContent($slides);
            $menu = $utility->buildMenu($tree);
            $menu = $utility->flattenArray($menu, 1);
            $this->grav['twig']->twig_vars['fullpage_menu'] = $menu;
        }

        if ($config['builtin_js']) {
            $this->grav['assets']->addJs('jquery', 110);
            $this->grav['assets']->addJs('plugin://fullpage/js/jquery.fullpage.min.js', 105);
            $options = json_encode($config['options'], JSON_PRETTY_PRINT);
            if ($config['change_titles']) {
                $changeTitles = ",
                afterLoad: function(anchorLink, index) {
                    document.title = $(this).data('name');
                }
                ";
                $options = $utility->stringInsert($options, $changeTitles, strlen($options)-1);
            }
            $this->grav['assets']->addInlineJs("
                $(document).ready(function() {
                    $('#fullpage').fullpage($options);
                });
            ");
        }
        if ($config['builtin_css']) {
            $this->grav['assets']->addCss('plugin://fullpage/css/jquery.fullpage.min.css', 105);
            $this->grav['assets']->addCss('plugin://fullpage/css/default.css');
        }
    }

    /**
     * Add templates-directory to Twig paths
     * @return void
     */
    public function templates()
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }
}
