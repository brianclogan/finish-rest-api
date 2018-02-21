
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


#### Development

If you are helping with development, you can add `FINSIH_REST_API_BASIC_AUTH` to the `wp-config.php` file using `define()`, and get basic authentication enabled on the site.

Just add `define('FINSIH_REST_API_BASIC_AUTH', true);` to the `wp-config.php` file.


___

## Version 1
Files for Version 1 are in `/routes/v1/`.

#### Plugins

File: `class-wp-rest-plugins-controller.php`

##### Parameters
| Parameter | Usage | Example |
|--|--|--|
| `slug` | Used to look up the plugin. Can be either the full file path, or the slug. | `akismet` or `akismet/akismet.php` |

##### Routes

| Endpoint | Method | Function | Result |
|--|--|--|-|
| `api/v1/plugins` | `GET` | `get_plugins()` | `WP_REST_Response\|WP_Error` |
| `api/v1/plugins` | `POST` | `install_plugins()` | `WP_REST_Response\|WP_Error` |
| `api/v1/plugins/{slug}` | `GET` | `get_plugin()` | `WP_REST_Response\|WP_Error` |
| `api/v1/plugins/activate` | `POST` | `activate_plugin()` | `WP_REST_Response\|WP_Error` |
| `api/v1/plugins/deactivate` | `POST` | `deactivate_plugin()` | `WP_REST_Response\|WP_Error` |
| `api/v1/plugins/delete` | `DELETE` | `delete_plugin()` | `WP_REST_Response\|WP_Error` |


____


#### Installation
1. Upload `finish-rest-api.zip` through the Add Plugin uploader
2. Activate the plugin through the 'Plugins' menu in WordPress

##### Change Log

= 1.0 =
* Initial Release
