<template>
    <v-row :justify="justify">
        <v-col :cols="cols">
            <v-card class="v-page">
                <slot></slot>
            </v-card>
        </v-col>
    </v-row>
</template>

<script>
export default {
    name: 'Page',
    props: {
        cols: {
            type: Number,
            default: 12
        },
        justify: {
            type: String,
            default: 'start'
        }
    }
};
</script>
