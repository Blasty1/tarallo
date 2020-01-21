<?php


namespace WEEEOpen\TaralloTest\Database\data;

use WEEEOpen\Tarallo\Database\Database;
use WEEEOpen\Tarallo\Feature;
use WEEEOpen\Tarallo\Item;
use WEEEOpen\Tarallo\Product;
use WEEEOpen\Tarallo\ProductCode;

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';

$pdo = new \PDO(TARALLO_DB_DSN, TARALLO_DB_USERNAME, TARALLO_DB_PASSWORD, [
	\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
	\PDO::ATTR_CASE => \PDO::CASE_NATURAL,
	\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
	\PDO::ATTR_AUTOCOMMIT => false,
	\PDO::ATTR_EMULATE_PREPARES => false,
]);
$pdo->exec(/** @lang MariaDB */ "TRUNCATE TABLE Tree;");
$pdo->exec(/** @lang MariaDB */ "TRUNCATE TABLE ItemFeature;");
$pdo->exec(/** @lang MariaDB */ "TRUNCATE TABLE ProductFeature;");
$pdo->exec(/** @lang MariaDB */ "SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE Audit; SET FOREIGN_KEY_CHECKS = 1;");
$pdo->exec(/** @lang MariaDB */ "SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE Item; SET FOREIGN_KEY_CHECKS = 1;");
$pdo->exec(/** @lang MariaDB */ "SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE Product; SET FOREIGN_KEY_CHECKS = 1;");
$pdo->exec(/** @lang MariaDB */ "SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE SearchResult; SET FOREIGN_KEY_CHECKS = 1;");
$pdo->exec(/** @lang MariaDB */ "SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE Search; SET FOREIGN_KEY_CHECKS = 1;");

$db = new Database(TARALLO_DB_USERNAME, TARALLO_DB_PASSWORD, TARALLO_DB_DSN);
$db->productDAO()->addProduct(new Product("Samsong", "KAI39"));
$db->productDAO()->addProduct(new Product("Caste", "Payton", "Brutto"));
$db->productDAO()->addProduct((new Product("AMD", "Opteron 3300", "AM1234567"))
	->addFeature(new Feature('frequency-hertz', 3000000000))
	->addFeature(new Feature('isa', 'x86-64'))
	->addFeature(new Feature('cpu-socket', 'am3'))
	->addFeature(new Feature('type', 'cpu')));
$db->productDAO()->addProduct((new Product("Intel", "Centryno", "SL666"))
	->addFeature(new Feature('frequency-hertz', 1500000000))
	->addFeature(new Feature('isa', 'x86-64'))
	->addFeature(new Feature('cpu-socket', 'lga771'))
	->addFeature(new Feature('type', 'cpu')));
$db->productDAO()->addProduct((new Product("Intel", "Centryno", "SL7AB"))
	->addFeature(new Feature('frequency-hertz', 1500000000))
	->addFeature(new Feature('isa', 'x86-64'))
	->addFeature(new Feature('cpu-socket', 'lga771'))
	->addFeature(new Feature('type', 'cpu')));
$db->productDAO()->addProduct((new Product("Intel", "Centryno", "SL88C"))
	->addFeature(new Feature('frequency-hertz', 1500000000))
	->addFeature(new Feature('isa', 'x86-64'))
	->addFeature(new Feature('cpu-socket', 'lga771'))
	->addFeature(new Feature('type', 'cpu')));
$db->productDAO()->addProduct((new Product("Intel", "MB346789", "v2.0"))
	->addFeature(new Feature('color', 'green'))
	->addFeature(new Feature('cpu-socket', 'lga771'))
	->addFeature(new Feature('motherboard-form-factor', 'miniitx'))
	->addFeature(new Feature('parallel-ports-n', 1))
	->addFeature(new Feature('serial-ports-n', 1))
	->addFeature(new Feature('ps2-ports-n', 3))
	->addFeature(new Feature('usb-ports-n', 4))
	->addFeature(new Feature('ram-form-factor', 'dimm'))
	->addFeature(new Feature('ram-type', 'ddr2'))
	->addFeature(new Feature('type', 'motherboard')));
