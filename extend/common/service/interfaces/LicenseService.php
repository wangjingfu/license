<?php
namespace common\service\interfaces;

interface LicenseService
{
    /**
     * 查询license
     * @param string $cname 客户名称
     * @param string $ename 企业名称
     * @param int $page 页码
     * @param int $limit 偏移量
     * @return array
     */
    public function getLicenseListByPage($cname, $ename, $page, $limit = 20);

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
    public function createLicense($cname, $eid, $ename, $sid, array $robotNumberList, array $robotStartDateList, array $robotEndDateList, $effectiveTime, $expireTime, $remark = '');

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
    public function extendLicense($licenseId, array $robotNumberList, array $robotStartDateList, array $robotEndDateList, $effectiveTime, $expireTime, $remark = '');

    /**
     * 删除license
     * @param int $licenseId
     * @return mixed
     */
    public function deleteLicense($licenseId);

    /**
     * 根据ID获取License
     * @param int $licenseId
     * @return array
     */
    public function getLicenseById($licenseId);

    /**
     * 获取License文件地址
     * @param $licenseId
     * @return string
     */
    public function getLicenseFileById($licenseId);
}