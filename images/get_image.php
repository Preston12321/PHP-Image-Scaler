<?php

ignore_user_abort(true);
set_time_limit(0);
ob_start();

// TODO: Protect image proportionality better

// Define a few constants for easy editability
$DEFAULT_IMAGE_WIDTH = 0;                           // Dimension will scale proportionally when set to 0
$DEFAULT_IMAGE_HEIGHT = 0;                          // Dimension will scale proportionally when set to 0
$MAX_IMAGE_WIDTH = 1200;                            // Default is 1200px width
$MAX_IMAGE_HEIGHT = 0;                              // Height will scale proportionally when set to 0
$IMAGE_DIRECTORY = dirname(__FILE__);               // Default is the directory of this script
$SCALED_IMAGE_DIRECTORY = $IMAGE_DIRECTORY . "get_image";   // Directory for scaled images
$FAILURE_IMAGE_NAME = "__image-get-failed__.png";   // Image to send if intended image not found
$WIDTH_RELATIVE_FOLDER = "width";
$HEIGHT_RELATIVE_FOLDER = "height";
$WIDTH_HEIGHT_RELATIVE_FOLDER = "width_height";
$IMAGE_RESIZE_FILTER = Imagick::FILTER_LANCZOS;
$ENABLE_UPSCALING = false;  // When disabled, images won't scale past the max or size of original
$IMAGE_NAME_URL_PARAMETER = "image";
$IMAGE_WIDTH_URL_PARAMETER = "width";
$IMAGE_HEIGHT_URL_PARAMETER = "height";

// Define variables used to locate and scale image
$image_name = "";
$image_width = $DEFAULT_IMAGE_WIDTH;
$image_height = $DEFAULT_IMAGE_HEIGHT;

// Make sure script is passed an image name to locate
if (!isset($_GET[$IMAGE_NAME_URL_PARAMETER])) {
    fail();
}

// Get image name passed in request parameters
$image_name = $_GET[$IMAGE_NAME_URL_PARAMETER];

// Get image width if passed in request parameters
if (isset($_GET[$IMAGE_WIDTH_URL_PARAMETER]) && is_numeric($_GET[$IMAGE_WIDTH_URL_PARAMETER])) {
    $image_width = (int) $_GET[$IMAGE_WIDTH_URL_PARAMETER];
}

// Get image height if passed in request parameters
if (isset($_GET[$IMAGE_HEIGHT_URL_PARAMETER]) && is_numeric($_GET[$IMAGE_HEIGHT_URL_PARAMETER])) {
    $image_height = (int) $_GET[$IMAGE_HEIGHT_URL_PARAMETER];
}

// Make sure script is passed at least one image dimension to scale
if ($image_width == 0 && $image_height == 0) {
    fail($image_name);
}

// Make sure image file exists on server
if (!file_exists($IMAGE_DIRECTORY . $image_name)) {
    fail();
}

$valid_cache = true;

if (!$ENABLE_UPSCALING) {
    // If requesting a larger image than max, use max size instead
    if ($image_width > $MAX_IMAGE_WIDTH) {
        $image_width = $MAX_IMAGE_WIDTH;
        $valid_cache = false;
    }
    // If requesting a larger image than max, use max size instead
    if ($image_height > $MAX_IMAGE_HEIGHT) {
        $image_height = $MAX_IMAGE_HEIGHT;
        $valid_cache = false;
    }
}

// Load image
$image = new Imagick($IMAGE_DIRECTORY . $image_name);

if (!$ENABLE_UPSCALING) {
    // If requesting a larger image than original, use original size instead
    if ($image_width > $image->getImageWidth()) {
        $image_width = $image->getImageWidth();
        $valid_cache = false;
    }
    // If requesting a larger image than original, use original size instead
    if ($image_height > $image->getImageHeight()) {
        $image_height = $image->getImageHeight();
        $valid_cache = false;
    }
}

$folder = "";
$dimension = "";

if ($image_width > 0 && $image_height == 0) {
    // USE WIDTH FOLDER
    $folder = $WIDTH_RELATIVE_FOLDER;
    $dimension = (string) $image_width;
}
else if ($image_height > 0 && $image_width = 0) {
    // USE HEIGHT FOLDER
    $folder = $HEIGHT_RELATIVE_FOLDER;
    $dimension = (string) $image_height;
}
else {
    // USE WIDTH_HEIGHT FOLDER
    $folder = $WIDTH_HEIGHT_RELATIVE_FOLDER;
    $dimension = (string) $image_width . "_" . (string) $image_height;
}

$path = pathinfo($image_name);
$location = $SCALED_IMAGE_DIRECTORY . "/" . $folder . "/" . $path['filename'] . "/" . $dimension . $path['extension'];

$headers = apache_request_headers();

// If client's cache is still valid, send 304 response
if ($valid_cache && isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) == filemtime($location))) {
    header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($location)).' GMT', true, 304);
    send_response();
    exit();
}

if (file_exists($location)) {
    $img = new Imagick($location);
    echo $img->getImageBlob();
    header("Content-type: image/{$img->getImageFormat()}");
    header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($location)).' GMT', true, 200);
    send_response();
    exit();
}

// Resize image
$image->resizeImage($image_width, $image_height, $IMAGE_RESIZE_FILTER, 1);

// Send image data as response
echo $image->getImageBlob();
header("Content-type: image/{$image->getImageFormat()}");
send_response();

// Image did not previously exist, save it for later
$img->writeImage($location);

function fail($intended_image_name = null) {

    // If there was no image name to locate, send an error
    if ($intended_image_name == null) {
        // If default failure image exists, send it as the image
        if (file_exists($IMAGE_DIRECTORY . "/" . $FAILURE_IMAGE_NAME)) {
            $img = new Imagick($IMAGE_DIRECTORY . "/" . $FAILURE_IMAGE_NAME);
            echo $img->getImageBlob();
            header("Content-type: image/{$img->getImageFormat()}");
        }
        else {
            // Send file not found HTTP error
            http_response_code(404);
        }
    }
    else {
        // Send the intended image as its original version, with no scaling
        $img = new Imagick($IMAGE_DIRECTORY . "/" . $intended_image_name);
        echo $img->getImageBlob();
        header("Content-type: image/{$img->getImageFormat()}");
        header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($IMAGE_DIRECTORY . "/" . $intended_image_name)).' GMT', true, 200);
    }
    send_response();
    exit;

}

function send_response() {

    header("Content-Encoding: none");
    header('Connection: close');
    header('Content-Length: ' . ob_get_length());
    ob_end_flush();
    ob_flush();
    flush();

}

?>