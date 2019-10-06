<?php

function env($key, $default = null)
{
    global $dotenv;
    if ($dotenv == null)
        envInit();

    return isset($dotenv[$key]) ? $dotenv[$key] : $default;
}

function envInit()
{
    global $dotenv;
    $dotenv = parse_ini_file('.env');
}
