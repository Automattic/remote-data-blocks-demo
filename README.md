#Remote Data Blocks Demo

This repo use [WordPress Playground](https://playground.wordpress.net/) to provide interactive examples of Remote Data Blocks

[![Launch in WordPress Playground](https://img.shields.io/badge/Launch%20in%20WordPress%20Playground-DA9A45?style=for-the-badge&logo=wordpress)](https://wordpress-playground.atomicsites.blog/?blueprint-url=https://raw.githubusercontent.com/smithjw1/remote-data-blocks-demo/trunk/blueprint-with-jetpack.json)

**Note: WordPress Playground sometimes retains state from previous sessions. Opening a new incognito window may be necessary to get a fresh start.**

Remote Data Blocks is a WordPress plugin that makes it easy to combine content and remote data in the block editor. Easily register blocks that load data from Airtable, Google Sheets, Shopify, GitHub, or any other API. [Read more about well-supported use cases](https://github.com/Automattic/remote-data-blocks).

A [simple plugin](./api-monster.php) is included to demonstrate the use of remote data blocks. It also loads information about monsters from the open-source rules of Dungeons and Dragons.

Without this plugin, significant custom development would be necessary to retrieve, cache, and output this third-party information.

## Testing with WordPress playground
Because WordPress playground runs completely in the browser it is easy to create links to test this package with alternative WordPress configurations.

[With PHP 8.4](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/smithjw1/remote-data-blocks-demo/trunk/blueprint.json&php=8.4)

[With the nightly WP build](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/smithjw1/remote-data-blocks-demo/trunk/blueprint.json&wp=nightly)
