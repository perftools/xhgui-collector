<?php

/**
 * Upload handler
 */
class Xhgui_Saver_Upload implements Xhgui_Saver_Interface
{
    /**
     * @var string
     */
    protected $uri;

    /**
     * @var int
     */
    protected $timeout;

    /**
     * Xhgui_Saver_Upload constructor.
     *
     * @param $uri
     * @param $timeout
     */
    public function __construct($uri, $timeout)
    {
        $this->uri      = $uri;
        $this->timeout  = $timeout;
    }

    /**
     * @param array $data
     *
     * @return mixed|void
     */
    public function save(array $data)
    {
        $json = json_encode($data);

        $ch = curl_init($this->uri);

        $headers = array(
            'Accept: application/json',         // Prefer to receive JSON back
            'Content-Type: application/json'    // The sent data is JSON
        );

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        curl_exec($ch);

        curl_close($ch);
    }
}
