<?php

use Phpml\Classification\KNearestNeighbors;
use Phpml\Classification\NaiveBayes;

class ML
{
  function naive_bayes($matches)
  {
    $samples = [];
    $labels = [];
    foreach ($matches as $match) {
      $samples[] = [
        $match['blue_top_champion'],
        $match['blue_jungle_champion'],
        $match['blue_mid_champion'],
        $match['blue_adc_champion'],
        $match['blue_support_champion'],
        $match['red_top_champion'],
        $match['red_jungle_champion'],
        $match['red_mid_champion'],
        $match['red_adc_champion'],
        $match['red_support_champion']
      ];
      $labels[] = $match['status'];
    }

    $classifier = new NaiveBayes();
    $classifier->train($samples, $labels);

    return $classifier;
  }

  function knn($matches)
  {
    $samples = [];
    $labels = [];
    foreach ($matches as $match) {
      $samples[] = [
        $match['blue_top_champion'],
        $match['blue_jungle_champion'],
        $match['blue_mid_champion'],
        $match['blue_adc_champion'],
        $match['blue_support_champion'],
        $match['red_top_champion'],
        $match['red_jungle_champion'],
        $match['red_mid_champion'],
        $match['red_adc_champion'],
        $match['red_support_champion']
      ];
      $labels[] = $match['status'];
    }

    $classifier = new KNearestNeighbors();
    $classifier->train($samples, $labels);

    return $classifier;
  }
}
