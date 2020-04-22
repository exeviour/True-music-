<?php

/**
 * ownCloud - Music app
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Morris Jobke <hey@morrisjobke.de>
 * @copyright Morris Jobke 2013, 2014
 */

namespace OCA\Music\Controller;

use \OCA\Music\AppFramework\Utility\ControllerTestUtility;
use \OCP\AppFramework\Http\JSONResponse;

use OCA\Music\DB\Artist;
use OCA\Music\DB\Album;
use OCA\Music\DB\Track;

class APIControllerTest extends ControllerTestUtility {
	private $trackBusinessLayer;
	private $artistBusinessLayer;
	private $albumBusinessLayer;
	private $collectionHelper;
	private $request;
	private $controller;
	private $userId = 'john';
	private $appname = 'music';
	private $urlGenerator;
	private $l10n;
	private $scanner;
	private $coverHelper;
	private $detailsHelper;
	private $maintenance;
	private $userFolder;
	private $logger;

	protected function setUp() {
		$this->request = $this->getMockBuilder('\OCP\IRequest')
			->disableOriginalConstructor()
			->getMock();
		$this->urlGenerator = $this->getMockBuilder('\OCP\IURLGenerator')
			->disableOriginalConstructor()
			->getMock();
		$this->l10n = $this->getMockBuilder('\OCP\IL10N')
			->disableOriginalConstructor()
			->getMock();
		$this->userFolder = $this->getMockBuilder('\OCP\Files\Folder')
			->disableOriginalConstructor()
			->getMock();
		$this->trackBusinessLayer = $this->getMockBuilder('\OCA\Music\BusinessLayer\TrackBusinessLayer')
			->disableOriginalConstructor()
			->getMock();
		$this->artistBusinessLayer = $this->getMockBuilder('\OCA\Music\BusinessLayer\ArtistBusinessLayer')
			->disableOriginalConstructor()
			->getMock();
		$this->albumBusinessLayer = $this->getMockBuilder('\OCA\Music\BusinessLayer\AlbumBusinessLayer')
			->disableOriginalConstructor()
			->getMock();
		$this->playlistBusinessLayer = $this->getMockBuilder('\OCA\Music\BusinessLayer\PlaylistBusinessLayer')
			->disableOriginalConstructor()
			->getMock();
		$this->bookmarkBusinessLayer = $this->getMockBuilder('\OCA\Music\BusinessLayer\BookmarkBusinessLayer')
			->disableOriginalConstructor()
			->getMock();
		$this->genreBusinessLayer = $this->getMockBuilder('\OCA\Music\BusinessLayer\GenreBusinessLayer')
			->disableOriginalConstructor()
			->getMock();
		$this->scanner = $this->getMockBuilder('\OCA\Music\Utility\Scanner')
			->disableOriginalConstructor()
			->getMock();
		$this->collectionHelper = $this->getMockBuilder('\OCA\Music\Utility\CollectionHelper')
			->disableOriginalConstructor()
			->getMock();
		$this->coverHelper = $this->getMockBuilder('\OCA\Music\Utility\CoverHelper')
			->disableOriginalConstructor()
			->getMock();
		$this->detailsHelper = $this->getMockBuilder('\OCA\Music\Utility\DetailsHelper')
			->disableOriginalConstructor()
			->getMock();
		$this->maintenance = $this->getMockBuilder('\OCA\Music\Db\Maintenance')
			->disableOriginalConstructor()
			->getMock();
		$this->logger = $this->getMockBuilder('\OCA\Music\AppFramework\Core\Logger')
			->disableOriginalConstructor()
			->getMock();
		$this->controller = new ApiController(
			$this->appname,
			$this->request,
			$this->urlGenerator,
			$this->trackBusinessLayer,
			$this->artistBusinessLayer,
			$this->albumBusinessLayer,
			$this->playlistBusinessLayer,
			$this->bookmarkBusinessLayer,
			$this->genreBusinessLayer,
			$this->scanner,
			$this->collectionHelper,
			$this->coverHelper,
			$this->detailsHelper,
			$this->maintenance,
			$this->userId,
			$this->l10n,
			$this->userFolder,
			$this->logger);
	}

	/**
	 * @param string $methodName
	 */
	private function assertAPIControllerAnnotations($methodName) {
		$annotations = ['NoAdminRequired', 'NoCSRFRequired'];
		$this->assertAnnotations($this->controller, $methodName, $annotations);
	}

