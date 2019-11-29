<?php


namespace WEEEOpen\Tarallo\Database;


use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\String_;
use WEEEOpen\Tarallo\Product;

final class ProductDAO extends DAO{
	public function addProduct(Product $product) {

		$statement = $this->getPDO()->prepare('INSERT INTO Product (`Brand`, `Model`, `Variant`) VALUES (:prod, :mod, :var)');
		try {
			$statement->bindValue(':prod', $product->getBrand(), \PDO::PARAM_STR);
			$statement->bindValue(':mod', $product->getModel(), \PDO::PARAM_STR);
			$variant = $product->getVariant() ? $product->getVariant() : '';
			$statement->bindValue(':var', $variant, \PDO::PARAM_STR);
			$result = $statement->execute();
			assert($result === true, 'Add product');
		} catch(\PDOException $e) {
			if($e->getCode() === '23000' && $statement->errorInfo()[1] === 1062) {
				throw new DuplicateItemCodeException((string) $product);
			}
			throw $e;
		} finally {
			$statement->closeCursor();
		}
	}

	/**
	 * It gets product in exact match, requires model, brand and variant
	 *
	 * @param Product $product
	 *
	 * @return Product
	 */

	public function getProduct(Product $product): Product {
		//TODO: To implement
	}

	/**
	 *  It returns an array of product through brand and model. So it will get all variants of that product.
	 *
	 * @param String $brand
	 * @param String $model
	 *
	 * @return Array
	 */

	public function getProducts(String $brand, String $model): Array {
		//TODO: To implement
	}

	public function getProduct(string $brand, string $model, ?string  $variant = ''): Product{
		$statement = $this->getPDO()->prepare('SELECT * FROM Product WHERE Brand = :prod AND Model = :mod AND Variant = :var');
		try{
			$statement->bindValue(':prod', $brand, \PDO::PARAM_STR);
			$statement->bindValue(':mod', $model, \PDO::PARAM_STR);
			$statement->bindValue(':var', $variant, \PDO::PARAM_STR);
			$result =  $statement->execute();
			assert($result === true, 'Get product');
			$row = $statement->fetch(\PDO::FETCH_ASSOC);
			$product = new Product($row['Brand'], $row['Model'], $row['Variant']);
		} finally{
			$statement->closeCursor();
		}

		return $product;
	}

	//Is this useful?
	public function deleteProduct(Product $product){
		$statement = $this->getPDO()->prepare('DELETE FROM Product WHERE Brand = :prod AND Model = :mod AND Variant = :var ');
		try{
			$statement->bindValue(':prod', $product->getBrand(), \PDO::PARAM_STR);
			$statement->bindValue(':mod', $product->getModel(), \PDO::PARAM_STR);
			$statement->bindValue(':var', $product->getVariant(), \PDO::PARAM_STR);
			$result =  $statement->execute();
			assert($result === true, 'Delete product');
		} finally{
			$statement->closeCursor();
		}
	}
}