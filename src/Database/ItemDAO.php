<?php

namespace WEEEOpen\Tarallo\Database;
use WEEEOpen\Tarallo\InvalidParameterException;
use WEEEOpen\Tarallo\Item;
use WEEEOpen\Tarallo\ItemIncomplete;
use WEEEOpen\Tarallo\Query\SearchTriplet;

final class ItemDAO extends DAO {
    private function depthPrepare($depth) {
        if(is_int($depth)) {
            return 'WHERE `Depth` <= :depth';
        } else {
            return 'WHERE `Depth` IS NOT NULL';
        }
    }

    private function locationPrepare($locations) {
        if(self::isArrayAndFull($locations)) {
            $locationWhere = 'AND `Name` ' . $this->multipleIn(':location', $locations);
            return rtrim($locationWhere, ', ').')';
        } else {
            return '';
        }
    }

    private function sortPrepare($sorts) {
        if(self::isArrayAndFull($sorts)) {
            $order = 'ORDER BY ';
            if(self::isArrayAndFull($sorts)) {
                foreach($sorts as $key => $ascdesc) {
                    $order .= $key . ' ' . $ascdesc . ', ';
                }
            }
            return $order;
        } else {
            return '';
        }
    }

    private function tokenPrepare($token) {
        if(is_string($token) && $token !== null) {
            return 'Token = :token';
        } else {
            return '';
        }
    }

	/**
	 * @param $searches array of SearchTriplet
	 * @return string piece of query string
	 * @see FeatureDAO::getWhereStringFromSearches
	 */
	private function searchPrepare($searches) {
		if(!is_array($searches)) {
			throw new \InvalidArgumentException('Search parameters must be passed as an array');
		}
		if(empty($searches)) {
			return '';
		}

		return $this->database->featureDAO()->getWhereStringFromSearches($searches);
    }

    private static function implodeOptionalAndAnd() {
        $args = func_get_args();
        $where = self::implodeAnd($args);
        if($where === '') {
            return '';
        } else {
            return ' AND ' . $where;
        }
    }

    private static function implodeOptionalAnd() {
        $args = func_get_args();
        return self::implodeAnd($args);
    }

    /**
     * Join non-empty string arguments via " AND " to add in a WHERE clause.
     *
     * @see implodeOptionalAnd
     * @see implodeOptionalWhereAnd
     * @param $args string[]
     * @return string empty string or WHERE clauses separated by AND (no WHERE itself)
     */
    private static function implodeAnd($args) {
        $stuff = [];
        foreach($args as $arg) {
            if(is_string($arg) && strlen($arg) > 0) {
                $stuff[] = $arg;
            }
        }
        $c = count($stuff);
        if($c === 0) {
            return '';
        }
        return implode(' AND ', $stuff);
    }

    public function getItem($locations, $searches, $depth, $sorts, $token) {
        $items = $this->getItemItself($locations, $searches, $depth, $sorts, $token);
        $itemIDs = []; // TODO: implement
        if(!empty($itemIDs)) {
            $features = $this->database->featureDAO()->getFeatures($itemIDs);
            foreach($features as $k => $feat) {
                foreach($feat as $f => $val) {
                    $items[$k]->addFeature($f, $val);
                }
            }
        }
        return $items;
    }

