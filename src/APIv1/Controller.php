<?php

namespace WEEEOpen\Tarallo\APIv1;

use FastRoute;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Relay\RelayBuilder;
use Slim\Http\Body;
use WEEEOpen\Tarallo\Database\Database;
use WEEEOpen\Tarallo\Database\DatabaseException;
use WEEEOpen\Tarallo\Database\DuplicateItemCodeException;
use WEEEOpen\Tarallo\Database\ItemDAO;
use WEEEOpen\Tarallo\Database\TreeDAO;
use WEEEOpen\Tarallo\BaseFeature;
use WEEEOpen\Tarallo\Feature;
use WEEEOpen\Tarallo\HTTP\AbstractController;
use WEEEOpen\Tarallo\HTTP\AuthenticationException;
use WEEEOpen\Tarallo\HTTP\AuthorizationException;
use WEEEOpen\Tarallo\HTTP\DatabaseConnection;
use WEEEOpen\Tarallo\HTTP\InvalidParameterException;
use WEEEOpen\Tarallo\HTTP\InvalidPayloadParameterException;
use WEEEOpen\Tarallo\HTTP\InvalidRequestBodyException;
use WEEEOpen\Tarallo\HTTP\MissingMandatoryParameterException;
use WEEEOpen\Tarallo\HTTP\Validation;
use WEEEOpen\Tarallo\Item;
use WEEEOpen\Tarallo\ItemCode;
use WEEEOpen\Tarallo\ItemNestingException;
use WEEEOpen\Tarallo\ItemValidator;
use WEEEOpen\Tarallo\NotFoundException;
use WEEEOpen\Tarallo\Session;
use WEEEOpen\Tarallo\ValidationException;


class Controller extends AbstractController {
	const cachefile = __DIR__ . '/router.cache';

	public static function sessionWhoami(Request $request, Response $response, ?callable $next = null): Response {
		$user = $request->getAttribute('User');

		Validation::authorize($user, 3);

		$request = $request
			->withAttribute('Status', JSend::SUCCESS)
			->withAttribute('Data', ['username' => $user->getUsername()]);

		return $next ? $next($request, $response) : $response;
	}

	public static function sessionStart(Request $request, Response $response, ?callable $next = null): Response {
		/** @var Database $db */
		$db = $request->getAttribute('Database');
		$payload = $request->getParsedBody();

		$username = Validation::validateHasString($payload, 'username');
		$password = Validation::validateHasString($payload, 'password');

		$user = $db->userDAO()->getUserFromLogin($username, $password);
		if($user === null) {
			throw new AuthenticationException('Wrong username or password');
		}
		Session::start($user, $db);

		$response = $response
			->withStatus(204);

		return $next ? $next($request, $response) : $response;
	}

	public static function sessionClose(Request $request, Response $response, ?callable $next = null): Response {
		/** @var Database $db */
		$db = $request->getAttribute('Database');
		$user = $request->getAttribute('User');

		// If we ever add another level for e.g. banned users, this at least allows them to log out
		Validation::authenticate($user);
		Session::close($user, $db);

		$response = $response
			->withStatus(204);

		return $next ? $next($request, $response) : $response;
	}

	public static function sessionRefresh(Request $request, Response $response, ?callable $next = null): Response {
		$user = $request->getAttribute('User');

		// The refresh itself has already been done by Session::restore, sooooo...
		Validation::authenticate($user);

		$response = $response
			->withStatus(204);

		return $next ? $next($request, $response) : $response;
	}

	public static function getItem(Request $request, Response $response, ?callable $next = null): Response {
		/** @var Database $db */
		$db = $request->getAttribute('Database');
		$user = $request->getAttribute('User');
		$parameters = $request->getAttribute('parameters', []);

		$id = Validation::validateOptionalString($parameters, 'id');
		$token = Validation::validateOptionalString($parameters, 'token');
		$depth = Validation::validateOptionalString($parameters, 'depth');

		if($token === null) {
			Validation::authorize($user, 3);
		}

		if($id === null) {
			throw new \LogicException('Not implemented');
		} else {
			try {
				$item = new ItemCode($id);
			} catch(ValidationException $e) {
				if($e->getCode() === 3) {
					throw new NotFoundException();
				} else {
					throw $e;
				}
			}

			if(!$db->itemDAO()->itemVisible($item)) {
				throw new NotFoundException();
			}
			$data = $db->itemDAO()->getItem($item, $token, $depth);

			$request = $request
				->withAttribute('Status', JSend::SUCCESS)
				->withAttribute('Data', $data);
			$response = $response
				->withStatus(200);
		}

		return $next ? $next($request, $response) : $response;
	}

