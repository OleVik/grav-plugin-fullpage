<?php
namespace Fullpage;

use Grav\Common\Grav;
use Grav\Common\Plugin;
use Grav\Common\Page\Page;
use Grav\Common\Page\Collection;

class Utilities
{
    /**
     * Plugin configuration
     * @var array
     */
    protected $config;

    /**
     * Instantiate Fullpage Utilities
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Creates page-structure recursively
     * @param string $route Route to page
     * @param string $mode Reserved collection-mode for handling child-pages
     * @param integer $depth Reserved placeholder for recursion depth
     * @return array Page-structure with children
     */
    public function buildTree($route, $mode = false, $depth = 0)
    {
        $page = Grav::instance()['page'];
        $depth++;
        $mode = '@page.self';
        if ($depth > 1) {
            $mode = '@page.children';
        }
        $pages = $page->evaluate([$mode => $route]);
        $pages = $pages->published()->order($this->config['order']['by'], $this->config['order']['dir']);
        $paths = array();
        foreach ($pages as $page) {
            $route = $page->rawRoute();
            $paths[$route]['depth'] = $depth;
            $paths[$route]['title'] = $page->title();
            $paths[$route]['menu'] = array(
                'anchor' => $page->slug(),
                'title' => $page->title()
            );
            $paths[$route]['route'] = $route;
            $paths[$route]['slug'] = $page->slug();
            if (isset($this->config['inject_footer'])) {
                $paths[$route]['inject_footer'] = $this->config['inject_footer'];
            }
            if (isset($page->header()->inject_footer)) {
                $paths[$route]['inject_footer'] = $page->header()->inject_footer;
            }
            if (!empty($paths[$route]['inject_footer'])) {
                $paths[$route]['inject_footer'] = Grav::instance()['twig']->processTemplate($paths[$route]['inject_footer'], ['page' => $page]);
            }
            if (isset($page->header()->horizontal)) {
                $paths[$route]['horizontal'] = $page->header()->horizontal;
            }
            if (isset($page->header()->styles)) {
                $paths[$route]['styles'] = $page->header()->styles;
            } elseif (isset($this->config['styles'])) {
                $paths[$route]['styles'] = $this->config['styles'];
            }
            $paths[$route]['content'] = $page->rawMarkdown();

            if (!empty($paths[$route])) {
                $children = $this->buildTree($route, $mode, $depth);
                if (!empty($children)) {
                    $paths[$route]['children'] = $children;
                }
            }
        }
        if (!empty($paths)) {
            return $paths;
        } else {
            return null;
        }
    }

    /**
     * Create HTML to use with fullPage.js
     * @param array $pages Page-structure with children
     * @return string HTML-structure
     */
    public function buildContent($pages)
    {
        $parsedown = new \Parsedown();
        $return = '';
        foreach ($pages as $route => $page) {
            ob_start();
            $title = $page['title'];
            $content = $page['content'];
            $content = $parsedown->text($content);
            $index = 0;
            $styleIndex = 0;
            $styles = array();

            if (isset($page['styles']) && isset($page['styles'][$styleIndex])) {
                $styles = $page['styles'][$styleIndex];
            }
            $breaks = explode("<hr />", $content);

            if (isset($page['horizontal'])) {
                $type = 'slide';
                echo '<div data-name="' . $title . '" data-anchor="' . $page['slug'] . '" class="section">';
            } else {
                $type = 'section';
            }
            foreach ($breaks as $break) {
                if ($index == 0) {
                    $id = $page['slug'];
                } else {
                    $id = $index;
                }
                if ($this->config['shortcodes']) {
                    $shortcodes = $this->interpretShortcodes($break);
                    $break = $shortcodes['content'];
                    $shortcodeStyles = $shortcodes['styles'];
                    // $styles = array_merge($styles, $shortcodeStyles);
                }
                if (!empty($shortcodeStyles)) {
                    echo '<div data-name="' . $title . '" data-anchor="' . $id . '" class="' . $type . '" style="' . $this->applyStyles($shortcodeStyles) . '">';
                } else {
                    if (isset($page['styles']) && isset($page['styles'][$styleIndex])) {
                        echo '<div data-name="' . $title . '" data-anchor="' . $id . '" class="' . $type . '" style="' . $this->applyStyles($styles) . '">';
                    } else {
                        echo '<div data-name="' . $title . '" data-anchor="' . $id . '" class="' . $type . '">';
                    }
                }
                echo $break;
                if (isset($page['inject_footer'])) {
                    echo $page['inject_footer'];
                }
                echo '</div>';
                if (isset($page['styles']) && count($page['styles']) == $styleIndex+1) {
                    $styleIndex = 0;
                } else {
                    $styleIndex++;
                }
                $index++;
            }
            if (isset($page['horizontal'])) {
                echo '</div>';
            }
            $return .= ob_get_contents();
            ob_end_clean();
            if (isset($page['children'])) {
                $return .= $this->buildContent($page['children']);
            }
        }
        return $return;
    }