    private function getItemItself($locations, $searches, $depth, $sorts, $token) {
        if(self::isArrayAndFull($searches)) {
	        $searchSubquery = '
	        ItemID IN (
                    SELECT ItemID
		            FROM Feature, ItemFeature
		            LEFT JOIN FeatureValue ON ItemFeature.FeatureID = FeatureValue.FeatureID
		            WHERE ItemFeature.FeatureID = Feature.FeatureID AND (ItemFeature.ValueEnum = FeatureValue.ValueEnum OR ItemFeature.ValueEnum IS NULL)
		            AND (' . $this->searchPrepare($searches) . ')
		        )
	        ';
        } else {
	        $searchSubquery = '';
        }

	    //$sortOrder  = $this->sortPrepare($sorts); // $arrayOfSortKeysAndOrder wasn't a very good name, either...
	    $parentWhere = $this->implodeOptionalAnd(''); // TODO: implement, "WHERE Depth = 0" by default, use = to find only the needed roots (descendants are selected via /Depth)
	    $depthDefaultWhere  = $this->implodeOptionalAnd($this->depthPrepare($depth), 'isDefault = 0');
	    $whereLocationTokenSearch = $this->implodeOptionalAnd($this->locationPrepare($locations), $this->tokenPrepare($token), $searchSubquery);

        // This will probably blow up in a spectacular way.
        // Search items by features, filter by location and token, tree lookup using these items as descendants
        // (for /Parent), tree lookup using new root items as roots (find all descendants), filter by depth,
        // join with items, SELECT.
        // TODO: somehow sort the result set (not the innermost query, Parent returns other items...).
        $s = $this->getPDO()->prepare('
        SELECT `ItemID`, `Code`, `AncestorID`, `Depth`
        FROM Tree, Item
        WHERE Tree.AncestorID = Item.ItemID
        AND AncestorID IN (
            SELECT `ItemID`
            FROM Tree
            WHERE DescendantID IN ( 
                SELECT `ItemID`
                FROM Item
                WHERE
                ' . $whereLocationTokenSearch . '
            ) AND ' . $parentWhere . ';
        ) ' . $depthDefaultWhere . '
		');

        $s->bindValue(':token', $token);

        foreach($locations as $numericKey => $location) {
	        $s->bindValue(':location' . $numericKey, $location);
        }

        foreach($searches as $numericKey => $triplet) {
        	/** @var SearchTriplet $triplet */
        	$s->bindValue(':searchname' . $numericKey, $triplet->getKey());
        	$s->bindValue(':searchvalue' . $numericKey, $triplet->getValue());
        }

        $s->execute();
        if($s->rowCount() === 0) {
            return [];
        } else {
        	$all = $s->fetchAll();
        	$s->closeCursor();
            return $all; // TODO: return Item objects
        }
    }

    public function addItems($items, ItemIncomplete $parent = null, $default = false) { // TODO: somehow find parent (pass code from JSON request?)
        if($items instanceof Item) {
            $items = [$items];
        } else if(!is_array($items)) {
            throw new \InvalidArgumentException('Items must be passed as an array or a single Item');
        }

        if(empty($items)) {
            return;
        }

        if($parent instanceof ItemIncomplete) {
        	$parent = $this->getItemId($parent);
        }

        foreach($items as $item) {
            $this->addItem($item, $parent, $default);
        }

        return;
    }

    private $addItemStatement = null;

	/**
	 * Insert a single item into the database, return its id. Basically just add a row to Item, no features are added.
	 * Must be called while in transaction.
	 *
	 * @param Item $item the item to be inserted
	 * @param ItemIncomplete $parent parent item
	 * @param bool $default
	 *
	 * @see addItems
	 *
	 */
    private function addItem(Item $item, ItemIncomplete $parent = null, $default = false) {
        if(!($item instanceof Item)) {
            throw new \InvalidArgumentException('Items must be objects of Item class, ' . gettype($item) . ' given'); // will say "object" if it's another object which is kinda useless, whatever
        }

        $pdo = $this->getPDO();
        if(!$pdo->inTransaction()) {
            throw new \LogicException('addItem called outside of transaction');
        }

        if($this->addItemStatement === null) {
	        $this->addItemStatement = $pdo->prepare('INSERT INTO Item (`Code`, IsDefault) VALUES (:c, :d)');
        }

	    $this->addItemStatement->bindValue(':c', $item->getCode(), \PDO::PARAM_STR);
	    $this->addItemStatement->bindValue(':d', $default, \PDO::PARAM_INT);
	    $this->addItemStatement->execute();

	    /** @var Item $item */
	    $this->database->featureDAO()->addFeatures($item);

	    $this->setItemModified($item);

	    $this->addToTree($item, $parent);

	    $childItems = $item->getChildren();
	    foreach($childItems as $childItem) {
	    	// yay recursion!
	    	$this->addItem($childItem, $item, $default);
	    }
    }

    private $itemModifiedStatement = null;

    private function setItemModified(ItemIncomplete $item) {
        $pdo = $this->getPDO();
        if($this->itemModifiedStatement === null) {
	        $this->itemModifiedStatement = $pdo->prepare('INSERT INTO ItemModification (ModificationID, ItemID) SELECT ?, ItemID FROM Item WHERE Item.Code = ?');
        }
	    $this->itemModifiedStatement->execute([$this->database->getModificationId(), $item->getCode()]);
    }

	private function addToTree(ItemIncomplete $child, ItemIncomplete $parent = null) {

    	if($parent === null) {
    		$parent = $child;
	    }

    	$this->addToTreeOnlyItself($child);
    	$this->setParentInTree($parent, $child);
    }

	private $addToTreeOnlyItselfStatement = null;
	private function addToTreeOnlyItself($id) {
		if($this->addToTreeOnlyItselfStatement === null) {
			$this->addToTreeOnlyItselfStatement = $this->getPDO()->prepare('INSERT INTO Tree (AncestorID, DescendantID, Depth) VALUES (?, ?, 0)');
		}
		$this->addToTreeOnlyItselfStatement->execute([$id, $id]);
	}

	private $setParentInTreeStatement = null;
	/**
	 * addEdge, basically. Use addToTreeOnlyItself() first. Or use addToTree() and that's it.
	 *
	 * @see addToTreeOnlyItself
	 * @see addToTree
	 * @param ItemIncomplete $parent
	 * @param ItemIncomplete $child
	 */
	private function setParentInTree(ItemIncomplete $parent, ItemIncomplete $child) {
    	$pdo = $this->getPDO();
    	if($this->setParentInTreeStatement === null) {
		    $this->setParentInTreeStatement = $pdo->prepare('
			INSERT INTO Tree (AncestorID, DescendantID, Depth)
			SELECT ltree.AncestorID, rtree.DescendantID, ltree.Depth+rtree.Depth+1
            FROM Tree ltree, Tree rtree
			WHERE ltree.DescendantID = ? AND rtree.AncestorID = ?');
	    }
	    $this->setParentInTreeStatement->execute([$this->getItemId($parent), $this->getItemId($child)]);
	}

	private $getItemIdStatement = null;
	private $getItemIdCache = [];
	public function getItemId(ItemIncomplete $item) {
		$code = $item->getCode();
		if(isset($this->getItemIdCache[$code])) {
			// let's just HOPE this thing doesn't blow up catastrophically.
			return $this->getItemIdCache[$code];
		}

		if($this->getItemIdStatement === null) {
			$this->getItemIdStatement = $this->getPDO()->prepare('SELECT ItemID FROM Item WHERE `Code` = ? LIMIT 1');
		}

		$this->getItemIdStatement->execute([$code]);
		if($this->getItemIdStatement->rowCount() === 0) {
			throw new InvalidParameterException('Unknown item ' . $item->getCode());
		} else {
			$id = $this->getItemIdStatement->fetch(\PDO::FETCH_NUM)[0];
			$this->getItemIdStatement->closeCursor();
			$this->getItemIdCache[$code] = $id;
			return $id;
		}
	}
}