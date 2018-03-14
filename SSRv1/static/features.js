// class Features {
	"use strict";
	// Just rename to features-en-US.js if we ever get around translating the entire software...
	let featureTypes = new Map();
	let featureValues = new Map();
	let featureValuesTranslated = new Map();

	// BEGIN GENERATED CODE
	featureTypes.set('brand', 's');
	featureTypes.set('model', 's');
	featureTypes.set('owner', 's');
	featureTypes.set('sn', 's');
	featureTypes.set('mac', 's');
	featureTypes.set('type', 'e');
	featureValues.set('type', ['location', 'case', 'motherboard', 'cpu', 'graphics-card', 'ram', 'hdd', 'odd', 'psu', 'audio-card', 'ethernet-card', 'monitor', 'mouse', 'keyboard', 'network-switch', 'network-hub', 'modem-router', 'fdd', 'ports-bracket', 'other-card', 'fan-controller', 'modem-card', 'scsi-card', 'wifi-card', 'bluetooth-card', 'external-psu', 'zip-drive', 'printer', 'scanner', 'inventoried-object', 'adapter', 'usbhub', 'tv-card']);
	featureValuesTranslated.set('type', ['Location', 'Case', 'Motherboard', 'CPU', 'Graphics card', 'RAM', 'HDD', 'ODD', 'PSU', 'Audio card', 'Ethernet card', 'Monitor', 'Mouse', 'Keyboard', 'Network switch', 'Network hub', 'Modem/router', 'FDD', 'Bracket with ports', 'Other internal card', 'Fan controller (rheobus)', 'Modem card', 'SCSI card', 'WiFi card', 'Bluetooth card', 'External PSU', 'ZIP drive', 'Printer', 'Scanner', 'Other (with invetory sticker)', 'Adapter', 'USB hub', 'TV tuner card']);
	featureTypes.set('working', 'e');
	featureValues.set('working', ['no', 'yes', 'maybe']);
	featureValuesTranslated.set('working', ['No', 'Yes', 'Maybe (unclear)']);
	featureTypes.set('capacity-byte', 'i');
	featureTypes.set('frequency-hertz', 'i');
	featureTypes.set('diameter-mm', 'i');
	featureTypes.set('diagonal-inch', 'i');
	featureTypes.set('isa', 'e');
	featureValues.set('isa', ['x86-32', 'x86-64', 'ia-64', 'arm']);
	featureValuesTranslated.set('isa', ['x86 32 bit', 'x86 64 bit', 'IA-64', 'ARM']);
	featureTypes.set('color', 'e');
	featureValues.set('color', ['black', 'white', 'green', 'yellow', 'red', 'blue', 'grey', 'darkgrey', 'lightgrey', 'pink', 'transparent', 'brown', 'orange', 'violet', 'sip-brown', 'lightblue', 'yellowed', 'transparent-dark', 'golden']);
	featureValuesTranslated.set('color', ['Black', 'White', 'Green', 'Yellow', 'Red', 'Blue', 'Grey', 'Dark grey', 'Light grey', 'Pink', 'Transparent', 'Brown', 'Orange', 'Violet', 'SIP brown', 'Light blue', 'Yellowed', 'Transparent (dark)', 'Golden']);
	featureTypes.set('motherboard-form-factor', 'e');
	featureValues.set('motherboard-form-factor', ['atx', 'miniatx', 'microatx', 'miniitx', 'proprietary', 'btx', 'microbtx', 'nanobtx', 'picobtx', 'wtx', 'flexatx', 'proprietary-laptop', 'eatx']);
	featureValuesTranslated.set('motherboard-form-factor', ['ATX', 'Mini ATX (not standard)', 'Micro ATX', 'Mini ITX', 'Proprietary (desktop)', 'BTX (slots ≤ 7)', 'Micro BTX (slots ≤ 4)', 'Nano BTX (slots ≤ 2)', 'Pico BTX (slots ≤ 1)', 'WTX', 'Flex ATX', 'Laptop', 'Extended ATX']);
	featureTypes.set('notes', 's');
	featureTypes.set('agp-sockets-n', 'i');
	featureTypes.set('arrival-batch', 's');
	featureTypes.set('capacity-decibyte', 'i');
	featureTypes.set('cib', 's');
	featureTypes.set('core-n', 'i');
	featureTypes.set('cpu-socket', 'e');
	featureValues.set('cpu-socket', ['other-slot', 'other-socket', 'other-dip', 'g1', 'g2', 'socket3', 'socket7', 'p', 'am1', 'am2', 'am2plus', 'am3', 'am3plus', 'am4', 'fm1', 'fm2', 'fm2plus', 'g34', 'c32', 'g3', 'slot1', 'socket370', 'socket462a', 'socket423', 'socket478', 'socket479a', 'socket479c', 'socket479m', 'socket495', 'socket603', 'socket604', 'socket615', 'socket754', 'socket940', 'socket939', 'lga775', 'lga771', 'lga1366', 'lga1156', 'lga1248', 'lga1567', 'lga1155', 'lga2011', 'lga1150', 'lga1151', 'lga2066', 'lga3647']);
	featureValuesTranslated.set('cpu-socket', ['Other (slot)', 'Other (socket)', 'Other (DIP)', 'G1', 'G2', 'Socket 3', 'Socket 7', 'P', 'AM1', 'AM2', 'AM2+', 'AM3', 'AM3+', 'AM4', 'FM1', 'FM2', 'FM2+', 'G34', 'C32', 'G3 (rPGA988A)', 'Slot 1', '370', '462 (Socket A)', '423', '478 (desktop; mPGA478B)', '479 (mobile; mPGA478A)', '479 (mobile; mPGA478C)', '479 (mobile; socket M)', '495', '603', '604', '615', '754', '940', '939', 'LGA775 (Socket T)', 'LGA771 (Socket J)', 'LGA1366 (Socket B)', 'LGA1156 (Socket H1)', 'LGA1248', 'LGA1567', 'LGA1155 (Socket H2)', 'LGA2011 (Socket R)', 'LGA1150 (Socket H3)', 'LGA1151 (Socket H4)', 'LGA2066', 'LGA3647']);
	featureTypes.set('dvi-ports-n', 'i');
	featureTypes.set('ethernet-ports-1000m-n', 'i');
	featureTypes.set('ethernet-ports-100m-n', 'i');
	featureTypes.set('ethernet-ports-10base2-bnc-n', 'i');
	featureTypes.set('ethernet-ports-10m-n', 'i');
	featureTypes.set('hdd-odd-form-factor', 'e');
	featureValues.set('hdd-odd-form-factor', ['5.25', '3.5', '2.5-15mm', '2.5-7mm', '2.5-9.5mm', 'm2', 'm2.2', 'laptop-odd-standard', 'laptop-odd-slim']);
	featureValuesTranslated.set('hdd-odd-form-factor', ['5.25 in.', '3.5 in.', '2.5 in. (15 mm thick; uncommon)', '2.5 in. (7 mm thick)', '2.5 in. (9.5 mm thick)', 'M2', 'M2.2', 'Laptop ODD (standard)', 'Laptop ODD (slimmer; uncommon)']);
	featureTypes.set('ide-ports-n', 'i');
	featureTypes.set('odd-type', 'e');
	featureValues.set('odd-type', ['cd-r', 'cd-rw', 'dvd-r', 'dvd-rw', 'bd-r', 'bd-rw']);
	featureValuesTranslated.set('odd-type', ['CD-R', 'CD-RW', 'DVD-R', 'DVD-RW', 'BD-R', 'BD-RW']);
	featureTypes.set('pcie-power-pin-n', 'i');
	featureTypes.set('pcie-sockets-n', 'i');
	featureTypes.set('pci-sockets-n', 'i');
	featureTypes.set('power-connector', 'e');
	featureValues.set('power-connector', ['other', 'c13', 'c19', 'barrel', 'miniusb', 'microusb', 'proprietary', 'da-2']);
	featureValuesTranslated.set('power-connector', ['Other', 'C13/C14', 'C19/C20', 'Barrel (standard)', 'Mini USB', 'Micro USB', 'Proprietary', 'Dell DA-2']);
	featureTypes.set('power-idle-watt', 'i');
	featureTypes.set('power-rated-watt', 'i');
	featureTypes.set('ps2-ports-n', 'i');
	featureTypes.set('psu-ampere', 'd');
	featureTypes.set('psu-connector-motherboard', 'e');
	featureValues.set('psu-connector-motherboard', ['proprietary', 'at', 'atx-20pin', 'atx-24pin', 'atx-24pin-mini', 'atx-20pin-aux']);
	featureValuesTranslated.set('psu-connector-motherboard', ['Proprietary', 'AT', 'ATX 20 pin', 'ATX 24 pin', 'Mini ATX 24 pin', 'ATX 20 pin + AUX']);
	featureTypes.set('psu-volt', 'd');
	featureTypes.set('ram-type', 'e');
	featureValues.set('ram-type', ['simm', 'edo', 'sdr', 'ddr', 'ddr2', 'ddr3', 'ddr4']);
	featureValuesTranslated.set('ram-type', ['SIMM', 'EDO', 'SDR', 'DDR', 'DDR2', 'DDR3', 'DDR4']);
	featureTypes.set('sata-ports-n', 'i');
	featureTypes.set('software', 's');
	featureTypes.set('usb-ports-n', 'i');
	featureTypes.set('usb-header-n', 'i');
	featureTypes.set('internal-header-n', 'i');
	featureTypes.set('vga-ports-n', 'i');
	featureTypes.set('os-license-code', 's');
	featureTypes.set('os-license-version', 's');
	featureTypes.set('power-idle-pfc', 's');
	featureTypes.set('firewire-ports-n', 'i');
	featureTypes.set('mini-firewire-ports-n', 'i');
	featureTypes.set('serial-ports-n', 'i');
	featureTypes.set('parallel-ports-n', 'i');
	featureTypes.set('ram-form-factor', 'e');
	featureValues.set('ram-form-factor', ['simm', 'dimm', 'sodimm', 'minidimm', 'microdimm', 'fbdimm']);
	featureValuesTranslated.set('ram-form-factor', ['SIMM', 'DIMM', 'SODIMM', 'Mini DIMM', 'Micro DIMM', 'FB-DIMM']);
	featureTypes.set('weight-gram', 'i');
	featureTypes.set('spin-rate-rpm', 'i');
	featureTypes.set('dms-59-ports-n', 'i');
	featureTypes.set('check', 'e');
	featureValues.set('check', ['missing-data', 'wrong-data', 'wrong-location', 'wrong-content', 'missing-content', 'wrong-data-and-content', 'wrong-location-and-data']);
	featureValuesTranslated.set('check', ['Missing data', 'Wrong data', 'Wrong location/lost', 'Wrong content', 'Missing content', 'Wrong data and content', 'Wrong location and data (and content)']);
	featureTypes.set('ram-ecc', 'e');
	featureValues.set('ram-ecc', ['no', 'yes']);
	featureValuesTranslated.set('ram-ecc', ['No', 'Yes']);
	featureTypes.set('other-code', 's');
	featureTypes.set('hdmi-ports-n', 'i');
	featureTypes.set('scsi-sca2-ports-n', 'i');
	featureTypes.set('scsi-db68-ports-n', 'i');
	featureTypes.set('mini-ide-ports-n', 'i');
	featureTypes.set('data-erased', 'e');
	featureValues.set('data-erased', ['yes']);
	featureValuesTranslated.set('data-erased', ['Yes️']);
	featureTypes.set('surface-scan', 'e');
	featureValues.set('surface-scan', ['fail', 'pass']);
	featureValuesTranslated.set('surface-scan', ['Failed', 'Passed']);
	featureTypes.set('smart-data', 'e');
	featureValues.set('smart-data', ['fail', 'old', 'ok']);
	featureValuesTranslated.set('smart-data', ['Failed', 'Old', 'Ok']);
	featureTypes.set('wireless-receiver', 'e');
	featureValues.set('wireless-receiver', ['inside', 'near', 'missing']);
	featureValuesTranslated.set('wireless-receiver', ['Inside the peripheral', 'Near the peripheral', 'Missing']);
	featureTypes.set('rj11-ports-n', 'i');
	featureTypes.set('ethernet-ports-10base5-aui-n', 'i');
	featureTypes.set('midi-ports-n', 'i');
	featureTypes.set('mini-jack-ports-n', 'i');
	featureTypes.set('rca-mono-ports-n', 'i');
	featureTypes.set('tv-out-ports-n', 'i');
	featureTypes.set('s-video-ports-n', 'i');
	featureTypes.set('s-video-7pin-ports-n', 'i');
	featureTypes.set('composite-video-ports-n', 'i');
	featureTypes.set('serial-db25-ports-n', 'i');
	featureTypes.set('isa-sockets-n', 'i');
	featureTypes.set('mini-pcie-sockets-n', 'i');
	featureTypes.set('mini-pci-sockets-n', 'i');
	featureTypes.set('brand-reseller', 's');
	featureTypes.set('psu-form-factor', 'e');
	featureValues.set('psu-form-factor', ['atx', 'cfx', 'lfx', 'sfx-lowprofile', 'sfx-topfan', 'sfx-topfan-reduceddepth', 'sfx', 'sfx-ps3', 'sfx-l', 'tfx', 'flexatx', 'proprietary', 'eps']);
	featureValuesTranslated.set('psu-form-factor', ['ATX', 'CFX', 'LFX', 'SFX Low Profile', 'SFX Topfan', 'SFX Topfan reduced depth', 'SFX', 'SFX PS3', 'SFX-L', 'TFX', 'Flex ATX', 'Proprietary', 'EPS']);
	featureTypes.set('cib-old', 's');
	featureTypes.set('integrated-graphics-brand', 's');
	featureTypes.set('integrated-graphics-model', 's');
	featureTypes.set('restrictions', 'e');
	featureValues.set('restrictions', ['loan', 'in-use', 'bought', 'training', 'ready', 'other']);
	featureValuesTranslated.set('restrictions', ['Loaned (to be returned)', 'In use', 'Bought', 'Training/demonstrations', 'Ready', 'Other (cannot be donated)']);
	featureTypes.set('displayport-ports-n', 'i');
	featureTypes.set('pci-low-profile', 'e');
	featureValues.set('pci-low-profile', ['no', 'possibile', 'dual', 'yes']);
	featureValuesTranslated.set('pci-low-profile', ['No', 'Possible (no bracket)', 'Yes (both brackets)', 'Yes (low profile only)']);
	featureTypes.set('psu-connector-cpu', 'e');
	featureValues.set('psu-connector-cpu', ['none', '4pin', '6pin-hp', '6pin-hp-brown', '6pin', '8pin', 'proprietary']);
	featureValuesTranslated.set('psu-connector-cpu', ['None', '4 pin', '6 pin (HP; 1 purple + 1 blue)', '6 pin (HP; 2 brown)', '6 pin (other)', '8 pin', 'Proprietary']);
	featureTypes.set('jae-ports-n', 'i');
	featureTypes.set('game-ports-n', 'i');
	// END GENERATED CODE
// }
