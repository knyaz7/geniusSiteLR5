<?php
function dbConnect()
{
    $env = parse_ini_file('.env');
    return [
        'servername' => $env['DB_HOST'],
        'username' => $env['DB_USERNAME'],
        'password' => $env['DB_PASSWORD'],
        'database' => $env['DB_DATABASE'],
    ];
}
