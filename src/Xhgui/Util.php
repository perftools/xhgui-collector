<?php

class Xhgui_Util
{
    /**
     * Creates a simplified URL given a standard URL.
     * Does the following transformations:
     *
     * - Remove numeric values after =.
     *
     * @param string $url
     * @return string
     */
    public static function simpleUrl($url)
    {
        $callable = Xhgui_Config::read('profiler.simple_url');
        if (is_callable($callable)) {
            return call_user_func($callable, $url);
        }
        return preg_replace('/\=\d+/', '', $url);
    }

    /**
     * @return string
     */
    public static function getXHProfFileName(){
        if (empty($_SERVER['REQUEST_TIME_FLOAT'])) {
            $t = explode('.', microtime(true));
        } else {
            $t = explode('.', $_SERVER['REQUEST_TIME_FLOAT']);
        }
        // join float part to main part and pad it to make every filename same length
        return $t[1].str_pad($t[2], 4, 0, STR_PAD_RIGHT).'.data.xhprof';
    }

    /**
     * @param $data
     * @return false|string
     */
    public static function getDataForStorage($data) {
        switch (Xhgui_Config::read('save.handler.serializer', 'json')) {
            case 'json':
                return json_encode($data);
                break;
            case 'serialize':
                return serialize($data);
                break;

            case 'igbinary_serialize':
            case 'igbinary_unserialize':
            case 'igbinary':
                return igbinary_serialize($data);
                break;
        }
    }

    /**
     * @param $data
     * @return false|string
     */
    public static function getDataFromStorage($data) {
        switch (Xhgui_Config::read('save.handler.serializer', 'json')) {
            case 'json':
                return json_decode($data, true);
                break;
            case 'serialize':
                if (PHP_MAJOR_VERSION > 7) {
                    return unserialize($data, false);
                } else {
                    return unserialize($data);
                }

                break;

            case 'igbinary_serialize':
            case 'igbinary_unserialize':
            case 'igbinary':
                return igbinary_unserialize($data);
                break;
        }
    }
}
