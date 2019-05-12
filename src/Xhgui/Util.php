<?php

/**
 * Common utilities
 */
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
    public static function getXHProfFileName()
    {
        if (empty($_SERVER['REQUEST_TIME_FLOAT'])) {
            $t = explode('.', microtime(true));
        } else {
            $t = explode('.', $_SERVER['REQUEST_TIME_FLOAT']);
        }
        // join float part to main part and pad it to make every filename same length
        return $t[1].str_pad($t[2], 4, 0, STR_PAD_RIGHT).'.data.xhprof';
    }

    /**
     * Serialize data for storage
     *
     * @param $data
     * @return false|string
     */
    public static function getDataForStorage($data)
    {
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

            case 'php':
            case 'var_export':
                return var_export($data, true);
                break;
        }
    }

    /**
     * @param $data
     * @return false|string
     */
    public static function getDataFromStorage($data)
    {
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

            case 'php':
            case 'var_export':
                //@todo exception ?
                break;
        }
    }

    /**
     * Get id for a record.
     *
     * By default this method will try to re-use request id from http server.
     * This is needed for some storage engines that don't have string/hashlike id generation.
     *
     * @param bool $useRequestId
     *
     * @return string
     */
    public static function getId($useRequestId = true)
    {
        if ($useRequestId) {
            foreach(['REQUEST_ID', 'HTTP_REQUEST_ID', 'HTTP_X_REQUEST_ID', 'X_CORRELATION_ID', 'HTTP_X_CORRELATION_ID'] as $header) {
                if (array_key_exists($header, $_SERVER) !== false) {
                    return $_SERVER[$header];
                }
            }
        }

        // try php 7+ function.
        if (function_exists('random_bytes')) {
            try {
                return bin2hex(random_bytes(16));
            } catch (\Exception $e) {
                // entropy source is not available
            }
        }

        // try openssl. For purpose of this id we can ignore info if this value is strong or not
        if (function_exists('openssl_random_pseudo_bytes')) {
            /** @noinspection CryptographicallySecureRandomnessInspection */
            return openssl_random_pseudo_bytes(32, $strong);
        }

        // fallback to most generic method. Make sure it has 32 characters :)
        return md5(uniqid('xhgui', true).microtime());
    }
}
