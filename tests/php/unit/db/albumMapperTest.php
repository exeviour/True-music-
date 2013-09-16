<?php

/**
 * ownCloud - Music app
 *
 * @author Morris Jobke
 * @copyright 2013 Morris Jobke <morris.jobke@gmail.com>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Music\Db;

class AlbumMapperTest extends \OCA\AppFramework\Utility\MapperTestUtility {

	private $mapper;
	private $albums;

	private $userId = 'john';
	private $id = 5;
	private $rows;

	public function setUp()
	{
		$this->beforeEach();

		$this->mapper = new AlbumMapper($this->api);

		// create mock items
		$album1 = new Album();
		$album1->setName('Test name');
		$album1->setYear(2013);
		$album1->setCover('http://example.org');
		$album1->resetUpdatedFields();
		$album2 = new Album();
		$album2->setName('Test name2');
		$album2->setYear(2012);
		$album2->setCover('http://example.org/1');
		$album2->resetUpdatedFields();

		$this->albums = array(
			$album1,
			$album2
		);

		$this->rows = array(
			array('id' => $this->albums[0]->getId(), 'name' => 'Test name', 'year' => 2013, 'cover' => 'http://example.org'),
			array('id' => $this->albums[1]->getId(), 'name' => 'Test name2', 'year' => 2012, 'cover' => 'http://example.org/1'),
		);

	}


	private function makeSelectQuery($condition=null){
		return 'SELECT `album`.`name`, `album`.`year`, `album`.`id`, '.
			'`album`.`cover` '.
			'FROM `*PREFIX*music_albums` `album` '.
			'WHERE `album`.`user_id` = ? ' . $condition;
	}

	public function testFind(){
		$sql = $this->makeSelectQuery('AND `album`.`id` = ?');
		$this->setMapperResult($sql, array($this->userId, $this->id), array($this->rows[0]));
		$result = $this->mapper->find($this->id, $this->userId);
		$this->assertEquals($this->albums[0], $result);
	}

	public function testFindAll(){
		$sql = $this->makeSelectQuery();
		$this->setMapperResult($sql, array($this->userId), $this->rows);
		$result = $this->mapper->findAll($this->userId);
		$this->assertEquals($this->albums, $result);
	}

	public function testGetAlbumArtistsByAlbumId(){
		$sql = 'SELECT DISTINCT * FROM `*PREFIX*music_album_artists` `artists`'.
			' WHERE `artists`.`album_id` IN (?,?,?)';
		$albumIds = array(1,2,3);
		$rows = array(
			array('album_id' => 1, 'artist_id' => 2),
			array('album_id' => 1, 'artist_id' => 5),
			array('album_id' => 2, 'artist_id' => 1),
			array('album_id' => 2, 'artist_id' => 3),
			array('album_id' => 2, 'artist_id' => 5),
			array('album_id' => 3, 'artist_id' => 4)
		);
		$albumArtists = array(
			1 => array(2,5),
			2 => array(1,3,5),
			3 => array(4)
		);
		$this->setMapperResult($sql, $albumIds, $rows);
		$result = $this->mapper->getAlbumArtistsByAlbumId($albumIds);
		$this->assertEquals($albumArtists, $result);
	}

	public function testFindAllByArtist(){
		$sql = 'SELECT `album`.`name`, `album`.`year`, `album`.`id`, '.
			'`album`.`cover` '.
			'FROM `*PREFIX*music_albums` `album` '.
			'JOIN `*PREFIX*music_album_artists` `artists` '.
			'ON `album`.`id` = `artists`.`album_id` '.
			'WHERE `album`.`user_id` = ? AND `artists`.`artist_id` = ? ';
		$artistId = 3;
		$this->setMapperResult($sql, array($this->userId, $artistId), $this->rows);
		$result = $this->mapper->findAllByArtist($artistId, $this->userId);
		$this->assertEquals($this->albums, $result);
	}

	public function testFindByNameAndYear(){
		$sql = 'SELECT `album`.`name`, `album`.`year`, `album`.`id`, '.
			'`album`.`cover` '.
			'FROM `*PREFIX*music_albums` `album` '.
			'WHERE `album`.`user_id` = ? AND `album`.`name` = ? AND `album`.`year` = ?';
		$albumName = 'test';
		$albumYear = 2005;
		$this->setMapperResult($sql, array($this->userId, $albumName, $albumYear), array($this->rows[0]));
		$result = $this->mapper->findByNameAndYear($albumName, $albumYear, $this->userId);
		$this->assertEquals($this->albums[0], $result);
	}

	public function testFindByNameAndYearYearIsNull(){
		$sql = 'SELECT `album`.`name`, `album`.`year`, `album`.`id`, '.
			'`album`.`cover` '.
			'FROM `*PREFIX*music_albums` `album` '.
			'WHERE `album`.`user_id` = ? AND `album`.`name` = ? AND `album`.`year` IS NULL';
		$albumName = 'test';
		$albumYear = null;
		$this->setMapperResult($sql, array($this->userId, $albumName), array($this->rows[0]));
		$result = $this->mapper->findByNameAndYear($albumName, $albumYear, $this->userId);
		$this->assertEquals($this->albums[0], $result);
	}

	public function testAddAlbumArtistRelationIfNotExistNoAdd(){
		$sql = 'SELECT 1 FROM `*PREFIX*music_album_artists` `relation` '.
			'WHERE `relation`.`album_id` = ? AND `relation`.`artist_id` = ?';
		$albumId = 1;
		$artistId = 2;
		$this->setMapperResult($sql, array($albumId, $artistId), array(array('select' => '1')));
		$this->mapper->addAlbumArtistRelationIfNotExist($albumId, $artistId);
	}

	public function testAddAlbumArtistRelationIfNotExistAdd(){
		$albumId = 1;
		$artistId = 2;
		$sql = 'SELECT 1 FROM `*PREFIX*music_album_artists` `relation` '.
			'WHERE `relation`.`album_id` = ? AND `relation`.`artist_id` = ?';
		$arguments = array($albumId, $artistId);
		$sql2 = 'INSERT INTO `*PREFIX*music_album_artists` (`album_id`, `artist_id`) '.
			'VALUES (?, ?)';

		$pdoResult = $this->getMock('Result',
			array('fetchRow'));
		$pdoResult->expects($this->any())
			->method('fetchRow');

		$query = $this->getMock('Query',
			array('execute'));
		$query->expects($this->at(0))
			->method('execute')
			->with($this->equalTo($arguments))
			->will($this->returnValue($pdoResult));
		$this->api->expects($this->at(0))
			->method('prepareQuery')
			->with($this->equalTo($sql))
			->will(($this->returnValue($query)));

		$query->expects($this->at(1))
			->method('execute')
			->with($this->equalTo($arguments))
			->will($this->returnValue($pdoResult));
		$this->api->expects($this->at(1))
			->method('prepareQuery')
			->with($this->equalTo($sql2))
			->will(($this->returnValue($query)));

		$this->mapper->addAlbumArtistRelationIfNotExist($albumId, $artistId);
	}

	public function testDeleteByIdNone(){
		$albumIds = array();

		$this->api->expects($this->never())
			->method('prepareQuery');

		$this->mapper->deleteById($albumIds);
	}

	public function testDeleteById(){
		$albumIds = array(1, 2);

		$sql = 'DELETE FROM `*PREFIX*music_album_artists` WHERE `album_id` IN (?,?)';
		$arguments = $albumIds;
		$sql2 = 'DELETE FROM `*PREFIX*music_albums` WHERE `id` IN (?,?)';

		$pdoResult = $this->getMock('Result',
			array('fetchRow'));
		$pdoResult->expects($this->any())
			->method('fetchRow');

		$query = $this->getMock('Query',
			array('execute'));
		$query->expects($this->at(0))
			->method('execute')
			->with($this->equalTo($arguments))
			->will($this->returnValue($pdoResult));
		$this->api->expects($this->at(0))
			->method('prepareQuery')
			->with($this->equalTo($sql))
			->will(($this->returnValue($query)));

		$query->expects($this->at(1))
			->method('execute')
			->with($this->equalTo($arguments))
			->will($this->returnValue($pdoResult));
		$this->api->expects($this->at(1))
			->method('prepareQuery')
			->with($this->equalTo($sql2))
			->will(($this->returnValue($query)));

		$this->mapper->deleteById($albumIds);
	}
}