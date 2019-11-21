<?php

namespace Toustobot\LunchMenu\MenuCrawler;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Nette\Utils\Json;
use Toustobot\LunchMenu\IMenuCrawler;
use Toustobot\LunchMenu\MenuOption;


class SuziesMenuCrawler implements IMenuCrawler
{
	/** @var string */
	private $apiKey;


	public function __construct(string $apiKey)
	{
		$this->apiKey = $apiKey;
	}


	public function getName(): string
	{
		return 'Suzie\'s';
	}


	public function getUrl(): string
	{
		return 'https://www.zomato.com/cs/brno/suzies-veve%C5%99%C3%AD-brno-st%C5%99ed/denn%C3%AD-menu';
	}


	public function getMenu(\DateTimeInterface $date): array
	{
		$httpClient = new Client();
		$request = $httpClient->get(
			'https://developers.zomato.com/api/v2.1/dailymenu',
			[
				RequestOptions::HEADERS => [
					'user-key' => $this->apiKey,
				],
				RequestOptions::QUERY => [
					'res_id' => 16506939,
				],
			]
		);
		$response = $request->getBody()->getContents();
		$parsed = Json::decode($response);

		$soup = null;
		$options = [];
		$id = 1;

		foreach ($parsed->daily_menus[0]->daily_menu->dishes as $i => $optionData) {
			$dish = $optionData->dish;
			if ($i === 0) {
				continue;
			} else if ($i === 1) {
				$soup = $dish->name;
			} else {
				$option = new MenuOption($id++, $dish->name ?? '');
				$option->setPrice((float) $dish->price);
				$options[] = $option;
			}
		}

		return [
			'url' => $this->getUrl(),
			'options' => $options,
			'soups' => [$soup]
		];
	}
}
