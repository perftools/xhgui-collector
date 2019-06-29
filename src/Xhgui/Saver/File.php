<?php

/**
 * File saving handler
 */
class Xhgui_Saver_File implements Xhgui_Saver_Interface
{
    /**
     * @var string
     */
    private $file;

    /**
     * @var bool
     */
    private $separateMeta;

    /**
     * Xhgui_Saver_File constructor.
     * @param $file
     * @param bool $separateMeta
     */
    public function __construct($file, $separateMeta = false)
    {
        $this->file             = $file;
        $this->separateMeta     = $separateMeta;
    }

    /**
     * @param array $data
     * @return bool|int
     */
    public function save(array $data)
    {
        if ($this->separateMeta) {
            $profiles           = Xhgui_Util::getDataForStorage($data['profile'], true);

            $meta               = $data['meta'];

            // store summary in separate meta file to speed up aggregation
            $meta['summary']    = $data['profile']['main()'];
            $meta               = Xhgui_Util::getDataForStorage($meta, false);

            file_put_contents($this->file.'.meta',$meta.PHP_EOL, FILE_APPEND);

            return file_put_contents($this->file,$profiles.PHP_EOL, FILE_APPEND);
        }

        $json = Xhgui_Util::getDataForStorage($data);
        return file_put_contents($this->file, $json.PHP_EOL, FILE_APPEND);
    }

    /**
     * Get filename to use to store data
     * @param string $dir
     * @return string
     */
    public static function getFilename($dir = '.') {

        $fileNamePattern = '';

        if (empty($_SERVER['REQUEST_URI'])) {
            if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
                try {
                    $fileNamePattern = dirname($dir) .
                        '/cache/xhgui.data.' .
                        microtime(true) .
                        bin2hex(random_bytes(5));
                } catch (Exception $e) {
                }
            }

            if (empty($fileNamePattern) &&
                function_exists('openssl_random_pseudo_bytes') &&
                $b = openssl_random_pseudo_bytes(5, $strong)
            ) {
                $fileNamePattern = dirname($dir) .
                    '/cache/xhgui.data.' .
                    microtime(true).
                    bin2hex($b);
            }

            if (empty($fileNamePattern)) {
                $fileNamePattern = dirname($dir) .
                    '/cache/xhgui.data.' .
                    microtime(true).
                    getmypid().
                    uniqid('last_resort_unique_string', true);
            }
        } else {
            $fileNamePattern = dirname($dir) .
                '/cache/xhgui.data.' .
                microtime(true).
                substr(md5($_SERVER['REQUEST_URI']), 0, 10);
        }

        return $fileNamePattern;
    }
}
