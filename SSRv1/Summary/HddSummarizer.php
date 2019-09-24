<?php

namespace WEEEOpen\Tarallo\SSRv1\Summary;


use WEEEOpen\Tarallo\Server\ItemWithFeatures;
use WEEEOpen\Tarallo\SSRv1\FeaturePrinter;

class HddSummarizer implements Summarizer {

	public static function summarize(ItemWithFeatures $item): string {
		$type = $item->getFeature('type');
		$capacity = $item->getFeature('capacity-decibyte');
		$sataPorts = $item->getFeature('sata-ports-n');
		$idePorts = $item->getFeature('ide-ports-n');
		$miniIdePorts = $item->getFeature('mini-ide-ports-n');
		$scsiPorts = $item->getFeature('scsi-sca2-ports-n');
		$scsi2Ports = $item->getFeature('scsi-db68-ports-n');
		$formFactor = $item->getFeature('hdd-form-factor');
		$spinRate = $item->getFeature('spin-rate-rpm');
		$dataErased = $item->getFeature('data-erased');
		$smartData = $item->getFeature('smart-data');
		$surfaceScan = $item->getFeature('surface-scan');
		$brand = $item->getFeature('brand');
		$family = $item->getFeature('family');
		$model = $item->getFeature('model');
		$os = $item->getFeature('software');


		$hardware = FeaturePrinter::printableValue($type);
		$hardware .= $capacity ? ' ' . FeaturePrinter::printableValue($capacity) : '';
		$ports = '';
		if($sataPorts !== null) {
			if($sataPorts->value == 1) {
				$ports .= ' ' . FeaturePrinter::printableName('sata-ports-n');
			} else {
				$ports .= ' ' . $sataPorts . '×' . FeaturePrinter::printableName('sata-ports-n');
			}
		}

		if($idePorts !== null) {
			$ports .= $ports ? ' +' : '';
			if($idePorts->value == 1) {
				$ports .= ' ' . FeaturePrinter::printableName('ide-ports-n');
			} else {
				$ports .= ' ' . $idePorts . '×' . FeaturePrinter::printableName('ide-ports-n');
			}
		}

		if($miniIdePorts !== null) {
			$ports .= $ports ? ' +' : '';
			if($miniIdePorts->value == 1) {
				$ports .= ' ' . FeaturePrinter::printableName('mini-ide-ports-n');
			} else {
				$ports .= ' ' . $miniIdePorts . '×' . FeaturePrinter::printableName('mini-ide-ports-n');
			}
		}

		if($scsiPorts !== null) {
			$ports .= $ports ? ' +' : '';
			if($scsiPorts->value == 1) {
				$ports .= ' ' . FeaturePrinter::printableName('scsi-sca2-ports-n');
			} else {
				$ports .= ' ' . $scsiPorts . '×' . FeaturePrinter::printableName('scsi-sca2-ports-n');
			}
		}

		if($scsi2Ports !== null) {
			$ports .= $ports ? ' +' : '';
			if($scsi2Ports->value == 1) {
				$ports .= ' ' . FeaturePrinter::printableName('scsi-db68-ports-n');
			} else {
				$ports .= ' ' . $scsi2Ports . '×' . FeaturePrinter::printableName('scsi-db68-ports-n');
			}
		}

		$hardware .= $ports;

		$hardware .= $formFactor ? ' ' . FeaturePrinter::printableValue($formFactor) : '';
		$hardware .= $spinRate ? ' ' . FeaturePrinter::printableValue($spinRate) : '';

		$dataErased = $dataErased ? $dataErased->value : '_';
		$procedures = $dataErased === 'yes' ? 'E' : '_';

		$smartData = $smartData ? $smartData->value : '_';
		if($smartData === 'ok') {
			$procedures .= 'S';
		} else if($smartData === 'old') {
			$procedures .= 'O';
		} else if($smartData === 'fail') {
			$procedures .= 'X';
		} else {
			$procedures .= '_';
		}

		$surfaceScan = $surfaceScan ? $surfaceScan->value : '_';
		if($surfaceScan === 'pass') {
			$procedures .= 'P';
		} else if($surfaceScan === 'fail') {
			$procedures .= 'X';
		} else {
			$procedures .= '_';
		}

		// TODO: create a CommercialSummarizer for PartialSummaries that considers family (only for some items like HDD and RAM)
		$commercial = $brand ? FeaturePrinter::printableValue($brand) : '';
		$commercial .= $family ? ' ' . FeaturePrinter::printableValue($family) : '';
		$commercial .= $model ? ' ' . FeaturePrinter::printableValue($model) : '';

		$pretty = $hardware . ', ' . $procedures;
		$pretty .= $commercial ? ', ' . $commercial : '';
		$pretty .= $os ? ', ' . $os : '';

		return $pretty;
	}
}
