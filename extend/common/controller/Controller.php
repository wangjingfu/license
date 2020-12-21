<?php
namespace common\controller;

use app\BaseController;

class Controller extends BaseController
{
    protected function initialize()
    {
        parent::initialize();
    }

    protected function result($data, $status = 0, $info = "success", $code = 200, $header = [], $options = [])
    {
        $response = [];
        $response['status'] = $status;
        $response['info'] = $info;
        $response['data'] = $data;
        return json($response, $code, $header, $options);
    }
}