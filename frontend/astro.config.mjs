import { defineConfig } from "astro/config";
import sitemap from "@astrojs/sitemap";
import tailwindcss from "@tailwindcss/vite";

const toolPages = new Set([
  "/gamertag-price-checker",
  "/gamercard-generator",
  "/gamertag-availability-checker",
  "/gamer-profile-checker",
]);

const standardPages = new Set([
  "/",
  "/blog",
  "/tools",
  "/about",
  "/contact",
  "/privacy-policy",
  "/terms-and-conditions",
]);

export default defineConfig({
  output: "static",
  site: "https://xboxgamertaggenerator.com",

  prefetch: {
    prefetchAll: false,
    defaultStrategy: "hover",
  },

  integrations: [
    sitemap({
      filter: (page) =>
        !page.includes("/api/") &&
        !page.includes("/admin/"),

      serialize(item) {
        const path = new URL(item.url).pathname;

        if (path === "/") {
          return { ...item, priority: 1.0, changefreq: "daily" };
        }

        if (toolPages.has(path)) {
          return { ...item, priority: 0.9, changefreq: "weekly" };
        }

        if (!standardPages.has(path)) {
          return { ...item, priority: 0.8, changefreq: "monthly" };
        }

        return { ...item, priority: 0.6, changefreq: "monthly" };
      },
    }),
  ],

  vite: {
    plugins: [tailwindcss()],
  },
});