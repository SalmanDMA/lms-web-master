import { defineConfig } from "vite"
import laravel from "laravel-vite-plugin"
import path from "path"
import react from "@vitejs/plugin-react"

export default defineConfig({
  resolve: {
    alias: {
      "@": path.resolve(__dirname, "./resources"),
      "~bootstrap": path.resolve(__dirname, "node_modules/bootstrap"),
      "~bootstrap-icons": path.resolve(
        __dirname,
        "node_modules/bootstrap-icons",
      ),
      "~perfect-scrollbar": path.resolve(
        __dirname,
        "node_modules/perfect-scrollbar",
      ),
      "~@fontsource": path.resolve(__dirname, "node_modules/@fontsource"),
    },
  },
  plugins: [
    laravel({
      input: [
        "resources/sass/bootstrap.scss",
        "resources/sass/themes/dark/app-dark.scss",
        "resources/sass/app.scss",
        "resources/sass/pages/auth.scss",
        "resources/css/app.css",
        "resources/js/app.js",
        "resources/js/initTheme.js"
      ],
      refresh: true,
    }),
    react(),
  ],
})