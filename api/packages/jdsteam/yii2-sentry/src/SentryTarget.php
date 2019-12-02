<?php

namespace Jdsteam\Yii2Sentry;

use Sentry;
use Yii;
use yii\base\Exception;
use yii\log\Target;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

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
        $user = Yii::$app->user->identity;

        if ($this->enabled === false || $user === null) {
            return false;
        }

        foreach ($this->messages as $message) {
            return $this->captureErrors($message);
        }
    }

    protected function captureErrors($message)
    {
        list($text, $level, $category, $timestamp, $traces) = $message;

        if ($text instanceof NotFoundHttpException ||
            $text instanceof ForbiddenHttpException) {
            return false;
        }

        if ($text instanceof \Throwable || $text instanceof \Exception) {
            $user = Yii::$app->user->identity;

            $releaseVersion = getenv('APP_VERSION');
            $releaseString  = "sapawarga-api@{$releaseVersion}";

            Sentry\init(['dsn' => $this->dsn, 'environment' => $this->environment, 'release' => $releaseString]);

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

    protected function setIdentity(&$scope)
    {
        if (isset(Yii::$app->user)) {
            $user = Yii::$app->user->identity;
            $scope->setUser([
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
            ]);
        }
    }
}
