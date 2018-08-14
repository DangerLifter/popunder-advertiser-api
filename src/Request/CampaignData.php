<?php

namespace PopUnderAdvertiser\Request;

class CampaignData
{
	/** @var string */
	private $_name;

	/** @var bool */
	private $_active;

	/** @var float */
	private $_minBid;

	/** @var float */
	private $_maxBid;

	/** @var bool */
	private $_adultAllowed;

	/** @var string */
	private $_url;

	/** @var int */
	private $_limitAllCnt;

	/** @var int */
	private $_limitDayCnt;

	/** @var int */
	private $_limitHourCnt;

	/** @var float */
	private $_limitDayMoney;

	/** @var float */
	private $_limitAllMoney;

	/** @var int[] */
	private $_locations;

	/** @var int[] */
	private $_browsers;

	/** @var int[] */
	private $_topics;

	/** @var int[] */
	private $_languages;

	public static function create(): self
	{
		return new self();
	}

	public function setName(string $name): self
	{
		$this->_name = $name;
		return $this;
	}

	public function setActive(bool $active): self
	{
		$this->_active = $active;
		return $this;
	}

	public function setMinBid(float $minBid = null): self
	{
		$this->_minBid = $minBid;
		return $this;
	}

	public function setMaxBid(float $maxBid = null): self
	{
		$this->_maxBid = $maxBid;
		return $this;
	}

	public function setUrl(string $url): self
	{
		$this->_url = $url;
		return $this;
	}

	public function setLimitAllCnt(int $limitAllCnt = null): self
	{
		$this->_limitAllCnt = $limitAllCnt;
		return $this;
	}

	public function setLimitDayCnt(int $limitDayCnt = null): self
	{
		$this->_limitDayCnt = $limitDayCnt;
		return $this;
	}

	public function setLimitHourCnt(int $limitHourCnt = null): self
	{
		$this->_limitHourCnt = $limitHourCnt;
		return $this;
	}

	public function setLimitDayMoney(float $limitDayMoney = null): self
	{
		$this->_limitDayMoney = $limitDayMoney;
		return $this;
	}

	public function setLimitAllMoney(float $limitAllMoney = null): self
	{
		$this->_limitAllMoney = $limitAllMoney;
		return $this;
	}

	public function adultAllowed(): self
	{
		$this->_adultAllowed = true;
		return $this;
	}

	public function adultNotAllowed(): self
	{
		$this->_adultAllowed = false;
		return $this;
	}

	public function isAdultAllowed(): bool
	{
		return $this->_adultAllowed;
	}

	public function addLocation(int $location): self
	{
		$this->_locations[] = $location;
		return $this;
	}

	public function setLocations(array $locations): self
	{
		$this->_locations = $locations;
		return $this;
	}

	public function addLanguage(int $language): self
	{
		$this->_languages[] = $language;
		return $this;
	}

	public function setLanguages(array $languages): self
	{
		$this->_languages = $languages;
		return $this;
	}

	public function addBrowser(int $browser): self
	{
		$this->_browsers[] = $browser;
		return $this;
	}

	public function setBrowsers(array $browsers): self
	{
		$this->_browsers = $browsers;
		return $this;
	}

	public function addTopic(int $topic): self
	{
		$this->_topics[] = $topic;
		return $this;
	}

	public function setTopics(array $topics): self
	{
		$this->_topics = $topics;
		return $this;
	}

	public function getName(): ?string
	{
		return $this->_name;
	}

	public function isActive(): ?bool
	{
		return $this->_active;
	}

	public function getMinBid(): ?float
	{
		return $this->_minBid;
	}

	public function getMaxBid(): ?float
	{
		return $this->_maxBid;
	}

	public function getUrl(): ?string
	{
		return $this->_url;
	}

	public function getLimitAllCnt(): ?int
	{
		return $this->_limitAllCnt;
	}

	public function getLimitDayCnt(): ?int
	{
		return $this->_limitDayCnt;
	}

	public function getLimitHourCnt(): ?int
	{
		return $this->_limitHourCnt;
	}

	public function getLimitDayMoney(): ?float
	{
		return $this->_limitDayMoney;
	}

	public function getLimitAllMoney(): ?float
	{
		return $this->_limitAllMoney;
	}

	public function getLocations(): ?array
	{
		return $this->_locations;
	}

	public function getBrowsers(): ?array
	{
		return $this->_browsers;
	}

	public function getTopics(): ?array
	{
		return $this->_topics;
	}

	public function getLanguages(): ?array
	{
		return $this->_languages;
	}

	public function toQueryData(): array
	{
		$data = [
			'fields' => [
				'adult' => (int) $this->_adultAllowed,
			]
		];

		if (\is_bool($this->_active)) {
			$data['active'] = (int) $this->_active;
		}

		$this->addData('name', $data);
		$this->addData('minBid', $data, 'minsum');
		$this->addData('maxBid', $data, 'maxsum');
		$this->addData('url', $data, 'address');

		$this->addData('limitAllCnt', $data);
		$this->addData('limitDayCnt', $data);
		$this->addData('limitHourCnt', $data);
		$this->addData('limitDayMoney', $data);
		$this->addData('limitAllMoney', $data);

		$this->addData('languages', $data, 'langs');
		$this->addData('locations', $data);
		$this->addData('topics', $data);
		$this->addData('browsers', $data);

		return $data;
	}

	private function addData(
		string $name, array &$data, string $queryFieldName = null
	): self
	{
		$fieldName = '_'.$name;
		if (null !== $this->$fieldName) {
			$data['fields'][\strtolower($queryFieldName ?? $name)] = $this->$fieldName;
		}
		return $this;
	}
}