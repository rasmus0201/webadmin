import Vue from "vue";

const NOTIFICATION_DURATION = 5000;

const NotificationService = {
    success(message) {
        NotificationService.notify(message, {
            type: "success"
        });
    },

    info(message) {
        NotificationService.notify(message, {
            type: "info"
        });
    },

    error(message) {
        NotificationService.notify(message, {
            type: "error"
        });
    },

    notify(message, options) {
        const defaultOptions = {
            type: "success",
            duration: NOTIFICATION_DURATION
        };

        Vue.toasted.show(message, Object.assign({}, defaultOptions, options));
    }
};

export default NotificationService;
