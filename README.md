# Craft Age Gate

A full-featured age gate plugin for Craft CMS 5 to protect your site from under-aged visitors.

## Features

- **Flexible Display Options**: Choose between a modal overlay or a dedicated redirect page.
- **Customizable Design**: Easily change colors, blur amounts, border radii, and text without writing CSS.
- **Validation Types**: Support for simple Yes/No validation or full birthdate entry.
- **Regional Formats**: Configurable birthdate formats (e.g., DD/MM/YYYY, MM/DD/YYYY) to support international audiences.
- **Template Overrides**: Create your own custom age gate templates while utilizing the plugin's built-in validation logic. 
- **Crawler Friendly**: Automatically allows search engine crawlers to bypass the age gate.

## Requirements

This plugin requires Craft CMS 5.8.0 or later, and PHP 8.2 or later.

## Installation

You can install this plugin from the Plugin Store or with Composer.

#### From the Plugin Store

Go to the Plugin Store in your project’s Control Panel and search for “AgeGate”. Then press “Install”.

#### With Composer

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project.test

# tell Composer to load the plugin
composer require thekitchen-agency/craft-age-gate

# tell Craft to install the plugin
./craft plugin/install craft-age-gate
```

## Configuration

Once installed, navigate to **Settings &rarr; Plugins &rarr; Age Gate** in your Craft Control Panel to configure the plugin. 

You can configure:
- **Display Type**: Modal or Redirect
- **Validation**: Minimum age required, validation type, and birthdate format
- **Appearance**: Colors, text, button labels, and background blur
- **Links**: Redirection URLs for declined users, and policy links

## Advanced Usage & Custom Templates

If you need full control over the markup, Age Gate provides a robust template override system and JavaScript helper functions. 

For detailed instructions on creating custom templates and accessing the available Twig and JavaScript variables, please read the [Custom Age Gate Integration Guide](CUSTOM_AGE_GATE_INTEGRATION.md).

## Support

- **Issues**: [GitHub Issues](https://github.com/thekitchen-agency/TKA-AgeGate/issues)
- **Author**: [TheKitchen Agency](https://thekitchen.agency)
