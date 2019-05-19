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
}
