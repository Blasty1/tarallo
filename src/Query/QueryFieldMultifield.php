<?php
namespace WEEEOpen\Tarallo\Query;


abstract class QueryFieldMultifield extends AbstractQueryField implements QueryField {
	protected function arrayInit() {
		if($this->content === null) {
			$this->content = [];
		}
	}

	public function add($something) {
		$this->arrayInit();
		$this->content[] = $something;
	}

}