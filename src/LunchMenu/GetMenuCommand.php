<?php

namespace Toustobot\LunchMenu;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Toustobot\LunchMenu\MenuCrawler\PivniceUCapaMenuCrawler;
use Toustobot\LunchMenu\MenuCrawler\SuziesMenuCrawler;
use Toustobot\LunchMenu\MenuCrawler\UZlateKonveMenuCrawler;


class GetMenuCommand extends Command
{
	protected function configure()
	{
		$this
			->setName('get-menu')
			->addOption('slack-url', null, InputOption::VALUE_OPTIONAL, 'Slack webhook URL to send lunch menus')
			->addOption('zomato-user-key', null, InputOption::VALUE_OPTIONAL, 'Zomato user API-KEY for some menu crawlers');
	}


	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$now = new \DateTime();

		$menuCrawlers = [
//			new HelanMenuCrawler(),
//			new PlzenskyDvurMenuCrawler(),
//			new SelepkaMenuCrawler(),
//			new SonoMenuCrawler(),
//			new ZelenaKockaMenuCrawler(),
			new UZlateKonveMenuCrawler(),
			new PivniceUCapaMenuCrawler(),
			new SuziesMenuCrawler($input->getOption('zomato-user-key'))
		];


		// get & format menus
		$formatter = new MenuFormatter();
		$formattedMenus = $formatter->formatHeader($now) . "\n\n";

		foreach ($menuCrawlers as $menuCrawler) {
			assert($menuCrawler instanceof IMenuCrawler);

			$name = $menuCrawler->getName();
			$url = $menuCrawler->getUrl();

			try {
				// try to load the menu twice (sometimes we get random web/network errors)
				try {
					$menu = $menuCrawler->getMenu($now);
				} catch (\Throwable $e) {
					sleep(1);
					$menu = $menuCrawler->getMenu($now);
				}
				$formattedMenus .= $formatter->formatMenuHeader($name, $url);
				$formattedMenus .= $formatter->formatMenuSoups($menu);
				$formattedMenus .= $formatter->formatMenuBody($menu) . "\n";
			} catch (\Throwable $e) {
				$formattedMenus .= $formatter->formatMenuHeader($name, $url);
				$formattedMenus .= "_Nepodařilo se načíst menu._\n\n";
			}
		}

		echo $formattedMenus;

		// notify Slack
		$slackUrl = $input->getOption('slack-url');
		if ($slackUrl) {
			$slackNotifier = new SlackNotifier($slackUrl);
			$slackNotifier->notify($formattedMenus);
		}
	}
}
