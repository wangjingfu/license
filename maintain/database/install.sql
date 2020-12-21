CREATE TABLE IF NOT EXISTS `ai_license` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `eid` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '企业ID',
  `ename` varchar(255) NOT NULL DEFAULT '' COMMENT '企业名称',
  `cname` varchar(255) NOT NULL DEFAULT '' COMMENT '客户名称',
  `sid` mediumtext NOT NULL COMMENT '机器码',
  `effective_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'license文件生效时间',
  `expire_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'license文件过期时间',
  `total` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '机器人总数',
  `detail` mediumtext NOT NULL COMMENT '机器人配置详情',
  `remark` mediumtext NOT NULL COMMENT '备注',
  `license` mediumtext NOT NULL COMMENT 'license',
  `license_file` varchar(255) NOT NULL DEFAULT '' COMMENT 'license文件地址',
  `delete_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '软删除',
  `create_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='机器人授权管理表';