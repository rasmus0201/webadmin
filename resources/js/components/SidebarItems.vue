<template>
    <v-list dense>
        <template v-for="item in items">
            <v-row v-if="item.heading" :key="item.heading" align="center">
                <v-col cols="12">
                    <v-subheader v-if="item.text">
                        {{ item.text }}
                    </v-subheader>
                </v-col>
            </v-row>
            <v-list-group v-else-if="item.children" :key="item.text" v-model="item.model" :prepend-icon="item.model ? item.icon : item['iconAlt']" append-icon="">
                <template v-slot:activator>
                    <v-list-item-content>
                        <v-list-item-title>
                            {{ item.text }}
                        </v-list-item-title>
                    </v-list-item-content>
                </template>
                <v-list-item v-for="(child, i) in item.children" :key="i" :to="child.route" link exact>
                    <v-list-item-action v-if="child.icon">
                        <v-icon>{{ child.icon }}</v-icon>
                    </v-list-item-action>
                    <v-list-item-content>
                        <v-list-item-title>
                            {{ child.text }}
                        </v-list-item-title>
                    </v-list-item-content>
                </v-list-item>
            </v-list-group>
            <v-list-item v-else :key="item.text" :to="item.route" link exact>
                <v-list-item-action>
                    <v-icon>{{ item.icon }}</v-icon>
                </v-list-item-action>
                <v-list-item-content>
                    <v-list-item-title>
                        {{ item.text }}
                    </v-list-item-title>
                </v-list-item-content>
            </v-list-item>
        </template>
    </v-list>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
    name: 'SidebarItems',
    data() {
        return {
            items: [
                {
                    icon: '$vuetify.icons.fasTachometerAlt',
                    text: 'Dashboard',
                    route: { name: 'dashboard' }
                },
                {
                    icon: '$vuetify.icons.fasNetworkWired',
                    text: 'Websites',
                    route: { name: 'websites.index' }
                },
                {
                    icon: '$vuetify.icons.fasDatabase',
                    text: 'Databases',
                    route:  { name: 'databases.index' }
                },
                {
                    icon: '$vuetify.icons.fasChevronUp',
                    iconAlt: '$vuetify.icons.fasChevronDown',
                    text: 'Backup',
                    children: [
                        {
                            text: 'File',
                            icon: '$vuetify.icons.fasFolderOpen',
                            route:  { name: 'backup.websites.index' }
                        },
                        {
                            text: 'Database',
                            icon: '$vuetify.icons.fasServer',
                            route:  { name: 'backup.databases.index' }
                        },
                    ],
                }
            ]
        };
    }
};
</script>
