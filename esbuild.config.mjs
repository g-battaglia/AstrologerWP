import esbuild from "esbuild";

const watch = process.argv.includes("--watch");

const buildOptions = {
    bundle: true,
    minify: true,
    target: ["es2015"],
    entryPoints: ["assets/src/js/*.js"],
    outdir: "assets/dist/js",
    sourcemap: true,
};

async function build() {
    try {
        if (watch) {
            const context = await esbuild.context(buildOptions);
            await context.watch();
            console.log("🔨 ESBuild is watching for changes!");
        } else {
            await esbuild.build(buildOptions);
            console.log("✨ Build succeeded.");
        }
    } catch (error) {
        console.error("Build failed:", error);
        process.exit(1);
    }
}

build();
