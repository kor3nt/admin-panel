<?php
    // Open connection
    function start_connection(): mysqli
    {
        require_once "./config.php";

        $connect = new mysqli($host, $db_user, $db_password, $db_name);

        if ($connect->connect_errno!=0) {
            throw new Exception(mysqli_connect_errno());
        }

        return $connect;
    }

    // Close connection
    function stop_connection(mysqli $connect): void
    {
        $connect->close();
    }