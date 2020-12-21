<?php
namespace site\service;

use common\constant\Constant;
use common\dao\LicenseDao;
use common\service\interfaces\LicenseService;
use common\utils\safe\Rsa;
use think\Exception;
use think\facade\Config;
use think\facade\Filesystem;
use think\facade\Log;
use think\File;

class LicenseServiceImpl implements LicenseService
{
    protected $licenseDao;

    public function __construct()
    {
        $this->licenseDao = new LicenseDao();
    }

    /**
     * 查询license
     * @param string $cname 客户名称
     * @param string $ename 企业名称
     * @param int $page 页码
     * @param int $limit 偏移量
     * @return array
     */
    public function getLicenseListByPage($cname, $ename, $page, $limit = 20)
    {
        $where = [];
        if ($cname) {
            $where[] = ['cname', 'LIKE', "%{$cname}%"];
        }
        if ($ename) {
            $where[] = ['ename', 'LIKE', "%{$ename}%"];
        }
        $licenses = [];
        $total = $this->licenseDao->getCountByWhere($where);
        if ($total) {
            $currentTime = time();
            $licenses = $this->licenseDao->getList($where, '*', $page, $limit, "create_time DESC");
            foreach ($licenses as $key => $license) {
                $licenses[$key]['download'] = 1;
                if ($license['expire_time'] < $currentTime) {
                    $licenses[$key]['download'] = 0;
                } else {
                    $robotDetail = json_decode($license['detail'], true);
                    $robotTotal = 0;
                    foreach ($robotDetail as $robot) {
                        if ($robot['end_time'] > $currentTime) {
                            $robotTotal += $robot['num'];
                        }
                    }
                    if (empty($robotTotal)) {
                        $licenses[$key]['download'] = 0;
                    }
                }
                $licenses[$key]['extend'] = $this->licenseDao->getCountByWhere([
                    ['eid', '=', $license['eid']],
                    ['sid', '=', $license['sid']],
                    ['id', '>', $license['id']],
                ]) ? 0 : 1;
                $licenses[$key]['effective_time'] = date("Y-m-d", $license['effective_time']);
                $licenses[$key]['expire_time'] = date("Y-m-d", $license['expire_time']);
            }
        }
        return [
            'total' => $total,
            'item' => $licenses,
        ];
    }

    /**
     * 创建License
     * @param string $cname 客户名称
     * @param int $eid 企业ID
     * @param string $ename 企业名称
     * @param string $sid 机器码
     * @param array $robotNumberList 机器人数量
     * @param array $robotStartDateList 机器人开始时间
     * @param array $robotEndDateList 机器人到期时间
     * @param string $effectiveTime License文件生效时间
     * @param string $expireTime License文件到期时间
     * @param string $remark 备注
     */
    public function createLicense($cname, $eid, $ename, $sid, array $robotNumberList, array $robotStartDateList, array $robotEndDateList, $effectiveTime, $expireTime, $remark = '')
    {
        $dateList = [];
        foreach ($robotStartDateList as $key => $robotStartDate) {
            $robotEndDate = $robotEndDateList[$key];
            if (!$this->checkDate($robotStartDate, $robotEndDate)) {
                throw new Exception("机器人有效期：开始时间不能大于到期时间", 1);
            }
            $dateList[] = "{$robotStartDate}:{$robotEndDate}";
        }

        $dateList = array_unique($dateList);
        Log::info("dateList => ". var_export($dateList, true));
        if (count($dateList) !== count($robotStartDateList)) {
            throw new Exception("不允许重复添加开始时间、结束时间相同的记录", 1);
        }

        if (!$this->checkDate($effectiveTime, $expireTime)) {
            throw new Exception("License文件有效期：开始时间不能大于到期时间", 1);
        }

        $data = [];
        $data['cname'] = $cname;
        $data['eid'] = $eid;
        $data['ename'] = $ename;
        $data['sid'] = $sid;
        $data['expire_time'] = currentDateEndTime($expireTime);
        $data['total'] = array_sum($robotNumberList);

        $detail = [];
        $robotStartDate = '';
        foreach ($robotNumberList as $key => $robotNumber) {
            if ($robotNumber < 1 || $robotNumber > 1000) {
                throw new Exception("机器人数量：只允许输入1-1000之间的整数", 1);
            }
            if (empty($key)) {
                $robotStartDate = $robotStartDateList[$key];
            }
            $endDate = $robotEndDateList[$key];
            $detail[] = [
                'num' => $robotNumber,
                'start_time' => currentDateBeginTime($robotStartDate),
                'end_time' => currentDateEndTime($endDate),
            ];
        }

        if (count($detail) > 20) {
            throw new Exception('机器人有效分组最多为20个，当前为：'. count($detail) .'个', 1);
        }

        if ($data['total'] > 1000) {
            throw new Exception("机器人总数不能超过1000", 1);
        }

        $data['detail'] = $detail;
        $data['effective_time'] = currentDateBeginTime($robotStartDate);
        $license = $this->generate($data);
        $data['detail'] = json_encode($detail, JSON_UNESCAPED_UNICODE);
        $data['license'] = $license;
        $data['remark'] = $remark;
        $data['create_time'] = time();

        Log::info("正在添加License到数据库...");
        Log::info($data);
        return $this->licenseDao->insertGetId($data);
    }

