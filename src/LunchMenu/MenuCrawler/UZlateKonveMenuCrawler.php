<?php

namespace Toustobot\LunchMenu\MenuCrawler;

use Nette\Utils\Strings;
use Symfony\Component\DomCrawler\Crawler;
use Toustobot\LunchMenu\IMenuCrawler;
use Toustobot\LunchMenu\MenuOption;


class UZlateKonveMenuCrawler implements IMenuCrawler
{
	private const NAME = 'U Zlaté konve';
	private const URL = 'http://www.u-zlate-konve.cz/cz/page/tydenni-menu.html';


	public function getName(): string
	{
		return self::NAME;
	}


	public function getUrl(): string
	{
		return self::URL;
	}


	public function getMenu(\DateTimeInterface $date): array
	{
		$html = file_get_contents(self::URL);

		$crawler = new Crawler($html);
		$title = $crawler->filter('.inner div')
			->reduce(function (Crawler $node, int $i) : bool {
				return $node->text() === 'MENU RESTAURACE U ZLATÉ KONVE';
			});

		$parent = $title->parents()->first();
		$neededDayIndex = max(min((int) $date->format('w'), 5), 1);
		$dayIndex = 0;
		$options = [];

		foreach ($parent->filter('div') as $node) {
			$line = trim($node->textContent);
			if (Strings::match($line, '~\d\)~')) {
				if (Strings::startsWith($line, '1)')) {
					$dayIndex++;
				}
				if ($dayIndex === $neededDayIndex) {
					$id = $line[0];
					$matches = Strings::match($line, '~^\d\)\s*(.+?)(?:(\d+)\s*(?:,-)?\s*Kč\s*)?$~');
					$title = trim($matches[1]);
					$price = (float) ($matches[2] ?? 99.0);
					$option = new MenuOption($id, $title);
					$option->setPrice($price);
					$options[] = $option;
				}
			}
		}

		return [
			'url' => self::URL,
			'options' => $options,
			'soups' => [

			],
		];
	}
}
