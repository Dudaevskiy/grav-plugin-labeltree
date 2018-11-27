# Grav Label Tree Plugin

The **Label Tree** Plugin is for [Grav CMS](http://github.com/getgrav/grav). It creates a master/detail tree of taxonomies found together on same pages of blog.

It was primarily build for demo purposes and is not available on [Grav CMS](http://github.com/getgrav/grav) for download or installation.

You are however free to use the plugin and feedback is welcome.

## Manual Installation

To install this plugin, just download the zip version of this repository and unzip it under `/your/site/user/plugins`. Then, rename the folder to `labeltree`.

You should now have all the plugin files under

    /your/site/user/plugins/labeltree

## Configuration

Before configuring this plugin, you should copy the `user/plugins/labeltree/labeltree.yaml` to `user/config/plugins/labeltree.yaml` and only edit that copy.

Here is the default configuration and an explanation of available options:

```yaml
enabled: true
```

Note that if you use the admin plugin, a file with your configuration, and named labeltree.yaml will be saved in the `user/config/plugins/` folder once the configuration is saved in the admin.

## Usage

In the header of a page, a hierarchy of labels can be assigned. For example:
```
---
labels:
    - Label-1
        - Label-11
    - Label-2
        - Label-22
---
```
The labels form all pages are combined into one large label tree.

To show the label tree in the sidebar of the blog page, the following could be added to the 'partials/sidebar.html.twig' templates provied by the Quark theme.
```
{% if config.plugins.labeltree.enabled %}
<div class="sidebar-content">
    <h4>{{ 'SIDEBAR.LABELTREE.HEADLINE'|t }}</h4>
    {% include 'partials/labeltree.html.twig' }%}
</div>
{% endif %}
```

To show the pages containing a certain label hierarchy on the main blog page, the following should be changed in 'blog.html.twig' in theme Quark:
```
{% set collection = page.collection() %}
   - replace by -
{% set collection = labeltree.getPages(uri.param('labels')) %}

```

### Options
- taxa: The taxonomies that are used to define the parent/child relationship.
- sorting: The tree can be sorted by page count or label name and both desc and asc.

