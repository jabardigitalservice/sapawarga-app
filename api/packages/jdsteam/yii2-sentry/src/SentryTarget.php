<?php

namespace Jdsteam\Yii2Sentry;

use Sentry;
use yii\base\Exception;
use yii\log\Target;

class SentryTarget extends Target
{
    /**
     * @var string
     */
    public $dsn;

    /**
     * @var bool
     */
    public $context;

    /**
     * @var bool
     */
    public $enabled;

    /**
     * @var string
     */
    public $environment;

    /**
     * Initializes the route.
     * This method is invoked after the route is created by the route manager.
     */
    public function init()
    {
        parent::init();

        if ($this->enabled) {
            Sentry\init(['dsn' => $this->dsn, 'environment' => $this->environment]);
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
