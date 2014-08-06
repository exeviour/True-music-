
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


angular.module('Music').controller('PlaylistController',
	['$rootScope', '$scope', '$routeParams', 'PlaylistFactory', 'playlistService', 'gettextCatalog', 'Restangular', '$timeout',
	function ($rootScope, $scope, $routeParams, PlaylistFactory, playlistService, gettextCatalog, Restangular , $timeout) {

		$scope.playlistSongs = [];
		$scope.playlists = [];

		$scope.newPlaylistForm = {
			name: null,
		};

		// Create playlist by providing the name
		$scope.createPlaylist = function(playlist) {
			var playlists = Restangular.all('playlists');
			playlists.post(playlist).then(function(){
				$scope.playlists = [];
				console.log("new plist:" +arguments);

				$scope.getPlaylists();
				$scope.newPlaylistForm = {
					name: null,
				};
			});

		};

		// Return all playlists
		$scope.getPlaylists = function() {
			Restangular.all('playlists').getList().then(function(getPlaylists){
				var plist;
				for(var i=0; i < getPlaylists.length; i++) {
					plist = getPlaylists[i];
					$scope.playlists.push(plist);
				}
				return $scope.playlists;

				}, function error(reason) {
					console.log("Cannot get playlists due to: " + reason);
			});
		};

		// Return playlist and its songs
		$scope.getPlaylist = function(id) {
			var playlist = Restangular.one('playlists', id).get().then(function(playlist){

				console.log("==========HERE GOES THE LIST "+id+"=============");
				console.log("name: "+playlist.name);
				$scope.currentPlaylistName = playlist.name;
				console.log("trackIds: "+playlist.trackIds);
				$scope.currentPlaylistSongs = playlist.trackIds;
				console.log("=======================");

			});
		};

		// Rename playlist
		$scope.updatePlaylist = function(id, newName) {
			Restangular.one('playlists', id).get().then(function(playlist){
				var playlists = Restangular.one('playlists', id);
				playlists.name = newName;
				playlists.put();
				$scope.playlists = [];
				$scope.getPlaylists();
				OC.Notification.show(t('music', '{oldName} has been renamed to {newPlaylistName}', {oldName: playlist.name, newPlaylistName: newName}));
				$timeout(OC.Notification.hide, 5000);
			});

		};

		// Remove playlist
		$scope.removePlaylist = function(id) {
			Restangular.one('playlists', id).get().then(function(playlist){
				var pl = Restangular.one('playlists', id);
				pl.remove().then(function(){
					OC.Notification.show(t('music', 'Playlist {playlistName} removed', {playlistName: playlist.name}));
					$timeout(OC.Notification.hide, 5000);
					$scope.playlists = [];
					$scope.getPlaylists();
				});
			});
		};

		$scope.currentPlaylist = $routeParams.playlistId;
		console.log("Current Playlist: "+ $scope.currentPlaylist);


		// Add tracks to the playlist
		$scope.addTracks = function(playlistId, songs) {

			var message = Restangular.one('playlists', playlistId).all("add");
			message.post({trackIds: songs}).then(function() {
				Restangular.one('playlists', playlistId).get().then(function(playlist){
					OC.Notification.show(t('music', 'Track {songs} was added to the playlist {playlistName}', {songs: songs, playlistName: playlist.name}));
					$timeout(OC.Notification.hide, 3000);
				});
			}, function error(reason) {
				console.log("error :(");
			});
		};

		// Remove chosen track from the list
		$scope.removeTrack = function(songs) {
			var message = Restangular.one('playlists', $scope.currentPlaylist).all("remove");
			message.post({trackIds: songs}).then(function() {
				Restangular.one('playlists', $scope.currentPlaylist).get().then(function(playlist){
					OC.Notification.show(t('music', 'Track {songs} was removed from the playlist {playlistName}', {songs: songs, playlistName: playlist.name}));
					$timeout(OC.Notification.hide, 3000);
					$scope.getPlaylist($scope.currentPlaylist);
				});
			}, function error(reason) {
				console.log("error :(");
			});
		};

		// Call playlistService to play all songs in the current playlist
		$scope.playAll = function() {
			playlistService.setPlaylist($scope.currentPlaylistSongs);
			playlistService.publish('play');
		};

		// Play only one song from the playlist
		$scope.playTrack = function(track) {
			var tracks = [];
			tracks[0] = track;
			playlistService.setPlaylist(tracks);
			playlistService.publish('play');
		};

		// Emitted by MainController after dropping a  song on a playlist
		$scope.$on('droppedSong', function(event, songId, playlistId) {
			$scope.addTracks(playlistId, songId);
		});

		$scope.getPlaylists();

}]);
