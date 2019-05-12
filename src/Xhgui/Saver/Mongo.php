<?php

/**
 * Class Xhgui_Saver_Mongo
 */
class Xhgui_Saver_Mongo implements Xhgui_Saver_Interface
{
    /**
     * @var MongoCollection
     */
    private $_collection;

    /**
     * @var MongoId lastProfilingId
     */
    private static $lastProfilingId;

    /**
     * Xhgui_Saver_Mongo constructor.
     *
     * @param MongoCollection $collection
     */
    public function __construct(MongoCollection $collection)
    {
        $this->_collection = $collection;
    }

    /**
     * @param array $data
     *
     * @return array|bool
     * @throws MongoCursorException
     * @throws MongoCursorTimeoutException
     * @throws MongoException
     */
    public function save(array $data)
    {
        $data['meta']['request_ts']         = new MongoDate($data['meta']['request_ts']['sec']);
        $data['meta']['request_ts_micro']   = new MongoDate(
            $data['meta']['request_ts_micro']['sec'],
            $data['meta']['request_ts_micro']['usec']
        );

        $data['_id'] = self::getLastProfilingId();

        return $this->_collection->insert($data, array('w' => 0));
    }

    /**
     * Return profiling ID
     * @return MongoId lastProfilingId
     */
    public static function getLastProfilingId()
    {
        if (!self::$lastProfilingId) {
            self::$lastProfilingId = new MongoId();
        }
        return self::$lastProfilingId;
    }
}