    public function interpretShortcodes($content)
    {
        $styles = array();
        $re = '~((?:\[\s*(?<name>[a-zA-Z0-9-_]+)\s*(?:\=\s*(?<bbCode>\"(?:[^\"\\\\]*(?:\\\\.[^\"\\\\]*)*)\"|((?:(?!=\s*|\]|\/\])[^\s])+)))?\s*(?<parameters>(?:\s*(?:\w+(?:\s*\=\s*\"(?:[^\"\\\\]*(?:\\\\.[^\"\\\\]*)*)\"|\s*\=\s*((?:(?!=\s*|\]|\/\])[^\s])+)|(?=\s|\]|\/\s*\]|$))))*)\s*(?:\](?<content>.*?)\[\s*(?<markerContent>\/)\s*(\k<name>)\s*\]|\]|(?<marker>\/)\s*\])))~u';
        preg_match_all($re, $content, $matches, PREG_SET_ORDER, 0);
        if (!empty($matches)) {
            foreach ($matches as $match) {
                $styles[$match['name']] = $match['bbCode'];
                $content = str_replace($match[0], '', $content);
            }
        }
        return ['content' => $content, 'styles' => $styles];
    }

    /**
     * Generate menu with anchors and titles from pages
     * @param array $tree Page-structure with children
     * @return array Slide-anchors with titles
     */
    public function buildMenu($tree)
    {
        $items = array();
        foreach ($tree as $key => $value) {
            if (is_array($value['menu'])) {
                $items[$value['menu']['anchor']] = $value['menu']['title'];
            }
            if (isset($value['children'])) {
                $items[] = $this->buildMenu($value['children']);
            }
        }
        return $items;
    }

    /**
     * Format styles for critical-path inlining
     * @return string CSS-styles
     */
    public function applyStyles($styles)
    {
        if (empty($styles)) {
            return null;
        }
        $return = '';
        foreach ($styles as $key => $value) {
            /* If background is defined, and color is not, try to find a suitable contrast */
            if (!array_key_exists('color', $styles) && array_key_exists('background', $styles)) {
                if (isset($config['color_function'])) {
                    if ($config['color_function'] == '50') {
                        $color = $this->getContrast50($styles['background']);
                    } elseif ($config['color_function'] == 'YIQ') {
                        $color = $this->getContrastYIQ($styles['background']);
                    }
                } else {
                    $color = $this->getContrast50($styles['background']);
                }
                $return .= 'color: ' . $color . ';';
            }
            $return .= $key . ': ' . $value . ';';
        }
        return $return;
    }

    /**
     * Find contrasting color from 50%-equation
     * @param string $hexcolor Hexadecimal color-value
     * @return string black|white
     * @see https://24ways.org/2010/calculating-color-contrast
     */
    public function getContrast50($hexcolor)
    {
        return (hexdec($hexcolor) > 0xffffff/2) ? 'black':'white';
    }

    /**
     * Find contrasting color from YIQ-equation
     * @param string $hexcolor Hexadecimal color-value
     * @return string black|white
     * @see https://24ways.org/2010/calculating-color-contrast
     */
    public function getContrastYIQ($hexcolor)
    {
        $r = hexdec(substr($hexcolor, 0, 2));
        $g = hexdec(substr($hexcolor, 2, 4));
        $b = hexdec(substr($hexcolor, 4, 6));
        $yiq = (($r*299)+($g*587)+($b*114))/1000;
        return ($yiq >= 128) ? 'black' : 'white';
    }

    /**
     * Flatten a multidimensional array to one dimension, optionally preserving keys
     * @param array $array Array to flatten
     * @param integer $preserveKeys 0 (default) to not preserve keys, 1 to preserve string keys only, 2 to preserve all keys
     * @param array &$out Internal parameter for recursion
     * @return array Flattened array
     * @see https://stackoverflow.com/a/7256477/603387
     */
    public function flattenArray($array, $preserveKeys = 0, &$out = array())
    {
        foreach ($array as $key => $child) {
            if (is_array($child)) {
                $out = $this->flattenArray($child, $preserveKeys, $out);
            } elseif ($preserveKeys + is_string($key) > 1) {
                $out[$key] = $child;
            } else {
                $out[] = $child;
            }
        }
        return $out;
    }

    /**
     * Insert string within string
     * @param string $str Original string
     * @param string $insert String to insert
     * @param int $index Position to insert to
     * @return string Original string with new string inserted
     * @see https://stackoverflow.com/a/30820401/603387
     */
    public function stringInsert($str, $insert, $index)
    {
        $str = substr($str, 0, $index) . $insert . substr($str, $index);
        return $str;
    }
}
