<?php

namespace common\services;

use backend\modules\accident\logic\AccidentLogic;
use common\lib\Common;
use yii;

/**
 * Class Request --工具类获取请求参数
 * @package common\util
 */
class PHPExport
{
    /**
     * 导出数据
     * @param $data
     * @param $field
     * @return false
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public static function exportExcel($data, $field, $filename)
    {
        $objPHPExcel = new \PHPExcel();
        $objSheet = $objPHPExcel->getActiveSheet();
        $objSheet->setTitle($filename);

        $zm = array('A1', 'B1', 'C1', 'D1', 'E1', 'F1', 'G1', 'H1', 'I1', 'J1', 'K1', 'L1', 'M1', 'N1', 'O1',
            'P1', 'Q1', 'R1', 'S1', 'T1', 'U1', 'V1', 'W1', 'X1', 'Y1', 'Z1');
        $z = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O',
            'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        if (empty($field)) {
            return false;
        }

        $title = $field['value'];
        $key = $field['key'];

        foreach ($title as $k => $v) {
            $objSheet->setCellValue($zm[$k], $v);
        }
        $progree = Yii::$app->params["progress"];
        $shotClass = Yii::$app->params["shot_class"];
        $videoClass = Yii::$app->params["video_class"];
        $colsWidth = [];
        $i = 2;
        foreach ($data as $k => $v) {
            foreach ($key as $kk => $vv) {

                if ($vv == 'progress') {
                    $v["{$vv}"] = $progree[$v["{$vv}"]];
                }

                if ($vv == 'shot_class') {
                    $v["{$vv}"] = $shotClass[$v["{$vv}"]];
                }

                if ($vv == 'video_class') {
                    $v["{$vv}"] = $videoClass[$v["{$vv}"]];
                }

                if ($vv == 'script_delay') {
                    $v["{$vv}"] = $v["{$vv}"] == 1 ? "延期":"正常";
                }

                if ($vv == 'slip_delay') {
                    $v["{$vv}"] = $v["{$vv}"] == 1 ? "延期":"正常";
                }

                if (isset($colsWidth[$z[$kk]])) {
                    $colsWidth[$z[$kk]] = $colsWidth[$z[$kk]] > strlen($v["{$vv}"]) ? $colsWidth[$z[$kk]] : strlen($v["{$vv}"]);
                } else {
                    $colsWidth[$z[$kk]] = strlen($v["{$vv}"]);
                }

                $objSheet->setCellValueExplicit($z[$kk] . $i, $v["{$vv}"], \PHPExcel_Cell_DataType::TYPE_STRING);
            }
            $i++;
        }

        foreach ($colsWidth as $k => $v) {
            $objSheet->getColumnDimension($k)->setWidth($v + 5);
        }

        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $filename . '.xlsx"');
        header("Content-Disposition:attachment;filename={$filename}.xlsx");
        header('Cache-Control: max-age=0');
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }


    /**
     * 导出账户规则列表数据
     * @param $data
     * @param $field
     * @return false
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public static function exportAccidentExcel($data, $field, $filename)
    {
        $objPHPExcel = new \PHPExcel();
        $objSheet = $objPHPExcel->getActiveSheet();
        $objSheet->setTitle($filename);

        $zm = array('A1', 'B1', 'C1', 'D1', 'E1', 'F1', 'G1', 'H1', 'I1', 'J1', 'K1', 'L1', 'M1', 'N1', 'O1',
            'P1', 'Q1', 'R1', 'S1', 'T1', 'U1', 'V1', 'W1', 'X1', 'Y1', 'Z1');
        $z = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O',
            'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        if (empty($field)) {
            return false;
        }

        $title = $field['value'];
        $key = $field['key'];

        foreach ($title as $k => $v) {
            $objSheet->setCellValue($zm[$k], $v);
        }

        $colsWidth = [];
        $i = 2;
        foreach ($data as $k => $v) {
            foreach ($key as $kk => $vv) {

                if ($vv == 'pangolin') { //是否启用穿山甲
                    $v["{$vv}"] = $v["{$vv}"] == 1 ? "是" : "否";
                }

                if ($vv == 'auto_bid') { //是否启用智能放量
                    $v["{$vv}"] = $v["{$vv}"] == 1 ? "是" : "否";
                }

                if ($vv == 'detect_url') { //是否关闭链接监测
                    $v["{$vv}"] = $v["{$vv}"] == 1 ? "是" : "否";
                }

                if ($vv == 'pause_account') {//是否暂停账户
                    $v["{$vv}"] = $v["{$vv}"] == 1 ? "是" : "否";
                }

                if ($vv == 'white_rule') {//规则白是否加白
                    $v["{$vv}"] = $v["{$vv}"] == 1 ? "是" : "否";
                }

                if ($vv == 'white_account') {//账户是否加白
                    $v["{$vv}"] = $v["{$vv}"] == 1 ? "是" : "否";
                }

                if (isset($colsWidth[$z[$kk]])) {
                    $colsWidth[$z[$kk]] = $colsWidth[$z[$kk]] > strlen($v["{$vv}"]) ? $colsWidth[$z[$kk]] : strlen($v["{$vv}"]);
                } else {
                    $colsWidth[$z[$kk]] = strlen($v["{$vv}"]);
                }

                $objSheet->setCellValueExplicit($z[$kk] . $i, $v["{$vv}"], \PHPExcel_Cell_DataType::TYPE_STRING);
            }
            $i++;
        }

        foreach ($colsWidth as $k => $v) {
            $objSheet->getColumnDimension($k)->setWidth($v + 5);
        }

        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $filename . '.xlsx"');
        header("Content-Disposition:attachment;filename={$filename}.xlsx");
        header('Cache-Control: max-age=0');
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }


    /**
     *  上新数据导入
     */
    public static function ImportExport($fileName, $exts = 'xls',$start=2)
    {
        //创建PHPExcel对象，注意，不能少了\
        $PHPExcel = new \PHPExcel();
        //如果excel文件后缀名为.xls，导入这个类
        if (!in_array($exts, ['xls', 'xlsx'])) {
            return false;
        }

        $fileType = \PHPExcel_IOFactory::identify($fileName); //文件名自动判断文件类型
        $excelReader = \PHPExcel_IOFactory::createReader($fileType);

        //载入文件
        $PHPExcel = $excelReader->load($fileName);
        //获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
        $currentSheet = $PHPExcel->getSheet(0);
        //获取总列数
        $allColumn = $currentSheet->getHighestColumn();
        //获取总行数
        $allRow = $currentSheet->getHighestRow();
        //循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
        $data = [];
        for ($currentRow = $start; $currentRow <= $allRow; $currentRow++) {
            //从哪列开始，A表示第一列
            for ($currentColumn = 'A'; $currentColumn <= $allColumn; $currentColumn++) {
                //数据坐标
                $address = $currentColumn . $currentRow;
                //读取到的数据，保存到数组$data中
                $cell = $currentSheet->getCell($address)->getValue();

                if ($cell instanceof PHPExcel_RichText) {
                    $cell = $cell->__toString();
                }
                $data[$currentRow - 1][$currentColumn] = $cell;
            }
        }
        //返回组装的数据
        return $data;
    }

