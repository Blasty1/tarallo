SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

TRUNCATE `Feature`;
INSERT INTO `Feature` (`FeatureID`, `FeatureName`, `FeatureType`) VALUES
	(1, 'brand', 0),
	(2, 'model', 0),
	(3, 'owner', 0),
	(4, 'sn', 0),
	(5, 'mac', 0),
	(6, 'type', 2),
	(7, 'working', 2),
	(8, 'capacity-byte', 1),
	(9, 'frequency-hertz', 1),
	(10, 'diameter-mm', 1),
	(11, 'diagonal-inch', 1),
	(12, 'has-gpu', 2), -- TODO: decide if this makes sense
	(13, 'color', 2),
	(14, 'motherboard-form-factor', 2),
	(15, 'notes', 0),
	(16, 'agp-sockets-n', 1),
	(17, 'arrival-batch', 0),
	(19, 'capacity-decibyte', 1),
	(20, 'cib', 0),
	(21, 'core-n', 1),
	(22, 'cpu-socket', 2),
	(23, 'dvi-ports-n', 1),
	(24, 'ethernet-ports-1000m-n', 1),
	(25, 'ethernet-ports-100m-n', 1),
	(26, 'ethernet-ports-10base2-n', 1),
	(27, 'ethernet-ports-10m-n', 1),
	(28, 'hdd-odd-form-factor', 2),
	(29, 'ide-ports-n', 1),
	(31, 'odd-type', 2),
	(32, 'pcie-power', 2),
	(33, 'pcie-sockets-n', 1),
	(34, 'pci-sockets-n', 1),
	(35, 'power-connector', 2),
	(36, 'power-idle-watt', 1),
	(37, 'power-rated-watt', 1),
	(38, 'ps2-ports-n', 1),
	(39, 'psu-ampere', 1),
	(40, 'psu-socket', 2),
	(41, 'psu-volt', 1),
	(42, 'ram-socket', 2),
	(43, 'sata-ports-n', 1),
	(44, 'software', 0),
	(45, 'usb-ports-n', 1),
	(46, 'vga-ports-n', 1),
	(47, 'windows-serial-number', 0),
	(48, 'windows-serial-version', 0),
	(49, 'soldered-in-place', 2),
	(50, 'power-idle-pfc', 0),
	(51, 'firewire-ports-n', 1),
	(52, 'serial-ports-n', 1),
	(53, 'parallel-ports-n', 1),
	(54, 'ram-form-factor', 2),
	(55, 'weight-g', 1);
-- TODO: SCSI ports

