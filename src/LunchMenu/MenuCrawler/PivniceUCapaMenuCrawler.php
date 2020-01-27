<?php

namespace Toustobot\LunchMenu\MenuCrawler;

use Nette\Utils\Strings;
use Symfony\Component\DomCrawler\Crawler;
use Toustobot\LunchMenu\IMenuCrawler;
use Toustobot\LunchMenu\MenuOption;


class PivniceUCapaMenuCrawler implements IMenuCrawler
{
	private const NAME = 'Pivnice u čápa';
	private const URL = 'https://www.pivnice-ucapa.cz/denni-menu.php';


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
		$day = $crawler->filter('.listek > .row')
			->reduce(function (Crawler $node, int $i) use ($date) : bool {
				return $node->filter('.date')->text() === $date->format('j. n. Y');
			});

		$menus = $day->filter('.cont');
		$dishes = $menus->filter('.row-food');
		$options = [];

		$dishes->each(function ($dish) use (& $options) {
			$text = $dish->filter('.food')->text();
			$id = Strings::replace($text, '~(\d+)\..*~u', '\\1');
			$title = trim(Strings::replace($text, '~\d+\.(.*)~u', '\\1'));
			$price = (int) $dish->filter('.price')->text();

			$option = new MenuOption($id, $title);
			$option->setPrice($price);
			$options[] = $option;
		});

		return [
			'url' => self::URL,
			'options' => $options,
			'soups' => [

			],
		];
	}
}
