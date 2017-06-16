---
title: Configuration
horizontal: true
styles:
  - background: "#a12e27"
    color: white
  - background: "#643325"
  - background: "#a75830"
  - background: "#bb7b3b"
  - background: "#ce914e"
twig_first: true
process:
  twig: true
fullpage:
  shortcodes: false
---

## Configuration

---

As with other plugins, the main configuration is changed by copying `user/plugins/fullpage/fullpage.yaml` into `user/config/plugins/fullpage.yaml` and editing it. Configuration can of course also be edited via the plugin's page in the Admin-plugin.

---

### Plugin configuration

In addition to options for the fullPage.js-library, you can define the order of the of how the pages are rendered through `order.by` and `order.dir`, and whether to use the plugin's built-in CSS and JS with `builtin_css` and `builtin_js`. Further, you can define inline-styles for each section or slide through `styles`. This last property is a list of CSS-properties that will be applied to the page, or pages if using horizontal rules, in the order they appear.

---

### Page-specific configuration

Any configuration set in `fullpage.yaml` can be overridden through a page's FrontMatter, like this:

```
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

---


### Example styles

```
styles:
  - background: "#F0A202"
  - background: "#F18805"
  - background: "#D95D39"
  - background: "#202C59"
  - background: "#581F18"
```

Styles are defined as `property: value` and processed by the plugin. If the amount of pages exceed the amount of styles, they will be reused in the order they are defined. If the `background`-property is defined, but `color` is not, the plugin tries to estimate a suitable text-color to apply. The equations available to estimate this color is either `50` or `YIQ`, set by `color_function`.

---

You can of course also style the plugin using your theme's /css/custom.css-file, by targeting the `#fullpage`-selector which wraps around all of the plugin's content. This behavior can be enabled or disabled with the `theme_css`-setting. All pages have a `data-anchor`-attribute set on their sections, which can be utilized by CSS like this:

```css
#fullpage [data-anchor="constructing-pages"] {
  background: red;
}
```

---

### Using section- or slide-specific styles

If configured with `shortcodes: true` any section or slide can use shortcodes to declare specific styles. These take the format of `[property=value]` and are defined in multiples, eg:

```
[background=#195b69]
[color=cyan]
```

If the shortcode is found and applied, it is stripped from the further evaluated content. This method uses regular expressions for speed, and takes precedence over plugin- or page-defined `styles`.

---

### Injecting Twig

Using the `inject_footer`-setting you can append a Twig-template to each section globally, or a specific page's section. For example, `inject_footer: "partials/inject.html.twig"` will render the theme's `partials/inject.html.twig`-template and append it to the section(s). If the element was constructed like this: `<div class="inject">Injected</div>`, you could style it like this:

```css
#fullpage .inject {
  display: block;
  position: absolute;
  bottom: 2em;
}
```

You can also arbitrarily execute Twig within a page's Markdown by enabling it in the FrontMatter with:

```yaml
twig_first: true
process:
  twig: true
```

{% verbatim %}
For example, `<p>{{ site.author.name }}</p>` will render the name of the author defined in site.yaml.
{% endverbatim %}

---

### Creating a menu

The plugin makes a `fullpage_menu`-variable available through Twig on pages which use the fullscreen-template, which can be used to construct an overall menu of pages. It is an array with anchors and titles for each page, and a list of them with links to sections can be constructed like this:

{% verbatim %}
```
<ul id="menu" class="menu">
{% for anchor, title in fullpage_menu %}
  <li>
    <a href="#{{ anchor }}">{{ title }}</a>
  </li>
{% endfor %}
</ul>
```
{% endverbatim %}

---

### Configuring fullPage.js

All options available to the fullPage.js-library can be configured, see its [documentation for available options](https://github.com/alvarotrigo/fullPage.js#options).