	public static function getDeletedItem(Request $request, Response $response, ?callable $next = null): Response {
		/** @var Database $db */
		$db = $request->getAttribute('Database');
		$user = $request->getAttribute('User');
		$parameters = $request->getAttribute('parameters', []);

		$id = Validation::validateHasString($parameters, 'id');

		Validation::authorize($user, 3);

		try {
			$item = new ItemCode($id);
		} catch(ValidationException $e) {
			throw new NotFoundException();
		}

		$deleted = $db->itemDAO()->itemDeletedAt($item);

		if($deleted === null) {
			throw new NotFoundException();
		} else {
			$data = (new \DateTime($deleted, new \DateTimeZone('UTC')))->format('c');
			$request = $request
				->withAttribute('Status', JSend::SUCCESS)
				->withAttribute('Data', $data);
			$response = $response
				->withStatus(200);
		}

		return $next ? $next($request, $response) : $response;
	}


	public static function restoreItemParent(Request $request, Response $response, ?callable $next = null): Response {
		/** @var Database $db */
		$db = $request->getAttribute('Database');
		$user = $request->getAttribute('User');
		$query = $request->getQueryParams();
		$payload = json_decode($request->getBody()->getContents(), true);
		$parameters = $request->getAttribute('parameters', []);

		Validation::authorize($user);

		// Could allow restoring items as roots by not calling self::moveWithValidation at all, BTW...
		Validation::validateRequestBodyIsString($payload);
		$id = Validation::validateHasString($parameters, 'id');
		$fix = !isset($query['nofix']);
		$validate = !isset($query['novalidate']);

		$item = Validation::newItemIncomplete($id);
		try {
			$newParent = new ItemCode($payload);
		} catch(ValidationException $e) {
			throw new InvalidPayloadParameterException('*', $payload, 'Location does not exist');
		}

		$db->itemDAO()->undelete($item);
		$created = TreeDAO::moveWithValidation($db, $item, $newParent, $fix, $validate);

		if($created) {
			$response = $response->withStatus(201);
		} else {
			// Moved or done nothing
			$response = $response->withStatus(204);
		}

		return $next ? $next($request, $response) : $response;
	}

	public static function getByFeature(Request $request, Response $response, ?callable $next = null): Response {
		/** @var Database $db */
		$db = $request->getAttribute('Database');
		$user = $request->getAttribute('User');
		$parameters = $request->getAttribute('parameters', []);

		Validation::authorize($user, 3);

		$id = (string) $parameters['feature'];
		$value = (string) $parameters['value'];
		$limit = Validation::validateOptionalInt($parameters, 'limit', 5);

		if($limit > 10) {
			throw new InvalidPayloadParameterException('limit', $limit, 'Maximum number of results is 10');
		} else if($limit < 1) {
			throw new InvalidPayloadParameterException('limit', $limit, 'Limit < 1 doesn\'t make sense');
		}

		try {
			if(BaseFeature::getType($id) !== BaseFeature::STRING) {
				throw new InvalidPayloadParameterException('*', $id, "Only text features are supported, $id isn't");
			}
		} catch(\InvalidArgumentException $e) {
			throw new InvalidPayloadParameterException('*', $value, $e->getMessage());
		}

		$feature = new Feature($id, $value);

		$data = $db->statsDAO()->getItemsByFeatures($feature, null, $limit);

		$request = $request
			->withAttribute('Status', JSend::SUCCESS)
			->withAttribute('Data', $data);
		$response = $response
			->withStatus(200);

		return $next ? $next($request, $response) : $response;
	}

