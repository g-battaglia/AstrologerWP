import esbuild from "esbuild";

// Check fo flags
const watch = process.argv.includes("--watch");

// Funzione di build comune
const buildOptions = {
  bundle: true,
  minify: true,
  sourcemap: false,
  target: ["es2015"],
};

// Funzione asincrona per la build e la modalità watch
async function build() {
  try {
    const context = await esbuild.context({
      ...buildOptions,
      entryPoints: ["assets/src/js/*.js"],
      outdir: "assets/dist/js",
      sourcemap: true,
    });

    console.log("✨ Build succeeded.");
    if (watch) {
      context.watch();
      console.log("🔨 ESBuild is watching for changes!");
      return;
    }

    process.exit(0);
  } catch (error) {
    console.error("Build failed:", error);
    process.exit(1);
  }
}

// Esegui la funzione di build
build();