$db->productDAO()->addProduct((new Product("eMac", "EZ1600", "boh"))
	->addFeature(new Feature('motherboard-form-factor', 'miniitx'))
	->addFeature(new Feature('color', 'white'))
	->addFeature(new Feature('type', 'case')));
$db->productDAO()->addProduct((new Product("Dill", "DI-360", "SFF"))
	->addFeature(new Feature('motherboard-form-factor', 'proprietary'))
	->addFeature(new Feature('color', 'grey'))
	->addFeature(new Feature('type', 'case')));
foreach([256, 512, 1024, 2048] as $size) {
	$db->productDAO()->addProduct(
		(new Product("Samsung", "S667ABC" . $size, "v1"))
			->addFeature(new Feature('capacity-byte', $size * 1024 * 1024))
			->addFeature(new Feature('frequency-hertz', 667 * 1000 * 1000))
			->addFeature(new Feature('color', 'green'))
			->addFeature(new Feature('ram-ecc', 'no'))
			->addFeature(new Feature('ram-type', 'ddr2'))
			->addFeature(new Feature('ram-form-factor', 'dimm'))
			->addFeature(new Feature('type', 'ram'))
	);
}

$polito = (new Item('Polito'))->addFeature(new Feature('type', 'location'));
$chernobyl = (new Item('Chernobyl'))->addFeature(new Feature('type', 'location'))->addFeature(new Feature('color', 'grey'));
$polito->addContent($chernobyl);
$table = (new Item('Table'))->addFeature(new Feature('type', 'location'))->addFeature(new Feature('color', 'white'));
$chernobyl->addContent($table);
$rambox = (new Item('RamBox'))->addFeature(new Feature('type', 'location'))->addFeature(new Feature('color', 'red'));
$table->addContent($rambox);

$pc20 = (new Item('PC20'))
	->addFeature(new Feature('brand', 'Dill'))
	->addFeature(new Feature('model', 'DI-360'))
	->addFeature(new Feature('variant', 'SFF'))
	->addFeature(new Feature('owner', 'DISAT'))
	->addFeature(new Feature('working', 'yes'));
$pc90 = (new Item('PC90'))
	->addFeature(new Feature('brand', 'Dill'))
	->addFeature(new Feature('model', 'DI-360'))
	->addFeature(new Feature('variant', 'SFF'))
	->addFeature(new Feature('todo', 'install-os'))
	->addFeature(new Feature('owner', 'DISAT'))
	->addFeature(new Feature('working', 'yes'));
$pc55 = (new Item('PC55'))
	->addFeature(new Feature('brand', 'TI'))
	->addFeature(new Feature('model', 'GreyPC-\'98'))
	->addFeature(new Feature('type', 'case'))
	->addFeature(new Feature('owner', 'DISAT'))
	->addFeature(new Feature('motherboard-form-factor', 'atx'));
$pc22 = (new Item('PC22'))
	->addFeature(new Feature('brand', 'Dill'))
	->addFeature(new Feature('model', 'DI-360'))
	->addFeature(new Feature('variant', 'SFF'))
	->addFeature(new Feature('color', 'black')) // override
	->addFeature(new Feature('owner', 'DISAT'))
	->addFeature(new Feature('working', 'yes'));
$SCHIFOMACCHINA = (new Item('SCHIFOMACCHINA'))
	->addFeature(new Feature('brand', 'eMac'))
	->addFeature(new Feature('model', 'EZ1600'))
	->addFeature(new Feature('owner', 'Area IT'))
	->addFeature(new Feature('variant', 'boh'));
