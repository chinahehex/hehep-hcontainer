<?php

return [
    'user'=>[
        'id'=>'user',
        'class'=>'hcontainer\tests\common\UserBean',

        'refRole'=>'<ref::role>',
        // 属性定义
        'real_name'=>'hehe',
        'pwd'=>'123123',
    ],

    'role'=>[
        'id'=>'role',
        'class'=>'hcontainer\tests\common\RoleBean',
    ],
];
