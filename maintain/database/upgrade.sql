CREATE TABLE IF NOT EXISTS `ai_user` (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` varchar(200) NOT NULL DEFAULT '' COMMENT '用户名',
    `passwd` varchar(200) NOT NULL DEFAULT '' COMMENT '密码',
    `nickname` varchar(200) NOT NULL DEFAULT '' COMMENT '昵称',
    `realname` varchar(200) NOT NULL DEFAULT '' COMMENT '真实姓名',
    `status` tinyint(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态: 1可用 0 不可用',
    `mobile` varchar(20) NOT NULL DEFAULT '' COMMENT '手机号',
    `last_login` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '最后登录时间',
    `last_ip` varchar(20) NOT NULL DEFAULT '' COMMENT 'ip',
    `create_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '注册时间',
    `update_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '最后编辑时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `index_unique_username` (`username`) USING BTREE COMMENT '用户名称唯一索引'
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='用户表';