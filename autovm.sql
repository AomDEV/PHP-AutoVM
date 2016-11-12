-- phpMyAdmin SQL Dump
-- version 4.2.11
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Aug 12, 2016 at 11:58 AM
-- Server version: 5.6.21
-- PHP Version: 5.6.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `autovm`
--

-- --------------------------------------------------------

--
-- Table structure for table `account`
--

CREATE TABLE IF NOT EXISTS `account` (
`uid` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(200) NOT NULL,
  `email` varchar(100) NOT NULL,
  `balance` double(64,2) NOT NULL,
  `phone` varchar(11) NOT NULL,
  `last_token` varchar(80) NOT NULL,
  `last_login` varchar(15) NOT NULL,
  `status` int(1) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `account`
--

INSERT INTO `account` (`uid`, `username`, `password`, `email`, `balance`, `phone`, `last_token`, `last_login`, `status`) VALUES
(1, 'lomerduster', '$SHA512$463be2d237ea01dd614f217195e640df$8f230736eb2ceffc1dffb89659e7282863295d86d11d690ef50e4594dea3ef104a4c33d8f96b5672b891c89109e7af40c4101b360158d61c22e845444c64ba12', 'siriwat576@gmail.com', 2000.00, '66956091008', '1f347e1fcef8b62e0db86cc29e4ba285fbf32d06028cb3ba6af4811759ca7ed3', '1470482070', 0);

-- --------------------------------------------------------

--
-- Table structure for table `a_billing_account`
--

CREATE TABLE IF NOT EXISTS `a_billing_account` (
`id` int(255) NOT NULL,
  `username` varchar(25) NOT NULL,
  `password` varchar(80) NOT NULL,
  `account_name` varchar(20) NOT NULL,
  `account_type` varchar(12) NOT NULL,
  `signature` varchar(60) NOT NULL,
  `fee` double(64,2) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `a_billing_account`
--

INSERT INTO `a_billing_account` (`id`, `username`, `password`, `account_name`, `account_type`, `signature`, `fee`) VALUES
(1, 'bank', '315-0-6062-68', 'SURINTORN JANKE', 'ktb', 'undefinded', 0.00),
(2, 'bank', '316-2-2564-82', 'PINAI JANKE', 'kbank', 'undefinded', 0.00),
(3, 'siriwat576_api1.gmail.com', '5CAXTTM7KGY3DC98', 'siriwat576@gmail.com', 'paypal', 'AcmIEsRyktOZJ6l6fEkqZLRPsbq9ABY6xui2wyJfbtY5ZqzMmJGpo-C9', 0.00),
(4, 'payment_api1.ninepay.net', 'FUJEJ9YECLW3WKVQ', 'payment@ninepay.net', 'paypal.pro', 'An5ns1Kso7MWUdW4ErQKJJJ4qi4-AJig-QrED8OIZ-.EHyMWXfi0Q6Dk', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `a_billing_report`
--

CREATE TABLE IF NOT EXISTS `a_billing_report` (
`id` int(255) NOT NULL,
  `user_id` int(255) NOT NULL,
  `account_type` varchar(20) NOT NULL,
  `account_id` int(255) NOT NULL,
  `transaction_time` varchar(20) NOT NULL,
  `amount` double(9,2) NOT NULL,
  `transaction_id` varchar(30) NOT NULL,
  `status` int(1) NOT NULL,
  `last_report` varchar(20) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `a_billing_report`
--

INSERT INTO `a_billing_report` (`id`, `user_id`, `account_type`, `account_id`, `transaction_time`, `amount`, `transaction_id`, `status`, `last_report`) VALUES
(1, 1, 'bank', 1, '1468638300', 100.00, 'A16O07M20168207274755789a4ed15', 0, '1468638445'),
(2, 1, 'credit.card', 4, '1468937885', 1000.00, 'A19O07M2016269306989578e369d16', 1, '1468937885'),
(3, 1, 'credit.card', 4, '1469799142', 1000.00, 'A29O07M2016866608280579b5ae66c', 1, '1469799143');

-- --------------------------------------------------------

--
-- Table structure for table `a_payment_log`
--

CREATE TABLE IF NOT EXISTS `a_payment_log` (
`log_id` int(255) NOT NULL,
  `log_name` varchar(100) NOT NULL,
  `log_action` varchar(20) NOT NULL,
  `log_return` text NOT NULL,
  `log_ip` varchar(16) NOT NULL,
  `log_time` varchar(20) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `a_payment_log`
--

INSERT INTO `a_payment_log` (`log_id`, `log_name`, `log_action`, `log_return`, `log_ip`, `log_time`) VALUES
(1, 'billing:result.credit_card', 'result:success', 'f7f3d72c4f4fc28c0f0e121b70398f3538e87ec56594fb38776a14aae7f9435d573831561738b64f8855bcf2d0043920229d1497c7ae23c5dfc735b29f657229010824c6a6fe2aaa9e55349ecbd7ddc2939caaceb55bb30932227339f6f7bed48ad9c1da29a01010345ebbeae48b0974355e02f013c291392a66d5e381ef0b8cd902f792db19e9e3cf57d10ca51dbaa4eeb7e745acdc2e1af59994f8b5c3432dd9d8463914eac5e20590a4625f6d4cf3e367c05804d20267fec2cfce6b903d127ce4f9fdabc298d259d145dc3b5db77e1b16fcc5353babc3a5dd832da420863a', '::1', '1468913893'),
(2, 'billing:result.credit_card', 'result:success', 'f7f3d72c4f4fc28c0f0e121b70398f35abb5f0c95b9386b8f3f909dc165c757a7b56c27a1805f07017e0b28413b87ca162d00da41259a3edfec43d87b88de6233f98d0d2c510070deaedf8904541396f38375b96527e0f1e631b6e77416e67b5e40dca5e138b7bf06bd6c9f54ad239a5b0084dba903e88e22650a345f12be18fc97a06b670ca39c79c0c4f22443ae01ce76adfa3a79d57d8f486030bfc61125d43fcfa2f75cce09af2c5d764678de68d2946ba02d03f5947392be9f9d31bff193db14e224fe6595cac926d7136e8c1d834b43296c66a13e5e74dfc30c27435f8', '::1', '1468937884'),
(3, 'billing:result.credit_card', 'result:success', 'f7f3d72c4f4fc28c0f0e121b70398f352e00053adc7091653873be77dc2f58d281c6b813e3fc8dbe97449749db30a165920e3ab95b2d964875908e3cd0f29efcc0bafda96f7683063d664ec2ab2025af1e836b5b198fc6cdbac589d379000cd2360b77508774af4e8dbcd182b25de86b04aca7110aac427658453524844e6ce34d566f3644130a82a752ead1cef7979e8006674f37186313ac5778a1eb29c31000a28990264e4f2c794cbe93bc458c6b4e6d99cd26ea1a5bc7bf22ef277baa41cd6b8bae7be20cb41dfe72e21f55486892877ff106536dd2a08878fc6aad1942', '::1', '1469799140');

-- --------------------------------------------------------

--
-- Table structure for table `credit_card`
--

CREATE TABLE IF NOT EXISTS `credit_card` (
`card_id` int(255) NOT NULL,
  `user_id` int(255) NOT NULL,
  `card_no` varchar(70) NOT NULL,
  `card_exp` varchar(80) NOT NULL,
  `card_cvv2` varchar(80) NOT NULL,
  `card_type` varchar(20) NOT NULL,
  `first_name` varchar(30) NOT NULL,
  `last_name` varchar(40) NOT NULL,
  `phone` varchar(11) NOT NULL,
  `street` varchar(220) NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(16) NOT NULL,
  `country_code` varchar(2) NOT NULL,
  `zip_code` int(5) NOT NULL,
  `last_time` int(20) NOT NULL,
  `last_ip` varchar(20) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `credit_card`
--

INSERT INTO `credit_card` (`card_id`, `user_id`, `card_no`, `card_exp`, `card_cvv2`, `card_type`, `first_name`, `last_name`, `phone`, `street`, `city`, `state`, `country_code`, `zip_code`, `last_time`, `last_ip`) VALUES
(1, 1, 'a97b97f4ba35c7dff065a56baa1a17faa8087622e9154c391c252edac7c1eb8e', 'bc7e6dabe8ca1abba1b4445d2631d239', '123', 'visa', 'Aom', 'Siriwat', '0956091008', '267 Wareeratchadate Road', 'Yasothon', 'Yasothon', 'TH', 35000, 1469799142, '::1');

-- --------------------------------------------------------

--
-- Table structure for table `vm`
--

CREATE TABLE IF NOT EXISTS `vm` (
`vm_id` int(255) NOT NULL,
  `uid` int(255) NOT NULL,
  `hostname` varchar(20) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(80) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `memory` int(8) NOT NULL,
  `disk` int(8) NOT NULL,
  `region` int(2) NOT NULL,
  `os` int(2) NOT NULL,
  `core` int(2) NOT NULL,
  `status` int(1) NOT NULL,
  `expire` varchar(20) NOT NULL,
  `last_create` varchar(20) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `vm`
--

INSERT INTO `vm` (`vm_id`, `uid`, `hostname`, `username`, `password`, `ip`, `memory`, `disk`, `region`, `os`, `core`, `status`, `expire`, `last_create`) VALUES
(1, 1, 'vps.9cloud.com', 'administrator', 'Lomer123', '192.168.159.135', 512, 10240, 1, 2, 2, 0, '0', '0');

-- --------------------------------------------------------

--
-- Table structure for table `vm_os`
--

CREATE TABLE IF NOT EXISTS `vm_os` (
`os_id` int(2) NOT NULL,
  `os_name` varchar(255) NOT NULL,
  `os_code` varchar(12) NOT NULL,
  `os_product` varchar(8) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `vm_os`
--

INSERT INTO `vm_os` (`os_id`, `os_name`, `os_code`, `os_product`) VALUES
(1, 'Windows Server 2008 R2 x64', 'win2008x64', 'windows'),
(2, 'Ubuntu Server 16.04 LTS', 'ubntu1604lts', 'ubuntu');

-- --------------------------------------------------------

--
-- Table structure for table `vm_region`
--

CREATE TABLE IF NOT EXISTS `vm_region` (
`region_id` int(255) NOT NULL,
  `region_name` varchar(20) NOT NULL,
  `region_code` varchar(8) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `vm_region`
--

INSERT INTO `vm_region` (`region_id`, `region_name`, `region_code`) VALUES
(1, 'Thailand', 'th');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account`
--
ALTER TABLE `account`
 ADD PRIMARY KEY (`uid`);

--
-- Indexes for table `a_billing_account`
--
ALTER TABLE `a_billing_account`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `a_billing_report`
--
ALTER TABLE `a_billing_report`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `a_payment_log`
--
ALTER TABLE `a_payment_log`
 ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `credit_card`
--
ALTER TABLE `credit_card`
 ADD PRIMARY KEY (`card_id`);

--
-- Indexes for table `vm`
--
ALTER TABLE `vm`
 ADD PRIMARY KEY (`vm_id`);

--
-- Indexes for table `vm_os`
--
ALTER TABLE `vm_os`
 ADD PRIMARY KEY (`os_id`);

--
-- Indexes for table `vm_region`
--
ALTER TABLE `vm_region`
 ADD PRIMARY KEY (`region_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account`
--
ALTER TABLE `account`
MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `a_billing_account`
--
ALTER TABLE `a_billing_account`
MODIFY `id` int(255) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `a_billing_report`
--
ALTER TABLE `a_billing_report`
MODIFY `id` int(255) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `a_payment_log`
--
ALTER TABLE `a_payment_log`
MODIFY `log_id` int(255) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `credit_card`
--
ALTER TABLE `credit_card`
MODIFY `card_id` int(255) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `vm`
--
ALTER TABLE `vm`
MODIFY `vm_id` int(255) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `vm_os`
--
ALTER TABLE `vm_os`
MODIFY `os_id` int(2) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `vm_region`
--
ALTER TABLE `vm_region`
MODIFY `region_id` int(255) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
