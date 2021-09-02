<?php
namespace common\events;

use yii\base\Behavior;
use yii\base\Event;
use yii\web\Application;

class EventBehavior extends Behavior
{
    public function events()
    {
        return [Application::EVENT_BEFORE_ACTION => 'handle'];
    }

    public function handle()
    {
        $eventsConfig = \Yii::$app->params['events'];

        if (!is_array($eventsConfig) || !count($eventsConfig)) {
            return true;
        }
        foreach ($eventsConfig as $key => $senders) {
            $class = explode('.', $key);
            foreach ($senders as $sender) {
                Event::on($class[0], $class[1], $sender);
            }
        }
        return parent::events(); // TODO: Change the autogenerated stub
    }
}