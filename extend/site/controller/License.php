<?php
namespace site\controller;

use common\controller\AuthController;
use common\service\interfaces\IdentityService;
use common\service\interfaces\LicenseService;
use think\App;
use think\Exception;
use think\facade\Log;

abstract class License extends AuthController
{
    /**
     * @var LicenseService
     * @see LicenseService
     */
    protected $licenseService;

    public function __construct(App $app, LicenseService $licenseService)
    {
        parent::__construct($app);
        $this->licenseService = $licenseService;
    }

    public function getLicenseList()
    {
        $page = $this->request->param('page');
        $limit = $this->request->param('limit');
        $cname = $this->request->param('cname');
        $ename = $this->request->param('ename');
        $result = $this->licenseService->getLicenseListByPage($cname, $ename, $page ?: 1, $limit ?: 20);
        return $this->result($result);
    }

    public function createLicense()
    {
        $cname = $this->request->param('cname');
        $eid = $this->request->param('eid');
        $ename = $this->request->param('ename');
        $sid = $this->request->param('sid');
        $numberList = $this->request->param('number/a');
        $startDateList = $this->request->param('start_date/a');
        $endDateList = $this->request->param('end_date/a');
        $effectiveTime = $this->request->param('effective_time');
        $expireTime = $this->request->param('expire_time');
        $remark = $this->request->param('remark');
        try {
            $result = $this->licenseService->createLicense($cname, $eid, $ename, $sid, $numberList, $startDateList, $endDateList, $effectiveTime, $expireTime, $remark);
            return $this->result($result);
        } catch (Exception $exception) {
            return $this->result('', $exception->getCode(), $exception->getMessage());
        }
    }

    public function extendLicense()
    {
        $licenseId = $this->request->param('license_id');
        $numberList = $this->request->param('number/a');
        $startDateList = $this->request->param('start_date/a');
        $endDateList = $this->request->param('end_date/a');
        $effectiveTime = $this->request->param('effective_time');
        $expireTime = $this->request->param('expire_time');
        $remark = $this->request->param('remark');
        try {
            $result = $this->licenseService->extendLicense($licenseId, $numberList, $startDateList, $endDateList, $effectiveTime, $expireTime, $remark);
            return $this->result($result);
        } catch (Exception $exception) {
            return $this->result('', $exception->getCode(), $exception->getMessage());
        }
    }

    public function getLicense()
    {
        $licenseId = $this->request->param('license_id');
        $result = $this->licenseService->getLicenseById($licenseId);
        return $this->result($result);
    }

    public function deleteLicense()
    {
        $licenseId = $this->request->param('license_id');
        $result = $this->licenseService->deleteLicense($licenseId);
        return $this->result($result);
    }

    public function download()
    {
        $licenseId = $this->request->param('license_id');
        try {
            $licenseFile = $this->licenseService->getLicenseFileById($licenseId);
            Log::info("文件名：".basename($licenseFile));
            return download($licenseFile, basename($licenseFile));
        } catch (Exception $exception) {
            return "下载失败：" . $exception->getMessage();
        }
    }

    public function upload()
    {
        $file = $this->request->file('eFile');
        try {
            $result = $this->licenseService->upload($file);
            return $this->result($result);
        } catch (Exception $exception) {
            return $this->result('', $exception->getCode(), $exception->getMessage());
        }
    }
}