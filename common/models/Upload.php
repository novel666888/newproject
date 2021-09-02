<?php
/**
 * Created by PhpStorm.
 * author: lijin
 * Date: 2021/3/12
 * Time: 13:48
 */
namespace common\models;

use yii\base\Model;
use yii\web\UploadedFile;

class Upload extends Model
{
    /**
     * @var UploadedFile
     */
    public $file;

    public function rules()
    {
        return [
            [['file'], 'file'],
        ];
    }

}