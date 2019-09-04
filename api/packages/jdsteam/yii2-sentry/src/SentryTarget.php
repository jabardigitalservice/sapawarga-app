<?php

namespace Jdsteam\Yii2Sentry;

use Sentry;
use yii\base\Exception;
use yii\log\Target;

class SentryTarget extends Target
{
    public $dsn;

    public $context;

    /**
     * @var bool
     */
    public $enabled;

    /**
     * Initializes the route.
     * This method is invoked after the route is created by the route manager.
     */
    public function init()
    {
        parent::init();

        if ($this->enabled) {
            Sentry\init(['dsn' => $this->dsn, 'environment' => 'staging']);
        }
    }

    /**
     * Exports log [[messages]] to a specific destination.
     * Child classes must implement this method.
     */
    public function export()
    {
        //
    }
}
