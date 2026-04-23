import { defineConfig } from "astro/config";
import sitemap from "@astrojs/sitemap";
import tailwindcss from "@tailwindcss/vite";

const toolPages = new Set([
  "/gamertag-price-checker",
  "/gamercard-generator",
  "/xbox-gamertag-availability-checker",
  "/xbox-gamertag-search",
  "/gamertag-optimizer",
]);

const corePages = new Set([
  "/",
  "/tools",
  "/blog",
  "/glossary",
]);

const supportPages = new Set([
  "/about",
  "/contact",
]);

const legalPages = new Set([
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
        const path = new URL(item.url).pathname.replace(/\/$/, "") || "/";

        if (path === "/") {
          return { ...item, priority: 1.0, changefreq: "daily" };
        }

        if (toolPages.has(path)) {
          return { ...item, priority: 0.9, changefreq: "weekly" };
        }

        if (corePages.has(path)) {
          return { ...item, priority: 0.8, changefreq: "weekly" };
        }

        if (supportPages.has(path)) {
          return { ...item, priority: 0.6, changefreq: "monthly" };
        }

        if (legalPages.has(path)) {
          return { ...item, priority: 0.3, changefreq: "yearly" };
        }

        return { ...item, priority: 0.7, changefreq: "monthly" };
      },
    }),
  ],

  vite: {
    plugins: [tailwindcss()],
  },
});