TRUNCATE `FeatureValue`;
INSERT INTO `FeatureValue` (`FeatureID`, `ValueEnum`, `ValueText`) VALUES
	(6, 0, 'location'),
	(6, 1, 'case'),
	(6, 2, 'motherboard'),
	(6, 3, 'cpu'),
	(6, 4, 'graphics-card'),
	(6, 5, 'ram'),
	(6, 6, 'hdd'),
	(6, 7, 'odd'),
	(6, 8, 'psu'),
	(6, 9, 'audio-card'),
	(6, 10, 'network-card'),
	(6, 11, 'monitor'),
	(6, 12, 'mouse'),
	(6, 13, 'keyboard'),
	(6, 14, 'switch'),
	(6, 15, 'hub'),
	(6, 16, 'modem-router'),
	(6, 17, 'fdd'),
	(6, 18, 'ports-bracket'),
	(6, 19, 'other-card'),
	(6, 20, 'heatsink'),
	(6, 21, 'fan'),
	(6, 22, 'fan-controller'),
	(7, 0, 'no'),
	(7, 1, 'yes'),
	(7, 2, 'maybe'),
	(12, 0, 'no'),
	(12, 1, 'yes'),
	(13, 0, 'black'),
	(13, 1, 'white'),
	(13, 2, 'green'),
	(13, 3, 'yellow'),
	(13, 4, 'red'),
	(13, 5, 'blue'),
	(13, 6, 'grey'),
	(13, 7, 'darkgrey'),
	(13, 8, 'lightgrey'),
	(13, 9, 'pink'),
	(13, 10, 'transparent'),
	(13, 11, 'brown'),
	(13, 12, 'orange'),
	(13, 13, 'violet'),
	(13, 14, 'sip-brown'),
	(13, 15, 'lightblue'),
	(14, 0, 'atx'),
	(14, 1, 'miniatx'),
	(14, 2, 'microatx'),
	(14, 3, 'miniitx'),
	(14, 4, 'proprietary'),
	(14, 5, 'btx'),
	(14, 6, 'flexatx'),
	(22, 0, 'other'),
	(22, 1, 'other-slot'),
	(22, 2, 'other-socket'),
	(22, 3, 'other-dip'),
	(22, 4, 'g1'),	
	(22, 5, 'g2'),
	(22, 7, 'socket7'),
	(22, 8, 'm'),	
	(22, 370, 'socket370'),
	(22, 462, 'socket462a'), -- A aka 462
	(22, 423, 'socket423'),
	(22, 478, 'socket478'), -- 478 aka mPGA478B
	(22, 603, 'socket603'),
	(22, 754, 'socket754'),
	(22, 940, 'socket940'),
	(22, 939, 'socket939'),
	(22, 775, 'lga775'), -- LGA775 aka socket T aka socket775
	(22, 771, 'lga771'), -- LGA775 aka socket J
	(22, 10, 'am1'),
	(22, 11, 'am2'),
	(22, 12, 'am2plus'),
	(22, 13, 'am3'),
	(22, 14, 'am3plus'),
	(22, 15, 'am4'),
	(22, 16, 'fm1'),
	(22, 17, 'fm2'),
	(22, 18, 'fm2plus'),
	(22, 1366, 'lga1366'), -- LGA775 aka socket B
	(22, 1156, 'lga1156'), -- aka H1
	(22, 19, 'g34'),
	(22, 20, 'c32'),
	(22, 1248, 'lga1248'),
	(22, 1567, 'lga1567'),
	(22, 1155, 'lga1155'), -- aka H2
	(22, 2011, 'lga2011'), -- R
	(22, 1150, 'lga1150'), -- aka H3
	(22, 21, 'g3'), -- aka rPGA988A
	(22, 1151, 'lga1151'), -- aka H4
	(22, 3647, 'lga3647'),
	(22, 2066, 'lga2066'),
	(28, 0, '5.25'),
	(28, 1, '3.5'),
	(28, 2, '2.5'),
	(28, 3, '2.5slim'), -- TODO: does this exist?
	(28, 4, 'm2'),
	(31, 0, 'cd-r'),
	(31, 1, 'cd-rw'),
	(31, 2, 'dvd-r'),
	(31, 3, 'dvd-rw'),
	(31, 4, 'bd-r'),
	(31, 5, 'bd-rw'),
	(32, 0, '4pin'),
	(32, 1, '6pin'),
	(32, 2, '8pin'),
	(35, 0, 'other'),
	(35, 1, 'other-barrell'),
	(35, 2, ''), -- TODO: every barrel connector size in existence
	(40, 0, 'other'),
	(40, 1, 'at'),
	(40, 2, 'atx'),
	(40, 3, 'atx-p4'),
	(40, 4, 'atx-p4-extended'),
	(40, 5, 'atx-p4-4pin'),
	(40, 6, 'atx-p4-8pin'),
	(40, 7, 'atx-p4-8pin'),
	(42, 0, 'pc66'),	
	(42, 1, 'pc100'),
	(42, 2, 'pc133'),
	(42, 3, 'ddr'),
	(42, 4, 'ddr2'),
	(42, 5, 'ddr3'),
	(42, 6, 'ddr4'),
	(49, 0, 'no'),
	(49, 1, 'yes'),
	(54, 0, 'dimm'),
	(54, 1, 'so-dimm'),
	(54, 2, 'minidimm'),
	(54, 3, 'microdimm');
-- TRUNCATE `Codes`;
-- TRUNCATE `Item`;
-- TRUNCATE `ItemFeature`;
-- TRUNCATE `ItemLocationModification`;
-- TRUNCATE `ItemModification`;
-- TRUNCATE `ItemModificationDelete`;
-- TRUNCATE `Modification`;
-- TRUNCATE `Tree`;
-- TRUNCATE `User`;
