<?php
require_once(APPPATH . "angular-app/$appName/ng-app.html");
?>
	
<script type="text/javascript">
window.session = <?php echo $jsonSession; ?>;
</script>
	
<?php if (SF_USE_MINIFIED_JS): ?>
<!-- TODO: Switch to using Google's CDN for Angular -->
<!--	<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.2.0-rc.2/angular.min.js"></script> -->
<!--	<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.2.0-rc.2/angular-route.min.js"></script> -->
<!--	<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.2.0-rc.2/angular-sanitize.min.js"></script> -->
<!-- TODO: Switch to Angular 1.2.0-rc.3 instead of 1.2.0-rc.2 to pull in some important bugfixes -->
	<script src="/js/lib/angular_stable_1.2.0-rc.2/angular.min.js"></script>
	<script src="/js/lib/angular_stable_1.2.0-rc.2/angular-route.min.js"></script>
	<script src="/js/lib/angular_stable_1.2.0-rc.2/angular-sanitize.min.js"></script>
	<!-- ng-ui-*.js included in combined scriptureforge-min.js script -->
<?php else: ?>
	<script src="/js/lib/angular_stable_1.2.0-rc.2/angular.js"></script>
	<script src="/js/lib/angular_stable_1.2.0-rc.2/angular-route.js"></script>
	<script src="/js/lib/angular_stable_1.2.0-rc.2/angular-sanitize.js"></script>
	<script src="/js/lib/ng-ui-bootstrap-tpls-0.4.0.js"></script>
	<script src="/js/lib/ng-ui-utils-validate.js"></script>
<?php endif; ?>
	<script src="/js/lib/rangy-1.3alpha.772/rangy-core.js"></script>
	<script src="/js/lib/rangy-1.3alpha.772/rangy-cssclassapplier.js"></script>
	<script src="/js/lib/jquery-te-1.4.0.min.js"></script>
	<script src="/js/lib/moment-2.1.0-min.js"></script>

<?php if (SF_USE_MINIFIED_JS): ?>
	<script src="/js/lib/scriptureforge-min.js"></script>
<?php else: ?>
	<?php foreach($jsCommonFiles as $filename): ?>
	<script src="/<?php echo $filename; ?>"></script>
	<?php endforeach; ?>

	<?php foreach($jsProjectFiles as $filename): ?>
	<script src="/<?php echo $filename; ?>"></script>
	<?php endforeach; ?>
<?php endif; ?>

