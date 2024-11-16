<template>
    <v-container>
        <header-bar
            :user="user"
            @logout="handleLogout"
            @openAuthDialog="authDialog = true"
        />
        <slot />
        <auth-dialog
            :authDialog="authDialog"
            @update:authDialog="authDialog = $event"
            @authenticated="onAuthenticated"
        />
        <v-snackbar v-model="snackbar.visible" :timeout="snackbar.timeout" :color="snackbar.color">
            {{ snackbar.message }}
        </v-snackbar>
    </v-container>
</template>

<script>
import HeaderBar from './Header.vue';
import AuthDialog from './AuthDialog.vue';

export default {
    components: {
        HeaderBar,
        AuthDialog,
    },
    props: {
        user: Object,
        snackbar: Object,
    },
    emits: ['refresh', 'showSnackbar'],
    data() {
        return {
            authDialog: false,
        };
    },
    methods: {
        onAuthenticated() {
            this.authDialog = false;
            this.$emit('refresh');
        },
        handleLogout() {
            this.$emit('refresh');
        },
    },
};
</script>