	public function testAnnotations() {
		$this->assertAPIControllerAnnotations('artists');
		$this->assertAPIControllerAnnotations('artist');
		$this->assertAPIControllerAnnotations('albums');
		$this->assertAPIControllerAnnotations('album');
		$this->assertAPIControllerAnnotations('tracks');
		$this->assertAPIControllerAnnotations('track');
	}

	public function testArtists() {
		$artist1 = new Artist();
		$artist1->setId(3);
		$artist1->setName('The artist name');
		$artist1->setImage('The image url');
		$artist2 = new Artist();
		$artist2->setId(4);
		$artist2->setName('The other artist name');
		$artist2->setImage('The image url number 2');

		$this->artistBusinessLayer->expects($this->once())
			->method('findAll')
			->with($this->equalTo($this->userId))
			->will($this->returnValue([$artist1, $artist2]));

		$result = [
			[
				'name' => 'The artist name',
				'image' => 'The image url',
				'uri' => null,
				'slug' => '3-the-artist-name',
				'id' => 3
			],
			[
				'name' => 'The other artist name',
				'image' => 'The image url number 2',
				'uri' => null,
				'slug' => '4-the-other-artist-name',
				'id' => 4
			]
		];

		$response = $this->controller->artists(false /*fulltree*/, false /*albums*/);

		$this->assertEquals($result, $response->getData());
		$this->assertTrue($response instanceof JSONResponse);
	}

	public function testArtistsFulltree() {
		$artist1 = new Artist();
		$artist1->setId(3);
		$artist1->setName('The artist name');
		$artist1->setImage('The image url');
		$artist2 = new Artist();
		$artist2->setId(4);
		$artist2->setName('The other artist name');
		$artist2->setImage('The image url number 2');
		$artist3 = new Artist();
		$artist3->setId(5);
		$artist3->setName('The other new artist name');
		$artist3->setImage('The image url number 3');
		$album = new Album();
		$album->setId(4);
		$album->setName('The name');
		$album->setYears([2011, 2013]);
		$album->setCoverFileId(5);
		$album->setArtistIds([3]);
		$album->setAlbumArtistId(5);
		$track = new Track();
		$track->setId(1);
		$track->setTitle('The title');
		$track->setArtistId(3);
		$track->setAlbumId(4);
		$track->setNumber(4);
		$track->setLength(123);
		$track->setFileId(3);
		$track->setMimetype('audio/mp3');
		$track->setBitrate(123);

		$albumId = 4;

		$this->artistBusinessLayer->expects($this->once())
			->method('findAll')
			->with($this->equalTo($this->userId))
			->will($this->returnValue([$artist1, $artist2]));
		$this->albumBusinessLayer->expects($this->at(0))
			->method('findAllByArtist')
			->with($this->equalTo(3), $this->equalTo($this->userId))
			->will($this->returnValue([$album]));
		$this->albumBusinessLayer->expects($this->at(1))
			->method('findAllByArtist')
			->with($this->equalTo(4), $this->equalTo($this->userId))
			->will($this->returnValue([$album]));
		$this->trackBusinessLayer->expects($this->exactly(2))
			->method('findAllByAlbum')
			->with($this->equalTo($albumId))
			->will($this->returnValue([$track]));

		$result = [
			[
				'name' => 'The artist name',
				'image' => 'The image url',
				'uri' => null,
				'slug' => '3-the-artist-name',
				'id' => 3,
				'albums' => [
					[
						'name' => 'The name',
						'cover' => null,
						'uri' => null,
						'slug' => '4-the-name',
						'id' => 4,
						'year' => 2013,
						'artists' => [
							['id' => 3, 'uri' => null]
						],
						'albumArtistId' => 5,
						'tracks' => [
							[
								'title' => 'The title',
								'uri' => null,
								'slug' => '1-the-title',
								'id' => 1,
								'ordinal' => 4,
								'bitrate' => 123,
								'length' => 123,
								'artist' => ['id' => 3, 'uri' => null],
								'album' => ['id' => 4, 'uri' => null],
								'files' => [
									'audio/mp3' => null
								]
							]
						]
					]
				]
			],
			[
				'name' => 'The other artist name',
				'image' => 'The image url number 2',
				'uri' => null,
				'slug' => '4-the-other-artist-name',
				'id' => 4,
				'albums' => [
					[
						'name' => 'The name',
						'cover' => null,
						'uri' => null,
						'slug' => '4-the-name',
						'id' => 4,
						'year' => 2013,
						'artists' => [
							['id' => 3, 'uri' => null]
						],
						'albumArtistId' => 5,
						'tracks' => [
							[
								'title' => 'The title',
								'uri' => null,
								'slug' => '1-the-title',
								'id' => 1,
								'ordinal' => 4,
								'bitrate' => 123,
								'length' => 123,
								'artist' => ['id' => 3, 'uri' => null],
								'album' => ['id' => 4, 'uri' => null],
								'files' => [
									'audio/mp3' => null
								]
							]
						]
					]
				]
			]
		];

		$response = $this->controller->artists(true /*fulltree*/, false /*albums*/);

		$this->assertEquals($result, $response->getData());
		$this->assertTrue($response instanceof JSONResponse);
	}

