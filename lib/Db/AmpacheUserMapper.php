<?php declare(strict_types=1);

/**
 * ownCloud - Music app
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Pauli Järvinen <pauli.jarvinen@gmail.com>
 * @copyright Morris Jobke 2013, 2014
 * @copyright Pauli Järvinen 2018 - 2024
 */

namespace OCA\Music\Db;

use OCP\IDBConnection;

/**
 * Note: Despite the name, this mapper and the related database table are
 *       used both for Subsonic and Ampache users. Also, this isn't really
 *       a mapper either, since this does not extend OCP\AppFramework\Db\Mapper.
 */
class AmpacheUserMapper {
	private $db;

	public function __construct(IDBConnection $db) {
		$this->db = $db;
	}

	/**
	 * @return string[] Array keys are row IDs and values are hashes
	 */
	public function getPasswordHashes(string $userId) : array {
		$sql = 'SELECT `id`, `hash` FROM `*PREFIX*music_ampache_users` WHERE `user_id` = ?';
		$params = [$userId];
		$result = $this->db->executeQuery($sql, $params);
		$rows = $result->fetchAll();

		$hashes = [];
		foreach ($rows as $value) {
			$hashes[$value['id']] = $value['hash'];
		}

		return $hashes;
	}

	public function getPasswordHash(int $id) : ?string {
		$sql = 'SELECT `hash` FROM `*PREFIX*music_ampache_users` WHERE `id` = ?';
		$params = [$id];
		$result = $this->db->executeQuery($sql, $params);
		$row = $result->fetch();

		if ($row === false) {
			return null;
		}

		return $row['hash'];
	}

	/**
	 * @param string $hash Password hash
	 * @return ?string User ID matching the $hash or null if not found
	 */
	public function getUserByPasswordHash(string $hash) : ?string {
		$sql = 'SELECT `user_id` FROM `*PREFIX*music_ampache_users` WHERE `hash` = ?';
		$params = [$hash];
		$result = $this->db->executeQuery($sql, $params);
		$row = $result->fetch();

		if ($row === false) {
			return null;
		}

		return $row['user_id'];
	}

	/**
	 * @param string $user Username, case-insensitive
	 * @return ?string Case-sensitively correct username, if the user has any API key(s)
	 */
	public function getProperUserId(string $user) : ?string {
		$sql = 'SELECT `user_id` FROM `*PREFIX*music_ampache_users` WHERE LOWER(`user_id`) = LOWER(?)';
		$params = [$user];
		$result = $this->db->executeQuery($sql, $params);
		$row = $result->fetch();

		if ($row === false) {
			return null;
		}

		return $row['user_id'];
	}

	public function getUserId(int $id) : ?string {
		$sql = 'SELECT `user_id` FROM `*PREFIX*music_ampache_users` WHERE `id` = ?';
		$params = [$id];
		$result = $this->db->executeQuery($sql, $params);
		$row = $result->fetch();

		if ($row === false) {
			return null;
		}

		return $row['user_id'];
	}

	/**
	 * @return ?int ID of the added key on null on failure (which is highly unexpected)
	 */
	public function addUserKey(string $userId, string $hash, ?string $description) : ?int {
		$sql = 'INSERT INTO `*PREFIX*music_ampache_users`
				(`user_id`, `hash`, `description`) VALUES (?, ?, ?)';
		$params = [$userId, $hash, $description];
		$this->db->executeUpdate($sql, $params);

		$sql = 'SELECT `id` FROM `*PREFIX*music_ampache_users`
				WHERE `user_id` = ? AND `hash` = ?';
		$params = [$userId, $hash];
		$result = $this->db->executeQuery($sql, $params);
		$row = $result->fetch();

		if ($row === false) {
			return null;
		}

		return (int)$row['id'];
	}

	public function removeUserKey(string $userId, int $id) : void {
		$sql = 'DELETE FROM `*PREFIX*music_ampache_users`
				WHERE `user_id` = ? AND `id` = ?';
		$params = [$userId, $id];
		$this->db->executeUpdate($sql, $params);
	}

	public function getAll(string $userId) : array {
		$sql = 'SELECT `id`, `hash`, `description` FROM `*PREFIX*music_ampache_users`
				WHERE `user_id` = ?';
		$params = [$userId];
		$result = $this->db->executeQuery($sql, $params);
		$rows = $result->fetchAll();

		return $rows;
	}
}