	public static function createItem(Request $request, Response $response, ?callable $next = null): Response {
		/** @var Database $db */
		$db = $request->getAttribute('Database');
		$user = $request->getAttribute('User');
		$query = $request->getQueryParams();
		$payload = $request->getParsedBody();
		$parameters = $request->getAttribute('parameters', []);

		Validation::authorize($user);

		$id = Validation::validateOptionalString($parameters, 'id');
		$fix = !isset($query['nofix']);
		$validate = !isset($query['novalidate']);
		$loopback = isset($query['loopback']);

		$item = ItemBuilder::ofArray($payload, $id, $parent);

		// Validation and fixupLocation requires the full parent item, which may not exist.
		// Since this part is optional, its existence will be checked again later
		if($parent instanceof ItemCode && ($fix || $validate)) {
			try {
				$parent = $db->itemDAO()->getItem($parent, null, 1);
			} catch(NotFoundException $e) {
				throw new InvalidPayloadParameterException('parent', $parent->getCode(), 'Location doesn\'t exist');
			}
		}

		if($fix) {
			$parent = ItemValidator::fixupLocation($item, $parent);
			ItemValidator::fixupFeatures($item);
		}

		if($validate) {
			try {
				ItemValidator::validateLocation($item, $parent);
			} catch(ItemNestingException $e) {
				throw new InvalidPayloadParameterException('parent', $e->parentCode, $e->getMessage());
			} catch(ValidationException $e) {
				throw new InvalidPayloadParameterException('location', null, $e->getMessage());
			}
			try {
				ItemValidator::validateFeatures($item);
			} catch(ValidationException $e) {
				// Yyyyep, JSEnd "fail"s are basically unusable in the real world.
				// This stuff really needs to go.
				throw new InvalidPayloadParameterException('*', '*', $e->getMessage());
			}
		}

		try {
			$db->itemDAO()->addItem($item, $parent);
		} catch(DuplicateItemCodeException $e) {
			throw new InvalidPayloadParameterException('code', $e->duplicate, $e->getMessage());
		} catch(NotFoundException $e) {
			if($e->getCode() === TreeDAO::EXCEPTION_CODE_PARENT) {
				throw new InvalidPayloadParameterException(
					'parent', $parent->getCode(),
					'Requested location doesn\'t exist'
				);
			} else {
				throw $e;
			}
		} catch(\InvalidArgumentException $e) {
			if($e->getCode() === ItemDAO::EXCEPTION_CODE_GENERATE_ID) {
				throw new InvalidPayloadParameterException(
					'code', null,
					'Cannot generate code for an item (missing "type"?)'
				);
			} else {
				throw $e;
			}
		}

		if($loopback) {
			$request = $request
				->withAttribute('Status', JSend::SUCCESS)
				->withAttribute('Data', $db->itemDAO()->getItem($item));
			$response = $response
				->withHeader('Location', '/v1/items/' . urlencode($item->getCode()))
				->withStatus(201);
		} else {
			$request = $request
				->withAttribute('Status', JSend::SUCCESS)
				->withAttribute('Data', $item->getCode());
			$response = $response
				->withHeader('Location', '/v1/items/' . urlencode($item->getCode()))
				->withStatus(201);
		}

		return $next ? $next($request, $response) : $response;
	}

	public static function removeItem(Request $request, Response $response, ?callable $next = null): Response {
		/** @var Database $db */
		$db = $request->getAttribute('Database');
		$user = $request->getAttribute('User');
		$parameters = $request->getAttribute('parameters', []);

		Validation::authorize($user);

		$id = Validation::validateHasString($parameters, 'id');

		$responseCode = 204;
		try {
			$db->itemDAO()->deleteItem(new ItemCode($id));
		} catch(NotFoundException $ignored) {
			$responseCode = 404;
		} catch(ValidationException $e) {
			throw new InvalidPayloadParameterException('*', $id, $e->getMessage());
		}

		$response = $response
			->withStatus($responseCode);

		return $next ? $next($request, $response) : $response;
	}

	public static function setItemParent(Request $request, Response $response, ?callable $next = null): Response {
		/** @var Database $db */
		$db = $request->getAttribute('Database');
		$user = $request->getAttribute('User');
		$query = $request->getQueryParams();
		$payload = json_decode($request->getBody()->getContents(), true);
		$parameters = $request->getAttribute('parameters', []);

		Validation::authorize($user);

		Validation::validateRequestBodyIsString($payload);
		$id = Validation::validateHasString($parameters, 'id');
		$fix = !isset($query['nofix']);
		$validate = !isset($query['novalidate']);

		$item = Validation::newItemIncomplete($id);
		try {
			$newParent = new ItemCode($payload);
		} catch(ValidationException $e) {
			throw new InvalidPayloadParameterException('*', $payload, 'Location does not exist');
		}

		$created = TreeDAO::moveWithValidation($db, $item, $newParent, $fix, $validate);

		if($created) {
			$response = $response->withStatus(201);
		} else {
			// Moved or done nothing
			$response = $response->withStatus(204);
		}

		return $next ? $next($request, $response) : $response;
	}