	public function testArtistsAlbumsOnlyFulltree() {
		$artist1 = new Artist();
		$artist1->setId(3);
		$artist1->setName('The artist name');
		$artist1->setImage('The image url');
		$artist2 = new Artist();
		$artist2->setId(4);
		$artist2->setName('The other artist name');
		$artist2->setImage('The image url number 2');
		$album = new Album();
		$album->setId(4);
		$album->setName('The name');
		$album->setYears([2013]);
		$album->setCoverFileId(5);
		$album->setArtistIds([3]);
		$album->setAlbumArtistId(3);

		$this->artistBusinessLayer->expects($this->once())
			->method('findAll')
			->with($this->equalTo($this->userId))
			->will($this->returnValue([$artist1, $artist2]));
		$this->albumBusinessLayer->expects($this->at(0))
			->method('findAllByArtist')
			->with($this->equalTo(3), $this->equalTo($this->userId))
			->will($this->returnValue([$album]));
		$this->albumBusinessLayer->expects($this->at(1))
			->method('findAllByArtist')
			->with($this->equalTo(4), $this->equalTo($this->userId))
			->will($this->returnValue([$album]));
		$this->trackBusinessLayer->expects($this->never())
			->method('findAllByAlbum');

		$result = [
			[
				'name' => 'The artist name',
				'image' => 'The image url',
				'uri' => null,
				'slug' => '3-the-artist-name',
				'id' => 3,
				'albums' => [
					[
						'name' => 'The name',
						'cover' => null,
						'uri' => null,
						'slug' => '4-the-name',
						'id' => 4,
						'year' => 2013,
						'artists' => [
							['id' => 3, 'uri' => null]
						],
						'albumArtistId' => 3
					],
				]
			],
			[
				'name' => 'The other artist name',
				'image' => 'The image url number 2',
				'uri' => null,
				'slug' => '4-the-other-artist-name',
				'id' => 4,
				'albums' => [
					[
						'name' => 'The name',
						'cover' => null,
						'uri' => null,
						'slug' => '4-the-name',
						'id' => 4,
						'year' => 2013,
						'artists' => [
							['id' => 3, 'uri' => null]
						],
						'albumArtistId' => 3
					]
				]
			]
		];

		$response = $this->controller->artists(false /*fultree*/, true /*albums*/);

		$this->assertEquals($result, $response->getData());
		$this->assertTrue($response instanceof JSONResponse);
	}

	public function testArtist() {
		$artist = new Artist();
		$artist->setId(3);
		$artist->setName('The artist name');
		$artist->setImage('The image url');

		$artistId = 3;

		$this->artistBusinessLayer->expects($this->once())
			->method('find')
			->with($this->equalTo($artistId), $this->equalTo($this->userId))
			->will($this->returnValue($artist));

		$result = [
			'name' => 'The artist name',
			'image' => 'The image url',
			'uri' => null,
			'slug' => '3-the-artist-name',
			'id' => 3
		];

		$response = $this->controller->artist($artistId, false /*fulltree*/);

		$this->assertEquals($result, $response->getData());
		$this->assertTrue($response instanceof JSONResponse);
	}

