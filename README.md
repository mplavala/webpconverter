WebP Converter
==============

Evolution CMS plugin and snippet to convert all images on the page to WebP format.

The plugin works out of the box and converts all JPEG and PNG images to the WebP format. The plugin looks for images in `<img src="...">`, `<video poster="...">`, and `<source srcset="...">`.

The converted images are saved in `/assets/cache/webp` folder. The plugin checks if the image is not outdated, but removing the cache every now and then is recommended.

Usage
-----
There are two components: plugin that takes care of standard images and a snippet to handle images displayed via CSS.

### Plugin
The plugin WebP Converter works out of the box. Just install it and you are good to go.

### Snippet
The snippet webpBackground is to be used if you set background image via `style="background-image: url(...)"`, or in other similar situations.

Simply use:
```
[!webpBackground? &src='/path/to/image' !]
```
Note that the plugin has to be uncached, i.e. you have to use `[! ... !]` and NOT `[[ ... ]]`.

Image paths and cache folder
----------------------------
The plugin uses `/assets/cache/webp` to store WebP versions of images. The plugin changes filenames as follows: from `image.jpg` to `image.jpeg.webp` and `image.png` to `image.png.webp`.

To display the WebP image to a user, the plugin must changes the path inside the HTML sent to the user. To work correctly in all circumstances, the plugin adds a starting `/` to paths. This means that `assets/images/example.jpg` is changed to `/assets/cache/webp/images/example.jpg.webp`.

### Friendly URL alias path with relative image path
If you have _Use Friendly URL alias path_ set to _yes_, i.e., if your URL is structured like `/parent/child.html`, and you use image paths NOT begining with `/`, then:
*   Please consider using image paths starting with `/`. Using image paths without the starting `/` in this scenario is a bad idea.
*   The plugin will use `$_SERVER['REQUEST_URI']` to get the full path and work with that.
*   This can lead to collisions, i.e., to WebP images overwriting each other as images located at `/assets/parent/image.jpg` and `/parent/image.jpg` would have the same path for the WebP image.