	public static function deleteItemParent(Request $request, Response $response, ?callable $next = null): Response {
		/** @var Database $db */
		$db = $request->getAttribute('Database');
		$user = $request->getAttribute('User');
		$parameters = $request->getAttribute('parameters', []);

		Validation::authorize($user);

		$id = Validation::validateHasString($parameters, 'id');

		$responseCode = 204;
		try {
			$db->itemDAO()->loseItem(new ItemCode($id));
		} catch(NotFoundException $ignored) {
			$responseCode = 404;
		} catch(ValidationException $e) {
			throw new InvalidPayloadParameterException('*', $id, $e->getMessage());
		}

		$response = $response
			->withStatus($responseCode);

		return $next ? $next($request, $response) : $response;
	}

	public static function setItemFeatures(Request $request, Response $response, ?callable $next = null): Response {
		/** @var Database $db */
		$db = $request->getAttribute('Database');
		$user = $request->getAttribute('User');
		$query = $request->getQueryParams();
		$payload = $request->getParsedBody();
		$parameters = $request->getAttribute('parameters', []);

		Validation::authorize($user);
		Validation::validateRequestBodyIsArray($payload);

		$id = Validation::validateOptionalString($parameters, 'id');
		$loopback = isset($query['loopback']);

		$item = new Item($id);
		// PUT => delete every feature, replace with new ones
		ItemBuilder::addFeatures($payload, $item);
		$db->featureDAO()->deleteFeaturesAll($item);
		$db->featureDAO()->setFeatures($item);

		// TODO: this should maybe return 201 sometimes
		if($loopback) {
			$request = $request
				->withAttribute('Status', JSend::SUCCESS)
				->withAttribute('Data', $db->itemDAO()->getItem($item));
			$response = $response->withStatus(200);
		} else {
			$response = $response->withStatus(204);
		}

		return $next ? $next($request, $response) : $response;
	}

	public static function updateItemFeatures(Request $request, Response $response, ?callable $next = null): Response {
		/** @var Database $db */
		$db = $request->getAttribute('Database');
		$user = $request->getAttribute('User');
		$query = $request->getQueryParams();
		$payload = $request->getParsedBody();
		$parameters = $request->getAttribute('parameters', []);

		Validation::authorize($user);
		Validation::validateRequestBodyIsArray($payload);

		$id = Validation::validateOptionalString($parameters, 'id');
		$loopback = isset($query['loopback']);

		$item = new Item($id);
		// PATCH => specify features to update and to delete, other are left as they are
		$delete = ItemBuilder::addFeaturesDelta($payload, $item);

		$deleted = $db->featureDAO()->deleteFeature($item, $delete);
		$changed = $db->featureDAO()->setFeatures($item);

		// setFeatures generates an audit entry if anything changed, deleteFeature never does
		// so we may need to generate it manually
		if(!$changed && $deleted) {
			$db->featureDAO()->addAuditEntry($item);
		}

		if($loopback) {
			$request = $request
				->withAttribute('Status', JSend::SUCCESS)
				->withAttribute('Data', $db->itemDAO()->getItem($item));
			$response = $response->withStatus(200);
		} else {
			$response = $response->withStatus(204);
		}

		return $next ? $next($request, $response) : $response;
	}

	public static function doSearch(Request $request, Response $response, ?callable $next = null): Response {
		/** @var Database $db */
		$db = $request->getAttribute('Database');
		$user = $request->getAttribute('User');
		$payload = $request->getParsedBody();
		$parameters = $request->getAttribute('parameters', []);

		Validation::authorize($user, 3);
		Validation::validateRequestBodyIsArray($payload);

		$id = Validation::validateOptionalString($parameters, 'id');

		if($id) {
			// Refining a search: must be owner or admin
			$username = $db->searchDAO()->getOwnerUsername($id);
			if($username !== $user->getUsername()) {
				Validation::authorize($user, 0);
			}
		}

		$search = SearchBuilder::ofArray($payload);
		$resultId = $db->searchDAO()->search($search, $user, $id);

		$request = $request
			->withAttribute('Status', JSend::SUCCESS)
			->withAttribute('Data', $resultId);
		$response = $response
			->withStatus(200);

		return $next ? $next($request, $response) : $response;
	}

