<?php


// 通用配置

return [
    // 游戏id 9 代表英雄杀手q
    'game_id' => getenv('GAME_ID'),

    // 英雄杀商品包的配置
    'hero_goods' =>[
        1=>3,  //goods_id 1 对应的是 3元包商品
        2=>6,
        3=>18,
        4=>30,
        5=>68,
        6=>168,
    ],

];
