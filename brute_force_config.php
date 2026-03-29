<?php
// brute_force_config.php

return [
    // [fails_count => [duration_in_seconds, meme_gif_path]]
    // duration = 0: No block
    // duration = -1: Permanent ban
    'stages' => [
        1 => [0, null],
        2 => [0, null],
        3 => [30, 'video/meme1.gif'],
        4 => [120, 'video/meme2.gif'],
        5 => [300, 'video/meme3.gif'],
        6 => [600, 'video/meme4.gif'],
        7 => [1500, 'video/meme5.gif'],
        8 => [2700, 'video/meme5.gif'],
        9 => [3600, 'video/meme5.gif'],
        10 => [-1, 'video/meme5.gif'], // Permanent Ban
    ],
    // Time window to look back for failed attempts (in seconds)
    // 86400 = 24 hours
    'lookback_window' => 86400,
];
