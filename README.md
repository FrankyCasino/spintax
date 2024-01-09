# Spintax WordPress Plugin

## Description
Spintax is a dynamic content generation plugin for WordPress. It enables the creation of varied and unique content using the Spintax syntax, ideal for enhancing SEO and user engagement. This plugin supports nested Spintax, caching for performance optimization, and is compatible with multiple languages.

## Features
- Dynamic Content Creation
- Nested Spintax Support
- Caching Mechanism
- Multilingual Support
- Shortcode Integration
- User-friendly Interface

## Installation
1. Download the plugin archive.
2. Install using the Wordpress interface.
3. Activate 'Spintax' from your Plugins page.

## Usage
To use Spintax, simply wrap your text with curly braces `{}` and separate the options with a vertical bar `|`. Insert this Spintax syntax within the `[spintax][/spintax]` shortcode anywhere on your site.

### Examples:

## Basic Usage
**Description**: Randomly displays one of the provided options.
```html
[spintax]{Option1|Option2|Option3}[/spintax]
```
This will randomly display 'Option1', 'Option2', or 'Option3'.

**Nested Spintax:**
```html
[spintax]{This is {an example|a sample}|Here is {another example|another sample}} of nested Spintax.[/spintax]
```
Nested Spintax allows for more complex variations.

**With Caching:**
```html
[spintax cache="3600"] {Cachable|Content} for one hour. [/spintax]
```
This example caches the output for 3600 seconds (1 hour). To disable caching (for example, during testing), set cache="0".

**Using Permutations**

Introduces elements in a random order, adding variety to the output.
```html
[spintax]{Option1|Option2} combined with [OptionA|OptionB].[/spintax]
```
This shortcode generates various combinations by permuting 'Option1' and 'Option2' with 'OptionA' and 'OptionB'.


### Styling
You can style the output of Spintax like any other text in WordPress using CSS.

### License
This project is licensed under the MIT License - see the LICENSE.md file for details.

### Support
If you encounter any issues or have questions, please file them in the issues section of this repository.
