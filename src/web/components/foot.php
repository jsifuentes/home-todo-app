<div id="notifications" x-data="notifications()" class="w-full fixed bottom-0 right-0 md:mb-4 md:mr-4 md:w-auto">
    <template x-for="notification of notifications" :key="notification.id">
        <div class="p-2 flex w-full md:mb-4 md:max-w-xs md:rounded-lg md:p-4"
            :class="{
                'bg-green-100 text-green-800': notification.type === 'success',
                'bg-red-100 text-red-800': notification.type === 'error',
            }">
            <span class="flex-grow" x-text="notification.message"></span>
            <button @click="removeNotification(notification.id)" :class="{
                'text-green-800': notification.type === 'success',
                'text-red-800': notification.type === 'error',
            }">
                &times;
            </button>
        </div>
    </template>
</div>

</body>

<script>
    <?php if ($tvMode): ?>
        document.body.addEventListener('htmx:configRequest', function(evt) {
            evt.detail.parameters['tv'] = 1;
        });
    <?php endif; ?>
</script>

<script src="/assets/js/notifications.js"></script>

<?php foreach ($additionalScripts as $script): ?>
    <script src="<?= $script ?>"></script>
<?php endforeach; ?>

</html>