<?php

$includes = array('ting-dbc-php5-client/lib/TingClient',
									'ting-dbc-php5-client/lib/adapter/request/http/TingClientDrupal6HttpRequestAdapter',
									'ting-dbc-php5-client/lib/adapter/request/http/TingClientHttpRequestFactory',
									'ting-dbc-php5-client/lib/adapter/response/json/TingClientJsonResponseAdapter',
									'ting-dbc-php5-client/lib/log/TingClientDrupalWatchDogLogger',
									'addi-client/AdditionalInformationService');
foreach ($includes as $include)
{
	module_load_include('php', 'ting', 'lib/'.$include);
}

class TingClientFacade {
	
	private static $format = 'json';
	
	/**
	 * @var TingClient
	 */
	private static $client;
	
	/**
	 * @return TingClient
	 */
	private static function getClient() {
		if (!isset(self::$client))
		{
			$baseUrl = variable_get('ting_server', false);
			if (!$baseUrl) {
				throw new TingClientException('No Ting server defined');
			}
			$scanUrl = 'http://didicas.dbc.dk/openscan/server.php'; //TODO move this to administration
			//Create client with default configuration: Drupal for requests and logging, json for format.
			self::$client = new TingClient(new TingClientDrupal6HttpRequestAdapter(new TingClientHttpRequestFactory($baseUrl, $scanUrl)),
																				new TingClientJsonResponseAdapter(),
																				new TingClientDrupalWatchDogLogger());
		}
		return self::$client;
	}
	
	/**
	 * @param string $query
	 * @return TingClientSearchResult
	 */
	public static function search($query, $page = 1, $resultsPerPage = 10, $options = array()) {
		$searchRequest = new TingClientSearchRequest($query);
		$searchRequest->setOutput(self::$format); //use json format per default
		$searchRequest->setStart($resultsPerPage * ($page - 1) + 1);
		$searchRequest->setNumResults($resultsPerPage);
		
		$searchRequest->setFacets((isset($options['facets'])) ? $options['facets'] : array('facet.subject', 'facet.creator', 'facet.type', 'facet.date', 'facet.language'));
		$searchRequest->setNumFacets((isset($options['numFacets'])) ? $options['numFacets'] : ((sizeof($searchRequest->getFacets()) == 0) ? 0 : 10));
		
		$searchResult = self::getClient()->search($searchRequest);
		
		//Decorate search result with additional information
		foreach ($searchResult->collections as &$collection)
		{
			$collection = self::addCollectionInfo($collection);
			$collection = self::addAdditionalInfo($collection);
		}
		
		return $searchResult;
	}
	
	/**
	 * @param string $query The prefix to scan for
	 * @param int $numResults The numver of results to return
	 * @return TingClientScanResult
	 */
	public static function scan($query, $numResults = 10)
	{
		$scanRequest = new TingClientScanRequest();
		$scanRequest->setField('phrase.anyIndexes');
		$scanRequest->setLower($query);
		$scanRequest->setNumResults($numResults);
		$scanRequest->setOutput('json');
		return self::getClient()->scan($scanRequest);
	}
	
	/**
	 * @param string $objectId
	 * @return TingClientObjectCollection
	 */
	public static function getCollection($objectId)
	{
		$collection = self::getClient()->getCollection(new TingClientCollectionRequest($objectId, self::$format));
		return self::addCollectionInfo(self::addAdditionalInfo($collection));
	}
	
	/**
	 * @param string $objectId
	 * @return TingClientObject
	 */
	public static function getObject($objectId)
	{
		$object = self::getClient()->getObject(new TingClientObjectRequest($objectId, self::$format));
		return array_shift(self::addAdditionalInfo(array($object)));
	}

	private static function addCollectionInfo(TingClientObjectCollection $collection)
	{
		$collection->url = url('ting/collection/' . $collection->objects[0]->id, array('absolute' => true));
		$types = array();
		foreach ($collection->objects as &$object)
		{
			$object = self::addObjectUrl($object);
			$types = array_merge($types, $object->data->type);
		}
		$collection->types = array_unique($types);
		return $collection;
	}
	
	private static function addObjectUrl(TingClientObject $object)
	{
		$object->url = url('ting/object/'.$object->id, array('absolute' => true));
		return $object;
	}
	
	private static function addAdditionalInfo($collection)
	{
		//Add additional information info for cover images
		$isbns = array();
		
		$objects = (isset($collection->objects)) ? $collection->objects : $collection;
		
		foreach($objects as $object)
		{
			if (isset($object->data->identifier))
			{
				foreach ($object->data->identifier as $identifier)
				{
					if ($identifier->type == TingClientObjectIdentifier::ISBN)
					{
						$isbns[] = $identifier->id;
					}
				}
			}
		}
	
		if (sizeof($isbns) > 0)
		{
			//TODO: Move account information to admin settings page
			$additionalInformationService = new AdditionalInformationService('netpunkt', '710100', 'Juni1706');
			$additionalInformations = $additionalInformationService->getByIsbn($isbns);
			
			foreach ($additionalInformations as $id => $ai)
			{
				foreach ($objects as &$object)
				{
					if (isset($object->data->identifier))
					{						
						foreach ($object->data->identifier as $identifier)
						{
							if ($identifier->type == TingClientObjectIdentifier::ISBN &&
									$identifier->id == $id)
							{	
								$object->additionalInformation = $ai;
							}
						}
					}
				}
			}
		}
		
		if (isset($collection->objects))
		{
			$collection->objects = $objects;
		}
		else
		{
			$collection = $objects;			
		}
		
		return $collection;
	}
		
}

?>