    public static function exportTimesLinkExcel($data, $field, $filename, $type=1,$name)
    {
        $objPHPExcel = new \PHPExcel();
        $objSheet = $objPHPExcel->getActiveSheet(0);
        $objSheet->setTitle($filename);
        $objPHPExcel->getDefaultStyle()->getFont()->setName('微软雅黑');//字体
        if($type ==9) { //猿辅导
            $zm = array('A1', 'B1', 'C1', 'D1', 'E1', 'F1', 'G1', 'H1', 'I1', 'J1', 'K1', 'L1', 'M1',
                'P1', 'Q1', 'R1');
            $objPHPExcel->getActiveSheet()->getStyle('A1:M1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');//设置第一行背景色
            $i = 2;
        }
        if($type ==1) {
            $zm = array('A1', 'B1', 'C1', 'D1', 'E1', 'F1', 'G1', 'H1', 'I1', 'J1', 'K1', 'L1', 'M1', 'N1', 'O1',
                'P1', 'Q1', 'R1');
            $objPHPExcel->getActiveSheet()->getStyle('A1:R1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('3CB371');//设置第一行背景色
            $i = 2;
        }

        if($type == 101){
            $zm = array('A1', 'B1', 'C1', 'D1', 'E1', 'F1', 'G1', 'H1', 'I1', 'J1', 'K1', 'L1', 'M1', 'N1');
            $objPHPExcel->getActiveSheet()->getStyle('A1:N1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('3CB371');//设置第一行背景色
            $i = 2;
        }
        if($type == 14){
            $zm = array('A1', 'B1', 'C1', 'D1', 'E1', 'F1', 'G1', 'H1', 'I1', 'J1', 'K1', 'L1', 'M1');
            $objPHPExcel->getActiveSheet()->getStyle('A1:M1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFA500');//设置第一行背景色
            $i = 2;
            $title_number = 'A1:M';
            $count = count($data)+1;
            $objPHPExcel->getActiveSheet()->getStyle($title_number.$count)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); //水平居中
            $styleThinBlackBorderOutline = array(
                'borders' => array(
                    'allborders' => array( //设置全部边框
                        'style' => \PHPExcel_Style_Border::BORDER_THIN //粗的是thick
                    ),
                ),
            );
            $objPHPExcel->getActiveSheet()->getStyle($title_number.$count)->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle($title_number.$count)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); //水平居中

        }
        if($type == 102){
            $zm = array('A1', 'B1', 'C1', 'D1', 'E1', 'F1', 'G1', 'H1', 'I1', 'J1', 'K1', 'L1', 'M1', 'N1','O1');
            $objPHPExcel->getActiveSheet()->getStyle('A1:O1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('3CB371');//设置第一行背景色
            $i = 2;
        }
        if($type == 5){
            $zm = array('A1', 'B1', 'C1', 'D1', 'E1', 'F1', 'G1', 'H1', 'I1', 'J1');
            $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('3CB371');//设置第一行背景色
            $i = 2;
        }
        if($type == 6 ||$type == 8 ||$type == 801){
            $zm = array('A1', 'B1', 'C1', 'D1', 'E1', 'F1', 'G1', 'H1', 'I1', 'J1', 'K1');
            if($type == 6){
                $color = 'FFE4B5';
            }
            if($type == 8 || $type == 801){
                $color = '8FBC8F';
            }
            $objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($color);//设置第一行背景色
            $i = 2;
        }
        //少年有的
        if($type == 11){
            $objPHPExcel->getActiveSheet()->mergeCells('A1:F1'); //合并居中
            $zm = array('A2', 'B2', 'C2', 'D2', 'E2', 'F2');
            $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFFF');//设置第一行
            $objPHPExcel->getActiveSheet()->getStyle('A2:F2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FF8C00');//设置第一行背景色
            $i = 3;
            $time = date('Y-m-d');
            $objSheet->setCellValue('A1', "麦迪克-四端-".$time);
            $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getFont()->setSize(16);
            $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getFont()->setBold(true);
            $count = count($data)+2;
            $objPHPExcel->getActiveSheet()->getStyle('A1:F'.$count)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); //水平居中
            $styleThinBlackBorderOutline = array(
                'borders' => array(
                    'allborders' => array( //设置全部边框
                        'style' => \PHPExcel_Style_Border::BORDER_THIN //粗的是thick
                    ),
                ),
            );
            $objPHPExcel->getActiveSheet()->getStyle( 'A1:F'.$count)->applyFromArray($styleThinBlackBorderOutline);
        }
        //鸭鸭-49元搜索
        if($type == 12){
            $objPHPExcel->getActiveSheet()->mergeCells('A1:O1'); //合并居中
            $zm = array('A2', 'B2', 'C2', 'D2', 'E2', 'F2', 'G2', 'H2', 'I2', 'J2', 'K2','L2','M2','N2','O2');
            $objPHPExcel->getActiveSheet()->getStyle('A1:O1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFFF');//设置第一行
            $objPHPExcel->getActiveSheet()->getStyle('A2:O2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('6495ED');//设置第一行背景色
            $i = 3;
            $count=count($data)+2;
            $objSheet->setCellValue('A1', date('Y-m-d')."-49元语英思维");
            $objPHPExcel->getActiveSheet()->getStyle('A1:O1')->getFont()->setSize(18);
            $objPHPExcel->getActiveSheet()->getStyle('A1:O1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A1:O'.$count)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); //水平居中
            $styleThinBlackBorderOutline = array(
                'borders' => array(
                    'allborders' => array( //设置全部边框
                        'style' => \PHPExcel_Style_Border::BORDER_THIN //粗的是thick
                    ),
                ),
            );
            $objPHPExcel->getActiveSheet()->getStyle( 'A1:O'.$count)->applyFromArray($styleThinBlackBorderOutline);
        }
        if($type == 13){
            $zm = array('A1', 'B1', 'C1', 'D1', 'E1', 'F1', 'G1', 'H1', 'I1', 'J1', 'K1','L1','M1','N1','O1','P1','Q1');
            $objPHPExcel->getActiveSheet()->getStyle('A1:Q1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('2E8B57');//设置第一行背景色
            $count = count($data)+1;
            $phpColor = new \PHPExcel_Style_Color();
            $phpColor->setRGB('F8F8FF');
            $objPHPExcel->getActiveSheet()->getStyle('A1:Q1')->getFont()->setColor($phpColor);
            $i = 2;
            $objPHPExcel->getActiveSheet()->mergeCells('A2:A'.$count);//合并B3-F5之间的单元格
//            $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);


            $objPHPExcel->getActiveSheet()->mergeCells('B2:F4');//合并B3-F5之间的单元格
//            $objPHPExcel->getActiveSheet()->getStyle('B2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);


            $objPHPExcel->getActiveSheet()->getStyle('A1:Q'.$count)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $objPHPExcel->getActiveSheet()->getStyle('A1:Q'.$count)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); //水平居中
            $styleThinBlackBorderOutline = array(
                'borders' => array(
                    'allborders' => array( //设置全部边框
                        'style' => \PHPExcel_Style_Border::BORDER_THIN //粗的是thick
                    ),
                ),
            );

            $objPHPExcel->getActiveSheet()->getStyle( 'A1:Q'.$count)->applyFromArray($styleThinBlackBorderOutline);
        }
        //美术宝 - 小熊美术
        if($type == 10){
            $objPHPExcel->getActiveSheet()->mergeCells('A1:L1'); //合并居中
            $zm = array('A2', 'B2', 'C2', 'D2', 'E2', 'F2', 'G2', 'H2', 'I2', 'J2', 'K2','L2');
            $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('2E8B57');//设置第一行
            $objPHPExcel->getActiveSheet()->getStyle('A2:L2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');//设置第一行背景色
            $i = 3;
            $objSheet->setCellValue('A1', "美术宝 小熊美术");
            $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getFont()->setSize(18);
            $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A1:L20')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); //水平居中
        }
        if($type == 802){
            $objPHPExcel->getActiveSheet()->mergeCells('A1:K1'); //合并居中
            $zm = array('A2', 'B2', 'C2', 'D2', 'E2', 'F2', 'G2', 'H2', 'I2', 'J2', 'K2');
            $objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('2E8B57');//设置第一行
            $objPHPExcel->getActiveSheet()->getStyle('A2:K2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');//设置第一行背景色
            $i = 3;
            $objSheet->setCellValue('A1', "金囿学堂 税务师");
            $objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getFont()->setSize(18);
            $objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A1:Q1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); //水平居中
        }
        if($type == 7){
            $zm = array('A1', 'B1', 'C1', 'D1', 'E1', 'F1', 'G1', 'H1', 'I1', 'J1', 'K1');
            $objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('F4A460');//设置第一行背景色
            $i = 2;
        }