    /**
     * 扩展License
     * @param int $licenseId License主键
     * @param array $robotNumberList 机器人数量
     * @param array $robotStartDateList 机器人开始时间
     * @param array $robotEndDateList 机器人到期时间
     * @param string $effectiveTime License文件生效时间
     * @param string $expireTime License文件到期时间
     * @param string $remark 备注
     */
    public function extendLicense($licenseId, array $robotNumberList, array $robotStartDateList, array $robotEndDateList, $effectiveTime, $expireTime, $remark = '')
    {
        $licenseInfo = $this->licenseDao->getInfoById($licenseId);
        if (empty($licenseInfo)) {
            throw new Exception("license不存在", 1);
        }

        foreach ($robotStartDateList as $key => $robotStartDate) {
            $robotEndDate = $robotEndDateList[$key];
            if (!$this->checkDate($robotStartDate, $robotEndDate)) {
                throw new Exception("机器人有效期：开始时间不能大于到期时间", 1);
            }
        }

        if (!$this->checkDate($effectiveTime, $expireTime)) {
            throw new Exception("License文件有效期：开始时间不能大于到期时间", 1);
        }

        $data = [];
        $data['cname'] = $licenseInfo['cname'];
        $data['eid'] = $licenseInfo['eid'];
        $data['ename'] = $licenseInfo['ename'];
        $data['sid'] = $licenseInfo['sid'];
        $data['expire_time'] = currentDateEndTime($expireTime);
        $data['total'] = array_sum($robotNumberList);

        $robotDetail = json_decode($licenseInfo['detail'], true);
        $currentTime = time();
        $detail = [];
        foreach ($robotDetail as $robot) {
            if ($robot['end_time'] > $currentTime) {
                $detail[] = $robot;
                $data['total'] += $robot['num'];
            }
        }

        $robotStartDate = '';
        foreach ($robotNumberList as $key => $robotNumber) {
            if ($robotNumber < 1 || $robotNumber > 1000) {
                throw new Exception("机器人数量：只允许输入1-1000之间的整数", 1);
            }
            if (empty($key)) {
                $robotStartDate = $robotStartDateList[$key];
            }
            $endDate = $robotEndDateList[$key];
            $detail[] = [
                'num' => $robotNumber,
                'start_time' => currentDateBeginTime($robotStartDate),
                'end_time' => currentDateEndTime($endDate),
            ];

        }

        $dateList = [];
        foreach ($detail as $robot) {
            $startDate = date('Y-m-d', $robot['start_time']);
            $endDate = date('Y-m-d', $robot['end_time']);
            $dateList[] = "{$startDate}:{$endDate}";
        }
        $dateList = array_unique($dateList);

        Log::info("dateList => ". var_export($dateList, true));
        Log::info("detail => ". var_export($detail, true));
        if (count($dateList) !== count($detail)) {
            throw new Exception("不允许重复添加开始时间、结束时间相同的记录", 1);
        }

        if (count($detail) > 20) {
            throw new Exception('机器人有效分组最多为20个，当前为：'. count($detail) .'个', 1);
        }

        if ($data['total'] > 1000) {
            throw new Exception("机器人总数不能超过1000", 1);
        }

        $data['detail'] = $detail;
        $data['effective_time'] = currentDateBeginTime($robotStartDate);
        $license = $this->generate($data);
        $data['detail'] = json_encode($detail, JSON_UNESCAPED_UNICODE);
        $data['license'] = $license;
        $data['remark'] = $remark;
        $data['create_time'] = time();

        Log::info("正在扩容License到数据库...");
        Log::info($data);
        return $this->licenseDao->insertGetId($data);
    }

    /**
     * 删除license
     * @param int $licenseId
     * @return mixed
     */
    public function deleteLicense($licenseId)
    {
        Log::info("正在删除license：{$licenseId}");
        return LicenseDao::where('id', $licenseId)->useSoftDelete('delete_time',time())->delete();
    }

