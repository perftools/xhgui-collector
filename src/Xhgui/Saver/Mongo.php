<?php

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

    public function __construct(MongoCollection $collection)
    {
        $this->_collection = $collection;
    }

    public function save(array $data)
    {
        if (!isset($data['_id'])) {
            $data['_id'] = self::getLastProfilingId();
        }

        // Escape profile data keys according to the standard https://docs.mongodb.com/manual/reference/limits/#Restrictions-on-Field-Names
        if (isset($data['profile'])) {
            foreach ($data['profile'] as $key => $data) {
                $escapedKey = str_replace(array(".", "$"), "_", $key);
                $data['profile'][$escapedKey] = $data;
                unset($data['profile'][$key]);
            }
        }

        if (isset($data['meta']['request_ts'])) {
            $data['meta']['request_ts'] = new MongoDate($data['meta']['request_ts']['sec']);
        }

        if (isset($data['meta']['request_ts_micro'])) {
            $data['meta']['request_ts_micro'] = new MongoDate(
                $data['meta']['request_ts_micro']['sec'],
                $data['meta']['request_ts_micro']['usec']
            );
        }


        return $this->_collection->insert($data, array('w' => 0));
    }

    /**
     * Return profiling ID
     * @return MongoId lastProfilingId
     */
    public static function getLastProfilingId() {
        if (!self::$lastProfilingId) {
            self::$lastProfilingId = new MongoId();
        }
        return self::$lastProfilingId;
    }
}
