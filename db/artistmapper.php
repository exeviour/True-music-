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

use \OCA\Music\AppFramework\Db\Mapper;
use \OCA\Music\AppFramework\Core\API;

class ArtistMapper extends Mapper {

	public function __construct(API $api){
		parent::__construct($api, 'music_artists');
	}

	private function makeSelectQuery($condition=null){
		$collate = $CONFIG['dbtype'] == 'sqlite' ? 'COLLATE NOCASE' : '';
		return 'SELECT `artist`.`name`, `artist`.`image`, `artist`.`id` '.
			'FROM `*PREFIX*music_artists` `artist` '.
			'WHERE `artist`.`user_id` = ? ' . $condition .
			' ORDER BY `artist`.`name` ' . $collate . ' ASC';
	}

	public function findAll($userId){
		$sql = $this->makeSelectQuery();
		$params = array($userId);
		return $this->findEntities($sql, $params);
	}

	public function findMultipleById($artistIds, $userId){
		$questionMarks = array();
		for($i = 0; $i < count($artistIds); $i++){
			$questionMarks[] = '?';
		}
		$sql = $this->makeSelectQuery('AND `artist`.`id` IN (' .
			implode(',', $questionMarks) .')');
		$params = $artistIds;
		array_unshift($params, $userId);
		return $this->findEntities($sql, $params);
	}

	public function find($artistId, $userId){
		$sql = $this->makeSelectQuery('AND `artist`.`id` = ?');
		$params = array($userId, $artistId);
		return $this->findEntity($sql, $params);
	}

	public function findByName($artistName, $userId){
		if ($artistName === null) {
			$sql = $this->makeSelectQuery('AND `artist`.`name` IS NULL');
			$params = array($userId);
		} else {
			$sql = $this->makeSelectQuery('AND `artist`.`name` = ?');
			$params = array($userId, $artistName);
		}
		return $this->findEntity($sql, $params);
	}

	public function deleteById($artistIds){
		if(count($artistIds) === 0)
			return;
		$questionMarks = array();
		for($i = 0; $i < count($artistIds); $i++){
			$questionMarks[] = '?';
		}
		$sql = 'DELETE FROM `*PREFIX*music_artists` WHERE `id` IN ('. implode(',', $questionMarks) . ')';
		$this->execute($sql, $artistIds);
	}
}