	public function testArtistFulltree() {
		$artist = new Artist();
		$artist->setId(3);
		$artist->setName('The artist name');
		$artist->setImage('The image url');
		$album = new Album();
		$album->setId(3);
		$album->setName('The name');
		$album->setYears([1999, 2000, 2013]);
		$album->setCoverFileId(5);
		$album->setArtistIds([3]);
		$album->setAlbumArtistId(3);
		$track = new Track();
		$track->setId(1);
		$track->setTitle('The title');
		$track->setArtistId(3);
		$track->setAlbumId(1);
		$track->setNumber(4);
		$track->setLength(123);
		$track->setFileId(3);
		$track->setMimetype('audio/mp3');
		$track->setBitrate(123);

		$artistId = 3;
		$albumId = 3;

		$this->artistBusinessLayer->expects($this->once())
			->method('find')
			->with($this->equalTo($artistId), $this->equalTo($this->userId))
			->will($this->returnValue($artist));
		$this->albumBusinessLayer->expects($this->once())
			->method('findAllByArtist')
			->with($this->equalTo($artistId), $this->equalTo($this->userId))
			->will($this->returnValue([$album]));
		$this->trackBusinessLayer->expects($this->once())
			->method('findAllByAlbum')
			->with($this->equalTo($albumId))
			->will($this->returnValue([$track]));

		$result = [
			'name' => 'The artist name',
			'image' => 'The image url',
			'uri' => null,
			'slug' => '3-the-artist-name',
			'id' => 3,
			'albums' => [
				[
					'name' => 'The name',
					'cover' => null,
					'uri' => null,
					'slug' => '3-the-name',
					'id' => 3,
					'year' => 2013,
					'artists' => [
						['id' => 3, 'uri' => null]
					],
					'albumArtistId' => 3,
					'tracks' => [
						[
							'title' => 'The title',
							'uri' => null,
							'slug' => '1-the-title',
							'id' => 1,
							'ordinal' => 4,
							'bitrate' => 123,
							'length' => 123,
							'artist' => ['id' => 3, 'uri' => null],
							'album' => ['id' => 1, 'uri' => null],
							'files' => [
								'audio/mp3' => null
							]
						]
					]
				]
			]
		];

		$response = $this->controller->artist($artistId, true /*fulltree*/);

		$this->assertEquals($result, $response->getData());
		$this->assertTrue($response instanceof JSONResponse);
	}

	public function testAlbums() {
		$album1 = new Album();
		$album1->setId(3);
		$album1->setName('The name');
		$album1->setYears([2013]);
		$album1->setCoverFileId(5);
		$album1->setArtistIds([1]);
		$album1->setAlbumArtistId(1);
		$album2 = new Album();
		$album2->setId(4);
		$album2->setName('The album name');
		$album2->setYears([]);
		$album2->setCoverFileId(7);
		$album2->setArtistIds([3,5]);
		$album2->setAlbumArtistId(2);

		$this->albumBusinessLayer->expects($this->once())
			->method('findAll')
			->with($this->equalTo($this->userId))
			->will($this->returnValue([$album1, $album2]));

		$result = [
			[
				'name' => 'The name',
				'cover' => null,
				'uri' => null,
				'slug' => '3-the-name',
				'id' => 3,
				'year' => 2013,
				'artists' => [
					['id' => 1, 'uri' => null]
				],
				'albumArtistId' => 1
			],
			[
				'name' => 'The album name',
				'cover' => null,
				'uri' => null,
				'slug' => '4-the-album-name',
				'id' => 4,
				'year' => null,
				'artists' => [
					['id' => 3, 'uri' => null],
					['id' => 5, 'uri' => null]
				],
				'albumArtistId' => 2
			]
		];

		$response = $this->controller->albums(null /*artist*/, false /*fulltree*/);

		$this->assertEquals($result, $response->getData());
		$this->assertTrue($response instanceof JSONResponse);
	}

