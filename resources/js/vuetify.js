import Vue from 'vue';
import Vuetify from 'vuetify/lib';
import { icons, iconPrefixes } from './lib/icons';

Vue.use(Vuetify);

const toTitleCase = (str) =>  {
    return str.replace(/-/g, ' ').replace(
        /\w\S*/g,
        function(txt) {
            return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
        }
    ).replace(/\s/g, '');
}

const vuetifyFontAwesomeIconsGrouped = Object.assign({}, ...Object.keys(icons).map(type => {
    const typeIcons = {};
    const prefix = iconPrefixes[type];

    for (const name of icons[type]) {
        typeIcons[prefix + toTitleCase(name)] = {
            component: 'vc-icon',
            props: {
                icon: [prefix, name],
            }
        }
    };

    return typeIcons;
}));

export default new Vuetify({
    options: {
        customProperties: true,
    },
    icons: {
        iconfont: 'faSvg',
        values: vuetifyFontAwesomeIconsGrouped
    },
});
