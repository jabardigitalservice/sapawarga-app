<?php

namespace Jdsteam\Yii2Sentry;

use Sentry;
use Yii;
use yii\base\Exception;
use yii\log\Target;

class SentryTarget extends Target
{
    public $client;

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
     * Exports log [[messages]] to a specific destination.
     * Child classes must implement this method.
     */
    public function export()
    {
        if ($this->enabled === false) {
            return false;
        }

        foreach ($this->messages as $message) {
            list($text, $level, $category, $timestamp, $traces) = $message;

            if ($text instanceof \Throwable || $text instanceof \Exception) {
                $user = Yii::$app->user->identity;

                if ($user === null) {
                    return false;
                }

                Sentry\init(['dsn' => $this->dsn, 'environment' => $this->environment]);

                Sentry\configureScope(function (Sentry\State\Scope $scope) use ($user): void {
                    $scope->setUser([
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                    ]);
                });

                Sentry\captureException($text);
            }
        }
    }
}
