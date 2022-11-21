<?php
require_once __DIR__ . '/cache.php';

function getConfiguratedFormatImageObjectFromGDImage($gdimage): array
{
    ob_start();
    imagewebp($gdimage);
    $data = ob_get_contents();
    ob_end_clean();
    return array(
        'mime' => 'image/webp',
        'data' => $data,
    );
}

function loadImageObjectAndConvertToConfiguratedFormatImageObject($imgObj): array
{
    $img = imagecreatefromstring($imgObj['data']);
    return getConfiguratedFormatImageObjectFromGDImage($img);
}


function loadImageObjectAndResizeWidthAndConvertToConfiguratedFormatImageObject($imgObj, int $width)
{
    $img = imagecreatefromstring($imgObj['data']);
    if ($width > imagesx($img))
        $img = imagescale($img, $width);
    return getConfiguratedFormatImageObjectFromGDImage($img);
}

function writeImageObjectToResponse($imgObj, $response)
{
    $response->header('Content-Type', $imgObj['mime']);
    $response->body($imgObj['data']);
}

function isImageExistInTransformedStore(string $name): bool
{
    return isFileInTransformedStore($name . '.imgobj');
}

function writeImageInTransformedStoreToResponse(string $name, $response)
{
    writeImageObjectToResponse(unserialize(readFileInTransformedStore($name . '.imgobj')), $response);
}

function writeImageObjectToTransformedStore($imgObj, string $name)
{
    writeFileToTransformedStore($name . '.imgobj', serialize($imgObj));
}
