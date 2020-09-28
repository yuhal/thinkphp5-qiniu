/*
 Navicat Premium Data Transfer

 Source Server         : mysql5
 Source Server Type    : MySQL
 Source Server Version : 50728
 Source Host           : localhost:3305
 Source Schema         : api

 Target Server Type    : MySQL
 Target Server Version : 50728
 File Encoding         : 65001

 Date: 28/09/2020 11:45:13
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for api_user
-- ----------------------------
DROP TABLE IF EXISTS `api_user`;
CREATE TABLE `api_user` (
  `id` int(11) unsigned NOT NULL,
  `mobile` bigint(15) NOT NULL,
  `appid` varchar(200) NOT NULL COMMENT 'appid',
  `appsercet` varchar(100) NOT NULL COMMENT 'app密钥',
  `timestamp` bigint(20) NOT NULL COMMENT '时间戳',
  `create_at` bigint(20) NOT NULL,
  `update_at` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of api_user
-- ----------------------------
BEGIN;
INSERT INTO `api_user` VALUES (1, 15888888888, 'admin', '000000', 1544087223, 1544087223, 1544087223);
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
