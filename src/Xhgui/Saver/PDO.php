<?php

/**
 * Class Xhgui_Saver_PDO
 */
class Xhgui_Saver_PDO implements \Xhgui_Saver_Interface {

    /**
     * @var \PDO
     */
    protected $connection;

    /**
     * PDO constructor.
     */
    public function __construct($dsn) {
        $this->connection = new \PDO($dsn);
    }

    /**
     * @param array $data
     */
    public function save(array $data) {
        $this->connection->beginTransaction();

        try {
            //@todo tmp - replace with something more complex.
            $id = md5(microtime());

            $this->connection->exec('insert into profiles(id, profiles) VALUES("'.$id.'", "'.json_encode($data['profile']).'")');
            $this->connection->exec('insert into profiles_meta(id, meta) VALUES("'.$id.'", "'.json_encode($data['meta']).'")');
            $this->connection->exec('insert into profiles_info(
id,
url,
request_time,
method,
main_ct,
main_wt,
main_cpu,
main_mu,
main_pmu,
application,
version,
branch,
controller,
action
                          ) VALUES (
"'.$id.'",
"'.$data['meta']['simple_url'].'",
"'.(\DateTime::createFromFormat('U u', $data['meta']['request_ts_micro']['sec'].' '.$data['meta']['request_ts_micro']['usec']))->format('Y-m-d H:i:s.u').'",
"'.(php_sapi_name()==='cli' ? 'CLI' : $_SERVER['REQUEST_METHOD']).'",
"'.$data['profile']['main()']['ct'].'",
"'.$data['profile']['main()']['wt'].'",
"'.$data['profile']['main()']['cpu'].'",
"'.$data['profile']['main()']['mu'].'",
"'.$data['profile']['main()']['pmu'].'",
"'.$data['meta']['application'].'",
"'.$data['meta']['version'].'",
"'.$data['meta']['branch'].'",
"'.$data['meta']['controller'].'",
"'.$data['meta']['action'].'"
)');


            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
        }
    }
}
