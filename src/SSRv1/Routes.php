<?php

namespace WEEEOpen\Tarallo\SSRv1;

use FastRoute;
use Psr\Http\Message\ServerRequestInterface;
use WEEEOpen\Tarallo\User;

trait Routes
{
	// Add routes of web application (not API)
	private function route(ServerRequestInterface $request): array
	{
		$dispatcher = FastRoute\cachedDispatcher(
			function (FastRoute\RouteCollector $r) {
				$r->get('/auth', [null, [Controller::class, 'authError']]);
				$r->get('/logout', [null, [Controller::class, 'logout']]);
				$r->get('/', [User::AUTH_LEVEL_RO, [Controller::class, 'getHome']]);
				$r->get('', [User::AUTH_LEVEL_RO, [Controller::class, 'getHome']]);
				$r->get('/features.json', [null, [Controller::class, 'getFeaturesJson']]);
				// TODO: make token access public
				$r->get('/item/{id}', [User::AUTH_LEVEL_RO, [Controller::class, 'getItem']]);
				$r->get('/item/{id}/add/{add}', [User::AUTH_LEVEL_RW, [Controller::class, 'getItem']]);
				$r->get('/item/{id}/edit/{edit}', [User::AUTH_LEVEL_RW, [Controller::class, 'getItem']]);
				$r->get('/item/{id}/history', [User::AUTH_LEVEL_RO, [Controller::class, 'getItemHistory']]);
				$r->get('/products', [User::AUTH_LEVEL_RO, [Controller::class, 'getProductsPage']]);
				$r->get('/product', [User::AUTH_LEVEL_RO, [Controller::class, 'getAllProducts']]);
				$r->get('/product/{brand}', [User::AUTH_LEVEL_RO, [Controller::class, 'getAllProducts']]);
				$r->get('/product/{brand}/{model}', [User::AUTH_LEVEL_RO, [Controller::class, 'getAllProducts']]);
				$r->get('/product/{brand}/{model}/{variant}', [User::AUTH_LEVEL_RO, [Controller::class, 'getProduct']]);
				$r->get('/product/{brand}/{model}/{variant}/edit', [User::AUTH_LEVEL_RW, [Controller::class, 'getProduct']]);
				$r->get('/product/{brand}/{model}/{variant}/history', [User::AUTH_LEVEL_RO, [Controller::class, 'getProductHistory']]);
				$r->get('/product/{brand}/{model}/{variant}/items', [User::AUTH_LEVEL_RO, [Controller::class, 'getProductItems']]);
				$r->get('/product/{brand}/{model}/{variant}/items/add/{add}', [User::AUTH_LEVEL_RW, [Controller::class, 'getProductItems']]);
				$r->get('/product/{brand}/{model}/{variant}/items/edit/{edit}', [User::AUTH_LEVEL_RW, [Controller::class, 'getProductItems']]);
				$r->get('/new/item', [User::AUTH_LEVEL_RO, [Controller::class, 'addItem']]);
				$r->get('/new/product', [User::AUTH_LEVEL_RO, [Controller::class, 'addProduct']]);
				$r->post('/search', [User::AUTH_LEVEL_RO, [Controller::class, 'quickSearch']]);
				$r->get('/search/name/{name}', [User::AUTH_LEVEL_RO, [Controller::class, 'quickSearchName']]);
			//				$r->get('/search/value/{value}', [User::AUTH_LEVEL_RO, [Controller::class, 'quickSearchValue']]);
				$r->get('/search/feature/{name}/{value}', [User::AUTH_LEVEL_RO, [Controller::class, 'quickSearchFeatureValue']]);
				$r->get('/search/advanced[/{id:[0-9]+}[/page/{page:[0-9]+}]]', [User::AUTH_LEVEL_RO, [Controller::class, 'search']]);
				$r->get('/search/advanced/{id:[0-9]+}/add/{add}', [User::AUTH_LEVEL_RO, [Controller::class, 'search']]);
				$r->get('/search/advanced/{id:[0-9]+}/page/{page:[0-9]+}/add/{add}', [User::AUTH_LEVEL_RO, [Controller::class, 'search']]);
				$r->get('/search/advanced/{id:[0-9]+}/edit/{edit}', [User::AUTH_LEVEL_RO, [Controller::class, 'search']]);
				$r->get('/search/advanced/{id:[0-9]+}/page/{page:[0-9]+}/edit/{edit}', [User::AUTH_LEVEL_RO, [Controller::class, 'search']]);
				$r->get('/options', [User::AUTH_LEVEL_RO, [Controller::class, 'options']]);
				$r->post('/options', [User::AUTH_LEVEL_RO, [Controller::class, 'options']]);
				$r->get('/bulk', [User::AUTH_LEVEL_RO, [Controller::class, 'bulk']]);
				$r->get('/bulk/move', [User::AUTH_LEVEL_RO, [Controller::class, 'bulkMove']]);
				$r->post('/bulk/move', [User::AUTH_LEVEL_RW, [Controller::class, 'bulkMove']]);
				$r->get('/bulk/add', [User::AUTH_LEVEL_RO, [Controller::class, 'bulkAdd']]);
				$r->post('/bulk/add', [User::AUTH_LEVEL_RW, [Controller::class, 'bulkAdd']]);
				$r->get('/bulk/import', [User::AUTH_LEVEL_RO, [Controller::class, 'bulkImport']]);
				$r->post('/bulk/import', [User::AUTH_LEVEL_RW, [Controller::class, 'bulkImport']]);
				$r->get('/bulk/import/review/{id}', [User::AUTH_LEVEL_RO, [Controller::class, 'bulkImportReview']]);
				$r->get('/bulk/import/new/{id}', [User::AUTH_LEVEL_RW, [Controller::class, 'bulkImportAdd']]);
				$r->addGroup(
					'/info/stats',
					function (FastRoute\RouteCollector $r) {
						$r->get('', [User::AUTH_LEVEL_RO, [Controller::class, 'getStats']]);
						$r->get('/{which}', [User::AUTH_LEVEL_RO, [Controller::class, 'getStats']]);
					}
				);
				$r->get('/info/locations', [User::AUTH_LEVEL_RO, [Controller::class, 'infoLocations']]);
				$r->get('/info/todo', [User::AUTH_LEVEL_RO, [Controller::class, 'infoTodo']]);
                $r->get('/donations', [User::AUTH_LEVEL_RW, [Controller::class, 'getDonations']]);
                $r->get('/donation/new', [User::AUTH_LEVEL_RW, [Controller::class, 'newDonation']]);
                $r->get('/donation/{id}', [User::AUTH_LEVEL_RW, [Controller::class, 'getDonation']]);
                $r->get('/donation/{id}/delete', [User::AUTH_LEVEL_RW, [Controller::class, 'deleteDonation']]);
                $r->post('/donation/update', [User::AUTH_LEVEL_RW, [Controller::class, 'updateDonation']]);
            },

			[
				'cacheFile' => self::CACHEFILE,
				'cacheDisabled' => !TARALLO_CACHE_ENABLED,
			]
		);

		return $dispatcher->dispatch($request->getMethod(), $request->getUri()->getPath());
	}
}
