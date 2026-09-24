# Secure Encrypted Form

A WordPress plugin that adds a contact form whose message is encrypted in the
visitor's browser with your OpenPGP public key, so the plaintext never reaches
the web server. The encrypted message arrives by email as an ASCII armored
attachment that only your private key can open.

Published at [wordpress.org/plugins/secure-encrypted-form](https://wordpress.org/plugins/secure-encrypted-form/).
User facing documentation (installation, usage, FAQ, changelog) lives in
`readme.txt`, which is what WordPress.org renders on the plugin page.

## Requirements

- PHP 7.4 or newer
- WordPress 5.3 or newer
- [Composer](https://getcomposer.org/) for the PHP dependencies
- [Node.js](https://nodejs.org/) 20 or newer to run the encryption tests

## Getting started

```bash
composer install   # PHP dependencies, installed into lib/vendor
```

There are no npm dependencies. `package.json` only provides scripts.

## Project layout

| Path | What it holds |
| --- | --- |
| `secure-encrypted-form.php` | Plugin bootstrap: headers, version constant, activation and deactivation hooks |
| `includes/` | Core classes: the loader, i18n, activator, deactivator and the logger |
| `admin/` | Settings page, debug log viewer and the admin test form |
| `public/` | Shortcode, front end assets and the AJAX handler that sends the email |
| `lib/js/openpgp.min.js` | The OpenPGP.js build served to visitors |
| `lib/vendor/` | Composer dependencies (not in version control) |
| `languages/` | Translation template |
| `tests/` | Encryption tests and their throwaway keys |
| `.wordpress-org/` | Banners, icon and screenshots for the plugin directory |

The plugin follows the WordPress Plugin Boilerplate structure: hooks are
registered through `Secure_Encrypted_Form_Loader` rather than being scattered
across the classes.

## Commands

```bash
composer lint           # check the code against the WordPress coding standards
composer lint:fix       # fix what can be fixed automatically
composer test:crypto    # run the encryption tests
composer test:turnstile # run the Turnstile verification tests
composer test           # lint and test together
npm run keys:generate # regenerate the throwaway test keys
./build.sh            # produce release/secure-encrypted-form.zip
```

Run `composer lint` and `composer test` before tagging a release. Nothing runs
automatically on every commit.

## How the encryption works

`public/js/secure-encrypted-form-public.js` reads the armored public key from
the plugin settings, encrypts the contents of the message field with
OpenPGP.js, and posts the result to `admin-ajax.php`. The PHP side never sees
the plaintext: it writes the encrypted block to a temporary `.txt.gpg` file,
attaches it to the email and deletes it.

This is the property worth protecting from regressions, and `tests/js/` asserts
it directly: neither the ciphertext nor the request payload may contain the
plaintext.

**The plugin only works in a browser secure context**, because WebCrypto is only
exposed over HTTPS (and on `localhost`). On a plain HTTP site OpenPGP.js refuses
to start with "The WebCrypto API is not available" and nothing can be sent. Both
forms check `window.isSecureContext` before encrypting and say so plainly, and
the admin shows a notice, because the symptom otherwise looks exactly like a
corrupt key and sends people off re-exporting a perfectly good one.

Note this when testing locally: a LocalWP style `.local` domain over HTTP is not
a secure context, only literal `localhost` is.

## Tests

The encryption tests run on Node's built in test runner:

```bash
composer test:crypto
```

They load `lib/js/openpgp.min.js` itself rather than a copy from npm, so they
exercise the exact file that is served to visitors. That file is a browser
bundle, so it is evaluated with `node:vm` in the current realm; a separate VM
context would give it its own `Uint8Array`, which the library refuses to mix
with typed arrays created by the host.

`tests/fixtures/` holds an OpenPGP key pair and an expired public key, all
committed on purpose. **They are throwaway keys used only by the tests and
protect nothing.** Never reuse them. Regenerate them with `npm run keys:generate`.

## Spam protection

`Secure_Encrypted_Form_Turnstile` adds an optional Cloudflare Turnstile check,
off by default. It only switches on when the `turnstile_enabled` setting is set
and both keys are present; a half configured widget would block every
submission, so it counts as disabled.

Three decisions are worth knowing before changing this code:

- **Verification happens on the server**, in the AJAX handler, before anything
  is done with the submission. Checking in the browser would be pointless: a bot
  posting straight to `admin-ajax.php` would skip it.
- **It fails open.** If Cloudflare is unreachable, answers a non 200 status,
  returns something unreadable, or rejects the secret key, the message is sent
  and an error is logged. For a form whose purpose is receiving sensitive
  messages, losing a real one is worse than letting spam through. A *missing*
  token is the exception and is always rejected, otherwise any bot could skip
  the check by not sending the field.
- **The visitor IP is never sent to Cloudflare**, even though `siteverify`
  accepts it.

Turnstile tokens are single use and expire after five minutes, and this form
never reloads the page, so the widget is reset after every attempt. Forgetting
that is what breaks a second submission.

`tests/php/turnstile-test.php` covers these branches with stubs, including the
outage paths that are impractical to reproduce by hand.

### Testing it by hand

Cloudflare publishes dummy keys that work on any hostname, a `.local`
development domain included, so there is no need to register a real site:

| Behaviour | Site key | Secret key |
| --- | --- | --- |
| Always passes | `1x00000000000000000000AA` | `1x0000000000000000000000000000000AA` |
| Always blocks | `2x00000000000000000000AB` | `2x0000000000000000000000000000000AA` |
| Forces an interactive challenge | `3x00000000000000000000FF` | `1x0000000000000000000000000000000AA` |

The browser still downloads the widget from Cloudflare and the server still
calls `siteverify`, so both need internet access. The full list is in
[Cloudflare's testing documentation](https://developers.cloudflare.com/turnstile/troubleshooting/testing/).

Worth checking by hand: send two messages in a row without reloading the page.
That is what catches a missing widget reset, and no unit test will see it.

## Logging

The plugin writes a diagnostic log through `Secure_Encrypted_Form_Logger`. Three
things about it are deliberate:

- The log directory lives inside the uploads folder but its name carries a
  random suffix stored in the `secure_encrypted_form_log_dirname` option, and it
  ships `index.php`, `.htaccess` and `web.config` files that block direct web
  access. Uploads are normally web reachable, so a predictable path would make
  the logs downloadable by anyone who guessed the URL.
- The log records no personal data: not the sender, the destination or the
  subject, and never the message.
- Logging is controlled by the `logging` key of the settings option: `off`,
  `errors` (the default) or `debug`. The directory is only created when there is
  something to write.

Installations created before 1.2.0 kept their logs in a predictable directory.
They are moved to the protected location once, on `admin_init`, guarded by the
`secure_encrypted_form_logs_migrated` option.

## Releasing

Versions follow [Semantic Versioning](https://semver.org/). The version number
appears in three places that must stay in sync:

- the `Version:` header in `secure-encrypted-form.php`
- the `SECURE_ENCRYPTED_FORM_VERSION` constant in the same file
- `Stable tag:` in `readme.txt`

The release steps:

1. Update the three version numbers and set `Tested up to:` to the WordPress
   version you tested against.
2. Add a `== Changelog ==` section to `readme.txt` describing what changed for
   users, plus an `== Upgrade Notice ==` entry.
3. Run `composer lint` and `composer test`.
4. Check the plugin with [Plugin Check](https://wordpress.org/plugins/plugin-check/).
5. Commit, then push a tag named after the version.

Pushing a tag triggers `.github/workflows/deploy.yml`, which builds the plugin
and publishes it to the WordPress.org SVN repository. Pushing to the `trunk`
branch updates the readme and the directory assets without publishing a
release.

Before tagging, check by hand what the automated tests cannot: submit the form
on the front end and decrypt the attachment you receive, and send a test email
from the settings page.

## License

GPL-2.0-or-later. See `LICENSE.txt`.
