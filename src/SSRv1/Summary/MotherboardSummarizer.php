<?php

namespace WEEEOpen\Tarallo\SSRv1\Summary;

use WEEEOpen\Tarallo\ItemWithFeatures;
use WEEEOpen\Tarallo\SSRv1\FeaturePrinter;

class MotherboardSummarizer
{
	public static function summarize(ItemWithFeatures $item): string
	{
		$type = $item->getFeature('type');
		$socket = $item->getFeature('cpu-socket');
		$formFactor = $item->getFeature('motherboard-form-factor');
		$color = $item->getFeature('color');

		$type = FeaturePrinter::printableValue($type);
		$type .= $formFactor ? ' ' . FeaturePrinter::printableValue($formFactor) : '';

		if ($socket) {
			$value = FeaturePrinter::printableValue($socket);
			if (substr($value, 0, 6) === 'Socket') {
				$theWordSocketLiterally = '';
			} else {
				$theWordSocketLiterally = str_replace(' (CPU)', '', FeaturePrinter::printableName('cpu-socket')) . ' ';
			}
			$type .= ' ' . $theWordSocketLiterally . $value;
		}

		$ports = PartialSummaries::summarizePorts($item, false, ' ');
		$ports = $ports ? ", $ports" : '';

		$sockets = PartialSummaries::summarizeSockets($item, false, ' ');
		$sockets = $sockets ? ", $sockets" : '';

		$color = $color = $color ? ', ' . FeaturePrinter::printableValue($color) : '';

		$commercial = PartialSummaries::summarizeCommercial($item);
		$commercial = $commercial ? ", $commercial" : '';

		$pretty =  $type . $sockets . $ports . $color . $commercial;
		return $pretty;
	}
}
