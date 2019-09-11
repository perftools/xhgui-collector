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
     * @throws Exception
     */
    public static function getFilename() {
        $fileNamePattern = 'xhgui.data.'.microtime(true);
        try {
            $fileNamePattern .= bin2hex(random_bytes(12));
        } catch (Exception $e) {
            // Should we add logging here? random_bytes from paragonie/random_compat will throw exception if
            // it is unable to get safe enough data. It will not fall back to insecure random data.
        }

        return $fileNamePattern;
    }
}
