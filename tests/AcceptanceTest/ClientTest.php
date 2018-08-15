<?php

namespace PopUnderAdvertiser\Tests\AcceptanceTest;

use PHPUnit\Framework\TestCase;
use PopUnderAdvertiser\Client;
use PopUnderAdvertiser\EntityList;
use PopUnderAdvertiser\Exception\Exception;
use PopUnderAdvertiser\Request\CampaignData;

require_once __DIR__.'/../config.php';

class ClientTest extends TestCase
{
	/** @var Client */
	private $_client;

	protected function setUp()/* The :void return type declaration that should be here would cause a BC issue */
	{
		parent::setUp(); // TODO: Change the autogenerated stub

		$this->_client = new Client(
			POPUNDER_TEST_ACCOUNT_USER_ID,
			POPUNDER_TEST_ACCOUNT_KEY
		);
	}

	public function testGetCampaignList(): void
	{
		$this->assertInternalType('array', $this->_client->getCampaignList());
	}

	public function testGetLanguageByName_WrongName_Exception(): void
	{
		$this->expectException(Exception::class);
		$this->_client->getLanguages()->getByName('nl');
	}

	public function testGetLanguageByName(): void
	{
		$this->assertEquals(
			['id' => 2, 'name' => 'en'],
			$this->_client->getLanguages()->getByName('en')
		);
	}

	public function testGetLocationByName(): void
	{
		$this->assertEquals(
			['id' => 2750405, 'name' => 'Netherlands', 'parent_id' => 0],
			$this->_client->getLocations()->getByName('Netherlands')
		);
	}

	public function testGetTopicsByName(): void
	{
		$this->assertEquals(
			['id' => 6, 'name' => 'Home/Health ', 'parent_id' => 1],
			$this->_client->getTopics()->getByName('Home/Health')
		);
	}

	public function testGetBrowsersByName(): void
	{
		$this->assertEquals(
			['id' => 1, 'name' => 'Desktop', 'parent_id' => 0],
			$this->_client->getBrowsers()->getByName('Desktop')
		);
	}

	public function testGetLocations(): void
	{
		$locations = $this->_client->getLocations();
		$this->assertEquals(
			[
				'id' => 0,
				'name' => 'All locations',
				'parent_id' => null,
			],
			$locations->getByName(EntityList::NAME_ALL_LOCATIONS)
		);
	}

	public function testAddCampaign_WrongArguments_Exception(): void
	{
		$request = new CampaignData();
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('confirm_no_targets');
		$this->_client->addCampaign($request);
	}

	public function testGetCampaignInfo_WrongId_Exception(): void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Invalid campaign id');
		$this->_client->getTotalCampaignInfo('12111');
	}

	public function testSimpleAddAndRemoveCampaign_ValidArguments_Success(): void
	{
		$campaignIds = $this->_client->getCampaignList();
		$this->assertInternalType('array', $campaignIds);

		$request = $this->createCampaignData();
		$campaignId = $this->_client->addCampaign($request);
		$this->assertGreaterThan(0, $campaignId);

		$expected = clone $request;
		$this->assertCampaign($campaignId, $expected);

		$request =
			(new CampaignData())
				->setName('Updated test company')
		;
		$this->_client->editCampaign($campaignId, $request);
		$this->assertEquals(
			'Updated test company',
			$this->_client->getCampaignInfo($campaignId, Client::CAMPAIGN_INFO_NAME)
		);

		$this->_client->removeCampaign($campaignId);
		$this->assertEquals($campaignIds, $this->_client->getCampaignList());
	}


	public function testGetCampaignInfo_DeletedCampaign_Success(): void
	{
		$request = $this->createCampaignData();
		$campaignId = $this->_client->addCampaign($request);

		$this->_client->removeCampaign($campaignId);

		$info = $this->_client->getTotalCampaignInfo($campaignId);
		$this->assertEquals(1, $info['deleted']);
	}

	private function assertCampaign(int $campaignId, CampaignData $expected)
	{
		$this->assertNotFalse(
			\array_search($campaignId, $this->_client->getCampaignList(), true)
		);

		$this->assertEquals(
			$expected->getName(),
			$this->_client->getCampaignInfo($campaignId, Client::CAMPAIGN_INFO_NAME)
		);
		$this->assertEquals(
			$expected->getMinBid(),
			$this->_client->getCampaignInfo($campaignId, Client::CAMPAIGN_INFO_MINBID)
		);
		$this->assertEquals(
			$expected->getMaxBid(),
			$this->_client->getCampaignInfo($campaignId, Client::CAMPAIGN_INFO_MAXBID)
		);
		$this->assertEquals(
			[
				'name' => $expected->getName(),
				'minsum' => $expected->getMinBid(),
				'maxsum' => $expected->getMaxBid(),
				'active' => 1,
				'deleted' => 0,
				'url' => $expected->getUrl(),
				'verified' => 0,
				'comments' => ''
			],
			$this->_client->getTotalCampaignInfo($campaignId)
		);
	}

	private function createCampaignData(): CampaignData
	{
		$locationId = $this->_client->getLocations()->getByName('Russia')['id'];
		$topicId = $this->_client->getTopics()->getByName(EntityList::NAME_ALL_TOPICS)['id'];
		$langId = $this->_client->getLanguages()->getByName(EntityList::NAME_ALL_LANGUAGES)['id'];
		$browserId = $this->_client->getBrowsers()->getByName('Desktop')['id'];

		return
			(new CampaignData())
				->setName('Test company')
				->setUrl('http://example.com')
				->setMinBid('1.2')
				->setMaxBid('2.5')
				->setLimitDayCnt(1000)
				->setLimitAllCnt(1000)
				->addLocation($locationId)
				->addTopic($topicId)
				->addLanguage($langId)
				->addBrowser($browserId)
				->adultNotAllowed()
		;
	}
}