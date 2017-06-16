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
        $config = $this->config();
        if ($config['enabled'] && $page->template() == 'fullpage') {
            $utility = new Utilities($config);
            $tree = $utility->buildTree($page->route());
            $slides = $utility->buildContent($tree);
            $page->setRawContent($slides);
            $menu = $utility->buildMenu($tree);
            $menu = $utility->flattenArray($menu, 1);
            $this->grav['twig']->twig_vars['fullpage_menu'] = $menu;
            if ($config['transition']) {
                $this->grav['twig']->twig_vars['fullpage_transition'] = true;
            }

            if ($config['builtin_js']) {
                $this->grav['assets']->addJs('jquery', 110);
                $this->grav['assets']->addJs('plugin://fullpage/js/jquery.fullpage.min.js', 105);
                $options = json_encode($config['options'], JSON_PRETTY_PRINT);
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
                $this->grav['assets']->addInlineJs("
                    $(document).ready(function() {
                        $('#fullpage').fullpage($options);
                    });
                ");
            }
            if ($config['builtin_css']) {
                $this->grav['assets']->addCss('plugin://fullpage/css/jquery.fullpage.min.css', 105);
                $this->grav['assets']->addCss('plugin://fullpage/css/fullpage.css', 104);
            }
            if ($config['theme_css']) {
                $this->grav['assets']->addCss('theme://css/custom.css', 103);
            }
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
     * Add templates-directory to Twig paths
     * @return void
     */
    public function templates()
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }
}
