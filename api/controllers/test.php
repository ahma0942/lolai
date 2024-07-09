<?php

use \RedBeanPHP\R as R;

DI::rest()->get('/ping', function () {
	http(200, "pong\n");
});

DI::rest()->get('/test', function () {
	$correct = ['RED_WIN', 'RED_WIN', 'BLUE_WIN', 'RED_WIN', 'BLUE_WIN', 'BLUE_WIN', 'RED_WIN', 'RED_WIN', 'BLUE_WIN'];
	$predicted = [];
	$algo = 'KNN';
	$lookups = R::findAll('lookup');
	$matches = R::exportAll(R::findAll('match',
		'(blue_top_id = ? OR
	blue_jungle_id = ? OR blue_mid_id = ? OR
	blue_adc_id = ? OR blue_support_id = ? OR
	red_top_id = ? OR red_jungle_id = ? OR
	red_mid_id = ? OR red_adc_id = ? OR
	red_support_id = ?) AND status != ?',
		['4', '4', '4', '4', '4', '4', '4', '4', '4', '4', 'pending']
	));
	foreach ($lookups as $lookup) {
		if ($algo == 'NAIVE_BAYES') {
			$classifier = DI::ML()->naive_bayes($matches, $lookup);
			$predicted[] = $classifier->predict([
				$lookup['blue_top_champion'],
				$lookup['blue_jungle_champion'],
				$lookup['blue_mid_champion'],
				$lookup['blue_adc_champion'],
				$lookup['blue_support_champion'],
				$lookup['red_top_champion'],
				$lookup['red_jungle_champion'],
				$lookup['red_mid_champion'],
				$lookup['red_adc_champion'],
				$lookup['red_support_champion']
			]);
		} else {
			$classifier = DI::ML()->knn($matches, $lookup);
			$predicted[] = $classifier->predict([
				$lookup['blue_top_champion'],
				$lookup['blue_jungle_champion'],
				$lookup['blue_mid_champion'],
				$lookup['blue_adc_champion'],
				$lookup['blue_support_champion'],
				$lookup['red_top_champion'],
				$lookup['red_jungle_champion'],
				$lookup['red_mid_champion'],
				$lookup['red_adc_champion'],
				$lookup['red_support_champion']
			]);
		}
	}

	http(200, [$correct, $predicted], true);
});
