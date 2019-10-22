<?php

return [
    'class' => \yii\queue\sync\Queue::class,
    'handle' => true,
    'as log' => \yii\queue\LogBehavior::class,
];
