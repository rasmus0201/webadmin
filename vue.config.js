module.exports = {
  transpileDependencies: [
    "vuetify", "vuex-persist"
  ],
  devServer: {
    disableHostCheck: true,
    proxy: process.env.VUE_APP_API_URL,
  },
}