	public function testAlbumsFulltree() {
		$album1 = new Album();
		$album1->setId(3);
		$album1->setName('The name');
		$album1->setYears([2013]);
		$album1->setCoverFileId(5);
		$album1->setArtistIds([1]);
		$album1->setAlbumArtistId(5);
		$album2 = new Album();
		$album2->setId(4);
		$album2->setName('The album name');
		$album2->setYears([2003]);
		$album2->setCoverFileId(7);
		$album2->setArtistIds([3,5]);
		$album2->setAlbumArtistId(1);
		$artist1 = new Artist();
		$artist1->setId(1);
		$artist1->setName('The artist name');
		$artist1->setImage('The image url');
		$artist2 = new Artist();
		$artist2->setId(3);
		$artist2->setName('The artist name3');
		$artist2->setImage('The image url3');
		$artist3 = new Artist();
		$artist3->setId(5);
		$artist3->setName('The artist name5');
		$artist3->setImage('The image url5');
		$track = new Track();
		$track->setId(1);
		$track->setTitle('The title');
		$track->setArtistId(3);
		$track->setAlbumId(4);
		$track->setNumber(4);
		$track->setLength(123);
		$track->setFileId(3);
		$track->setMimetype('audio/mp3');
		$track->setBitrate(123);

		$this->albumBusinessLayer->expects($this->once())
			->method('findAll')
			->with($this->equalTo($this->userId))
			->will($this->returnValue([$album1, $album2]));
		$this->artistBusinessLayer->expects($this->at(0))
			->method('findById')
			->with($this->equalTo([1]), $this->equalTo($this->userId))
			->will($this->returnValue([$artist1]));
		$this->artistBusinessLayer->expects($this->at(1))
			->method('findById')
			->with($this->equalTo([3,5]), $this->equalTo($this->userId))
			->will($this->returnValue([$artist2, $artist3]));
		$this->trackBusinessLayer->expects($this->at(0))
			->method('findAllByAlbum')
			->with($this->equalTo(3))
			->will($this->returnValue([$track]));
		$this->trackBusinessLayer->expects($this->at(1))
			->method('findAllByAlbum')
			->with($this->equalTo(4))
			->will($this->returnValue([$track]));

		$result = [
			[
				'name' => 'The name',
				'cover' => null,
				'uri' => null,
				'slug' => '3-the-name',
				'id' => 3,
				'year' => 2013,
				'artists' => [
					[
						'name' => 'The artist name',
						'image' => 'The image url',
						'uri' => null,
						'slug' => '1-the-artist-name',
						'id' => 1
					]
				],
				'albumArtistId' => 5,
				'tracks' => [
					[
						'title' => 'The title',
						'uri' => null,
						'slug' => '1-the-title',
						'id' => 1,
						'ordinal' => 4,
						'bitrate' => 123,
						'length' => 123,
						'artist' => ['id' => 3, 'uri' => null],
						'album' => ['id' => 4, 'uri' => null],
						'files' => [
							'audio/mp3' => null
						]
					]
				]
			],
			[
				'name' => 'The album name',
				'cover' => null,
				'uri' => null,
				'slug' => '4-the-album-name',
				'id' => 4,
				'year' => 2003,
				'artists' => [
					[
						'name' => 'The artist name3',
						'image' => 'The image url3',
						'uri' => null,
						'slug' => '3-the-artist-name3',
						'id' => 3
					],
					[
						'name' => 'The artist name5',
						'image' => 'The image url5',
						'uri' => null,
						'slug' => '5-the-artist-name5',
						'id' => 5
					]
				],
				'albumArtistId' => 1,
				'tracks' => [
					[
						'title' => 'The title',
						'uri' => null,
						'slug' => '1-the-title',
						'id' => 1,
						'ordinal' => 4,
						'bitrate' => 123,
						'length' => 123,
						'artist' => ['id' => 3, 'uri' => null],
						'album' => ['id' => 4, 'uri' => null],
						'files' => [
							'audio/mp3' => null
						]
					]
				]
			]
		];

		$response = $this->controller->albums(null /*artist*/, true /*fulltree*/);

		$this->assertEquals($result, $response->getData());
		$this->assertTrue($response instanceof JSONResponse);
	}

