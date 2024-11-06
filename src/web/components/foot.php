</body>

<script>
	<?php if ($tvMode): ?>
		document.body.addEventListener('htmx:configRequest', function(evt) {
			evt.detail.parameters['tv'] = 1;
		});
	<?php endif; ?>
</script>

<?php foreach ($additionalScripts as $script): ?>
	<script src="<?= $script ?>"></script>
<?php endforeach; ?>

</html>