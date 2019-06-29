<?php
/**
 * A small factory to handle creation of the profile saver instance.
 *
 * This class only exists to handle cases where an incompatible version of pimple
 * exists in the host application.
 */
class Xhgui_Saver
{
    /**
     * Get a saver instance based on configuration data.
     *
     * @param array $config The configuration data.
     * @return Xhgui_Saver_Interface
     * @throws MongoConnectionException
     * @throws MongoException
     */
    public static function factory($config)
    {
        switch ($config['save.handler']) {

            case 'file':
                return new Xhgui_Saver_File(
                    $config['save.handler.filename'],
                    $config['save.handler.separate_meta']
                );

            case 'upload':
                $timeout = 3;
                if (isset($config['save.handler.upload.timeout'])) {
                    $timeout = $config['save.handler.upload.timeout'];
                }
                return new Xhgui_Saver_Upload(
                    $config['save.handler.upload.uri'],
                    $timeout
                );

            case 'pdo':
                return new Xhgui_Saver_PDO(
                    $config['db.dsn'],
                    (!empty($config['db.user'])) ? $config['db.user'] : null,
                    (!empty($config['db.password'])) ? $config['db.password'] : null,
                    $config['db.options']
                );
                break;

            case 'mongodb':
            default:
                $mongo = new MongoClient(
                    $config['db.host'],
                    $config['db.options'] +
                    [
                        'username' => (!empty($config['db.user'])) ? $config['db.user'] : null,
                        'password' => (!empty($config['db.password'])) ? $config['db.password'] : null,
                    ]
                );

                $collection = $mongo->{$config['db.db']}->results;
                $collection->findOne();
                return new Xhgui_Saver_Mongo($collection);
        }
    }

    /**
     * For usage with factory instance - for example for easier testing
     *
     * @param $config
     * @return Xhgui_Saver_Interface
     * @throws MongoConnectionException
     * @throws MongoException
     */
    public function create($config){
        return self::factory($config);
    }
}
