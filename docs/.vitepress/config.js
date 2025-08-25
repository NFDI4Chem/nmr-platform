import { defineConfig } from 'vitepress'

export default defineConfig({
  title: 'nmrXiv Platform',
  description: 'Documentation for the NMR Platform - A comprehensive platform for Nuclear Magnetic Resonance data management and analysis',
  
  // Theme configuration
  themeConfig: {
    // Logo
    logo: '/logo.svg',
    siteTitle: false,
    // Navigation (simplified for logo and search only)
    nav: [],

    // Sidebar with main navigation sections
    sidebar: [
      {
        text: 'Getting Started',
        collapsed: false,
        items: [
          { text: 'Introduction', link: '/getting-started/' },
          { text: 'Quick Start', link: '/getting-started/quick-start' }
        ]
      },
      {
        text: 'User Guide',
        collapsed: false,
        items: [
          { text: 'Overview', link: '/user-guide/' },
          { text: 'Dashboard', link: '/user-guide/dashboard' },
          { text: 'Data Management', link: '/user-guide/data-management' },
          { text: 'Analysis Tools', link: '/user-guide/analysis' },
          { text: 'Export & Import', link: '/user-guide/export-import' }
        ]
      },
      {
        text: 'API Reference',
        collapsed: false,
        items: [
          { text: 'Overview', link: '/api/' },
          { text: 'Authentication', link: '/api/authentication' },
          { text: 'Endpoints', link: '/api/endpoints' },
          { text: 'Data Models', link: '/api/models' },
          { text: 'Examples', link: '/api/examples' }
        ]
      },
      {
        text: 'Developer Guide',
        collapsed: false,
        items: [
          { text: 'Overview', link: '/developer/' },
          { text: 'Architecture', link: '/developer/architecture' },
          { text: 'Setup Development Environment', link: '/developer/setup' },
          { text: 'Database Schema', link: '/developer/database' },
          { text: 'Testing', link: '/developer/testing' },
          { text: 'Deployment', link: '/developer/deployment' }
        ]
      },
      {
        text: 'Contributing',
        collapsed: false,
        items: [
          { text: 'Contributing Guide', link: '/contributing' }
        ]
      }
    ],

    // Social links
    socialLinks: [
      { icon: 'github', link: 'https://github.com/NFDI4Chem/nmr-platform' }
    ],

    // Footer
    footer: {
      message: 'Released under the MIT License.',
      copyright: 'Copyright © 2024 NFDI4Chem'
    },

    // Search
    search: {
      provider: 'local'
    },

    // Edit link
    editLink: {
      pattern: 'https://github.com/NFDI4Chem/nmr-platform/edit/main/docs/:path',
      text: 'Edit this page on GitHub'
    },

    // Last updated
    lastUpdated: {
      text: 'Updated at',
      formatOptions: {
        dateStyle: 'full',
        timeStyle: 'medium'
      }
    }
  },

  // Head configuration
  head: [
    ['link', { rel: 'icon', href: '/favicon.ico' }],
    ['meta', { name: 'theme-color', content: '#3c82f6' }],
    ['meta', { property: 'og:type', content: 'website' }],
    ['meta', { property: 'og:locale', content: 'en' }],
    ['meta', { property: 'og:title', content: 'NMR Platform Documentation' }],
    ['meta', { property: 'og:site_name', content: 'NMR Platform' }],
    ['meta', { property: 'og:image', content: '/og-image.png' }],
    ['meta', { property: 'og:url', content: 'https://docs.nmrplatform.org/' }]
  ],

  // Clean URLs
  cleanUrls: true,

  // Base URL for deployment
  base: '/',

  // Markdown configuration
  markdown: {
    theme: {
      light: 'github-light',
      dark: 'github-dark'
    },
    lineNumbers: true
  },

  // Vite configuration
  vite: {
    // Custom vite config if needed
  }
})