	public static function getLogs(Request $request, Response $response, ?callable $next = null): Response {
		/** @var Database $db */
		$db = $request->getAttribute('Database');
		$user = $request->getAttribute('User');
		$query = $request->getQueryParams();
		$parameters = $request->getAttribute('parameters', []);

		Validation::authorize($user, 3);

		$page = Validation::validateOptionalInt($parameters, 'page', 1);
		$limit = Validation::validateOptionalInt($query, 'limit', 20);

		if($page < 1) {
			throw new InvalidPayloadParameterException('page', $page, 'Pages start from 1');
		}

		if($limit < 1) {
			throw new InvalidPayloadParameterException('limit', $limit, 'Length < 1 doesn\'t make sense');
		} else if($limit > 50) {
			throw new InvalidPayloadParameterException('limit', $limit, 'Maximum number of entries per page is 50');
		}

		$data = $db->auditDAO()->getRecentAudit($limit, $page);

		$request = $request
			->withAttribute('Status', JSend::SUCCESS)
			->withAttribute('Data', $data);
		$response = $response
			->withStatus(200);

		return $next ? $next($request, $response) : $response;
	}

	public static function getHistory(Request $request, Response $response, ?callable $next = null): Response {
		/** @var Database $db */
		$db = $request->getAttribute('Database');
		$user = $request->getAttribute('User');
		$query = $request->getQueryParams();
		$parameters = $request->getAttribute('parameters', []);

		Validation::authorize($user, 3);

		$id = Validation::validateOptionalString($parameters, 'id');
		// TODO: rename to limit?
		$length = Validation::validateOptionalInt($query, 'length', 20);

		$item = new ItemCode($id);

		if(!$db->itemDAO()->itemExists($item)) {
			throw new NotFoundException();
		}

		if($length > 50) {
			throw new InvalidPayloadParameterException('limit', $length, 'Maximum number of entries is 50');
		} else if($length < 1) {
			throw new InvalidPayloadParameterException('limit', $length, 'Length < 1 doesn\'t make sense');
		}

		$data = $db->auditDAO()->getHistory($item, $length);

		$request = $request
			->withAttribute('Status', JSend::SUCCESS)
			->withAttribute('Data', $data);
		$response = $response
			->withStatus(200);

		return $next ? $next($request, $response) : $response;
	}

	public static function ItemsNotFeature(Request $request, Response $response, ?callable $next = null): Response {
		/** @var Database $db */
		$db = $request->getAttribute('Database');
		$user = $request->getAttribute('User');
		$parameters = $request->getAttribute('parameters', []);

		Validation::authorize($user, 3);

		$feature = Validation::validateHasString($parameters, 'filter');
		$notFeature = Validation::validateHasString($parameters, 'notFeature');
		$location = Validation::validateOptionalString($parameters, 'location', null, null);
		$limit = Validation::validateOptionalInt($parameters, 'limit', 100);
		$creation = Validation::validateOptionalString($parameters, 'creation', null);
		$deleted = boolval(Validation::validateOptionalString($parameters, 'creation', false));
		//$deleted = isset($parameters['deleted']) ? $parameters['deleted'] : false;

		try {
			$explosion = Validation::explodeFeatureValuePair($feature);
		} catch(\InvalidArgumentException $e) {
			throw new InvalidPayloadParameterException('filter', $e->getMessage());
		}
		$array = $db->StatsDAO()->getItemByNotFeature(
			new Feature($explosion[0], $explosion[1]),
			$notFeature,
			$location === null ? null : new
		ItemCode($location), $limit, $creation === null ? null : new \DateTime($creation), $deleted);

		$request = $request
			->withAttribute('Status', JSend::SUCCESS)
			->withAttribute('Data', $array);
		$response = $response
			->withStatus(200);
		return $next ? $next($request, $response) : $response;
	}

