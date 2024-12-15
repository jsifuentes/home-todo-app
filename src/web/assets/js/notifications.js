document.addEventListener('alpine:init', () => {
    Alpine.data('notifications', () => ({
        notifications: [],

        init() {
            document.body.addEventListener('addNotification', (evt) => {
                this.notifications.push({
                    id: Math.floor(Date.now() * Math.random()).toString(),
                    ...evt.detail
                });

                setTimeout(() => {
                    this.removeNotification(this.notifications[0].id);
                }, 5000);
            });

            document.body.addEventListener('removeNotification', (evt) => {
                this.removeNotification(evt.detail);
            });
        },

        removeNotification(id) {
            this.notifications = this.notifications.filter((notification) => {
                return notification.id !== id;
            });
        }
    }));
    

});
