<?php

function cleanup(string $dir): void
{
    $name = date('Y-m-d');
    $file = $dir.DIRECTORY_SEPARATOR.$name.'.json';

    if (is_file($file) === true) {
        unlink($file);
    }

    if (str_starts_with($dir, sys_get_temp_dir()) === true) {
        rmdir($dir);
    }
}
