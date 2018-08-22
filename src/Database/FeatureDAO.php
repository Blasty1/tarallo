<?php

namespace WEEEOpen\Tarallo\Server\Database;

use WEEEOpen\Tarallo\Server\Feature;
use WEEEOpen\Tarallo\Server\Item;
use WEEEOpen\Tarallo\Server\ItemFeatures;
use WEEEOpen\Tarallo\Server\ItemIncomplete;


final class FeatureDAO extends DAO {

	/**
	 * Add features to ALL TEH ITEMS
	 *
	 * @param ItemFeatures[] $items
	 *
	 * @return ItemFeatures[]|Item[] same array
	 */
	public function getFeaturesAll(array $items) {
		foreach($items as $item) {
			$this->getFeatures($item);
		}

		return $items;
	}

	/**
	 * Get all items that have a certain value (exact match) for a feature.
	 * For anything more complicated use SearchDAO facilities.
	 *
	 * @param Feature $feature Feature and value to search
	 * @param int $limit Maximum number of results
	 *
	 * @return ItemIncomplete[] Items that have that feature (or empty array if none)
	 */
	public function getItemsByFeatures(Feature $feature, int $limit = 100): array {
		$pdo = $this->getPDO();
		switch($feature->type) {
			case Feature::STRING:
				$statement = $pdo->prepare('SELECT `Code` FROM ItemFeature WHERE Feature = ? AND ValueText = ? LIMIT ?');
				break;
			case Feature::INTEGER:
				$statement = $pdo->prepare('SELECT `Code` FROM ItemFeature WHERE Feature = ? AND `Value` = ? LIMIT ?');
				break;
			case Feature::ENUM:
				$statement = $pdo->prepare('SELECT `Code` FROM ItemFeature WHERE Feature = ? AND ValueEnum = ? LIMIT ?');
				break;
			case Feature::DOUBLE:
				$statement = $pdo->prepare('SELECT `Code` FROM ItemFeature WHERE Feature = ? AND ValueDouble = ? LIMIT ?');
				break;
			default:
				throw new \LogicException('Unknown feature type ' . $feature->type . ' returned by getFeatureTypeFromName (should never happen unless a cosmic ray flips a bit somewhere)');
		}

		$statement->bindValue(1, $feature->name, \PDO::PARAM_STR);
		$statement->bindValue(2, $feature->value);
		$statement->bindValue(3, $limit, \PDO::PARAM_INT);

		$result = [];

		try {
			$statement->execute();
			if($statement->rowCount() > 0) {
				foreach($statement as $row) {
					$result[] = new ItemIncomplete($row['Code']);
				}
				return $result;
			}
		} finally {
			$statement->closeCursor();
		}
		return $result;
	}

	private $getFeaturesStatement = null;

