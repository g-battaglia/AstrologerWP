import { defineConfig } from 'vite'
import path from 'path'

/**
 * Vite configuration for the WordPress frontend.
 *
 * The build step produces a JS + CSS bundle in dist/assets/
 * which is loaded by the PHP plugin via wp_enqueue_script/style.
 *
 * Note: we do not use @vitejs/plugin-react in this setup because its
 * React Refresh preamble expects full control over the HTML entry,
 * which WordPress manages independently. Vite/esbuild still handles
 * TSX/JSX just fine without it.
 */
export default defineConfig({
  plugins: [
    /*
    {
      name: 'strip-radix-use-client',
      enforce: 'pre',
      transform(code, id) {
        if (
          !id.includes('node_modules/@radix-ui/react-label') &&
          !id.includes('node_modules/@radix-ui/react-switch')
        ) {
          return null;
        }

        const cleaned = code.replace(/["']use client["'];?/g, '');
        return cleaned;
      },
    },
    */
  ],

  // Aliases for cleaner imports
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
      'react-dom/client': path.resolve(__dirname, 'src/wp-react-shim.js'),
      'react/jsx-runtime': path.resolve(__dirname, 'src/wp-react-shim.js'),
      'react/jsx-dev-runtime': path.resolve(__dirname, 'src/wp-react-shim.js'),
      'react': path.resolve(__dirname, 'src/wp-react-shim.js'),
      'react-dom': path.resolve(__dirname, 'src/wp-react-shim.js'),
    },
  },

  // Build configuration for WordPress
  build: {
    // Output directory
    outDir: 'dist',

    // Generate manifest for asset mapping
    manifest: true,

    // Fixed output file name (no hash for simplicity in WP)
    rollupOptions: {
      input: {
        main: path.resolve(__dirname, 'src/main.tsx'),
        blocks: path.resolve(__dirname, 'src/blocks/index.tsx'),
      },
      // Removed external/globals/iife config to use Alias Shim strategy instead
      output: {
        entryFileNames: 'assets/[name].js',
        chunkFileNames: 'assets/[name].js',
        assetFileNames: 'assets/[name][extname]',
      },
    },

    // Minification for production
    minify: 'terser',

    // Sourcemaps disabled in production (enabled only in dev)
    sourcemap: false,
  },

  // Development server
  server: {
    host: true, // Listen on all addresses (including [::1])
    port: 5173,
    strictPort: true,
    fs: {
      // Allow importing shared files from the plugin root (e.g. ../astrology-enums.json)
      allow: [path.resolve(__dirname, '..')],
    },
    cors: {
      origin: '*', // Allow any origin to prevent CORS issues
      methods: ['GET', 'HEAD', 'PUT', 'PATCH', 'POST', 'DELETE'],
      preflightContinue: false,
      optionsSuccessStatus: 204,
    },
  },
})
