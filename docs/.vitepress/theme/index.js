// .vitepress/theme/index.js
import DefaultTheme from 'vitepress/theme'
import './custom.css'

export default {
  extends: DefaultTheme,
  enhanceApp({ app, router, siteData }) {
    // Custom app enhancements can be added here
  }
}
