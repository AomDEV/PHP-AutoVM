-- phpMyAdmin SQL Dump
-- version 4.6.5.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 10, 2017 at 01:31 PM
-- Server version: 10.1.21-MariaDB
-- PHP Version: 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+07:00";

--
-- Database: `autovm`
--

-- --------------------------------------------------------

--
-- Table structure for table `account`
--

CREATE TABLE `account` (
  `uid` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(200) NOT NULL,
  `email` varchar(100) NOT NULL,
  `balance` double(64,2) NOT NULL,
  `phone` varchar(11) NOT NULL,
  `last_token` varchar(80) NOT NULL,
  `last_login` varchar(15) NOT NULL,
  `status` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `a_billing_account`
--

CREATE TABLE `a_billing_account` (
  `id` int(255) NOT NULL,
  `username` varchar(25) NOT NULL,
  `password` varchar(80) NOT NULL,
  `account_name` varchar(20) NOT NULL,
  `account_type` varchar(12) NOT NULL,
  `signature` varchar(60) NOT NULL,
  `fee` double(64,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `a_billing_account`
--

INSERT INTO `a_billing_account` (`id`, `username`, `password`, `account_name`, `account_type`, `signature`, `fee`) VALUES
(1, 'bank', '315-0-6062-68', 'SURINTORN JANKE', 'ktb', 'undefinded', 0.00),
(2, 'bank', '316-2-2564-82', 'PINAI JANKE', 'kbank', 'undefinded', 0.00),
(3, 'xxxxxxxxx.gmail.com', '5CAXTTM7KGYXXXXX', 'siriwat576@gmail.com', 'paypal', 'xxxxxxx', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `a_billing_report`
--

CREATE TABLE `a_billing_report` (
  `id` int(255) NOT NULL,
  `user_id` int(255) NOT NULL,
  `account_type` varchar(20) NOT NULL,
  `account_id` int(255) NOT NULL,
  `transaction_time` varchar(20) NOT NULL,
  `amount` double(9,2) NOT NULL,
  `transaction_id` varchar(30) NOT NULL,
  `status` int(1) NOT NULL,
  `last_report` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `a_payment_log`
--

CREATE TABLE `a_payment_log` (
  `log_id` int(255) NOT NULL,
  `log_name` varchar(100) NOT NULL,
  `log_action` varchar(20) NOT NULL,
  `log_return` text NOT NULL,
  `log_ip` varchar(16) NOT NULL,
  `log_time` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `credit_card`
--

CREATE TABLE `credit_card` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `vm`
--

CREATE TABLE `vm` (
  `vm_id` int(255) NOT NULL,
  `uid` int(255) NOT NULL,
  `hostname` varchar(30) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(80) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `memory` int(8) NOT NULL,
  `disk` int(8) NOT NULL,
  `region` int(2) NOT NULL,
  `osv_id` int(20) NOT NULL,
  `core` int(2) NOT NULL,
  `status` int(1) NOT NULL,
  `ip_updated` tinyint(1) NOT NULL,
  `transaction` varchar(20) NOT NULL,
  `package_id` int(20) NOT NULL,
  `expire` varchar(20) NOT NULL,
  `last_create` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `vm_os`
--

CREATE TABLE `vm_os` (
  `os_id` int(2) NOT NULL,
  `os_name` varchar(255) NOT NULL,
  `os_code` varchar(12) NOT NULL,
  `os_product` varchar(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `vm_os`
--

INSERT INTO `vm_os` (`os_id`, `os_name`, `os_code`, `os_product`) VALUES
(1, 'Windows Server', 'winserver', 'windows'),
(2, 'Ubuntu Server', 'ubuntu', 'ubuntu');

-- --------------------------------------------------------

--
-- Table structure for table `vm_os_version`
--

CREATE TABLE `vm_os_version` (
  `osv_id` int(2) NOT NULL,
  `osv_name` varchar(26) NOT NULL,
  `os_id` int(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `vm_os_version`
--

INSERT INTO `vm_os_version` (`osv_id`, `osv_name`, `os_id`) VALUES
(1, 'Windows Server 2008', 1),
(2, 'Ubuntu 12.04', 2),
(3, 'Ubuntu 16.04', 2);

-- --------------------------------------------------------

--
-- Table structure for table `vm_package`
--

CREATE TABLE `vm_package` (
  `package_id` int(20) NOT NULL,
  `package_name` varchar(40) NOT NULL,
  `vm_ram` int(21) NOT NULL,
  `vm_disk` int(21) NOT NULL,
  `is_ssd` tinyint(1) NOT NULL,
  `vm_core` int(2) NOT NULL,
  `vm_price` double(64,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

--
-- Dumping data for table `vm_package`
--

INSERT INTO `vm_package` (`package_id`, `package_name`, `vm_ram`, `vm_disk`, `is_ssd`, `vm_core`, `vm_price`) VALUES
(1, 'STARTER', 512, 40960, 0, 1, 599.00),
(2, 'BASIC', 1024, 81920, 1, 2, 899.00),
(3, 'BUSINESS', 8192, 102400, 1, 4, 1599.00);

-- --------------------------------------------------------

--
-- Table structure for table `vm_region`
--

CREATE TABLE `vm_region` (
  `region_id` int(255) NOT NULL,
  `region_name` varchar(20) NOT NULL,
  `region_code` varchar(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
-- Indexes for table `vm_os_version`
--
ALTER TABLE `vm_os_version`
  ADD PRIMARY KEY (`osv_id`);

--
-- Indexes for table `vm_package`
--
ALTER TABLE `vm_package`
  ADD PRIMARY KEY (`package_id`);

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
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `a_billing_account`
--
ALTER TABLE `a_billing_account`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `a_billing_report`
--
ALTER TABLE `a_billing_report`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `a_payment_log`
--
ALTER TABLE `a_payment_log`
  MODIFY `log_id` int(255) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `credit_card`
--
ALTER TABLE `credit_card`
  MODIFY `card_id` int(255) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `vm`
--
ALTER TABLE `vm`
  MODIFY `vm_id` int(255) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `vm_os`
--
ALTER TABLE `vm_os`
  MODIFY `os_id` int(2) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `vm_os_version`
--
ALTER TABLE `vm_os_version`
  MODIFY `osv_id` int(2) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `vm_package`
--
ALTER TABLE `vm_package`
  MODIFY `package_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `vm_region`
--
ALTER TABLE `vm_region`
  MODIFY `region_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
