/**
 * Loads the OpenPGP.js build that ships with the plugin.
 *
 * The tests deliberately use lib/js/openpgp.min.js instead of a copy from npm,
 * so they exercise the exact file that is served to visitors. That file is a
 * browser bundle which assigns a global, so it is evaluated in a VM context
 * holding the globals it expects to find in a browser.
 */

import { readFile } from 'node:fs/promises';
import { runInThisContext } from 'node:vm';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';

const here = dirname( fileURLToPath( import.meta.url ) );

export const pluginRoot = join( here, '..', '..', '..' );
export const bundlePath = join( pluginRoot, 'lib', 'js', 'openpgp.min.js' );

let cached;

export async function loadOpenPGP() {
	if ( cached ) {
		return cached;
	}

	const source = await readFile( bundlePath, 'utf8' );

	// The bundle is evaluated in the current realm: a separate VM context would
	// give it its own Uint8Array, which the library rejects when it meets typed
	// arrays created by the host (crypto.getRandomValues and friends).
	if ( typeof globalThis.self === 'undefined' ) {
		globalThis.self = globalThis;
	}

	runInThisContext( source, { filename: bundlePath } );

	if ( ! globalThis.openpgp ) {
		throw new Error( `The bundle at ${ bundlePath } did not define an openpgp global.` );
	}

	cached = globalThis.openpgp;

	return cached;
}

/**
 * Reads the version string the bundle reports, e.g. "6.3.0".
 */
export async function bundledVersion() {
	const source = await readFile( bundlePath, 'utf8' );
	const match = source.match( /OpenPGP\.js v([\d.]+)/ );

	return match ? match[ 1 ] : null;
}