$SCHIFOMACCHINA->addContent((new Item('B25'))
	->addFeature(new Feature('brand', 'Intel'))
	->addFeature(new Feature('model', 'MB346789'))
	->addFeature(new Feature('variant', 'v2.0'))
	->addFeature(new Feature('working', 'yes'))
	->addContent((new Item('R20'))
		->addFeature(new Feature('brand', 'Samsung'))
		->addFeature(new Feature('model', 'S667ABC512'))
		->addFeature(new Feature('variant', 'v1'))
		->addFeature(new Feature('owner', 'DISAT'))
		->addFeature(new Feature('sn', 'ASD' . strtoupper(substr(crc32(512), 0, 5) . rand(100000, 999999) . dechex(rand(0,255)))))
		->addFeature(new Feature('working', rand(0, 1) ? 'yes' : 'no'))
	)
	->addContent((new Item('R21'))
		->addFeature(new Feature('brand', 'Samsung'))
		->addFeature(new Feature('model', 'S667ABC512'))
		->addFeature(new Feature('variant', 'v1'))
		->addFeature(new Feature('owner', 'DISAT'))
		->addFeature(new Feature('sn', 'ASD' . strtoupper(substr(crc32(512), 0, 5) . rand(100000, 999999) . dechex(rand(0,255)))))
		->addFeature(new Feature('working', rand(0, 1) ? 'yes' : 'no'))
	));

// RAM(DOM) GENERATOR 2000
for($i = 100; $i < 222; $i++) {
	$ramSize = pow(2, rand(8, 11));
	$ram = (new Item('R' . $i))
		->addFeature(new Feature('brand', 'Samsung'))
		->addFeature(new Feature('model', 'S667ABC' . $ramSize))
		->addFeature(new Feature('variant', 'v1'))
		->addFeature(new Feature('sn', 'ASD' . strtoupper(substr(crc32($ramSize), 0, 5) . rand(100000, 999999) . dechex(rand(0,255)))))
		->addFeature(new Feature('working', rand(0, 1) ? 'yes' : 'no'));
	$rambox->addContent($ram);
}
for($i = 222; $i < 230; $i++) {
	$ramSize = pow(2, rand(8, 11));
	$ram = (new Item('R' . $i))
		->addFeature(new Feature('brand', 'Samsung'))
		->addFeature(new Feature('model', 'S667ABC' . $ramSize))
		->addFeature(new Feature('variant', 'v1'))
		->addFeature(new Feature('sn', 'ASD' . strtoupper(substr(crc32($ramSize), 0, 5) . rand(100000, 999999) . dechex(rand(0,255)))));
	$rambox->addContent($ram);
}
$ram = (new Item('R69'))
	->addFeature(new Feature('check', 'missing-data'))
	->addFeature(new Feature('notes', 'RAM di esempio con dati mancati'))
	->addFeature(new Feature('working', rand(0, 1) ? 'yes' : 'no'))
	->addFeature(new Feature('type', 'ram'));
$rambox->addContent($ram);
$ram666 = (new Item('R666'))
	->addFeature(new Feature('brand', 'Samsung'))
	->addFeature(new Feature('model', 'S667ABC1024'))
	->addFeature(new Feature('notes', 'RAM di esempio persa'))
	->addFeature(new Feature('working', rand(0, 1) ? 'yes' : 'no'))
	->addFeature(new Feature('type', 'ram'));
$rambox->addContent($ram666);

foreach([777, 778, 779] as $item) {
	$ram = (new Item('R' . $item))
		->addFeature(new Feature('brand', 'Samsung'))
		->addFeature(new Feature('model', 'S667ABC512'))
		->addFeature(new Feature('variant', 'v1'))
		->addFeature(new Feature('sn', 'ASD' . substr(strtoupper(md5('512')), 0, 5) . '123456'))
		->addFeature(new Feature('working', rand(0, 1) ? 'yes' : 'no'));
	$rambox->addContent($ram);
}

$lonelyCpu = (new Item(null))
	->addFeature(new Feature('brand', 'AMD'))
	->addFeature(new Feature('model', 'Opteron 3300'))
	->addFeature(new Feature('variant', 'AM1234567'))
	->setProduct($db->productDAO()->getProduct(new ProductCode('AMD', 'Opteron 3300', 'AM1234567')));
$table->addContent($lonelyCpu);

$table->addContent($SCHIFOMACCHINA);
$chernobyl->addContent($pc20)->addContent($pc22)->addContent($pc55)->addContent($pc90);

$db->itemDAO()->addItem($polito);
$db->itemDAO()->loseItem($ram666);