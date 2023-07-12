# WooCommerce Image Upload and Crop Plugin

The **WooCommerce Image Upload and Crop** plugin allows users to upload and crop images when ordering a product on a WooCommerce-powered website.

## Table of Contents
- [Installation](#installation)
- [Usage](#usage)
  - [Frontend Integration](#frontend-integration)
  - [Image Upload and Order Processing](#image-upload-and-order-processing)
  - [Email Notifications](#email-notifications)
- [Configuration](#configuration)
  - [Product Meta Box](#product-meta-box)
  - [Croppie Library](#croppie-library)
- [Development](#development)
- [License](#license)
- [Author](#author)
- [Support](#support)

## Installation

1. Download the plugin ZIP file.
2. Log in to your WordPress admin dashboard.
3. Navigate to **Plugins > Add New**.
4. Click on the **Upload Plugin** button at the top of the page.
5. Choose the plugin ZIP file you downloaded and click **Install Now**.
6. After the plugin is installed, click **Activate**.

## Usage

After activating the plugin, you can enable image upload and cropping for specific products by following these steps:

1. Edit a product and scroll down to the **Image Upload Options** meta box.
2. Check the **Allow Image Upload** checkbox to enable image upload for the product.
3. Select the desired crop type from the **Crop Type** dropdown (options: Circle or Square).
4. Update or publish the product.

## Frontend Integration

The plugin integrates with the WooCommerce product page on the frontend. When the **Allow Image Upload** option is enabled for a product, users will see an image upload section below the **Add to Cart** button.

Users can click on the **Choose File** button to select an image file from their device. After selecting the file, the image will be displayed in a container. Users can then use the croppie.js library to crop the image based on the selected crop type (Circle or Square). Once the image is cropped, users can click the **Crop Image** button to save the cropped image.

## Image Upload and Order Processing

When a user uploads and crops an image, the plugin saves the image file to the WordPress uploads directory. The image file path is stored in the user's session.

When the user adds the product to the cart and proceeds to checkout, the image file path is attached to the corresponding order item as meta data. This allows the image to be associated with the order.

## Email Notifications

If an image is attached to an order item, the image URL is included in the order email notifications. The image is displayed as part of the order details, allowing both customers and administrators to see the uploaded image.

## Configuration

The plugin provides configuration options through the WordPress admin dashboard.

### Product Meta Box

The **Image Upload Options** meta box appears on the product edit screen. It allows you to configure the following options for each product:

- **Allow Image Upload**: Check this option to enable image upload and cropping for the product.
- **Crop Type**: Select the desired crop type for the uploaded images (options: Circle or Square).

### Croppie Library

The plugin uses the [Croppie library](https://foliotek.github.io/Croppie/) for image cropping. The library is loaded from the following CDN:

- CSS: [https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.css](https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.css)
- JavaScript: [https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.js](https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.js)

## Development

If you want to modify or extend the plugin's functionality, you can follow these steps:

1. Clone the plugin repository or create a new plugin directory.
2. Copy the plugin files into your development environment.
3. Modify the PHP code as needed.
4. Modify the JavaScript code in the `script.js` file.
5. Use your preferred method to build and package the plugin for distribution.

## License

This plugin is released under the GPL-2.0 License. You are free to use, modify, and distribute this plugin in accordance with the license terms.

## Author

This plugin was developed by D Kandekore.

## Support

For support or assistance with this plugin, please contact the author or refer to the plugin's official documentation.
