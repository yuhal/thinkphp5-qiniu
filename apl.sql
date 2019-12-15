/*
 Navicat Premium Data Transfer

 Source Server         : api.yuhal.com
 Source Server Type    : MySQL
 Source Server Version : 50726
 Source Host           : localhost
 Source Database       : apl

 Target Server Type    : MySQL
 Target Server Version : 50726
 File Encoding         : utf-8

 Date: 12/15/2019 23:29:59 PM
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `api_user`
-- ----------------------------
DROP TABLE IF EXISTS `api_user`;
CREATE TABLE `api_user` (
  `id` int(11) unsigned NOT NULL,
  `mobile` bigint(15) NOT NULL,
  `appid` varchar(200) NOT NULL COMMENT 'appid',
  `appsercet` varchar(100) NOT NULL COMMENT 'app密码',
  `timestamp` bigint(20) NOT NULL COMMENT '时间戳',
  `create_at` bigint(20) NOT NULL,
  `update_at` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

SET FOREIGN_KEY_CHECKS = 1;
