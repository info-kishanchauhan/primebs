-- phpMyAdmin SQL Dump
-- version 4.4.12
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Oct 03, 2015 at 03:23 PM
-- Server version: 5.6.25
-- PHP Version: 5.6.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gst_erp`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_referenceno`
--

CREATE TABLE IF NOT EXISTS `tbl_referenceno` (
  `id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `module_table` varchar(75) NOT NULL,
  `prefix` varchar(10) NOT NULL,
  `running_no` varchar(20) NOT NULL,
  `delimiter_char` varchar(25) NOT NULL,
  `deleted_flag` tinyint(1) NOT NULL,
  `created_by` int(11) NOT NULL DEFAULT '0',
  `updated_by` int(11) NOT NULL DEFAULT '0',
  `created_on` datetime DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_referenceno`
--

INSERT INTO `tbl_referenceno` (`id`, `module_id`, `module_table`, `prefix`, `running_no`, `delimiter_char`, `deleted_flag`, `created_by`, `updated_by`, `created_on`, `updated_on`) VALUES
(1, 1, '5255', '2', '639', '565', 0, 12, 0, '2015-10-03 11:42:38', '2015-10-03 09:42:38');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_referenceno`
--
ALTER TABLE `tbl_referenceno`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_referenceno`
--
ALTER TABLE `tbl_referenceno`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
