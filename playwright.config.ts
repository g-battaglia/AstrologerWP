import { defineConfig } from '@playwright/test';

export default defineConfig( {
	testDir: './tests/e2e',
	timeout: 30_000,
	expect: {
		timeout: 10_000,
	},
	retries: 2,
	workers: 1,
	reporter: 'list',
	use: {
		baseURL: 'http://localhost:8888',
		screenshot: 'only-on-failure',
		trace: 'retain-on-failure',
	},
	projects: [
		{
			name: 'default',
			use: {
				browserName: 'chromium',
			},
		},
	],
} );
