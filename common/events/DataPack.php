<?php
namespace common\events;

use yii\base\Event;

class DataPack extends Event
{
    public $advertiserId = null;
    public $extInfo = null;
}