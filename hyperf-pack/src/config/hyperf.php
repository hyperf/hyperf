<?php


return [
    //命令
    'commands'=>[
        'build'=>[
            'Build',
            'description' => "\t" . "Package the project as a PHAR file.",
            'options'     => [
                [['d', 'dir'], 'description' => "\t" . 'The project directory to be packaged'],
                [['o', 'output'], 'description' => "\t" . 'The name of the output phar file'],
                [['b', 'bootstrap'], 'description' => 'The path to the Bootstrap file'],
                [['r', 'regex'], 'description' => "\t" . 'Extract regular expressions'],
            ],
        ],
    ]
];