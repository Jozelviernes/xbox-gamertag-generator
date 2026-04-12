import { defineConfig } from "astro/config";
import sitemap from "@astrojs/sitemap";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
  output: "static",
  site: "https://xboxgamertaggenerator.com",
  integrations: [
    sitemap({
      filter: (page) =>
        !page.includes("/api/") &&
        !page.includes("/admin/"),

      serialize(item) {
        if (item.url === "https://xboxgamertaggenerator.com/") {
          return { ...item, priority: 1.0, changefreq: "daily" };
        }
        if (item.url.includes("/tools/")) {
          return { ...item, priority: 0.9, changefreq: "weekly" };
        }
        if (item.url.includes("/blog/")) {
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