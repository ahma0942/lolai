<?php

namespace Models;

enum Role: int
{
  case BLUE_TOP = 0;
  case BLUE_JUNGLE = 1;
  case BLUE_MID = 2;
  case BLUE_ADC = 3;
  case BLUE_SUPPORT = 4;
  case RED_TOP = 5;
  case RED_JUNGLE = 6;
  case RED_MID = 7;
  case RED_ADC = 8;
  case RED_SUPPORT = 9;

  public function isRedSide(): bool
  {
    return in_array($this, [Role::RED_TOP, Role::RED_JUNGLE, Role::RED_MID, Role::RED_ADC, Role::RED_SUPPORT]);
  }

  public function isBlueSide(): bool
  {
    return in_array($this, [Role::BLUE_TOP, Role::BLUE_JUNGLE, Role::BLUE_MID, Role::BLUE_ADC, Role::BLUE_SUPPORT]);
  }
}
