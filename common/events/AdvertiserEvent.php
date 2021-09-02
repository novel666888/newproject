<?php
namespace common\events;

use yii\base\Component;

class AdvertiserEvent extends Component
{
    const EVENT_UPDATE_OPM = 'updateOpm'; // 更新优化师
    const EVENT_UPDATE_GUEST = 'updateGuest'; // 更新客户、子品牌、二级项目


    public function updateOpm($data)
    {
        $event = $this->packData($data);
        $this->trigger(self::EVENT_UPDATE_OPM, $event);
    }

    public function updateGuest($data)
    {
        $event = $this->packData($data);
        $this->trigger(self::EVENT_UPDATE_GUEST, $event);
    }

    private function packData($data)
    {
        $dataPack = new DataPack();
        $dataPack->advertiserId = isset($data['advertiserId']) ? $data['advertiserId'] : '';
        $dataPack->extInfo = isset($data['extInfo']) ? $data['extInfo'] : '';

        return $dataPack;
    }

}