	public static function RecentAuditByType(Request $request, Response $response, ?callable $next = null): Response {
		/** @var Database $db */
		$db = $request->getAttribute('Database');
		$user = $request->getAttribute('User');
		$parameters = $request->getAttribute('parameters', []);

		Validation::authorize($user, 3);

		$type = Validation::validateHasString($parameters, 'type');
		$limit = Validation::validateOptionalInt($parameters, 'howMany', 100);

		$array = $db->AuditDAO()->getRecentAuditByType($type, $limit);

		$request = $request
			->withAttribute('Status', JSend::SUCCESS)
			->withAttribute('Data', $array);
		$response = $response
			->withStatus(200);
		return $next ? $next($request, $response) : $response;
	}

	public static function CountByFeature(Request $request, Response $response, ?callable $next = null): Response {
		/** @var Database $db */
		$db = $request->getAttribute('Database');
		$user = $request->getAttribute('User');
		$parameters = $request->getAttribute('parameters', []);

		Validation::authorize($user, 3);

		$feature = Validation::validateHasString($parameters, 'feature');
		$filter = Validation::validateOptionalString($parameters, 'filter', null, null);
		$location = Validation::validateOptionalString($parameters, 'location', null, null);
		$creation = Validation::validateOptionalString($parameters, 'creation');
		$deleted = isset($parameters['deleted']) ? $parameters['deleted'] : false;
		$cutoff = Validation::validateOptionalInt($parameters, 'cutoff', 1);

		if($filter !== null) {
			try {
				$explosion = Validation::explodeFeatureValuePair($feature);
				$filter = new Feature($explosion[0], $explosion[1]);
			} catch(\InvalidArgumentException $e) {
				throw new InvalidPayloadParameterException('filter', $e->getMessage());
			}
		}

		$array = $db->statsDAO()->getCountByFeature(
			$feature,
			$filter,
			$location === null ? null : new ItemCode($location),
			$creation === null ? null : new \DateTime($creation),
			$deleted,
			$cutoff
		);

		$request = $request
			->withAttribute('Status', JSend::SUCCESS)
			->withAttribute('Data', $array);
		$response = $response
			->withStatus(200);
		return $next ? $next($request, $response) : $response;
	}

	public static function getDispatcher(string $cachefile): FastRoute\Dispatcher {
		return FastRoute\cachedDispatcher(
			function(FastRoute\RouteCollector $r) {

				$r->addGroup(
					'/v1',
					function(FastRoute\RouteCollector $r) {
						$r->addGroup(
							'/items',
							function(FastRoute\RouteCollector $r) {
								$r->get('', [[Controller::class, 'getItem']]);
								$r->post('', [[Controller::class, 'createItem']]);

								$r->addGroup(
									'/{id}',
									function(FastRoute\RouteCollector $r) {
										$r->get('[/token/{token}]', [[Controller::class, 'getItem']]);
										$r->get('/history', [[Controller::class, 'getHistory']]);
										$r->put('', [[Controller::class, 'createItem']]);
										$r->delete('', [[Controller::class, 'removeItem']]);

										// Useless
										//$r->get('/parent',  [[Controller::class, 'getItemParent']]);
										$r->put('/parent', [[Controller::class, 'setItemParent']]);
										$r->delete('/parent', [[Controller::class, 'deleteItemParent']]);

										//$r->get('/product', [[Controller::class, 'getItemProduct']]);
										//$r->put('/product',  [[Controller::class, 'setItemProduct']]);
										//$r->delete('/product',  [[Controller::class, 'deleteItemProduct']]);

										// Also useless, just get the item
										// $r->get('/features',  [[Controller::class, 'getItemFeatures']]);
										$r->put('/features', [[Controller::class, 'setItemFeatures']]);
										$r->patch('/features', [[Controller::class, 'updateItemFeatures']]);

										// $r->get('/contents',  [[Controller::class, 'getItemContents']]);
									}
								);
							}
						);
						$r->addGroup(
							'/deleted',
							function(FastRoute\RouteCollector $r) {
								$r->addGroup(
									'/{id}',
									function(FastRoute\RouteCollector $r) {
										$r->get('', [[Controller::class, 'getDeletedItem']]);
										$r->put('/parent', [[Controller::class, 'restoreItemParent']]);
										// TODO: this $r->delete('', [[Controller::class, 'removeItemPermanently']]);
									}
								);
							}
						);

						$r->post('/search', [[Controller::class, 'doSearch']]);
						$r->patch('/search/{id}', [[Controller::class, 'doSearch']]);
						$r->get('/search/{id}[/page/{page}]', [[Controller::class, 'getSearch']]); // TODO: implement

						$r->get('/features/{feature}/{value}', [[Controller::class, 'getByFeature']]);

						$r->addGroup(
							'/products',
							function(FastRoute\RouteCollector $r) {
								$r->get('', [[Controller::class, 'getProduct']]); // TODO: implement
								$r->get(
									'/{brand}[/{model}[/{variant}]]',
									[[Controller::class, 'getProduct']]
								); // TODO: implement

								$r->post('/{brand}/{model}', [[Controller::class, 'createProduct']]); // TODO: implement
								$r->put(
									'/{brand}/{model}/{variant}',
									[[Controller::class, 'createProduct']]
								); // TODO: implement

								$r->addGroup(
									'/{brand}/{model}/{variant}',
									function(FastRoute\RouteCollector $r) {
										//$r->get('/features',  [[Controller::class, 'getProductFeatures']]);
										$r->post(
											'/features',
											[[Controller::class, 'setProductFeatures']]
										); // TODO: implement
										$r->patch(
											'/features',
											[[Controller::class, 'updateProductFeatures']]
										); // TODO: implement
									}
								);
							}
						);

						$r->get('/logs[/page/{page}]', [[Controller::class, 'getLogs']]);

						$r->get('/session', [[Controller::class, 'sessionWhoami']]);
						$r->post('/session', [[Controller::class, 'sessionStart']]);
						$r->delete('/session', [[Controller::class, 'sessionClose']]);
						$r->head('/session', [[Controller::class, 'sessionRefresh']]);

						$r->addGroup(
							'/stats',
							function(FastRoute\RouteCollector $r) {
								$r->get(
									'/getItemByNotFeature/{filter}[/{notFeature}[/{location}[/{limit}[/{creation}[/{deleted}]]]]]',
									[[Controller::class, 'ItemsNotFeature']]
								);
								$r->get(
									'/getRecentAuditByType/{type}[/{howMany}]',
									[[Controller::class, 'RecentAuditByType']]
								);
								$r->get(
									'/getCountByFeature/{feature}[/{filter}[/{location}[/{creation[/{deleted[/{cutoff}]]]]]',
									[[Controller::class, 'CountByFeature']]
								);
							}
						);
					}
				);
			},
			[
				'cacheFile' => $cachefile,
				'cacheDisabled' => !TARALLO_CACHE_ENABLED,
			]
		);
	}