	public function testAlbum() {
		$album = new Album();
		$album->setId(3);
		$album->setName('The name');
		$album->setYears([2013]);
		$album->setCoverFileId(5);
		$album->setArtistIds([1]);
		$album->setAlbumArtistId(1);
		$artist = new Artist();
		$artist->setId(1);
		$artist->setName('The artist name');
		$artist->setImage('The image url');
		$track = new Track();
		$track->setId(1);
		$track->setTitle('The title');
		$track->setArtistId(3);
		$track->setAlbumId(4);
		$track->setNumber(4);
		$track->setLength(123);
		$track->setFileId(3);
		$track->setMimetype('audio/mp3');
		$track->setBitrate(123);

		$albumId = 3;

		$this->albumBusinessLayer->expects($this->once())
			->method('find')
			->with($this->equalTo($albumId), $this->equalTo($this->userId))
			->will($this->returnValue($album));
		$this->artistBusinessLayer->expects($this->once())
			->method('findById')
			->with($this->equalTo([1]), $this->equalTo($this->userId))
			->will($this->returnValue([$artist]));
		$this->trackBusinessLayer->expects($this->once())
			->method('findAllByAlbum')
			->with($this->equalTo($albumId))
			->will($this->returnValue([$track]));

		$result = [
			'name' => 'The name',
			'cover' => null,
			'uri' => null,
			'slug' => '3-the-name',
			'id' => 3,
			'year' => 2013,
			'artists' => [
				[
					'name' => 'The artist name',
					'image' => 'The image url',
					'uri' => null,
					'slug' => '1-the-artist-name',
					'id' => 1
				]
			],
			'albumArtistId' => 1,
			'tracks' => [
				[
					'title' => 'The title',
					'uri' => null,
					'slug' => '1-the-title',
					'id' => 1,
					'ordinal' => 4,
					'bitrate' => 123,
					'length' => 123,
					'artist' => ['id' => 3, 'uri' => null],
					'album' => ['id' => 4, 'uri' => null],
					'files' => [
						'audio/mp3' => null
					]
				]
			]
		];

		$response = $this->controller->album($albumId, true /*fulltree*/);

		$this->assertEquals($result, $response->getData());
		$this->assertTrue($response instanceof JSONResponse);
	}

	public function testAlbumFulltree() {
		$album = new Album();
		$album->setId(3);
		$album->setName('The name');
		$album->setYears([2013]);
		$album->setCoverFileId(5);
		$album->setArtistIds([1]);
		$album->setAlbumArtistId(2);

		$albumId = 3;

		$this->albumBusinessLayer->expects($this->once())
			->method('find')
			->with($this->equalTo($albumId), $this->equalTo($this->userId))
			->will($this->returnValue($album));

		$result = [
			'name' => 'The name',
			'cover' => null,
			'uri' => null,
			'slug' => '3-the-name',
			'id' => 3,
			'year' => 2013,
			'artists' => [
				['id' => 1, 'uri' => null]
			],
			'albumArtistId' => 2
		];

		$response = $this->controller->album($albumId, false /*fulltree*/);

		$this->assertEquals($result, $response->getData());
		$this->assertTrue($response instanceof JSONResponse);
	}

	public function testTracks() {
		$track1 = new Track();
		$track1->setId(1);
		$track1->setTitle('The title');
		$track1->setArtistId(3);
		$track1->setAlbumId(1);
		$track1->setNumber(4);
		$track1->setLength(123);
		$track1->setFileId(3);
		$track1->setMimetype('audio/mp3');
		$track1->setBitrate(123);
		$track2 = new Track();
		$track2->setId(2);
		$track2->setTitle('The second title');
		$track2->setArtistId(2);
		$track2->setAlbumId(3);
		$track2->setNumber(5);
		$track2->setLength(103);
		$track2->setFileId(3);
		$track2->setMimetype('audio/mp3');
		$track2->setBitrate(123);

		$this->trackBusinessLayer->expects($this->once())
			->method('findAll')
			->with($this->equalTo($this->userId))
			->will($this->returnValue([$track1, $track2]));

		$result = [
			[
				'title' => 'The title',
				'uri' => null,
				'slug' => '1-the-title',
				'id' => 1,
				'ordinal' => 4,
				'bitrate' => 123,
				'length' => 123,
				'artist' => ['id' => 3, 'uri' => null],
				'album' => ['id' => 1, 'uri' => null],
				'files' => [
					'audio/mp3' => null
				]
			],
			[
				'title' => 'The second title',
				'uri' => null,
				'slug' => '2-the-second-title',
				'id' => 2,
				'ordinal' => 5,
				'bitrate' => 123,
				'length' => 103,
				'artist' => ['id' => 2, 'uri' => null],
				'album' => ['id' => 3, 'uri' => null],
				'files' => [
					'audio/mp3' => null
				]
			]
		];

		$response = $this->controller->tracks(null /*artist*/, null /*album*/, false /*fulltree*/);

		$this->assertEquals($result, $response->getData());
		$this->assertTrue($response instanceof JSONResponse);
	}

