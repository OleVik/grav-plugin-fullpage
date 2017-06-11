# Fullpage Plugin

The **Fullpage** Plugin is for [Grav CMS](http://github.com/getgrav/grav). The [Fullpage](https://github.com/OleVik/grav-plugin-fullpage)-plugin provides a simple way of creating fullscreen slideshows that can be navigated vertically and horizontally, using the [fullPage.js](https://github.com/alvarotrigo/fullPage.js)-library.

A [demo is available](https://olevik.me/plugins/fullpage/book/), as are docs presented [by the plugin](https://olevik.me/plugins/fullpage/).

## Installation

Installing the Fullpage-plugin can be done in one of two ways. The GPM (Grav Package Manager) installation method enables you to quickly and easily install the plugin with a simple terminal command, while the manual method enables you to do so via a zip file.

### GPM Installation (Preferred)

The simplest way to install this plugin is via the [Grav Package Manager (GPM)](http://learn.getgrav.org/advanced/grav-gpm) through your system's terminal (also called the command line). From the root of your Grav install type:

    bin/gpm install fullpage

This will install the Fullpage-plugin into your `/user/plugins` directory within Grav. Its files can be found under `/your/site/grav/user/plugins/fullpage`.

### Manual Installation

To install this plugin, just download the zip version of this repository and unzip it under `/your/site/grav/user/plugins`. Then rename the folder to `fullpage`. You can find these files on [GitHub](https://github.com/ole-vik/grav-plugin-fullpage) or via [GetGrav.org](http://getgrav.org/downloads/plugins#extras).

You should now have all the plugin files under

    /your/site/grav/user/plugins/fullpage
	
> NOTE: This plugin is a modular component for Grav which requires [Grav](http://github.com/getgrav/grav) and the [Error](https://github.com/getgrav/grav-plugin-error) and [Problems](https://github.com/getgrav/grav-plugin-problems) to operate.

## Configuration

Before configuring this plugin, you should copy the `user/plugins/fullpage/fullpage.yaml` to `user/config/plugins/fullpage.yaml` and only edit that copy.

Here is the default configuration and an explanation of available options:

```yaml
enabled: true
order:
  by: folder
  dir: asc
builtin_css: true
builtin_js: true
styles:
  - background: "#93c0d3"
  - background: "#6f977d"
  - background: "#598495"
  - background: "#5e6168"
  - background: "#213533"
color_function: "50"
change_titles: true
options:
  ...
```

All options available to the fullPage.js-library can be configured through `options`, see its [documentation for available options](https://github.com/alvarotrigo/fullPage.js#options). For example:

```yaml
options:
  navigation: false
  navigationPosition: 'right'
  navigationTooltips: []
```

In addition to options for the fullPage.js-library, you can define the order of the of how the pages are rendered through `order.by` and `order.dir`, and whether to use the plugin's built-in CSS and JS with `builtin_css` and `builtin_js`. Further, you can define inline-styles for each section or slide through `styles`. This last property is a list of CSS-properties that will be applied to the page, or pages if using horizontal rules, in the order they appear. If `change_titles` is enabled, the plugin will use the titles of pages to override the title of the website upon navigation.

### Page-specific configuration

Any configuration set in `fullpage.yaml` can be overridden through a page's FrontMatter, like this:

```yaml
---
title: Alice’s Adventures in Wonderland
fullpage:
  order:
    by: date
    dir: desc
  options:
    navigation: true
---
```

### Styling

Styles are defined as `property: value` and processed by the plugin. If the amount of pages exceed the amount of styles, they will be reused in the order they are defined. If the `background`-property is defined, but `color` is not, the plugin tries to estimate a suitable text-color to apply. The equations available to estimate this color is either `50` or `YIQ`, set by `color_function`.

You can of course also style the plugin using your theme's CSS-files, by targeting the `.fullpage`-selector which wraps around all of the plugin's content.

### Creating a menu

The plugin makes a `fullpage_menu`-variable available through Twig on pages which use the fullscreen-template, which can be used to construct an overall menu of pages. It is an array with anchors and titles for each page, and a list of them with links to sections can be constructed like this:

```
<ul id="menu" class="menu">
{% for anchor, title in fullpage_menu %}
  <li>
    <a href="#{{ anchor }}">{{ title }}</a>
  </li>
{% endfor %}
</ul>
```

## Usage

The page-structure used in Fullpage is essentially the same as normally in Grav, with a few notable exceptions: Any horizontal rule, `---` in Markdown and `<hr />` in HTML, is treated as a _thematic break_, as it is defined in HTML5. This means that if you separate content with a horizontal rule within a page, the plugin treats this as a new section. This is equivalent to using child-pages for new sections, which work recursively: You can have as many pages below the root-page as you want, each of them will be treated as a section. Further, these methods can be mixed by some pages using horizontal rules, and some not.

### Nomenclature

With fullPage.js there is a distinction between sections and slides. Sections are single fullscreen pages listed vertically, and slides are single fullscreen pages listed horizontally. That is, if a page contains slides these are navigated horizontally rather than vertically. In the plugin, you define this by setting `horizontal: true` in the page's FrontMatter, which treats all content within it as slides.

### Example structure:

```
/user/pages/book
├── fullpage.md
├── 01.down-the-rabbit-hole
│   └── default.md
├── 02.advice-from-a-caterpillar
│   └── default.md
├── 03.were-all-mad-here
│   └── default.md
├── 04.a-mad-tea-party
│   └── default.md
├── 05.the-queens-crocquet-ground
│   └── default.md
├── 06.postscript
└───└── default.md
```

As seen in this example structure, only the initial page uses the `fullpage.html.twig`-template. The template used for child-pages is irrelevant, as only the content of these pages are processed. The plugin defines the `fullpage.html.twig`-template, but you can override it through your theme.

## Credits

- Grav [Fullpage](https://github.com/OleVik/grav-plugin-fullpage)-plugin is written by [Ole Vik](https://github.com/OleVik)
- jQuery [fullPage.js](https://github.com/alvarotrigo/fullPage.js)-plugin is created by [@imac](https://twitter.com/imac2)
- Both are MIT-licensed