    /**
     * 根据ID获取License
     * @param int $licenseId
     * @return array
     */
    public function getLicenseById($licenseId)
    {
        $licenseInfo = $this->licenseDao->getInfoById($licenseId);
        if ($licenseInfo) {
            $detail = json_decode($licenseInfo['detail'], true);
            foreach ($detail as $key => $value) {
                $detail[$key]['start_time'] = date('Y-m-d', $value['start_time']);
                $detail[$key]['end_time'] = date('Y-m-d', $value['end_time']);
            }
            $licenseInfo['detail'] = $detail;
            $licenseInfo['effective_time'] = date('Y-m-d', $licenseInfo['effective_time']);
            $licenseInfo['expire_time'] = date('Y-m-d', $licenseInfo['expire_time']);
        }
        return $licenseInfo;
    }

    /**
     * 获取License文件地址
     * @param $licenseId
     * @return string
     */
    public function getLicenseFileById($licenseId)
    {
        $license = $this->licenseDao->getInfoById($licenseId);
        if (empty($license)) {
            throw new Exception("授权信息不存在或已被删除", 1);
        }
        $robotDetail = json_decode($license['detail'], true);
        $currentTime = time();
        $detail = [];
        $total = 0;
        foreach ($robotDetail as $robot) {
            if ($robot['end_time'] > $currentTime) {
                $detail[] = $robot;
                $total += $robot['num'];
            }
        }
        $license['total'] = $total;
        $license['detail'] = $detail;
        $licenseContent = $this->generate($license);
        $licenseFileObject = $this->generateLicenseFile($license['eid'], $license['ename'], $license['expire_time']);
        $licenseFile = $licenseFileObject->getRealPath();
        @file_put_contents($licenseFile, $licenseContent);
        Log::info("正在更新License文件地址：{$licenseFile}");
        $this->licenseDao->updateByWhere([
            'license_file' => $licenseFile,
        ], [
            'id' => $license['id']
        ]);
        return $licenseFile;
    }

    /**
     * 生成License
     * @param array $license
     * @return string
     */
    protected function generate($license)
    {
        $data = [];
        $data['sid'] = $license['sid'];
        $data['effective_time'] = $license['effective_time'];
        $data['expire_time'] = $license['expire_time'];
        $data['enterprise_info'] = [
            "id" => $license['eid'],
            "name" => $license['ename'],
        ];
        $data['robot'] = [
            'total' => $license['total'],
            'detail' => $license['detail'],
        ];
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        return $this->encrypt($json);
    }

    /**
     * 加密
     * @param string $data
     * @return string
     */
    protected function encrypt($data)
    {
        Log::info("加密源数据为：{$data}");
        $privateKey = Config::get("app.license_private_key");
        $rsa = new Rsa(2048);
        $license = $rsa->privateEncrypt($data, $privateKey);
        Log::info("加密结果：{$license}");
        return $license;
    }

    protected function generateLicenseFile($eid, $ename, $expireTime)
    {
        $savePath = $this->getLicenseRootPath() . $eid . DIRECTORY_SEPARATOR;
        createDirectory($savePath);
        $filename = "emi-ai-license-{$ename}-文件有效期截止". date("Ymd", $expireTime) .".emi";
        Log::info("License文件地址：{$savePath}{$filename}");
        @fopen($savePath . $filename, "w");
        $licenseFile = new File($savePath . $filename, false);
        return $licenseFile;
    }

    protected function getLicenseRootPath()
    {
        return app()->getRootPath() . "maintain/license/";
    }

    public function upload(File $file)
    {
        validate([
            'image' => 'filesize:10240|fileExt:info'
        ])->check([$file]);
        Log::info("File MIME : ". $file->getMime());
        $savename = Filesystem::putFile('eFile', $file);
        $filePath = Constant::LICENSE_FILE_SAVE_PATH . $savename;
        Log::info("上传文件地址为：{$filePath}");

        $eFile = new File($filePath);
        $handle = $eFile->openFile("r");
        $content = $handle->fread(filesize($filePath));
        Log::info("企业信息文件内容为：{$content}");
        $content = str_replace(['EID:', 'ENAME:', 'GUID:'], '', $content);
        $enterpriseInfo = explode('#@#', $content);
        $eid = $enterpriseInfo[0] ?? '';
        $ename = $enterpriseInfo[1] ?? '';
        $sid = $enterpriseInfo[2] ?? '';
        return [
            'eid' => trim($eid),
            'ename' => trim($ename),
            'sid' => trim($sid),
        ];
    }

    protected function checkDate($startDate, $endDate)
    {
        return currentDateBeginTime($startDate) <= currentDateBeginTime($endDate);
    }
}