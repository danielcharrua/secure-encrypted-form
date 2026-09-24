/**
 * Encryption tests for the bundled OpenPGP.js build.
 *
 * These mirror what public/js/secure-encrypted-form-public.js does in the
 * browser: read the armored public key from the plugin settings, encrypt the
 * message field, and send the result as JSON. The private key is only used
 * here, to prove the message can be read back on the other side.
 */

import test from 'node:test';
import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';

import { loadOpenPGP, bundledVersion } from './helpers/load-openpgp.mjs';

const fixtures = join( dirname( fileURLToPath( import.meta.url ) ), '..', 'fixtures' );

const readFixture = ( name ) => readFile( join( fixtures, name ), 'utf8' );

const openpgp = await loadOpenPGP();

const PLAINTEXT = 'Contraseña del servidor: correcto-caballo-batería-grapa';

/**
 * Encrypts exactly the way the plugin does in the browser.
 */
async function encryptLikeThePlugin( armoredKey, text ) {
	const publicKey = await openpgp.readKey( { armoredKey } );

	return openpgp.encrypt( {
		message: await openpgp.createMessage( { text } ),
		encryptionKeys: publicKey,
	} );
}

test( 'the bundled library is the version the plugin declares', async () => {
	const version = await bundledVersion();
	const enqueued = await readFile(
		join( fixtures, '..', '..', 'public', 'class-secure-encrypted-form-public.php' ),
		'utf8'
	);

	assert.ok( version, 'the bundle should state its version' );
	assert.ok(
		enqueued.includes( `'openpgpjs', plugin_dir_url( __DIR__ ) . 'lib/js/openpgp.min.js', array(), '${ version }'` ),
		`wp_enqueue_script should declare OpenPGP.js ${ version }`
	);
} );

test( 'a message encrypted with the public key comes back unchanged', async () => {
	const encrypted = await encryptLikeThePlugin( await readFixture( 'test-public-key.asc' ), PLAINTEXT );

	assert.match( encrypted, /^-----BEGIN PGP MESSAGE-----/ );

	const privateKey = await openpgp.readPrivateKey( {
		armoredKey: await readFixture( 'test-private-key.asc' ),
	} );

	const { data } = await openpgp.decrypt( {
		message: await openpgp.readMessage( { armoredMessage: encrypted } ),
		decryptionKeys: privateKey,
	} );

	assert.equal( data, PLAINTEXT );
} );

test( 'the ciphertext does not leak the message', async () => {
	const encrypted = await encryptLikeThePlugin( await readFixture( 'test-public-key.asc' ), PLAINTEXT );

	assert.ok( ! encrypted.includes( PLAINTEXT ), 'the armored message must not contain the plaintext' );
	assert.ok( ! encrypted.includes( 'contraseña' ), 'the armored message must not contain message words' );

	// This is what actually travels in the AJAX request.
	const payload = JSON.stringify( encrypted );

	assert.ok( ! payload.includes( PLAINTEXT ), 'the request payload must not contain the plaintext' );
} );

test( 'the same message encrypts differently every time', async () => {
	const key = await readFixture( 'test-public-key.asc' );
	const first = await encryptLikeThePlugin( key, PLAINTEXT );
	const second = await encryptLikeThePlugin( key, PLAINTEXT );

	assert.notEqual( first, second, 'session keys should make each message unique' );
} );

test( 'a key that is not a key is rejected when read', async () => {
	await assert.rejects(
		() => openpgp.readKey( { armoredKey: 'this is not a key' } ),
		'readKey should reject so the plugin can show its feedback message'
	);
} );

test( 'a truncated key is rejected when read', async () => {
	const armored = await readFixture( 'test-public-key.asc' );
	const truncated = armored.split( '\n' ).slice( 0, 4 ).join( '\n' );

	await assert.rejects(
		() => openpgp.readKey( { armoredKey: truncated } ),
		'a corrupted key should not encrypt silently'
	);
} );

test( 'an expired key fails at encryption, not when read', async () => {
	const armoredKey = await readFixture( 'expired-public-key.asc' );

	// The plugin only wraps readKey() in a try/catch, so this is the step that
	// decides whether the visitor gets feedback or a form stuck on the spinner.
	const publicKey = await openpgp.readKey( { armoredKey } );

	assert.ok( publicKey, 'an expired key still parses correctly' );

	const message = await openpgp.createMessage( { text: PLAINTEXT } );

	await assert.rejects(
		() => openpgp.encrypt( { message, encryptionKeys: publicKey } ),
		/expired/i,
		'encrypting with an expired key should reject'
	);
} );
