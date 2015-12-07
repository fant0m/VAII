<?php

function json($array, $encode = false) {
    $output = [];
    foreach ($array as $a) {
        $output[] = $a->response(false);
    }
    if ($encode) {
        return json_encode($output);
    } else {
        return $output;
    }
}