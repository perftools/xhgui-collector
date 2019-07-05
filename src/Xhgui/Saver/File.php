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
     * @var string
     */
    private $path;

    /**
     * @var bool
     */
    private $separateMeta;

    /**
     * Xhgui_Saver_File constructor.
     * @param string $path
     * @param string $file or null for default
     * @param bool $separateMeta
     */
    public function __construct($path, $file, $separateMeta = false)
    {
        $this->path             = rtrim($path, '/\\').DIRECTORY_SEPARATOR;
        $this->file             = !empty($file) ? $file : self::getFilename();
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

            file_put_contents($this->path.$this->file.'.meta',$meta.PHP_EOL, FILE_APPEND);

            return file_put_contents($this->path.$this->file,$profiles.PHP_EOL, FILE_APPEND);
        }

        $json = Xhgui_Util::getDataForStorage($data);
        return file_put_contents($this->path.$this->file, $json.PHP_EOL, FILE_APPEND);
    }

    /**
     * Get filename to use to store data
     * @param string $dir
     * @return string
     */
    public static function getFilename() {

        $fileNamePattern = '';

        $prefix = 'xhgui.data.'.microtime(true);
        
        if (empty($_SERVER['REQUEST_URI'])) {
            if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
                try {
                    $fileNamePattern = $prefix.bin2hex(random_bytes(5));
                } catch (Exception $e) {
                }
            }

            if (empty($fileNamePattern) &&
                function_exists('openssl_random_pseudo_bytes') &&
                $b = openssl_random_pseudo_bytes(5, $strong)
            ) {
                $fileNamePattern = $prefix.bin2hex($b);
            }

            if (empty($fileNamePattern)) {
                $fileNamePattern = $prefix.getmypid().uniqid('last_resort_unique_string', true);
            }
        } else {
            $fileNamePattern = $prefix.substr(md5($_SERVER['REQUEST_URI']), 0, 10);
        }

        return $fileNamePattern;
    }
}
