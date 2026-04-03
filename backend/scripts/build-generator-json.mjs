import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const sourcePath = path.resolve(__dirname, "../../frontend/src/lib/gamertag-data.ts");
const outputDir = path.resolve(__dirname, "../database/data");
const outputPath = path.join(outputDir, "generator-data.json");

const source = fs.readFileSync(sourcePath, "utf8");

const runnable =
  source.replace(/export const /g, "const ") +
  `
return {
  languageWordSets,
  languageGenderWords,
  languageSuffixes,
  themes,
  languages,
  numbers
};
`;

const data = new Function(runnable)();

fs.mkdirSync(outputDir, { recursive: true });
fs.writeFileSync(outputPath, JSON.stringify(data, null, 2), "utf8");

console.log("generator-data.json created at:");
console.log(outputPath);