<?php
require_once 'daemon.php';
require_once 'util.php';
require_once 'database.php';

$config = require_once 'config.php';

Database::connect(
    $config['database']['username'],        //username
    $config['database']['password'],        //password
    $config['database']['hostname'],        //host IP address
    $config['database']['database']         //database name
);

class MyDaemon extends DaemonPHP
{

    protected $defSleep = 60;
    protected $ch_pids = [];

    public function run()
    {
        while (true) {
            $start_sch = microtime(true);
            Database::reconnect();
            $this->_cleanProcess();

            $Tasks = $this->_getTasks()->fetchAll();

            foreach ($Tasks AS $task) {

                if (array_key_exists($task['id'], $this->ch_pids)) {
                    if ($task['singleton']) {
                        continue;
                    }
                    $this->ch_pids[uniqid()] = $this->ch_pids[$task['id']];
                }

                $this->ch_pids[$task['id']] = pcntl_fork();

                if ($this->ch_pids[$task['id']] == -1) {
                    echo "Error fork process." . PHP_EOL;
                } else if ($this->ch_pids[$task['id']]) {
                    echo "Success fork process" . PHP_EOL;
                } else {
                    $this->_runTask($task);
                }
            }

            $mict_sch = floor((microtime(true) - $start_sch));
            if ($mict_sch > 1)
                sleep($this->defSleep - $mict_sch);
            else
                sleep($this->defSleep);
        }
    }


    private function _runTask($task)
    {
        Database::reconnect();
        $newLast = Database::datetime_add_interval($task['last_datetime'], $task['interval_diff'] * $task['interval_count'], $task['interval']);
        $pped = Database::prepare("Update tbl_task SET last_datetime = :last_datetime WHERE id = :id");
        $pped->execute([
            ':last_datetime' => $newLast,
            ':id' => $task['id'],
        ]);

        $start_sch = microtime(true);
        exec($task['command'], $output, $result);
        $mict_sch = (microtime(true) - $start_sch);

        print_r([$task['id'], Util::ArrayToString($output, "\n"), $mict_sch]);
        $pped = Database::prepare("Insert into tbl_task_history (id_task, result, execute_time) VALUES (:id_task, :result, :execute_time)");
        $pped->execute([
            ':id_task' => $task['id'],
            ':result' => Util::ArrayToString($output, "\n"),
            ':execute_time' => $mict_sch,
        ]);
        exit(0);
    }

    private function _cleanProcess()
    {
        foreach ($this->ch_pids as $key => $pid) {
            $res = pcntl_waitpid($pid, $status, WNOHANG);
            if ($res == -1 || $res > 0)
                unset($this->ch_pids [$key]);
        }
    }

    private function _getTasks()
    {
        return Database::query("
              SELECT `id`, `command`,`singleton`,`repeat`, `repeat_count`,`last_datetime`,`interval_count`,`interval`,
                  (select count(id) from tbl_task_history where id_task = tbl_task.id) AS run_count,
                  FLOOR(datetime_get_timestamp_diff(`interval`,last_datetime,now())/interval_count) AS interval_diff
              FROM tbl_task
              WHERE start_datetime <= now() AND (end_datetime IS NULL OR end_datetime > now())
              HAVING interval_diff > 0 AND (repeat_count = 0 OR run_count < repeat_count )
        ");
    }
}

$daemon = new MyDaemon();
$daemon->handle($argv);

