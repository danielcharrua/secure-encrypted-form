/**
 * Regenerates the throwaway OpenPGP keys used by the test suite.
 *
 * Run with: node tests/fixtures/generate-keys.mjs
 *
 * The keys it writes are committed to the repository on purpose. They protect
 * nothing: they exist only so the tests can encrypt and decrypt without any
 * manual setup. Never use them for anything else.
 */

import { writeFile } from 'node:fs/promises';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';

import { loadOpenPGP } from '../js/helpers/load-openpgp.mjs';

const here = dirname( fileURLToPath( import.meta.url ) );

const openpgp = await loadOpenPGP();

// A usable key pair.
const valid = await openpgp.generateKey( {
	type: 'ecc',
	curve: 'curve25519',
	userIDs: [ { name: 'Secure Encrypted Form Test', email: 'test@example.invalid' } ],
	format: 'armored',
} );

await writeFile( join( here, 'test-public-key.asc' ), valid.publicKey );
await writeFile( join( here, 'test-private-key.asc' ), valid.privateKey );

// A key that expired two years ago, to check the plugin reports expired keys
// instead of failing silently.
const twoYearsAgo = new Date( Date.now() - 2 * 365 * 24 * 60 * 60 * 1000 );

const expired = await openpgp.generateKey( {
	type: 'ecc',
	curve: 'curve25519',
	userIDs: [ { name: 'Secure Encrypted Form Expired', email: 'expired@example.invalid' } ],
	date: twoYearsAgo,
	keyExpirationTime: 60 * 60,
	format: 'armored',
} );

await writeFile( join( here, 'expired-public-key.asc' ), expired.publicKey );

console.log( 'Test keys written to', here );