        if($type == 2 ||$type == 3||$type == 4||$type == 401){
            $objPHPExcel->getActiveSheet()->mergeCells('A1:Q1'); //合并居中
            $zm = array('A2', 'B2', 'C2', 'D2', 'E2', 'F2', 'G2', 'H2', 'I2', 'J2', 'K2', 'L2', 'M2', 'N2', 'O2',
                'P2', 'Q2');
            $objPHPExcel->getActiveSheet()->getStyle('A1:Q1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('AFEEEE');//设置第一行背景色
            $objPHPExcel->getActiveSheet()->getStyle('A2:Q2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('C0C0C0');//设置第一行背景色
            $i = 3;
            $objSheet->setCellValue('A1', $name);
            $objPHPExcel->getActiveSheet()->getStyle('A1:Q1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); //水平居中
        }
        if($type == 301){
            $objPHPExcel->getActiveSheet()->mergeCells('A1:Q1'); //合并居中
            $zm = array('A2', 'B2', 'C2', 'D2', 'E2', 'F2', 'G2', 'H2', 'I2', 'J2', 'K2', 'L2', 'M2', 'N2', 'O2',
                'P2', 'Q2');
            $objPHPExcel->getActiveSheet()->getStyle('A1:Q1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('AFEEEE');//设置第一行背景色
            $objPHPExcel->getActiveSheet()->getStyle('A2:Q2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('C0C0C0');//设置第一行背景色
            $i = 3;
            $objSheet->setCellValue('A1', '小猴AI课：数语联报');
            $c = count($data)+2;
            $objPHPExcel->getActiveSheet()->getStyle('A1:Q'.$c)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); //水平居中
            $styleThinBlackBorderOutline = array(
                'borders' => array(
                    'allborders' => array( //设置全部边框
                        'style' => \PHPExcel_Style_Border::BORDER_THIN //粗的是thick
                    ),
                ),
            );
            $objPHPExcel->getActiveSheet()->getStyle( 'A1:Q'.$c)->applyFromArray($styleThinBlackBorderOutline);
        }
        if($type == 15){
            $objPHPExcel->getActiveSheet()->mergeCells('A1:P1'); //合并居中
            $zm = array('A2', 'B2', 'C2', 'D2', 'E2', 'F2', 'G2', 'H2', 'I2', 'J2', 'K2','L2','M2','N2','O2','P2');
            $objPHPExcel->getActiveSheet()->getStyle('A1:P1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFFF');//设置第一行
            $objPHPExcel->getActiveSheet()->getStyle('A2:P2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('6495ED');//设置第一行背景色
            $i = 3;
            $count=count($data)+2;
            $objSheet->setCellValue('A1',"陌陌-麦迪克-".date('H')."点实时数据");
            $objPHPExcel->getActiveSheet()->getStyle('A1:P1')->getFont()->setSize(18);
            $objPHPExcel->getActiveSheet()->getStyle('A1:P1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A1:P'.$count)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); //水平居中
            $styleThinBlackBorderOutline = array(
                'borders' => array(
                    'allborders' => array( //设置全部边框
                        'style' => \PHPExcel_Style_Border::BORDER_THIN //粗的是thick
                    ),
                ),
            );
            $objPHPExcel->getActiveSheet()->getStyle( 'A1:P'.$count)->applyFromArray($styleThinBlackBorderOutline);
        }
        if($type == 16 || $type == 17){
            $objPHPExcel->getActiveSheet()->mergeCells('A1:P1'); //合并居中
            $zm = array('A2', 'B2', 'C2', 'D2', 'E2', 'F2', 'G2', 'H2', 'I2', 'J2', 'K2','L2','M2','N2','O2','P2');
            $objPHPExcel->getActiveSheet()->getStyle('A1:P1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFFF');//设置第一行
            $objPHPExcel->getActiveSheet()->getStyle('A2:P2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('6495ED');//设置第一行背景色
            $i = 3;
            $count=count($data)+2;
            if($type == 16){
                $objSheet->setCellValue('A1',"陌陌常规召回-麦迪克-".date('H')."点实时数据");
            }else{
                $objSheet->setCellValue('A1',"陌陌RTA召回-麦迪克-".date('H')."点实时数据");
            }
            $objPHPExcel->getActiveSheet()->getStyle('A1:P1')->getFont()->setSize(18);
            $objPHPExcel->getActiveSheet()->getStyle('A1:P1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A1:P'.$count)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); //水平居中
            $styleThinBlackBorderOutline = array(
                'borders' => array(
                    'allborders' => array( //设置全部边框
                        'style' => \PHPExcel_Style_Border::BORDER_THIN //粗的是thick
                    ),
                ),
            );
            $objPHPExcel->getActiveSheet()->getStyle( 'A1:P'.$count)->applyFromArray($styleThinBlackBorderOutline);
        }
        if($type == 18){
            $objPHPExcel->getActiveSheet()->mergeCells('A1:L1'); //合并居中
            $zm = array('A2', 'B2', 'C2', 'D2', 'E2', 'F2', 'G2', 'H2', 'I2', 'J2', 'K2','L2');
            $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFFF');//设置第一行
            $objPHPExcel->getActiveSheet()->getStyle('A2:L2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('6495ED');//设置第一行背景色
            $i = 3;
            $count=count($data)+2;
            $objSheet->setCellValue('A1',"众安保险-百万防癌医疗险-".date('H')."点实时数据");
            $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getFont()->setSize(18);
            $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A1:L'.$count)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); //水平居中
            $styleThinBlackBorderOutline = array(
                'borders' => array(
                    'allborders' => array( //设置全部边框
                        'style' => \PHPExcel_Style_Border::BORDER_THIN //粗的是thick
                    ),
                ),
            );
            $objPHPExcel->getActiveSheet()->getStyle( 'A1:L'.$count)->applyFromArray($styleThinBlackBorderOutline);
        }
        if($type == 19||$type == 20){
            $objPHPExcel->getActiveSheet()->mergeCells('A1:L1'); //合并居中
            $zm = array('A2', 'B2', 'C2', 'D2', 'E2', 'F2', 'G2', 'H2', 'I2', 'J2', 'K2','L2');
            $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFFF');//设置第一行
            $objPHPExcel->getActiveSheet()->getStyle('A2:L2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('6495ED');//设置第一行背景色
            $i = 3;
            $count=count($data)+2;
            if($type == 19){
                $objSheet->setCellValue('A1',"亲亲-".date('H')."点实时数据");
            }else{
                $objSheet->setCellValue('A1',"趣约聊-".date('H')."点实时数据");
            }

            $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getFont()->setSize(18);
            $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A1:L'.$count)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); //水平居中
            $styleThinBlackBorderOutline = array(
                'borders' => array(
                    'allborders' => array( //设置全部边框
                        'style' => \PHPExcel_Style_Border::BORDER_THIN //粗的是thick
                    ),
                ),
            );
            $objPHPExcel->getActiveSheet()->getStyle( 'A1:L'.$count)->applyFromArray($styleThinBlackBorderOutline);
        }
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(25);
        $z = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O',
            'P', 'Q', 'R','S','T','U','V','W');

        if (empty($field)) {
            return false;
        }

        $title = $field['value'];
        $key = $field['key'];
        $colsWidth = [];
        foreach ($title as $k => $v) {
            $objSheet->setCellValue($zm[$k], $v);
        }

        foreach ($data as $k => $v) {
            foreach ($key as $kk => $vv) {
                if (isset($colsWidth[$z[$kk]])) {
                    $colsWidth[$z[$kk]] = $colsWidth[$z[$kk]] > strlen($v["{$vv}"]) ? $colsWidth[$z[$kk]] : strlen($v["{$vv}"]);
                } else {
                    $colsWidth[$z[$kk]] = strlen($v["{$vv}"]);
                }
                $objSheet->setCellValueExplicit($z[$kk] . $i, $v["{$vv}"], \PHPExcel_Cell_DataType::TYPE_STRING);
            }
            $i++;
            if($v['project_name'] == '合计'){
                $line = $k+2;
                $objPHPExcel->getActiveSheet()->getStyle('A'.$line.':J'.$line)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFA500');//设置第一行背景色
            }
            if($type == 101 && $v['stat_date'] == '合计'){
                $line = $k+2;
                $objPHPExcel->getActiveSheet()->getStyle('A'.$line.':N'.$line)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFA500');//设置第一行背景色
            }
            if($type == 14 && $v['stat_date'] == '合计'){
                $line = $k+2;
                $objPHPExcel->getActiveSheet()->getStyle('A'.$line.':M'.$line)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('3CB371');//设置第一行背景色
            }
            if($type == 102 && $v['stat_date'] == '合计'){
                $line = $k+2;
                $objPHPExcel->getActiveSheet()->getStyle('A'.$line.':O'.$line)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFA500');//设置第一行背景色
            }
            if($type == 6 && $v['ad_name'] == '总计'){
                $line = $k+2;
                $objPHPExcel->getActiveSheet()->getStyle('C'.$line.':J'.$line)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFE4B5');//设置第一行背景色
            }
            if($type == 301 && $v['ad_name'] == '汇总'){
                $line = $k+3;
                $objPHPExcel->getActiveSheet()->getStyle('A'.$line.':Q'.$line)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('F0E68C');//设置第一行背景色
            }
            if($type == 301 && $v['title_name']=='小猴AI课：语文单科'){
                $line = $k+3;
                $objPHPExcel->getActiveSheet()->getStyle('A'.$line.':Q'.$line)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('8FBC8F');//设置第一行背景色
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$line.':Q'.$line); //合并居中
                $objSheet->setCellValue('A'.$line, '小猴AI课：语文单科');
                $objPHPExcel->getActiveSheet()->getRowDimension($line)->setRowHeight(25);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$line.':Q'.$line)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); //水平居中
            }
            if($type == 301 && $v['title_name']=='小猴AI课：数学单科'){
                $line = $k+3;
                $objPHPExcel->getActiveSheet()->getStyle('A'.$line.':Q'.$line)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('ADD8E6');//设置第一行背景色
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$line.':Q'.$line); //合并居中
                $objSheet->setCellValue('A'.$line, '小猴AI课：数学单科');
                $objPHPExcel->getActiveSheet()->getRowDimension($line)->setRowHeight(25);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$line.':Q'.$line)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); //水平居中
            }
            if($type == 301 && $v['ad_name'] == '头条搜索'){
                $line = $k+3;
                $objPHPExcel->getActiveSheet()->getStyle('A'.$line.':Q'.$line)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('B0C4DE');//设置第一行背景色
            }
            if($type == 7 && $v['ad_name'] == '汇总'){
                $line = $k+2;
                $objPHPExcel->getActiveSheet()->getStyle('D'.$line.':k'.$line)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('F0E68C');//设置第一行背景色
            }
            if($type == 7 && $v['ad_name'] == '小马AI课 合计'){
                $line = $k+2;
                $objPHPExcel->getActiveSheet()->getStyle('D'.$line.':k'.$line)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('32CD32');//设置第一行背景色
            }
            if(($type == 8 && $v['ad_name'] == '总计')||($type == 801 && $v['ad_name'] == '总计')){
                $line = $k+2;
                $objPHPExcel->getActiveSheet()->getStyle('C'.$line.':J'.$line)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('8FBC8F');//设置第一行背景色
            }
            if(($type == 802 && $v['ad_name'] == '总计')){
                $line = $k+3;
                $objPHPExcel->getActiveSheet()->getStyle('C'.$line.':K'.$line)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');//设置第一行背景色
            }
            if(($type == 10 && $v['ad_name'] == '合计')){
                $line = $k+3;
                $objPHPExcel->getActiveSheet()->getStyle('A'.$line.':L'.$line)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');//设置第一行背景色
            }
            if(($type == 10 && $v['ad_name'] == '总计')){
                $line = $k+3;
                $objPHPExcel->getActiveSheet()->getStyle('A'.$line.':L'.$line)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('2E8B57');//设置第一行背景色
            }
            if($type == 11 && $v['ah_date'] == '汇总'){
                $line = $k+3;
                $objPHPExcel->getActiveSheet()->getStyle('A'.$line.':F'.$line)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFA500');//设置第一行背景色
            }
            if($type == 13 && $v['cp'] == '环比'){
                $line = $k+2;
                $objPHPExcel->getActiveSheet()->getStyle('A'.$line.':Q'.$line)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('90EE90');//设置第一行背景色
            }
            if($type == 15 && $v['optimizer_name'] == '汇总'){
                $line = $k+3;
                $objPHPExcel->getActiveSheet()->getStyle('A'.$line.':P'.$line)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('6495ED');//设置第一行背景色
            }
            if(($type == 16 && $v['optimizer_name'] == '汇总')||($type == 17 && $v['optimizer_name'] == '汇总')){
                $line = $k+3;
                $objPHPExcel->getActiveSheet()->getStyle('A'.$line.':R'.$line)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('6495ED');//设置第一行背景色
            }
            if(($type == 18 && $v['project_name'] == '汇总')||($type == 19 && $v['project_name'] == '汇总')||($type == 20 && $v['project_name'] == '汇总')){
                $line = $k+3;
                $objPHPExcel->getActiveSheet()->getStyle('A'.$line.':L'.$line)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('6495ED');//设置第一行背景色
            }
        }
        foreach($colsWidth as $k=>$v){
            if($type == 1 ||$type == 14) {
                $objSheet->getColumnDimension($k)->setWidth($v + 8);
            }
            if($type == 2 ||$type == 3||$type == 4||$type == 401||$type == 6 || $type == 802 ||$type == 11||$type == 7||$type == 12||$type == 13||$type == 15||$type == 16||$type == 17||$type == 18||$type == 19||$type == 20){
                $objSheet->getColumnDimension($k)->setWidth($v + 5);
            }
            if($type == 301){
                if($k == 'G'){
                    $objSheet->getColumnDimension($k)->setWidth($v + 6);
                }else{
                    $objSheet->getColumnDimension($k)->setWidth($v + 5);
                }
            }
            if($type == 8 || $type == 801 ||$type ==9||$type == 102 ||$type == 10){
                $objSheet->getColumnDimension($k)->setWidth($v + 2);
            }
            if($type == 5 ||$type == 101 ){
                // $objSheet->getColumnDimension($k)->setWidth($v);
                $objSheet->getColumnDimension($k)->setAutoSize(true); //内容自适应
            }
        }

        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $filename . '.xlsx"');
        header("Content-Disposition:attachment;filename={$filename}.xlsx");
        header('Cache-Control: max-age=0');
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    /**
     * User: lxm
     * Title:猿辅导素材分日数据
     * Date: 2021/3/26
     * @param $data
     * @param $field
     * @param $filename
     * @param int $type
     * @return bool
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public static function exportDayLinkExcel($data, $field, $filename, $type=1)
    {
        $objPHPExcel = new \PHPExcel();
        $objSheet = $objPHPExcel->getActiveSheet(0);
        $objSheet->setTitle($filename);
        $objPHPExcel->getDefaultStyle()->getFont()->setName('微软雅黑');//字体
        if($type == 901||$type == 902 ||$type == 903||$type == 904||$type == 905||$type == 906||$type == 907|| $type == 908|| $type == 909|| $type == 910) { //猿辅导
            $zm = array('A1', 'B1', 'C1', 'D1', 'E1', 'F1', 'G1', 'H1', 'I1');
            $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');//设置第一行背景色
            $i = 2;
        }

        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(25);
        $z = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O',
            'P', 'Q', 'R');

        if (empty($field)) {
            return false;
        }

        $title = $field['value'];
        $key = $field['key'];
        $colsWidth = [];
        foreach ($title as $k => $v) {
            $objSheet->setCellValue($zm[$k], $v);
        }

        foreach ($data as $k => $v) {
            foreach ($key as $kk => $vv) {
                if (isset($colsWidth[$z[$kk]])) {
                    $colsWidth[$z[$kk]] = $colsWidth[$z[$kk]] > strlen($v["{$vv}"]) ? $colsWidth[$z[$kk]] : strlen($v["{$vv}"]);
                } else {
                    $colsWidth[$z[$kk]] = strlen($v["{$vv}"]);
                }
                $objSheet->setCellValueExplicit($z[$kk] . $i, $v["{$vv}"], \PHPExcel_Cell_DataType::TYPE_STRING);
            }
            $i++;
        }
        foreach($colsWidth as $k=>$v){
            if($type == 901||$type == 902 ||$type == 903||$type == 904||$type == 905||$type == 906||$type == 907|| $type == 908|| $type == 909|| $type == 910){
                $objSheet->getColumnDimension($k)->setWidth($v + 5);
            }
        }

        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $filename . '.xlsx"');
        header("Content-Disposition:attachment;filename={$filename}.xlsx");
        header('Cache-Control: max-age=0');
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }


}