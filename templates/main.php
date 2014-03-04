<?php
\OCP\Util::addScript('music', 'vendor/angular/angular.min');
\OCP\Util::addScript('music', 'vendor/angular-route/angular-route.min');
\OCP\Util::addScript('music', 'vendor/angular-animate/angular-animate.min');
\OCP\Util::addScript('music', 'vendor/angular-touch/angular-touch.min');
\OCP\Util::addScript('music', 'vendor/underscore/underscore.min');
\OCP\Util::addScript('music', 'vendor/soundmanager/soundmanager2');
\OCP\Util::addScript('music', 'vendor/restangular/restangular.min');
\OCP\Util::addScript('music', 'vendor/angular-gettext/angular-gettext.min');
\OCP\Util::addScript('music', 'public/app');

\OCP\Util::addStyle('music', 'style-playerbar');
// \OCP\Util::addStyle('music', 'style-sidebar');
// \OCP\Util::addStyle('music', 'style');
\OCP\Util::addStyle('music', 'app');
?>

<div id="app" ng-app="Music" ng-cloak ng-init="started = false; lang = '<?php p($_['lang']) ?>'">

	<div ng-controller="MainController">

		<script type="text/ng-template" id="list.html">
			<?php print_unescaped($this->inc('list')) ?>
		</script>

		<script type="text/ng-template" id="artist-detail.html">
			<?php print_unescaped($this->inc('artist-detail')) ?>
		</script>

		<div ng-controller="PlayerController">
		</div>
		
		<div id="app-content" class='{{animationType}}' ng-view ng-class="{started: started}"></div>

		<!-- <div ng-show="artists" class="alphabet-navigation" ng-class="{started: started}" resize>
			<a scroll-to="{{ letter }}" ng-repeat="letter in letters" ng-class="{available: letterAvailable[letter]}">{{ letter }}</a>
		</div> -->

	</div>

</div>