	public function testTracksFulltree() {
		$track1 = new Track();
		$track1->setId(1);
		$track1->setTitle('The title');
		$track1->setArtistId(3);
		$track1->setAlbumId(1);
		$track1->setNumber(4);
		$track1->setLength(123);
		$track1->setFileId(3);
		$track1->setMimetype('audio/mp3');
		$track1->setBitrate(123);
		$album = new Album();
		$album->setId(3);
		$album->setName('The name');
		$album->setYears([2013]);
		$album->setCoverFileId(5);
		$album->setArtistIds([1]);
		$album->setAlbumArtistId(2);
		$artist = new Artist();
		$artist->setId(1);
		$artist->setName('The artist name');
		$artist->setImage('The image url');
		$artist2 = new Artist();
		$artist2->setId(2);
		$artist2->setName('The other artist name');
		$artist2->setImage('The image url 2');

		$this->trackBusinessLayer->expects($this->once())
			->method('findAll')
			->with($this->equalTo($this->userId))
			->will($this->returnValue([$track1]));
		$this->artistBusinessLayer->expects($this->once())
			->method('find')
			->with($this->equalTo(3), $this->equalTo($this->userId))
			->will($this->returnValue($artist));
		$this->albumBusinessLayer->expects($this->once())
			->method('find')
			->with($this->equalTo(1), $this->equalTo($this->userId))
			->will($this->returnValue($album));

		$result = [
			[
				'title' => 'The title',
				'uri' => null,
				'slug' => '1-the-title',
				'id' => 1,
				'ordinal' => 4,
				'bitrate' => 123,
				'length' => 123,
				'artist' => [
					'name' => 'The artist name',
					'image' => 'The image url',
					'uri' => null,
					'slug' => '1-the-artist-name',
					'id' => 1
				],
				'album' => [
					'name' => 'The name',
					'cover' => null,
					'uri' => null,
					'slug' => '3-the-name',
					'id' => 3,
					'year' => 2013,
					'artists' => [
						['id' => 1, 'uri' => null]
					],
					'albumArtistId' => 2
				],
				'files' => [
					'audio/mp3' => null
				]
			]
		];

		$response = $this->controller->tracks(null /*artist*/, null /*album*/, true /*fulltree*/);

		$this->assertEquals($result, $response->getData());
		$this->assertTrue($response instanceof JSONResponse);
	}

	public function testTrack() {
		$track = new Track();
		$track->setId(1);
		$track->setTitle('The title');
		$track->setArtistId(3);
		$track->setAlbumId(1);
		$track->setNumber(4);
		$track->setLength(123);
		$track->setFileId(3);
		$track->setMimetype('audio/mp3');
		$track->setBitrate(123);

		$trackId = 1;

		$this->trackBusinessLayer->expects($this->once())
			->method('find')
			->with($this->equalTo($trackId), $this->equalTo($this->userId))
			->will($this->returnValue($track));

		$result = [
			'title' => 'The title',
			'uri' => null,
			'slug' => '1-the-title',
			'id' => 1,
			'ordinal' => 4,
			'bitrate' => 123,
			'length' => 123,
			'artist' => ['id' => 3, 'uri' => null],
			'album' => ['id' => 1, 'uri' => null],
			'files' => [
				'audio/mp3' => null
			]
		];

		$response = $this->controller->track($trackId);

		$this->assertEquals($result, $response->getData());
		$this->assertTrue($response instanceof JSONResponse);
	}

	public function testTrackByFileId() {
		$trackId = 1;
		$fileId = 3;

		$track = new Track();
		$track->setId($trackId);
		$track->setTitle('The title');
		$track->setArtistId(3);
		$track->setAlbumId(1);
		$track->setNumber(4);
		$track->setDisk(1);
		$track->setLength(123);
		$track->setFileId($fileId);
		$track->setMimetype('audio/mp3');
		$track->setBitrate(123);

		$album = new Album();
		$album->setId(1);
		$album->setAlbumArtistId(1);

		$artist = new Artist();
		$artist->setId(3);
		$artist->setName('The track artist');

		$this->trackBusinessLayer->expects($this->once())
			->method('findByFileId')
			->with($this->equalTo($fileId), $this->equalTo($this->userId))
			->will($this->returnValue($track));

		$this->albumBusinessLayer->expects($this->once())
			->method('find')
			->with($this->equalTo($track->getAlbumId()), $this->equalTo($this->userId))
			->will($this->returnValue($album));

		$this->artistBusinessLayer->expects($this->once())
			->method('find')
			->with($this->equalTo($track->getArtistId()), $this->equalTo($this->userId))
			->will($this->returnValue($artist));

		$result = [
			'title' => 'The title',
			'artistName' => 'The track artist',
			'id' => 1,
			'number' => 4,
			'disk' => 1,
			'artistId' => 3,
			'files' => [
				'audio/mp3' => $fileId
			]
		];

		$response = $this->controller->trackByFileId($fileId);

		$this->assertEquals($result, $response->getData());
		$this->assertTrue($response instanceof JSONResponse);
	}

