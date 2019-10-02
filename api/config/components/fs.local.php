<?php

use creocoder\flysystem\LocalFilesystem;

return [
    'class' => LocalFilesystem::class,
    'path' => '@webroot/storage', // don't forget set permission (chmod 777 if needed)
];
