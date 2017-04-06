<?php

namespace WEEEOpen\Tarallo\Database;
use WEEEOpen\Tarallo\Item;
use WEEEOpen\Tarallo\InvalidParameterException;

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


    private function searchPrepare($searches) {
        // TODO: this need more thought, searches are for Feature(s)
        // TODO: search numeric values too!
        if(self::isArrayAndFull($searches)) {
            $where = '(';
            foreach($searches as $k => $loc) {
                $where .= '(`FeatureName` = :searchname'.$k.' AND ValueText LIKE :searchkey'.$k.' OR '; // TODO: %
            }
            return substr($where, 0, strlen($where)-4).')'; // remove last " OR "
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

    private static function isArrayAndFull($something) {
        if(is_array($something) && !empty($something)) {
            return true;
        } else {
            return false;
        }
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
        $sortOrder  = $this->sortPrepare($sorts); // $arrayOfSortKeysAndOrder wasn't a very good name, either...
        $whereLocationToken = $this->implodeOptionalAnd($this->locationPrepare($locations), $this->tokenPrepare($token));
        $searchWhere = $this->implodeOptionalAndAnd($this->searchPrepare($searches));
        $parentWhere = $this->implodeOptionalAnd(''); // TODO: implement, "WHERE Depth = 0" by default, use = to find only the needed roots (descendants are selected via /Depth)
        $depthDefaultWhere  = $this->implodeOptionalAnd($this->depthPrepare($depth), 'isDefault = 0');

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
                ' . $whereLocationToken . '
                AND ItemID IN (
                    SELECT `ItemID`
                    FROM ItemFeature, Feature
                    WHERE Feature.FeatureID = ItemFeature.FeatureID
                    ' . $searchWhere . '
                )
            ) AND ' . $parentWhere . ';
        ) ' . $depthDefaultWhere . '
        

		');
        $s->execute();
        if($s->rowCount() === 0) {
            return [];
        } else {
            return $s->fetchAll(); // TODO: return Item objects
        }
    }

    public function addItems($items, $parent = null, $default = false) { // TODO: somehow find parent (pass code from JSON request?)
        $pdo = $this->getPDO();
        // TODO: split these into other functions

        $this->addItemsInternal($items, $parent, $default);
    }

    public function addItemsInternal($items, $parent = null, $default = false) {
        if($items instanceof Item) {
            $items = [$items];
        } else if(!is_array($items)) {
            throw new \InvalidArgumentException('Items must be passed as an array or a single Item');
        }

        if(empty($items)) {
            return;
        }

        $pdo = $this->getPDO();
        // TODO: recursively insert other elements...

        foreach($items as $item) {
            $id = $this->addItem($item, $default);
            /** @var Item $item */
	        $this->database->featureDAO()->addFeatures($id, $item->getFeatures());
            $this->setItemModified($id);
        }

        return;
    }

    private $addItemStatement = null;

    /**
     * Insert a single item into the database, return its id. Basically just add a row to Item, no features are added.
     * Must be called while in transaction.
     *
     * @param bool $default
     * @param Item $item the item to be inserted
     * @see addItems
     *
     * @return int ItemID. 0 may also mean "error", BECAUSE PDO.
     */
    private function addItem(Item $item, $default = false) {
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
        return (int) $pdo->lastInsertId();
    }

    private function setItemModified($itemID) {
        $pdo = $this->getPDO();
        $stuff = $pdo->prepare('INSERT INTO ItemModification (ModificationID, ItemID) VALUES (?, ?)');
        $stuff->execute([$this->getModificationId(), $itemID]);
    }


}