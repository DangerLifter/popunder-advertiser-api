<?php

namespace PopUnderAdvertiser;

use PopUnderAdvertiser\Exception\Exception;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use \PopUnderAdvertiser\Request\CampaignData;

class Client
{
	public const CAMPAIGN_INFO_NAME 	= 'name';
	public const CAMPAIGN_INFO_VERIFIED = 'verified';
	public const CAMPAIGN_INFO_MINBID 	= 'minsum';
	public const CAMPAIGN_INFO_MAXBID 	= 'maxsum';
	public const CAMPAIGN_INFO_ACTIVE 	= 'active';
	public const CAMPAIGN_INFO_DELETED 	= 'deleted';
	public const CAMPAIGN_INFO_URL 		= 'url';

	private $_userId;
	private $_key;
	private $_httpClient;

	private $_baseUrl = 'http://new.popunder.net/back/api/account';

	public function __construct(
		string $userId, string $key, \GuzzleHttp\Client $httpClient = null
	)
	{
		$this->_key = $key;
		$this->_userId = $userId;
		$this->_httpClient = $httpClient ?? new \GuzzleHttp\Client();
	}

	public function getUserId(): string
	{
		return $this->_userId;
	}

	public function getKey(): string
	{
		return $this->_key;
	}

	public function getBaseUrl(): string
	{
		return $this->_baseUrl;
	}

	public function setBaseUrl(string $baseUrl): self
	{
		$this->_baseUrl = $baseUrl;
		return $this;
	}

	public function getLocationId(string $name): ?int
	{
		$locations = $this->getLocations();
		foreach ($locations as $location) {
			if (\strtolower($location['name']) === strtolower($name)) return $location['id'];
		}
		return null;
	}

	public function getLocations(): EntityList
	{
		$response = $this->request('targets/locations', [], true);
		return new EntityList($response['locations']);
	}

	public function getBrowsers(): EntityList
	{
		$response = $this->request('targets/browsers', [], true);
		return new EntityList($response['browsers']);
	}

	public function getTopics(): EntityList
	{
		$response = $this->request('targets/topics', [], true);
		return new EntityList($response['topics']);
	}

	public function getLanguages(): EntityList
	{
		$response = $this->request('targets/langs', [], true);
		return new EntityList($response['langs']);
	}

	/**
	 * @return int[]
	 */
	public function getCampaignList(): array
	{
		$response = $this->request('stat/campaigns_list');
		return $response['ids'];
	}

	public function getTotalCampaignStatistic(
		\DateTimeInterface $from = null,
		\DateTimeInterface $to = null,
		int $campaignId = null
	): array
	{
		if (!$from) $from = new \DateTimeImmutable();
		if (!$to) $to = new \DateTimeImmutable();
		$request = [
			'date1' => $from->format('Y-m-d'),
			'date2' => $to->format('Y-m-d')
		];

		if ($campaignId) $request['campaign_id'] = $campaignId;
		return $this->request('stat/default', $request, true);
	}

	public function getTotalCampaignInfo(int $campaignId): array
	{
		$response = $this->request(
			'campaign/get', ['campaign_id' => $campaignId, 'field' => 'total']
		);
		$result = $response['value'];
		if (isset($response['comments'])) {
			$result['comments'] = $response['comments'];
		}
		return $result;
	}

	public function getCampaignInfo(int $campaignId, string $field): string
	{
		$response = $this->request(
			'campaign/get', ['campaign_id' => $campaignId, 'field' => $field]
		);
		return $response['value'];
	}

	public function removeCampaign(int $campaignId): self
	{
		$this->request('campaign/delete', ['campaign_id' => $campaignId]);
		return $this;
	}

	public function addCampaign(CampaignData $request): int
	{
		$response = $this->request('campaign/add', $request->toQueryData());
		return (int) $response['id'];
	}

	public function editCampaign(int $campaignId, CampaignData $request): self
	{
		$data = $request->toQueryData();
		$data['campaign_id'] = $campaignId;
		$this->request('campaign/edit', $data);
		return $this;
	}

	public function startCampaign(int $campaignId): self
	{
		return $this->editCampaign($campaignId, CampaignData::create()->setActive(true));
	}

	public function pauseCampaign(int $campaignId): self
	{
		return $this->editCampaign($campaignId, CampaignData::create()->setActive(false));
	}

	private function parseResponse(ResponseInterface $response, bool $ignoreErrorCheck = false): array
	{
		$result = \json_decode((string) $response->getBody(), true);
		if (!\is_array($result)) throw new Exception('Invalid response');
		if ($ignoreErrorCheck) return $result;
		if (
			isset($result['error'])
			|| !isset($result['success'])
			|| !$result['success']
		) {
			if (isset($result['data'])) {
				if (\is_string($result['data'])) {
					throw new Exception($result['data']);
				}
				if (isset($result['data']['message'])) {
					throw new Exception($result['data']['message']);
				}
			}
			if (\is_string($result['error'])) {
				throw new Exception($result['error']);
			}
			throw new Exception('Unknown error');
		}
		return $result;
	}

	private function request(string $endPoint, array $data = [], bool $ignoreErrorCheck = false): array
	{
		$url = $this->_baseUrl.'/'.$endPoint.'/';
		$data['user_id'] = $this->_userId;
		$data['key'] = $this->_key;
		if ($data) $url .= '?'.http_build_query($data);

		try {
			return $this->parseResponse($this->_httpClient->get($url), $ignoreErrorCheck);
		} catch (GuzzleException $e) {
			throw new Exception('Failed request to service', 0, $e);
		}
	}
}