<?php

namespace Toustobot\LunchMenu;

use DateTime;
use Nette\Utils\Strings;


class MenuFormatter
{
	private static $weekdays = [
		'neděle',
		'pondělí',
		'úterý',
		'středa',
		'čtvrtek',
		'pátek',
		'sobota',
	];


	public function formatHeader(DateTime $date): string
	{
		return '*Těpéro sombréro! Jak jde život? Dnes je '
			. self::$weekdays[$date->format('w')]
			. ' '
			. $date->format('j.n.')
			. ' Nedá si někdo toast?*';
	}

	public function formatMenuHeader(string $name, string $url): string
	{
		return "*$name* (<$url|link>)\n";
	}

	public function formatMenuSoups(array $menu): string
	{
		if (count($menu['soups']) < 1) {
			return '';
		}

		if (count($menu['soups'])) {
			return 'Polévka: ' . $menu['soups'][0] . "\n";
		} else {
			return 'Polévky: ' . implode(', ', $menu['soups']) . "\n";
		}
	}

	public function formatMenuBody(array $menu): string
	{
		$text = '';
		foreach ($menu['options'] as $option) {
			assert($option instanceof MenuOption);
			$listNumber = count($menu['options']) <= 1 ? '' : $option->getId() . ') ';
			$price = (Strings::contains($option->getText(), "\n") ? "\n" : ' ')  . "[{$option->getPrice()}\u{2009}Kč]";
			$text .= $listNumber . $option->getText() . "$price\n";
		}
		return $text;
	}
}
