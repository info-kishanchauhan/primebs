-- phpMyAdmin SQL Dump
-- version 4.4.12
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Oct 03, 2015 at 04:17 PM
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
-- Structure for view `view_ref_no_list`
--

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_ref_no_list` AS select `ref`.`id` AS `id`,`ref`.`prefix` AS `prefix`,`ref`.`running_no` AS `running_no`,`module`.`module_name` AS `module_name`,`ref`.`delimiter_char` AS `delimiter_char` from (`tbl_referenceno` `ref` left join `tbl_modules` `module` on((`ref`.`module_id` = `module`.`id`))) where (`ref`.`deleted_flag` = 0) order by `ref`.`id` desc;

--
-- VIEW  `view_ref_no_list`
-- Data: None
--


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
