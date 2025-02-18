<?

namespace app\core;

class ImageProcessing
{
    public static $path = '';
    public static function imageToWebp($source, $output, $from = 'png')
    {
        $func = 'imagecreatefrom' . ($from !== 'jpg' ? $from : 'jpeg');
        $image = $func($source);
        if ($from === 'png') {
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
        }
        imagewebp($image, $output);
        imagedestroy($image);
    }
    public static function getAdditionalImage($source, $format, $type = 'webp')
    {
        $output = '';
        $pathOffset = strlen($_SERVER['DOCUMENT_ROOT']) + 1;
        if ($type === 'webp') {
            $webp = str_replace(".$format", '.webp', $source);
            if (!file_exists($webp))
                self::imageToWebp($source, $webp, $format);
            $output .= PHP_EOL . '<source srcset="' . self::$path . substr($webp, $pathOffset) . '" type="' . mime_content_type($webp) . '">';
        } elseif ($type === 'mini') {
            $mini = substr($source, 0, strrpos($source, '.')) . '-mini.' . $format;

            if (!file_exists($mini)) return '';
            if ($format !== 'webp') {
                $webp = str_replace(".$format", '.webp', $mini);
                if (!file_exists($webp))
                    self::imageToWebp($mini, $webp, $format);
                $output .= '<source srcset="' . self::$path . substr($webp, $pathOffset) . '" media="(max-width: 576px)" type="' . mime_content_type($webp) . '">';
            }
            $output .= '<source srcset="' . self::$path . substr($mini, $pathOffset) . '" media="(max-width: 576px)" type="' . mime_content_type($mini) . '">';
        }
        return $output;
    }
    public static function inputImage($source, $options = [])
    {
        $realPathToSource = "{$_SERVER['DOCUMENT_ROOT']}/$source";
        if (!file_exists($realPathToSource)) return false;

        $output = '<picture>';

        if (empty(self::$path)) {
            self::$path = Tech::getRequestProtocol() . "://{$_SERVER['SERVER_NAME']}";
        }

        $source = self::$path . $source;

        $format = str_replace('image/', '', mime_content_type($realPathToSource));

        $output .= self::getAdditionalImage($realPathToSource, $format, 'mini');

        if ($format !== 'webp')
            $output .= self::getAdditionalImage($realPathToSource, $format, 'webp');

        $attrs = '';
        foreach ($options as $attr => $value) {
            if ($attr !== 'title') {
                $attrs .= "$attr='$value' ";
            } else {
                $attrs .= "$attr='$value' alt='$value' ";
            }
        }
        if (empty($attrs) || (!isset($options['title']) && !isset($options['alt']))) {
            $attrs .= 'alt=""';
        }

        return str_ireplace($_SERVER['DOCUMENT_ROOT'], '.', $output . PHP_EOL .
            "<img $attrs src='$source' loading='lazy'>
		 </picture>");
    }
    public static function saveBase64Image($base64Image, $filename, $path)
    {
        try {
            $uri = substr($base64Image, strpos($base64Image, ",") + 1);
            preg_match('/data:image\/([^;]+)/', $base64Image, $matches);
            $extension = $matches[1];

            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            $filename .= ".$extension";

            $data = base64_decode($uri);

            $imageSize = getimagesizefromstring($data);

            if ($imageSize[0] > 720) {

                $image = imagecreatefromstring($data);
                $image = imagescale($image, 720);
                ob_start();
                imagejpeg($image);
                $data = ob_get_clean();
                // $data = ob_get_contents();
                // ob_end_clean();
            }
            file_put_contents("$path/$filename", $data);
            return ['filename' => $filename, 'data' => $data];
        } catch (\Throwable $th) {
            error_log($th->__toString());
            return false;
        }
    }
}
