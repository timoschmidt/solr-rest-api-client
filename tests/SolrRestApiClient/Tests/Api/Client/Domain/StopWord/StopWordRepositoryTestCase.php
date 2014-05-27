<?php
/**
 * Created by IntelliJ IDEA.
 * User: pavelbogomolenko
 * Date: 5/27/14
 * Time: 1:17 PM
 * To change this template use File | Settings | File Templates.
 */

namespace SolrRestApiClient\Tests\Api\Client\Domain\StopWord;

use SolrRestApiClient\Api\Client\Domain\StopWord\StopWord;
use SolrRestApiClient\Api\Client\Domain\StopWord\StopWordCollection;
use SolrRestApiClient\Api\Client\Domain\StopWord\StopWordDataMapper;
use SolrRestApiClient\Api\Client\Domain\StopWord\StopWordRepository;
use SolrRestApiClient\Tests\BaseTestCase;


/**
 * Class StopWordRepositoryTestCase
 * @package SolrRestApiClient\Tests\Api\Client\Domain\StopWord
 */
class StopWordRepositoryTestCase extends BaseTestCase {
	/**
	 * @var StopWordDataMapper
	 */
	protected $dataMapper = null;

	/**
	 * @var \SolrRestApiClient\Api\Client\Domain\StopWord\StopWordRepository()
	 */
	protected $stopwordRepository;

	/**
	 * @return void
	 */
	public function setUp() {
		$this->dataMapper = new StopWordDataMapper();
		$this->stopwordRepository = $this->getMock('SolrRestApiClient\Api\Client\Domain\StopWord\StopWordRepository',array('executeDeleteRequest','executeGetRequest','executePostRequest'));
		$this->stopwordRepository->injectDataMapper($this->dataMapper);
	}

	/**
	 * @return void
	 */
	public function tearDown() {}

	/**
	 * @test
	 */
	public function canAddAll() {
		$stopwordCollection = new StopWordCollection();
		$stopword = new StopWord();
		$stopword->setWord('foo');
		$stopwordCollection->add($stopword);

		$expectedJson = '["foo"]';
		$responseMock = $this->getMock('Guzzle\Http\Message\Response',array('getBody','getStatusCode'), array(),'',false);
		$responseMock->expects($this->once())->method('getStatusCode')->will($this->returnValue(200));
		$this->stopwordRepository->expects($this->once())->method('executePostRequest')->with('solr/schema/analysis/stopwords/it',$expectedJson)->will(
			$this->returnValue($responseMock)
		);

		$result = $this->stopwordRepository->addAll($stopwordCollection, 'it');
		$this->assertTrue($result);
	}

	/**
	 * @test
	 */
	public function canGetAllStopwords() {
		$responseMock = $this->getMock('Guzzle\Http\Message\Response',array('getBody'), array(),'',false);

		$fixtureResponse = '{
			  "responseHeader":{
			    "status":0,
			    "QTime":1
			  },
			  "wordSet":{
			    "initArgs":{"ignoreCase":true},
			    "initializedOn":"2014-03-28T20:53:53.058Z",
			    "managedList":[
			      "a",
			      "an",
			      "and"
			     ]
			  }
			}';

		$responseMock->expects($this->once())->method('getBody')->will($this->returnValue(
			$fixtureResponse
		));
		$this->stopwordRepository->expects($this->once())->method('executeGetRequest')->with('solr/schema/analysis/stopwords/it')->will(
			$this->returnValue($responseMock)
		);

		$stopwordAll = $this->stopwordRepository->getAll("it");
		$this->assertInstanceOf('SolrRestApiClient\Api\Client\Domain\StopWord\StopWordCollection', $stopwordAll);
		$this->assertEquals(3, $stopwordAll->getCount(),'Unexpected amount of stopwords retrieved');
	}
}