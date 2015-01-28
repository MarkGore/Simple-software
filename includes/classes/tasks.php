<?php

/**
 * Created by PhpStorm.
 * User: Mark
 * Date: 28/01/2015
 * Time: 00:19
 */
class tasks
{

    function tasks()
    {
        global $db;
        register_shutdown_function(array($this, 'execute'));
    }

    function execute()
    {
        global $db, $botwith;
        $tasks = $db->get('tasks');
        foreach ($tasks as $task) {
            if ($task->task_ran <= time()) {
                $taskFile = $task->task_file;
                if (file_exists($botwith->config['board_path'] . '/includes/tasks/' . $taskFile . '.php')) {
                    include($botwith->config['board_path'] . '/includes/tasks/' . $taskFile . '.php');
                    $nextRun = time() + ($task->task_mins * 60);
                    $update = array('uid' => $task->uid, 'task_ran' => $nextRun);
                    $db->update('tasks', $update);
                }
            }
        }
    }
}