	/**
	 * Add features to an item
	 *
	 * @param ItemFeatures $item
	 *
	 * @return ItemFeatures|Item same item
	 */
	public function getFeatures(ItemFeatures $item) {
		/*
		 * This seemed a good query to fetch default and non-default features, when database structure was different:
		 *
		 * SELECT Item2.ItemID, Item2.ItemFor, Feature.FeatureName, COALESCE(ItemFeature.`Value`, ItemFeature.ValueText, FeatureValue.ValueText) AS `FeatureValue`
		 * FROM (SELECT ItemID, ItemID AS ItemFor FROM Item UNION ALL SELECT `Default` AS ItemID, ItemID AS ItemFor FROM Item WHERE `Default` IS NOT NULL)  Item2
		 * JOIN ItemFeature ON  Item2.ItemID = ItemFeature.ItemID
		 * JOIN Feature ON ItemFeature.FeatureID = Feature.FeatureID
		 * LEFT JOIN FeatureValue ON ItemFeature.FeatureID = FeatureValue.FeatureID
		 * WHERE (ItemFeature.ValueEnum = FeatureValue.ValueEnum OR ItemFeature.ValueEnum IS NULL)
		 * AND Item2.ItemID IN (1, 2, 3);
		 *
		 * However, the subquery gives the correct and expected result, but the main query loses FOR UNFATHOMABLE REASONS the second half of the UNIONed data.
		 * So we're doing two queries. That UNION probably killed performance, too, so it's acceptable anyway.
		 *
		 * TODO: retry with new structure: who knows, it might work!
		 */

		// TODO: default features
		if($this->getFeaturesStatement === null) {
			$this->getFeaturesStatement = $this->getPDO()->prepare('SELECT Feature, COALESCE(`Value`, ValueText, ValueEnum, ValueDouble) AS `Value`
            FROM ItemFeature
            WHERE `Code` = :cod;');
		}

		$this->getFeaturesStatement->bindValue(':cod', $item->getCode(), \PDO::PARAM_STR);

		try {
			$this->getFeaturesStatement->execute();
			if($this->getFeaturesStatement->rowCount() > 0) {
				foreach($this->getFeaturesStatement as $row) {
					/** @var Item[] $items */
					$item->addFeature(Feature::ofString($row['Feature'], $row['Value']));
				}
			}
		} finally {
			$this->getFeaturesStatement->closeCursor();
		}

		return $item;
	}

	private $featureNumberStatement = null;
	private $featureTextStatement = null;
	private $featureEnumStatement = null;
	private $featureDoubleStatement = null;
	private $featureAudit = null;

	public function setFeatures(ItemFeatures $item) {
		$features = $item->getFeatures();

		if(empty($features)) {
			return;
		}

		$pdo = $this->getPDO();

		if($this->featureNumberStatement === null) {
			$this->featureNumberStatement = $pdo->prepare('INSERT INTO ItemFeature (Feature, `Code`, `Value`) VALUES (:feature, :item, :val) ON DUPLICATE KEY UPDATE `Value`=:val2');
		}
		if($this->featureTextStatement === null) {
			$this->featureTextStatement = $pdo->prepare('INSERT INTO ItemFeature (Feature, `Code`, `ValueText`) VALUES (:feature, :item, :val) ON DUPLICATE KEY UPDATE `ValueText`=:val2');
		}
		if($this->featureEnumStatement === null) {
			$this->featureEnumStatement = $pdo->prepare('INSERT INTO ItemFeature (Feature, `Code`, `ValueEnum`) VALUES (:feature, :item, :val) ON DUPLICATE KEY UPDATE `ValueEnum`=:val2');
		}
		if($this->featureDoubleStatement === null) {
			$this->featureDoubleStatement = $pdo->prepare('INSERT INTO ItemFeature (Feature, `Code`, `ValueDouble`) VALUES (:feature, :item, :val) ON DUPLICATE KEY UPDATE `ValueDouble`=:val2');
		}
		if($this->featureAudit === null) {
			$this->featureAudit = $pdo->prepare('INSERT INTO Audit (Code, `Change`, User) VALUES (?, \'U\', @taralloAuditUsername)');
		}

		foreach($features as $feature) {
			$name = $feature->name;
			$value = $feature->value;
			switch($feature->type) {
				// was really tempted to use variable variables here...
				case Feature::STRING:
					$statement = $this->featureTextStatement;
					$type = \PDO::PARAM_STR;
					break;
				case Feature::INTEGER:
					$statement = $this->featureNumberStatement;
					$type = \PDO::PARAM_INT;
					break;
				case Feature::ENUM:
					$statement = $this->featureEnumStatement;
					$type = \PDO::PARAM_STR;
					break;
				case Feature::DOUBLE:
					$statement = $this->featureDoubleStatement;
					$type = \PDO::PARAM_STR;
					break;
				default:
					throw new \LogicException('Unknown feature type ' . $feature->type . ' returned by getFeatureTypeFromName (should never happen unless a cosmic ray flips a bit somewhere)');
			}
			try {
				$statement->bindValue(':feature', $name, \PDO::PARAM_STR);
				$statement->bindValue(':item', $item->getCode(), \PDO::PARAM_STR);
				$statement->bindValue(':val', $value, $type);
				$statement->bindValue(':val2', $value, $type);
				if(!$statement->execute()) {
					throw new DatabaseException("Cannot add/upadate feature $name with value $value for item " . $item->getCode());
				}
			} finally {
				$statement->closeCursor();
			}
		}

		try {
			if(!$this->featureAudit->execute([$item->getCode()])) {
				throw new DatabaseException('Cannot add audit table entry for features update of ' . $item->getCode());
			}
		} finally {
			$this->featureAudit->closeCursor();
		}
	}

	private $deleteFeatureStatement = null;

	/**
	 * Delete a single feature from an item
	 *
	 * @param ItemFeatures $item
	 * @param string $feature
	 */
	public function deleteFeature(ItemFeatures $item, $feature) {
		if(!is_string($feature)) {
			throw new \InvalidArgumentException('Name of feature to be deleted should be a string');
		}

		if($this->deleteFeatureStatement === null) {
			$this->deleteFeatureStatement = $this->getPDO()->prepare('DELETE IGNORE FROM ItemFeature WHERE `Code` = ? AND `Feature`= ?');
		}

		try {
			if(!$this->deleteFeatureStatement->execute([$item->getCode(), $feature])) {
				throw new DatabaseException("Cannot delete feature $feature from " . $item->getCode() . ' for unknown reasons');
			}
		} finally {
			$this->deleteFeatureStatement->closeCursor();
		}
	}

	private $deleteFeaturesAllStatement = null;

	/**
	 * Delete all features from an item
	 *
	 * @param ItemIncomplete $item
	 */
	public function deleteFeaturesAll(ItemIncomplete $item) {
		if($this->deleteFeaturesAllStatement === null) {
			$this->deleteFeaturesAllStatement = $this->getPDO()->prepare('DELETE IGNORE FROM ItemFeature WHERE `Code` = ?');
		}

		try {
			if(!$this->deleteFeaturesAllStatement->execute([$item->getCode()])) {
				throw new DatabaseException('Cannot delete features from ' . $item->getCode() . ' for unknown reasons');
			}
		} finally {
			$this->deleteFeaturesAllStatement->closeCursor();
		}
	}

	/**
	 * Count how many items have each possible value for a feature
	 *
	 * e.g. with feature name = "color":
	 * - red: 10
	 * - yellow: 6
	 * - grey: 4
	 * and so on.
	 *
	 * If some (enum) values aren't assigned to an item they're not reported, actually,
	 * so it's not really every possible value.
	 *
	 * @param string $feature Feature name
	 * @param int $limit max number of rows to retrieve
	 *
	 * @return int[] value => count, sorted by count descending
	 */
	public function groupItemsByValue(string $feature, int $limit = 100): array {
		$pdo = $this->getPDO();

		$statement = $pdo->prepare('SELECT COALESCE(`Value`, ValueText, ValueEnum, ValueDouble) AS `Value`, COUNT(*) as Quantity FROM ItemFeature WHERE Feature = ? LIMIT ?');

		$result = [];
		try {
			if(!$statement->execute([$feature])) {
				throw new DatabaseException("Cannot search items with feature \"$feature\"");
			}
			if($statement->rowCount() > 0) {
				$result = $statement->fetchAll(\PDO::FETCH_NUM)[0];
			}
		} finally {
			$statement->closeCursor();
		}

		return $result;
	}
}
