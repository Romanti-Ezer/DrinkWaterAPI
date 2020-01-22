<?php

class Database
{
    public static function getConnection()
    {
        return new PDO('mysql: host=' . DBHOST . '; dbname=' . DBNAME . ';', DBUSER, DBPASSWORD);
    }
}