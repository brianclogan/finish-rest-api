
# Finish Rest API

 - Contributors: darkgoldblade
 - Tags: rest, api, finish, plugins, themes, updates, core
 - Requires at least: 4.0.0
 - Tested up to: 4.9.4
 - Requires PHP: 5.6
 - Stable tag: 1.0.0
 - License: GPLv2 or later
 - License URI: http://www.gnu.org/licenses/gpl-2.0.html

## Description

The WordPress REST API is an amazing tool, but it's missing some key features.
It's missing some things though, like Plugin and Theme management, and it also is not standardized with the responses.

That's where we came in, we decided to take what WordPress had, standardize it, and add the things that were missing.

We are following the WP REST files as closely as possible, so moving them into WordPress Core can be easy down the road.

FULL API DOCUMENTATION: http://bit.ly/2BHcgzS
---

#### Development

If you are helping with development, you can add `FINSIH_REST_API_BASIC_AUTH` to the `wp-config.php` file using `define()`, and get basic authentication enabled on the site.

Just add `define('FINSIH_REST_API_BASIC_AUTH', true);` to the `wp-config.php` file.


___

## Version 1
Files for Version 1 are in `/routes/v1/`.

### Plugins

File: `class-wp-rest-plugins-controller.php`

#### Parameters
| Parameter | Usage | Example |
|--|--|--|
| `slug` | Used to look up the plugin. Can be either the full file path, or the slug. | `akismet` or `akismet/akismet.php` |

#### Routes

| Endpoint | Method | Function | Result |
|--|--|--|-|
| `api/v1/plugins` | `GET` | `get_plugins()` | `WP_REST_Response\|WP_Error` |
| `api/v1/plugins` | `POST` | `install_plugins()` | `WP_REST_Response\|WP_Error` |
| `api/v1/plugins/{slug}` | `GET` | `get_plugin()` | `WP_REST_Response\|WP_Error` |
| `api/v1/plugins/activate` | `POST` | `activate_plugin()` | `WP_REST_Response\|WP_Error` |
| `api/v1/plugins/deactivate` | `POST` | `deactivate_plugin()` | `WP_REST_Response\|WP_Error` |
| `api/v1/plugins/delete` | `DELETE` | `delete_plugin()` | `WP_REST_Response\|WP_Error` |

### Themes

File: `class-wp-rest-themes-controller.php`

#### Parameters
| Parameter | Usage | Example |
|--|--|--|
| `theme` | Used to look up the theme. It is the folder the theme is in. | `twentyseventeen` |

#### Routes

| Endpoint | Method | Function | Result |
|--|--|--|-|
| `api/v1/themes` | `GET` | `get_themes()` | `WP_REST_Response\|WP_Error` |
| `api/v1/themes` | `POST` | `install_themes()` | `WP_REST_Response\|WP_Error` |
| `api/v1/themes/{theme}` | `GET` | `get_theme()` | `WP_REST_Response\|WP_Error` |
| `api/v1/themes/switch` | `POST` | `switch_theme()` | `WP_REST_Response\|WP_Error` |
| `api/v1/themes/delete` | `DELETE` | `delete_theme()` | `WP_REST_Response\|WP_Error` |

### Core

File: `class-wp-rest-core-controller.php`

#### Parameters
| Parameter | Usage | Example |
|--|--|--|
|  |  |  |

#### Routes

| Endpoint | Method | Function | Result |
|--|--|--|-|
| `api/v1/core` | `GET` | `get_core()` | `WP_REST_Response\|WP_Error` |
| `api/v1/core/update` | `POST` | `update_core()` | `WP_REST_Response\|WP_Error` |


### Examples

Most responses from the API are structured in a way to make it so you never have to guess.

Request:
```
https://example.com/wp-json/api/v1/plugins
```

Response:
```json
{
    "success": true,
    "total_records": 2,
    "data": [
        {
            "file": "akismet/akismet.php",
            "slug": "akismet",
            "name": "Akismet Anti-Spam",
            "plugin_uri": "https://akismet.com/",
            "version": "4.0.3",
            "description": "Used by millions, Akismet is quite possibly the best way in the world to <strong>protect your blog from spam</strong>. It keeps your site protected even while you sleep. To get started: activate the Akismet plugin and then go to your Akismet Settings page to set up your API key.",
            "author": "Automattic",
            "author_uri": "https://automattic.com/wordpress-plugins/",
            "text_domain": "akismet",
            "domain_path": "",
            "network": false,
            "title": "Akismet Anti-Spam",
            "author_name": "Automattic"
        },
        {
            "file": "finish-rest-api/finish-rest-api.php",
            "slug": "finish-rest-api",
            "name": "Finish Rest API",
            "plugin_uri": "https://twitter.com/darkgoldblade",
            "version": "1.0.0",
            "description": "A plugin that adds the rest of the functionality we want from the REST API.",
            "author": "darkgoldblade",
            "author_uri": "https://twitter.com/darkgoldblade",
            "text_domain": "darkgoldblade",
            "domain_path": "/languages",
            "network": false,
            "title": "Finish Rest API",
            "author_name": "darkgoldblade"
        }
    ]
}
```

Request:
```
https://example.com/wp-json/api/v1/plugins/akismet
```

Response:
```json
{
    "success": true,
    "total_records": 1,
    "data": {
        "file": "akismet/akismet.php",
        "name": "Akismet Anti-Spam",
        "plugin_uri": "https://akismet.com/",
        "version": "4.0.3",
        "description": "Used by millions, Akismet is quite possibly the best way in the world to <strong>protect your blog from spam</strong>. It keeps your site protected even while you sleep. To get started: activate the Akismet plugin and then go to your Akismet Settings page to set up your API key.",
        "author": "Automattic",
        "author_uri": "https://automattic.com/wordpress-plugins/",
        "text_domain": "akismet",
        "domain_path": "",
        "network": false,
        "title": "Akismet Anti-Spam",
        "author_name": "Automattic"
    }
}
```

Any upgrades/updates will also return the same array, but in the `data` object, it will have a `updated` key, with a `bool` as the result.

____


#### Installation
1. Upload `finish-rest-api.zip` through the Add Plugin uploader
2. Activate the plugin through the 'Plugins' menu in WordPress

##### Change Log

= 1.0 =
* Initial Release
