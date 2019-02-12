#!/usr/bin/env php
<?php

$options = getopt('', ["id::"]);
if (isset($options['id'])) {
    $gameId = (string) $options['id'];
} else {
    $gameId = null;
}


include (__DIR__ . '/TicTacGame.php');

new TicTacGame($gameId);


