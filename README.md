# PHP-Image-Scaler
A cache-friendly PHP script made for responsive websites to automatically scale and return images to the browser.

**NOTE:** Currently still in development and not yet functional.

# Introduction
With responsive design becoming the main method of creating new websites, there is now a call to build every webpage to serve any device with any screen size. As such, HTML5 includes the `<picture>` tag, which allows pages to define multiple sources for a single `<img>` tag depending on CSS media queries. This is great for loading pages faster for smaller devices, but it puts extra work in the hands of the developer putting images on their website. Each image must be scaled multiple times to provide for different device and screen sizes. PHP-Image-Scaler was made to reduce the work needed to provide responsive images on the web.

# How To Use It
Here's a simple HTML example of PHP-Image-Scaler in action.
```html
<picture>
  <source srcset="images/get_image.php?image=test.png&width=480" media="(max-width : 480px)">
  <source srcset="images/get_image.php?image=test.png&width=768" media="(max-width : 768px)">
  <source srcset="images/get_image.php?image=test.png&width=992" media="(max-width : 992px)">
  <source srcset="images/get_image.php?image=test.png&width=1200" media="(max-width : 1200px)">
  <img src="images/get_image.php?image=test.png">
</picture>
```
The above example would create an image with a `src` attribute that depends on the device's max-width. As a developer, this code would be enough to create all four scaled images.

# How It Works
PHP-Image-Scaler depends on a set file structure which can be customized in the script. The default structure requires [get_image.php](images/get_image.php) be located in the same directory as the images to be scaled and served. Once the script is requested, it searches the directory for an image with the filename matching the URL parameter `image`. If found, it then scales the image to the pixel dimensions provided by the URL parameters `width` and `height` and responds with the resulting image.

Images can be scaled proportionally by only specifying one of the two dimension parameters. For example, loading the image `images/get_image.php?image=foo.png&width=100` would result in foo.png being sized to 100px in width and a proportionally scaled height. If neither the width nor height are specified, the original image is returned unscaled.

Another useful feature of PHP-Image-Scaler is that it's **cache-friendly**. Since this script is made for serving images which can be quite large, it is important that it support browser-cacheing in order to speed up page load times. PHP-Image-Scaler does this by serving the necessary HTTP headers required by browser caches to properly validate resources.
NOTE: This feature is planned to be toggleable, but is currently always enabled.

# Want To Contribute?
By all means, help out as much as you want! You can make pull requests directly to the dev branch, or help test the script and open issues. Any assistance will be appreciated, and contributions will be credited.

# Credits
Lone wolf: [Preston Locke](http://prestonlocke.net)