	public function testTrackFulltree() {
		$this->markTestSkipped();
	}

	public function testTracksByArtist() {
		$track1 = new Track();
		$track1->setId(1);
		$track1->setTitle('The title');
		$track1->setArtistId(3);
		$track1->setAlbumId(1);
		$track1->setNumber(4);
		$track1->setLength(123);
		$track1->setFileId(3);
		$track1->setMimetype('audio/mp3');
		$track1->setBitrate(123);
		$track2 = new Track();
		$track2->setId(2);
		$track2->setTitle('The second title');
		$track2->setArtistId(3);
		$track2->setAlbumId(3);
		$track2->setNumber(5);
		$track2->setLength(103);
		$track2->setFileId(3);
		$track2->setMimetype('audio/mp3');
		$track2->setBitrate(123);

		$artistId = 3;

		$this->trackBusinessLayer->expects($this->once())
			->method('findAllByArtist')
			->with($this->equalTo($artistId), $this->equalTo($this->userId))
			->will($this->returnValue([$track1, $track2]));

		$result = [
			[
				'title' => 'The title',
				'uri' => null,
				'slug' => '1-the-title',
				'id' => 1,
				'ordinal' => 4,
				'bitrate' => 123,
				'length' => 123,
				'artist' => ['id' => 3, 'uri' => null],
				'album' => ['id' => 1, 'uri' => null],
				'files' => [
					'audio/mp3' => null
				]
			],
			[
				'title' => 'The second title',
				'uri' => null,
				'slug' => '2-the-second-title',
				'id' => 2,
				'ordinal' => 5,
				'bitrate' => 123,
				'length' => 103,
				'artist' => ['id' => 3, 'uri' => null],
				'album' => ['id' => 3, 'uri' => null],
				'files' => [
					'audio/mp3' => null
				]
			]
		];

		$response = $this->controller->tracks($artistId, null /*album*/, false /*fulltree*/);

		$this->assertEquals($result, $response->getData());
		$this->assertTrue($response instanceof JSONResponse);
	}

	public function testTracksByAlbum() {
		$track1 = new Track();
		$track1->setId(1);
		$track1->setTitle('The title');
		$track1->setArtistId(3);
		$track1->setAlbumId(1);
		$track1->setNumber(4);
		$track1->setLength(123);
		$track1->setFileId(3);
		$track1->setMimetype('audio/mp3');
		$track1->setBitrate(123);
		$track2 = new Track();
		$track2->setId(2);
		$track2->setTitle('The second title');
		$track2->setArtistId(2);
		$track2->setAlbumId(1);
		$track2->setNumber(5);
		$track2->setLength(103);
		$track2->setFileId(3);
		$track2->setMimetype('audio/mp3');
		$track2->setBitrate(123);

		$albumId = 1;

		$this->trackBusinessLayer->expects($this->once())
			->method('findAllByAlbum')
			->with($this->equalTo($albumId), $this->equalTo($this->userId))
			->will($this->returnValue([$track1, $track2]));

		$result = [
			[
				'title' => 'The title',
				'uri' => null,
				'slug' => '1-the-title',
				'id' => 1,
				'ordinal' => 4,
				'bitrate' => 123,
				'length' => 123,
				'artist' => ['id' => 3, 'uri' => null],
				'album' => ['id' => 1, 'uri' => null],
				'files' => [
					'audio/mp3' => null
				]
			],
			[
				'title' => 'The second title',
				'uri' => null,
				'slug' => '2-the-second-title',
				'id' => 2,
				'ordinal' => 5,
				'bitrate' => 123,
				'length' => 103,
				'artist' => ['id' => 2, 'uri' => null],
				'album' => ['id' => 1, 'uri' => null],
				'files' => [
					'audio/mp3' => null
				]
			]
		];

		$response = $this->controller->tracks(null /*artist*/, $albumId, false /*fulltree*/);

		$this->assertEquals($result, $response->getData());
		$this->assertTrue($response instanceof JSONResponse);
	}
}
