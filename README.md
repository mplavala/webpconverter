WebP Converter
==============

Evolution CMS plugin and snippet to convert all images on the page to webp format.

The plugin work out of the box and converts all jpeg and png images to the WebP format. The plugin looks for images in `<img src="...">`, `<video poster="...">`, and `<source srcset="...">`.

The converted images are saved in assets/.webp folder to be used again. The plugin checks if the image is not outdated, but removing the cache every now and then is recomended.

## Usage

### Plugin
The plugin WebP Converter works out of the box.

### Snippet
The snippet webpBackground is to be used if you set background image via `style="background-image: url(...)"`, or in other similar situations.

Simply use:
```
[!webpBackground? &src='/path/to/image' !]
```
Note that the plugin has to be uncached, i.e. you have to use `[! ... !]` and NOT `[[ ... ]]`.
