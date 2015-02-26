<?php

require_once __DIR__.'/../ArrayValidator.php';

$nestedModel = [
    'fields' => ['email', 'name', 'chain', 'subject', 'message', 'is_use_picture'],
    'rules'  => [
        [['email', 'name', 'subject', 'message', 'is_use_picture'],  ['Validators','required']],
        [['email', 'name', 'subject'], ['Validators', 'length'], 'options' => ['max_length' => 255]],
        ['message', ['Validators', 'length'], 'options' => ['max_length' => 500]],
        ['email', ['Validators', 'email']],
        ['is_use_picture', ['Validators', 'bool']],
        ['chain', ['Validators', 'integer'], 'on' => 'SENDTOMANY', 'when' => 'Validators::$parent[\'is_order\'] == true', 'options' => ['min_range' => 1]]
    ]
];

$model1 = [
    'fields' => ['callbackUrl', 'type', 'pin_type', 'pin', 'name', 'is_order', 'users'],
    'rules' => [
        [['type', 'name'], ['Validators','required']],
        ['type', ['Validators', 'in'], 'options' => ['range' => ['SENDTOMANY', 'SENDTOEACH']]],

        // **************** SENDTOEACH
        ['pin_type', ['Validators', 'in'], 'options' => ['range' => ['LOW', 'HIGH']], 'on' => 'SENDTOEACH'],
        ['pin', ['Validators', 'required'], 'on' => 'SENDTOEACH', 'when' => '$data[\'pin_type\'] === \'HIGH\''],
        // **************** SENDTOEACH

        // **************** SENDTOMANY
        ['is_order', ['Validators', 'bool'], 'on' => 'SENDTOMANY'],
        // **************** SENDTOMANY
        ['users', ['Validators', 'length'], 'options' => ['min_length' => 1]],
        ['users', ['Validators', 'nested'], 'options' => ['model' => $nestedModel]]
    ]
];




$inputData = json_decode(file_get_contents('./data.json'), true);



print_r(ArrayValidator::validate($model1, $inputData, 'SENDTOMANY'));