	public static function handle(Request $request): Response {
		$queue = [
			[static::class, 'isJson'],
			new DatabaseConnection(),
			[static::class, 'handleExceptions'],
		];

		$response = new \Slim\Http\Response();
		$response = $response
			->withHeader('Content-Type', 'application/json')
			->withBody(new Body(fopen('php://memory', 'r+')));

		$route = static::route($request);

		switch($route[0]) {
			case FastRoute\Dispatcher::FOUND:
				$queue = array_merge($queue, [[static::class, 'doTransaction']], $route[1]);
				$request = $request
					->withAttribute('parameters', $route[2]);
				$response = $response
					->withStatus(200);
				break;
			case FastRoute\Dispatcher::NOT_FOUND:
				// TODO: why is this not working? There's no message...
				$request = $request
					->withAttribute('Status', JSend::FAIL)
					->withAttribute('ErrorMessage', "API endpoint not found");
				$response = $response->withStatus(404);
				break;
			case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
				$allowed = implode(', ', $route[1]);
				$request = $request
					->withAttribute('Status', JSend::FAIL)
					->withAttribute('ErrorMessage', "Method not allowed for this endpoint, use one of: $allowed");
				$response = $response->withStatus(405)
					->withHeader('Allow', $allowed);
				break;
			default:
				$request = $request
					->withAttribute('Status', JSend::ERROR)
					->withAttribute('ErrorMessage', 'Unhandled router result');
				$response = $response->withStatus(500);
				break;
		}

		unset($route);

		$queue = array_merge($queue, [[static::class, 'renderResponse']]);

		$relayBuilder = new RelayBuilder();
		$relay = $relayBuilder->newInstance($queue);

		return $relay($request, $response);

	}

