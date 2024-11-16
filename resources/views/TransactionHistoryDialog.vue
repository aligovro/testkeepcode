<template>
    <v-dialog :model-value="transactionsDialog" @update:model-value="updateVisibility" max-width="800px">
        <v-card>
            <v-card-title>
                История транзакций
                <v-spacer></v-spacer>
                <v-btn icon @click="closeModal">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>
            <v-divider></v-divider>
            <v-card-text>
                <v-list v-if="transactions.length">
                    <v-list-item v-for="transaction in transactions" :key="transaction.id">
                            <v-list-item-title>{{ transaction.product.name }}</v-list-item-title>
                            <v-list-item-subtitle>
                                {{ transaction.type === 'purchase' ? 'Покупка' : 'Аренда' }} —
                                {{ formatDate(transaction.created_at) }}
                                <span v-if="transaction.type === 'rent'">до {{ formatDate(transaction.expires_at) }}</span>
                            </v-list-item-subtitle>
                    </v-list-item>
                </v-list>
                <v-alert v-else type="info" text>
                    У вас нет транзакций.
                </v-alert>
            </v-card-text>
        </v-card>
    </v-dialog>
</template>

<script>
export default {
    props: {
        transactionsDialog: Boolean,
        transactions: {
            type: Array,
            default: () => [],
        },
    },
    methods: {
        updateVisibility(newValue) {
            this.$emit('update:transactionsDialog', newValue);
        },
        closeModal() {
            this.$emit('update:transactionsDialog', false);
        },
        formatDate(date) {
            return new Date(date).toLocaleString();
        },
    },
};
</script>
