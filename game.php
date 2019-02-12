#!/usr/bin/env php
<?php

$options = getopt('', ["id::"]);
$gameId = (string) $options['id'];

include (__DIR__ . '/TicTacGame.php');

new TicTacGame($gameId);


