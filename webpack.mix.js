const mix = require('laravel-mix');
const VuetifyLoaderPlugin = require('vuetify-loader/lib/plugin');

mix.js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css');

mix.options({
    processCssUrls: false
});

mix.webpackConfig(webpack => {
    return {
        plugins: [
            new VuetifyLoaderPlugin()
        ]
    };
});