	public static function renderResponse(
		Request $request,
		Response $response,
		?callable $next = null
	): Response {

		$body = null;
		if($request->getMethod() !== 'HEAD') {
			$data = $request->getAttribute('Data');
			switch($request->getAttribute('Status', null)) {

				case JSend::SUCCESS:
					$body = JSend::success($data);
					break;
				case JSend::ERROR:
					$body = JSend::error(
						$request->getAttribute('ErrorMessage'),
						$request->getAttribute('ErrorCode'),
						$data
					);
					break;
				case JSend::FAIL:
					$body = JSend::fail($data);
					break;
				case null:
					// Do nothing
					break;
				default:
					$response = $response
						->withStatus(500);
					$body = JSend::error('Unhandled JSend response type, most probably a typo somewhere in the code');
			}
		}

		if($body === null) {
			$response->getBody()->close();
		} else {
			$response->getBody()->rewind();
			$response->getBody()->write($body);
		}

		if($next) {
			return $next($request, $response);
		} else {
			return $response;
		}
	}

	public static function isJson(
		Request $request,
		Response $response,
		?callable $next = null
	): Response {
		$method = $request->getMethod();
		if($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
			/** @noinspection PhpUndefinedMethodInspection it exists. */
			if(explode(';', $request->getContentType(), 2)[0] !== 'application/json') {
				$response = $response
					->withStatus(415)
					->withHeader('Content-Type', 'text/plain');
				$response->getBody()->rewind();
				$response->getBody()->write('Request must contain JSON, check your Content-Type');
				return $response;
			}
		}

		if($next) {
			return $next($request, $response);
		} else {
			return $response;
		}
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param callable|null $next
	 *
	 * @return Response
	 * @deprecated Remove it, see last point in https://github.com/WEEE-Open/tarallo/issues/50
	 */
	public static function handleExceptions(
		Request $request,
		Response $response,
		?callable $next = null
	): Response {
		if($next) {
			try {
				return $next($request, $response);
			} catch(AuthorizationException $e) {
				$request = $request
					->withAttribute('Status', JSend::ERROR)
					->withAttribute('ErrorMessage', 'Not authorized (insufficient permission)')
					->withAttribute('ErrorCode', 'AUTH403');
				$response = $response
					->withStatus(403);
			} catch(AuthenticationException $e) {
				$request = $request
					->withAttribute('Status', JSend::ERROR)
					->withAttribute('ErrorMessage', $e->getMessage())
					->withAttribute('ErrorCode', 'AUTH401')
					->withAttribute('Data', ['notes' => 'Try POSTing to /session']);
				$response = $response
					->withStatus(401)
					->withHeader('WWW-Authenticate', 'login');
			} catch(InvalidPayloadParameterException $e) {
				$request = $request
					->withAttribute('Status', JSend::FAIL)
					->withAttribute('Data', [$e->getParameter() => $e->getReason()]);
				$response = $response
					->withStatus(400);
			} catch(InvalidParameterException $e) {
				$request = $request
					->withAttribute('Status', JSend::FAIL)
					->withAttribute('Data', [$e->getParameter() => $e->getMessage()]);
				$response = $response
					->withStatus(400);
			} catch(MissingMandatoryParameterException $e) {
				$request = $request
					->withAttribute('Status', JSend::FAIL)
					->withAttribute('Data', [$e->getParameter() => $e->getMessage()]);
				$response = $response
					->withStatus(400);
			} catch(InvalidRequestBodyException $e) {
				$request = $request
					->withAttribute('Status', JSend::FAIL)
					->withAttribute('Data', ['*' => $e->getMessage()]);
				$response = $response
					->withStatus(400);
			} catch(DatabaseException $e) {
				$request = $request
					->withAttribute('Status', JSend::ERROR)
					->withAttribute('ErrorMessage', 'Database error: ' . $e->getMessage());
				$response = $response
					->withStatus(500);
			} catch(NotFoundException $e) {
				$request = $request
					->withAttribute('Status', JSend::ERROR)
					->withAttribute('ErrorMessage', $e->getMessage())
					->withAttribute('ErrorCode', $e->getCode());
				$response = $response
					->withStatus(404);
			} catch(\Throwable $e) {
				$request = $request
					->withAttribute('Status', JSend::ERROR)
					->withAttribute('ErrorMessage', 'Unhandled exception :(')
					->withAttribute('Data', ['message' => $e->getMessage(), 'code' => $e->getCode()]);
				$response = $response
					->withStatus(500);
			}

			return static::renderResponse($request, $response);
		} else {
			return $response;
		}
	}
}